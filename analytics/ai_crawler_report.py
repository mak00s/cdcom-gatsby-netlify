#!/usr/bin/env python3
"""Validate AI/search crawler candidates against vendors' current IP ranges."""

from __future__ import annotations

import argparse
import ipaddress
import json
import re
import sys
from collections import defaultdict
from datetime import datetime, timedelta
from pathlib import Path
from urllib.request import Request, urlopen


RANGE_SOURCES = {
    "OAI-SearchBot": "https://openai.com/searchbot.json",
    "ChatGPT-User": "https://openai.com/chatgpt-user.json",
    "GPTBot": "https://openai.com/gptbot.json",
    "PerplexityBot": "https://www.perplexity.com/perplexitybot.json",
    "Perplexity-User": "https://www.perplexity.com/perplexity-user.json",
    "Googlebot": "https://developers.google.com/crawling/ipranges/common-crawlers.json",
}
UA_TOKENS = tuple(RANGE_SOURCES) + ("ClaudeBot", "Claude-User")
LOG_PATTERN = re.compile(
    r'^(?P<host>\S+) (?P<client>\S+) \S+ \S+ \[(?P<date>[^]]+)\] '
    r'"(?P<method>\S+) (?P<path>\S+) [^"]+" (?P<status>\d{3}) \S+ '
    r'"[^"]*" "(?P<ua>[^"]*)"$'
)


def fetch_networks(url: str) -> list[ipaddress._BaseNetwork]:
    request = Request(url, headers={"User-Agent": "concept-diagram-crawler-audit/1.0"})
    with urlopen(request, timeout=30) as response:
        data = json.load(response)
    networks = []
    for prefix in data.get("prefixes", []):
        value = prefix.get("ipv4Prefix") or prefix.get("ipv6Prefix")
        if value:
            networks.append(ipaddress.ip_network(value))
    return networks


def client_ip(value: str) -> ipaddress._BaseAddress | None:
    try:
        return ipaddress.ip_address(value)
    except ValueError:
        match = re.fullmatch(r"(?:geo-)?crawl-(\d+)-(\d+)-(\d+)-(\d+)(?:\.geo)?\.googlebot\.com", value)
        return ipaddress.ip_address(".".join(match.groups())) if match else None


def crawler_from_ua(ua: str) -> str | None:
    for token in UA_TOKENS:
        if token.lower() in ua.lower():
            return token
    return None


def week_bounds(value: datetime) -> tuple[str, str]:
    start = value.date() - timedelta(days=value.weekday())
    return start.isoformat(), (start + timedelta(days=6)).isoformat()


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--output", type=Path, required=True)
    args = parser.parse_args()
    networks = {name: fetch_networks(url) for name, url in RANGE_SOURCES.items()}
    stats: dict[tuple[str, str, str], dict[str, object]] = defaultdict(
        lambda: {
            "candidate_requests": 0,
            "verified_requests": 0,
            "content_requests": 0,
            "robots_requests": 0,
            "http_2xx": 0,
            "http_4xx": 0,
            "http_5xx": 0,
            "last_seen": "",
        }
    )
    for raw in sys.stdin:
        match = LOG_PATTERN.match(raw.rstrip("\n"))
        if not match or match.group("host") not in {"concept-diagram.com", "www.concept-diagram.com"}:
            continue
        crawler = crawler_from_ua(match.group("ua"))
        if not crawler:
            continue
        timestamp = datetime.strptime(match.group("date"), "%d/%b/%Y:%H:%M:%S %z")
        week_start, week_end = week_bounds(timestamp)
        item = stats[(week_start, week_end, crawler)]
        item["candidate_requests"] += 1
        item["last_seen"] = max(str(item["last_seen"]), timestamp.isoformat())
        address = client_ip(match.group("client"))
        verified = bool(address and crawler in networks and any(address in network for network in networks[crawler]))
        if not verified:
            continue
        item["verified_requests"] += 1
        path = match.group("path").split("?", 1)[0]
        if path == "/robots.txt":
            item["robots_requests"] += 1
        elif not re.search(r"\.(?:css|js|png|jpe?g|gif|svg|webp|ico|woff2?|map)$", path, re.I):
            item["content_requests"] += 1
        status = int(match.group("status"))
        if 200 <= status < 300:
            item["http_2xx"] += 1
        elif 400 <= status < 500:
            item["http_4xx"] += 1
        elif status >= 500:
            item["http_5xx"] += 1

    rows = []
    for (week_start, week_end, crawler), item in sorted(stats.items()):
        note = "実行時点の公式公開IP範囲と照合。過去の範囲変更分は過少計上の可能性あり"
        if crawler.startswith("Claude"):
            note = "AnthropicはIP範囲を公開していないためUA候補のみ。検証済み件数には含めない"
        rows.append({"week_start": week_start, "week_end": week_end, "crawler": crawler, **item, "notes": note})
    args.output.write_text(json.dumps(rows, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"wrote {len(rows)} crawler rows to {args.output}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
