#!/usr/bin/env python3
"""Refresh the Concept Diagram weekly Google Sheets analytics report."""

from __future__ import annotations

import argparse
import json
import os
import sys
from dataclasses import dataclass
from datetime import date, datetime, timedelta, timezone
from pathlib import Path
from typing import Any, Iterable
from urllib.parse import urlparse

from google.analytics.data_v1beta import BetaAnalyticsDataClient
from google.analytics.data_v1beta.types import (
    DateRange,
    Dimension,
    Filter,
    FilterExpression,
    Metric,
    OrderBy,
    RunReportRequest,
)
from google.oauth2 import service_account
from googleapiclient.discovery import build


SCOPES = (
    "https://www.googleapis.com/auth/analytics.readonly",
    "https://www.googleapis.com/auth/webmasters.readonly",
    "https://www.googleapis.com/auth/spreadsheets",
)
REPORT_TZ = timezone(timedelta(hours=9))
DEFAULT_BACKFILL_WEEKS = 52
AI_SOURCE_PATTERNS = {
    "ChatGPT": ("chatgpt.com", "chat.openai.com"),
    "Claude": ("claude.ai",),
    "Gemini": ("gemini.google.com", "bard.google.com"),
    "Perplexity": ("perplexity.ai",),
    "Microsoft Copilot": ("copilot.microsoft.com", "bing.com/chat"),
}
AUDIT_DATA_PATH = Path(__file__).with_name("ai_answer_benchmark.json")


@dataclass(frozen=True)
class Config:
    credentials: service_account.Credentials
    spreadsheet_id: str
    ga4_property_id: str
    gsc_site_url: str
    site_host: str
    backfill_weeks: int


def credentials_from_environment() -> service_account.Credentials:
    raw = os.environ.get("GOOGLE_SERVICE_ACCOUNT_JSON", "").strip()
    path = os.environ.get("GOOGLE_APPLICATION_CREDENTIALS", "").strip()
    if raw:
        try:
            info = json.loads(raw)
        except json.JSONDecodeError as exc:
            raise RuntimeError("GOOGLE_SERVICE_ACCOUNT_JSON is not valid JSON") from exc
        return service_account.Credentials.from_service_account_info(info, scopes=SCOPES)
    if path:
        return service_account.Credentials.from_service_account_file(path, scopes=SCOPES)
    raise RuntimeError(
        "Set GOOGLE_SERVICE_ACCOUNT_JSON or GOOGLE_APPLICATION_CREDENTIALS"
    )


def load_config() -> Config:
    return Config(
        credentials=credentials_from_environment(),
        spreadsheet_id=required_env("GOOGLE_SHEET_ID"),
        ga4_property_id=os.environ.get("GA4_PROPERTY_ID", "386697973"),
        gsc_site_url=os.environ.get(
            "GSC_SITE_URL", "sc-domain:concept-diagram.com"
        ),
        site_host=os.environ.get("SITE_HOST", "concept-diagram.com"),
        backfill_weeks=int(os.environ.get("BACKFILL_WEEKS", DEFAULT_BACKFILL_WEEKS)),
    )


def required_env(name: str) -> str:
    value = os.environ.get(name, "").strip()
    if not value:
        raise RuntimeError(f"Missing required environment variable: {name}")
    return value


def completed_week_bounds(today: date | None = None) -> tuple[date, date]:
    today = today or datetime.now(REPORT_TZ).date()
    this_monday = today - timedelta(days=today.weekday())
    return this_monday - timedelta(days=7), this_monday - timedelta(days=1)


def ga_report(
    client: BetaAnalyticsDataClient,
    property_id: str,
    start: date,
    end: date,
    dimensions: Iterable[str],
    metrics: Iterable[str],
    *,
    limit: int = 10000,
    dimension_filter: FilterExpression | None = None,
    order_metric: str | None = None,
) -> list[dict[str, Any]]:
    request = RunReportRequest(
        property=f"properties/{property_id}",
        date_ranges=[DateRange(start_date=start.isoformat(), end_date=end.isoformat())],
        dimensions=[Dimension(name=name) for name in dimensions],
        metrics=[Metric(name=name) for name in metrics],
        dimension_filter=dimension_filter,
        limit=limit,
        order_bys=(
            [OrderBy(metric=OrderBy.MetricOrderBy(metric_name=order_metric), desc=True)]
            if order_metric
            else []
        ),
    )
    response = client.run_report(request)
    dimension_names = [header.name for header in response.dimension_headers]
    metric_names = [header.name for header in response.metric_headers]
    rows: list[dict[str, Any]] = []
    for row in response.rows:
        item: dict[str, Any] = {
            name: value.value
            for name, value in zip(dimension_names, row.dimension_values)
        }
        for name, value in zip(metric_names, row.metric_values):
            item[name] = float(value.value or 0)
        rows.append(item)
    return rows


