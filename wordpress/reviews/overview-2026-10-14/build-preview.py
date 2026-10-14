"""Generate the overview review artifact. No WordPress or Git writes."""
from pathlib import Path
import ast
import html
import json

ROOT = Path(__file__).resolve().parent
PREVIOUS = ROOT.parent / "drawing-guide-2026-10-14"
ASSETS = ROOT / "assets"
ASSETS.mkdir(exist_ok=True)

# Keep the same reviewed geometry and branch as the published drawing guide.
svg = (PREVIOUS / "assets/03-support.svg").read_text(encoding="utf-8")
svg = svg.replace('height="650" viewBox="0 0 800 650"', 'height="850" viewBox="0 0 800 850"', 1)
svg = svg.replace('<rect width="800" height="650"', '<rect width="800" height="850"', 1)
svg = svg.replace('ヘアケア事業の途中図3', 'コンセプトダイアグラムの読み方：ヘアケア事業')
legend = '''
<text x="400" y="682" text-anchor="middle" font-size="21" fill="#183c42">A：セルフチェック　B：サンプル・使い方ガイド</text>
<text x="400" y="718" text-anchor="middle" font-size="21" fill="#183c42">C：FAQ・知識コンテンツ　D：関連するケア情報</text>
<text x="400" y="754" text-anchor="middle" font-size="21" fill="#183c42">E・F・G：続けやすい体験と、困ったときの支援</text>
<rect x="65" y="780" width="675" height="48" rx="10" fill="#fff0c4"/>
<text x="402" y="810" text-anchor="middle" font-size="21" fill="#183c42">評価指標：本人の声 ＋ 試用後の実感・理解・継続状況</text>
'''
svg = svg.replace('</g></svg>', legend + '</g></svg>')
(ASSETS / 'overview.svg').write_text(svg, encoding='utf-8')

# Reuse only the preview CSS, without executing the earlier build script.
tree = ast.parse((PREVIOUS / 'build-preview.py').read_text(encoding='utf-8'))
css = next(ast.literal_eval(node.value) for node in tree.body if isinstance(node, ast.Assign) and any(isinstance(t, ast.Name) and t.id == 'css' for t in node.targets))
css += '\n.primary-guide{background:#f2f8f7;border-left:4px solid #38999a;padding:18px 24px;margin:24px 0}.primary-guide p{margin:0}\n'

data = json.loads((ROOT / 'review.json').read_text(encoding='utf-8'))
body = (ROOT / 'article.html').read_text(encoding='utf-8')
body = body.replace('<figure>', '<figure><p class="scroll-hint">スマートフォンでは図を横にスクロールして読めます。</p>')
related = ''.join(f'<section class="related-card"><h3><a href="{html.escape(item["url"])}">{html.escape(item["url"])}</a></h3><p class="placement">{html.escape(item["placement"])}</p>{item["html"]}</section>' for item in data['related_changes'])
review_points = ''.join('<li>' + html.escape(x) + '</li>' for x in data['review_points'])
expected = ''.join('<li>' + html.escape(x['expected']) + '</li>' for x in data['hypotheses'])
page = f'''<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>レビュー：{html.escape(data['title'])}</title><style>{css}</style></head><body>
<header><p>CONCEPT DIAGRAM · LOCAL REVIEW</p></header>
<div class="review-bar">未公開原稿 · タイトル維持 · <a href="#review-notes">関連変更とレビュー項目</a></div>
<main><p class="label">公式の基準ページ / 清水 誠</p><h1>{html.escape(data['title'])}</h1>{body}
<section id="review-notes"><h2>公開前のレビュー項目</h2><ul>{review_points}</ul>
<h3>個人サイトからの案内文</h3>{related}
<div class="meta"><h3>descriptionの変更案</h3><p>{html.escape(data['description'])}</p><h3>公開後の検証案</h3><ul>{expected}</ul><p>実際の公開日に基準期間と評価日を確定します。</p></div></section>
</main><footer>ローカルレビュー用。本番ページ・個人サイト・検索メタデータにはまだ反映していません。</footer></body></html>'''
(ROOT / 'index.html').write_text(page, encoding='utf-8')
print('Built', ROOT / 'index.html')
