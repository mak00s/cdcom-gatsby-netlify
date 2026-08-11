# Concept Diagram weekly analytics report

Every Monday at 09:15 JST, GitHub Actions refreshes a Google Sheets report from
GA4, Google Search Console, and the Sakura access logs. It rebuilds the latest
52 completed weeks and the latest four-week detail tables, so delayed Search
Console data is corrected on the next run.

## Required GitHub Actions secrets

- `GOOGLE_SERVICE_ACCOUNT_JSON`: complete service-account JSON
- `GOOGLE_SHEET_ID`: destination spreadsheet ID
- `SAKURA_CRAWLER_SSH_PRIVATE_KEY`: dedicated SSH private key whose public key
  is restricted to the fixed log-export command on the Sakura server
- `SAKURA_CRAWLER_SSH_KNOWN_HOSTS`: pinned Sakura ED25519 host key

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
- `AI Referrals` recognizes measured GA4 referral sources such as ChatGPT,
  Claude, Gemini, and Perplexity. Referrer suppression means this is a lower
  bound, not a complete count.
- `AI Crawlers` first finds matching User-Agent candidates, then counts a
  request as verified only when its IP is inside the current vendor-published
  range. Historical requests can be undercounted when a vendor changes its
  published ranges. Anthropic does not publish persistent IP ranges, so Claude
  rows remain User-Agent candidates and are not included in verified totals.
- `AI Answer Audit` is an evaluation log for the same cross-service prompt.
  Update `ai_answer_benchmark.json` when a new before/after observation is made.
- The Sakura key can only run `/home/mak-s/bin/export_ai_log_candidates.sh` and
  cannot open an interactive shell, allocate a PTY, or forward ports/agents.

## Local verification

```bash
export GOOGLE_APPLICATION_CREDENTIALS=/absolute/path/to/service_account.json
export GOOGLE_SHEET_ID=your_spreadsheet_id
python analytics/weekly_report.py --dry-run
```