def gsc_query(
    service: Any,
    site_url: str,
    start: date,
    end: date,
    dimensions: list[str],
    *,
    row_limit: int = 25000,
) -> list[dict[str, Any]]:
    body = {
        "startDate": start.isoformat(),
        "endDate": end.isoformat(),
        "dimensions": dimensions,
        "rowLimit": row_limit,
        "dataState": "final",
    }
    response = service.searchanalytics().query(siteUrl=site_url, body=body).execute()
    return response.get("rows", [])


def gsc_totals(service: Any, site_url: str, start: date, end: date) -> dict[str, float]:
    rows = gsc_query(service, site_url, start, end, [], row_limit=1)
    if not rows:
        return {"clicks": 0, "impressions": 0, "ctr": 0, "position": 0}
    return rows[0]


def first(rows: list[dict[str, Any]]) -> dict[str, Any]:
    return rows[0] if rows else {}


def n(value: Any) -> int | float:
    number = float(value or 0)
    return int(number) if number.is_integer() else number


def is_external_referrer(referrer: str, site_host: str) -> bool:
    if not referrer or referrer in {"(not set)", "(direct)"}:
        return False
    host = (urlparse(referrer).hostname or "").lower()
    return bool(host and host != site_host and not host.endswith(f".{site_host}"))


def ai_service(source: str) -> str | None:
    value = source.lower().strip()
    for service, patterns in AI_SOURCE_PATTERNS.items():
        if any(pattern in value for pattern in patterns):
            return service
    return None


def load_json_rows(path: Path, columns: list[str]) -> list[list[Any]]:
    if not path.exists():
        return []
    data = json.loads(path.read_text(encoding="utf-8"))
    return [[item.get(column, "") for column in columns] for item in data]


