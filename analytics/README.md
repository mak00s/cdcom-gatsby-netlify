# Concept Diagram weekly analytics report

Every Monday at 09:15 JST, GitHub Actions refreshes a Google Sheets report from
GA4 and Google Search Console. It rebuilds the latest 52 completed weeks and the
latest four-week detail tables, so delayed Search Console data is corrected on
the next run.

## Required GitHub Actions secrets

- `GOOGLE_SERVICE_ACCOUNT_JSON`: complete service-account JSON
- `GOOGLE_SHEET_ID`: destination spreadsheet ID

The spreadsheet must be shared as editor with
`gsheet@python-selenium-280217.iam.gserviceaccount.com`.

## Data notes

- `Observed Backlinks` is based on GA4 `pageReferrer`: external URLs that sent
  measured visits during the report period. It is not a complete backlink
  index.
- The Search Console Links report is not available through the official Search
  Console API. A full links export, when needed, must be downloaded manually
  from Search Console and kept separately from the automated observation.
- Search Console normally lags by several days. The workflow uses completed
  weeks and rebuilds history to absorb late-arriving data.

## Local verification

```bash
export GOOGLE_APPLICATION_CREDENTIALS=/absolute/path/to/service_account.json
export GOOGLE_SHEET_ID=your_spreadsheet_id
python analytics/weekly_report.py --dry-run
```
