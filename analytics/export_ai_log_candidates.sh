#!/bin/sh
set -eu

for file in /home/mak-s/log/access_log_*.gz; do
  gzip -dc "$file"
done \
  | awk '$1 == "concept-diagram.com" || $1 == "www.concept-diagram.com"' \
  | grep -Ei 'OAI-SearchBot|ChatGPT-User|GPTBot|PerplexityBot|Perplexity-User|ClaudeBot|Claude-User|Googlebot' \
  || true