def collect_report(config: Config) -> dict[str, list[list[Any]]]:
    ga = BetaAnalyticsDataClient(credentials=config.credentials)
    gsc = build("searchconsole", "v1", credentials=config.credentials, cache_discovery=False)
    last_week_start, last_week_end = completed_week_bounds()
    first_week_start = last_week_start - timedelta(weeks=config.backfill_weeks - 1)

    ga_weekly = ga_report(
        ga,
        config.ga4_property_id,
        first_week_start,
        last_week_end,
        ["isoYearIsoWeek"],
        ["sessions", "totalUsers", "screenPageViews", "engagedSessions"],
        limit=5000,
    )
    organic_weekly = ga_report(
        ga,
        config.ga4_property_id,
        first_week_start,
        last_week_end,
        ["isoYearIsoWeek"],
        ["sessions"],
        limit=5000,
        dimension_filter=FilterExpression(
            filter=Filter(
                field_name="sessionDefaultChannelGroup",
                string_filter=Filter.StringFilter(value="Organic Search"),
            )
        ),
    )
    ai_sources = ga_report(
        ga,
        config.ga4_property_id,
        first_week_start,
        last_week_end,
        ["isoYearIsoWeek", "sessionSource", "sessionMedium"],
        ["sessions", "totalUsers", "engagedSessions"],
        limit=10000,
    )
    gsc_daily = gsc_query(
        gsc,
        config.gsc_site_url,
        first_week_start,
        last_week_end,
        ["date"],
        row_limit=5000,
    )
    ga_by_week = {row["isoYearIsoWeek"]: row for row in ga_weekly}
    organic_by_week = {
        row["isoYearIsoWeek"]: row.get("sessions", 0) for row in organic_weekly
    }
    gsc_by_date = {
        date.fromisoformat(row["keys"][0]): row for row in gsc_daily
    }

    weekly: list[list[Any]] = []
    for offset in range(config.backfill_weeks):
        week_start = first_week_start + timedelta(weeks=offset)
        week_end = week_start + timedelta(days=6)
        iso_week = week_start.strftime("%G%V")
        week_dates = [week_start + timedelta(days=day) for day in range(7)]
        ga_total = ga_by_week.get(iso_week, {})
        sessions = ga_total.get("sessions", 0)
        users = ga_total.get("totalUsers", 0)
        views = ga_total.get("screenPageViews", 0)
        engaged = ga_total.get("engagedSessions", 0)
        organic = organic_by_week.get(iso_week, 0)
        clicks = sum(gsc_by_date.get(day, {}).get("clicks", 0) for day in week_dates)
        impressions = sum(gsc_by_date.get(day, {}).get("impressions", 0) for day in week_dates)
        weighted_position = sum(
            gsc_by_date.get(day, {}).get("position", 0)
            * gsc_by_date.get(day, {}).get("impressions", 0)
            for day in week_dates
        )
        weekly.append(
            [
                week_start.isoformat(),
                week_end.isoformat(),
                n(sessions),
                n(users),
                n(views),
                n(organic),
                engaged / sessions if sessions else 0,
                n(clicks),
                n(impressions),
                clicks / impressions if impressions else 0,
                weighted_position / impressions if impressions else 0,
            ]
        )

    detail_start = last_week_start - timedelta(weeks=3)
    detail_end = last_week_end
    pages = ga_report(
        ga,
        config.ga4_property_id,
        detail_start,
        detail_end,
        ["pagePath", "pageTitle"],
        ["screenPageViews", "sessions", "totalUsers", "averageSessionDuration"],
        limit=5000,
        order_metric="screenPageViews",
    )
    sources = ga_report(
        ga,
        config.ga4_property_id,
        detail_start,
        detail_end,
        ["sessionSource", "sessionMedium", "sessionDefaultChannelGroup"],
        ["sessions", "totalUsers", "engagedSessions"],
        limit=5000,
        order_metric="sessions",
    )
    referrers = ga_report(
        ga,
        config.ga4_property_id,
        detail_start,
        detail_end,
        ["pageReferrer", "landingPagePlusQueryString"],
        ["sessions", "totalUsers"],
        limit=10000,
        order_metric="sessions",
    )
    query_rows = gsc_query(
        gsc, config.gsc_site_url, detail_start, detail_end, ["query"], row_limit=25000
    )
    landing_rows = gsc_query(
        gsc, config.gsc_site_url, detail_start, detail_end, ["page"], row_limit=25000
    )
    query_landing_rows = gsc_query(
        gsc,
        config.gsc_site_url,
        detail_start,
        detail_end,
        ["query", "page"],
        row_limit=25000,
    )
    ai_referral_rows = sorted(
        [
            [
                datetime.strptime(row["isoYearIsoWeek"] + "-1", "%G%V-%u").date().isoformat(),
                (datetime.strptime(row["isoYearIsoWeek"] + "-1", "%G%V-%u").date() + timedelta(days=6)).isoformat(),
                ai_service(row.get("sessionSource", "")),
                row.get("sessionSource", ""),
                row.get("sessionMedium", ""),
                n(row.get("sessions", 0)),
                n(row.get("totalUsers", 0)),
                n(row.get("engagedSessions", 0)),
            ]
            for row in ai_sources
            if ai_service(row.get("sessionSource", ""))
        ],
        key=lambda row: (row[0], row[2], row[3]),
    )

    return {
        "Weekly": weekly,
        "Pages": [
            [
                detail_start.isoformat(),
                detail_end.isoformat(),
                row.get("pagePath", ""),
                row.get("pageTitle", ""),
                n(row.get("screenPageViews", 0)),
                n(row.get("sessions", 0)),
                n(row.get("totalUsers", 0)),
                row.get("averageSessionDuration", 0),
            ]
            for row in pages
        ],
        "Search Queries": [
            [detail_start.isoformat(), detail_end.isoformat(), *row.get("keys", [""]), n(row.get("clicks", 0)), n(row.get("impressions", 0)), row.get("ctr", 0), row.get("position", 0)]
            for row in query_rows
        ],
        "Search Landing Pages": [
            [detail_start.isoformat(), detail_end.isoformat(), *row.get("keys", [""]), n(row.get("clicks", 0)), n(row.get("impressions", 0)), row.get("ctr", 0), row.get("position", 0)]
            for row in landing_rows
        ],
        "Query × LP": [
            [detail_start.isoformat(), detail_end.isoformat(), *(row.get("keys", ["", ""]) + ["", ""])[:2], n(row.get("clicks", 0)), n(row.get("impressions", 0)), row.get("ctr", 0), row.get("position", 0)]
            for row in query_landing_rows
        ],
        "Traffic Sources": [
            [
                detail_start.isoformat(),
                detail_end.isoformat(),
                row.get("sessionSource", ""),
                row.get("sessionMedium", ""),
                row.get("sessionDefaultChannelGroup", ""),
                n(row.get("sessions", 0)),
                n(row.get("totalUsers", 0)),
                n(row.get("engagedSessions", 0)),
            ]
            for row in sources
        ],
        "Observed Backlinks": [
            [
                detail_start.isoformat(),
                detail_end.isoformat(),
                row.get("pageReferrer", ""),
                row.get("landingPagePlusQueryString", ""),
                n(row.get("sessions", 0)),
                n(row.get("totalUsers", 0)),
            ]
            for row in referrers
            if is_external_referrer(row.get("pageReferrer", ""), config.site_host)
        ],
        "AI Referrals": ai_referral_rows,
        "AI Answer Audit": load_json_rows(
            AUDIT_DATA_PATH,
            ["date", "phase", "service", "mode", "proposer", "definition", "official_url", "official_primary", "step_count", "old_url_avoided", "current_scope", "score", "notes"],
        ),
        "AI Crawlers": load_json_rows(
            Path(os.environ.get("AI_CRAWLER_JSON", "analytics/ai_crawler_snapshot.json")),
            ["week_start", "week_end", "crawler", "candidate_requests", "verified_requests", "content_requests", "robots_requests", "http_2xx", "http_4xx", "http_5xx", "last_seen", "notes"],
        ),
    }


HEADERS: dict[str, list[str]] = {
    "Weekly": ["週開始", "週終了", "セッション", "ユーザー", "表示回数", "自然検索セッション", "エンゲージメント率", "GSCクリック", "GSC表示回数", "GSC CTR", "平均掲載順位"],
    "Pages": ["期間開始", "期間終了", "ページパス", "ページタイトル", "表示回数", "セッション", "ユーザー", "平均セッション秒"],
    "Search Queries": ["期間開始", "期間終了", "検索クエリ", "クリック", "表示回数", "CTR", "平均掲載順位"],
    "Search Landing Pages": ["期間開始", "期間終了", "ランディングページ", "クリック", "表示回数", "CTR", "平均掲載順位"],
    "Query × LP": ["期間開始", "期間終了", "検索クエリ", "ランディングページ", "クリック", "表示回数", "CTR", "平均掲載順位"],
    "Traffic Sources": ["期間開始", "期間終了", "参照元", "メディア", "チャネル", "セッション", "ユーザー", "エンゲージドセッション"],
    "Observed Backlinks": ["期間開始", "期間終了", "参照元URL", "到達LP", "セッション", "ユーザー"],
    "AI Referrals": ["週開始", "週終了", "AIサービス", "参照元", "メディア", "セッション", "ユーザー", "エンゲージドセッション"],
    "AI Answer Audit": ["監査日", "段階", "AIサービス", "モード", "提唱者", "定義", "公式URL引用", "公式優先", "ステップ数", "旧URL回避", "現サービス", "合計", "所見"],
    "AI Crawlers": ["週開始", "週終了", "クローラー", "UA候補", "検証済みリクエスト", "コンテンツ取得", "robots.txt", "2xx", "4xx", "5xx", "最終確認", "所見"],
}


def ensure_report_tabs(sheets: Any, spreadsheet_id: str) -> None:
    spreadsheet = sheets.spreadsheets().get(
        spreadsheetId=spreadsheet_id, fields="sheets.properties"
    ).execute()
    existing = {sheet["properties"]["title"] for sheet in spreadsheet.get("sheets", [])}
    missing = [tab for tab in HEADERS if tab not in existing]
    if missing:
        sheets.spreadsheets().batchUpdate(
            spreadsheetId=spreadsheet_id,
            body={"requests": [{"addSheet": {"properties": {"title": tab}}} for tab in missing]},
        ).execute()
    for tab, headers in HEADERS.items():
        sheets.spreadsheets().values().update(
            spreadsheetId=spreadsheet_id,
            range=f"'{tab}'!A1",
            valueInputOption="RAW",
            body={"values": [headers]},
        ).execute()


def replace_tab_values(sheets: Any, spreadsheet_id: str, tab: str, rows: list[list[Any]]) -> None:
    sheets.spreadsheets().values().clear(
        spreadsheetId=spreadsheet_id, range=f"'{tab}'!A2:Z", body={}
    ).execute()
    if rows:
        sheets.spreadsheets().values().update(
            spreadsheetId=spreadsheet_id,
            range=f"'{tab}'!A2",
            valueInputOption="RAW",
            body={"values": rows},
        ).execute()


def update_dashboard(sheets: Any, spreadsheet_id: str, report: dict[str, list[list[Any]]]) -> None:
    latest = report["Weekly"][-1] if report["Weekly"] else [""] * 11
    previous = report["Weekly"][-2] if len(report["Weekly"]) > 1 else [""] * 11
    def delta(index: int) -> float:
        old = float(previous[index] or 0)
        return (float(latest[index] or 0) / old - 1) if old else 0

    primary = [[latest[2], delta(2), latest[3], delta(3), latest[5], delta(5)]]
    search = [[latest[7], delta(7), latest[8], delta(8), latest[10], ""]]
    period = [["対象週", latest[0], latest[1], f"更新: {datetime.now(REPORT_TZ).isoformat(timespec='seconds')}"]]
    ai_rows = report.get("AI Referrals", [])
    latest_report_week = date.fromisoformat(latest[0]) if latest[0] else None
    ai_cutoff = latest_report_week - timedelta(weeks=3) if latest_report_week else None
    ai_last_four_weeks = sum(
        float(row[5] or 0)
        for row in ai_rows
        if ai_cutoff and ai_cutoff <= date.fromisoformat(row[0]) <= latest_report_week
    )
    crawler_requests = sum(float(row[4] or 0) for row in report.get("AI Crawlers", []))
    completed_audits = [row for row in report.get("AI Answer Audit", []) if row[1] == "after" and isinstance(row[11], (int, float))]
    audit_average = sum(float(row[11]) for row in completed_audits) / len(completed_audits) if completed_audits else "未測定"
    audit_label = f"{audit_average:.1f}/7" if isinstance(audit_average, float) else audit_average
    ai_summary = (
        "復旧後はセッション・自然検索・GSCクリック／表示・平均順位を週次比較します。\n"
        f"AI可視性: 直近4週AI流入 {n(ai_last_four_weeks)}件 / "
        f"保存ログ内の公式IP検証済みAI・検索クロール {n(crawler_requests)}件 / "
        f"回答監査平均 {audit_label}\n"
        "AI流入は参照元、クロールは公式IP範囲照合、回答監査は同一プロンプトで測定。"
    )
    sheets.spreadsheets().values().update(
        spreadsheetId=spreadsheet_id,
        range="'Dashboard'!B4:G4",
        valueInputOption="RAW",
        body={"values": primary},
    ).execute()
    sheets.spreadsheets().values().update(
        spreadsheetId=spreadsheet_id,
        range="'Dashboard'!B6:G6",
        valueInputOption="RAW",
        body={"values": search},
    ).execute()
    sheets.spreadsheets().values().update(
        spreadsheetId=spreadsheet_id,
        range="'Dashboard'!A7:D7",
        valueInputOption="RAW",
        body={"values": period},
    ).execute()
    sheets.spreadsheets().values().update(
        spreadsheetId=spreadsheet_id,
        range="'Dashboard'!A9",
        valueInputOption="RAW",
        body={"values": [[ai_summary]]},
    ).execute()


def write_report(config: Config, report: dict[str, list[list[Any]]]) -> None:
    sheets = build("sheets", "v4", credentials=config.credentials, cache_discovery=False)
    ensure_report_tabs(sheets, config.spreadsheet_id)
    for tab, rows in report.items():
        replace_tab_values(sheets, config.spreadsheet_id, tab, rows)
    update_dashboard(sheets, config.spreadsheet_id, report)
    sheets.spreadsheets().values().append(
        spreadsheetId=config.spreadsheet_id,
        range="'Run Log'!A:D",
        valueInputOption="RAW",
        insertDataOption="INSERT_ROWS",
        body={
            "values": [[datetime.now(REPORT_TZ).isoformat(timespec="seconds"), "success", len(report["Weekly"]), "GA4 + GSC refresh completed"]]
        },
    ).execute()


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--dry-run", action="store_true", help="Fetch data without writing Sheets")
    args = parser.parse_args()
    try:
        config = load_config()
        report = collect_report(config)
        if args.dry_run:
            print(json.dumps({tab: len(rows) for tab, rows in report.items()}, ensure_ascii=False))
        else:
            write_report(config, report)
            print(f"Updated spreadsheet {config.spreadsheet_id}")
        return 0
    except Exception as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
