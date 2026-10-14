"""Build a local review artifact; does not contact or change WordPress."""
from pathlib import Path
import html
import json

ROOT = Path(__file__).resolve().parent
ASSETS = ROOT / "assets"
ASSETS.mkdir(exist_ok=True)

def text(x, y, value, size=22, fill="#183c42", anchor="middle"):
    return f'<text x="{x}" y="{y}" text-anchor="{anchor}" font-size="{size}" fill="{fill}">{html.escape(value)}</text>'

def start_svg(title, height=650):
    return f'''<svg xmlns="http://www.w3.org/2000/svg" width="800" height="{height}" viewBox="0 0 800 {height}" role="img">
<title>{html.escape(title)}</title>
<defs><marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto"><path d="M0 0 L10 5 L0 10Z" fill="#58868a"/></marker></defs>
<rect width="800" height="{height}" rx="16" fill="#f2f8f7"/>
<g font-family="system-ui, sans-serif">'''

def box(x, y, label, color="#ffffff", width=180):
    out = f'<rect x="{x-width/2}" y="{y-32}" width="{width}" height="64" rx="12" fill="{color}" stroke="#43777c" stroke-width="2"/>'
    lines = label.split("|")
    for i, line in enumerate(lines):
        out += text(x, y+8 if len(lines)==1 else y-3+26*i, line)
    return out

def chart(stage):
    out = start_svg(f"ヘアケア事業の途中図{stage}")
    out += '<path d="M80 80 H755" fill="none" stroke="#58868a" stroke-width="3" marker-end="url(#arrow)"/><path d="M80 80 V610" fill="none" stroke="#58868a" stroke-width="3" marker-end="url(#arrow)"/>'
    out += text(420, 42, "自分ケアの知識 → 深まる", 24)
    out += '<text x="30" y="350" text-anchor="middle" transform="rotate(-90 30 350)" font-size="24" fill="#183c42">健康への意識 → 深まる</text>'
    out += box(185, 120, "何でもいい", "#fff0c4", 160)
    out += box(660, 580, "身も心も|充実の毎日", "#ffe4e5")
    if stage == 1:
        out += text(395, 290, "理解だけでも、意識だけでも足りない", 23)
        out += text(395, 328, "この人のゴールに必要な2つの変化は？", 23)
        out += text(430, 465, "状態と施策は、次の手順で加える", 21, "#546f72")
    else:
        if stage == 3:
            paths = [
                ('M185 152 L240 196', 'A', 190, 182),
                ('M250 252 V307', 'B', 280, 285),
                ('M340 339 H400', 'C', 369, 319),
                ('M455 371 V426', 'D', 485, 401),
                ('M660 371 V426', 'E', 690, 401),
                ('M545 458 H570', 'F', 558, 439),
                ('M660 490 V544', 'G', 690, 523),
            ]
            for d, label, x, y in paths:
                out += f'<path d="{d}" fill="none" stroke="#58868a" stroke-width="3" marker-end="url(#arrow)"/>'
                out += text(x, y, label, 22, "#a33b43")
        for x,y,label in [(250,220,"問題かも"),(250,339,"意外と良い"),(570,339,"しっかり納得"),(455,458,"他もケアしたい"),(660,458,"ずっとキープ|したい")]:
            out += box(x,y,label,width=340 if label=="しっかり納得" else 180)
        out += text(430, 635, "中間状態5個。スタートとゴールは含めない。", 20, "#546f72")
    return out + "</g></svg>"

for stage, name in [(1,"01-axes.svg"),(2,"02-states.svg"),(3,"03-support.svg")]:
    (ASSETS/name).write_text(chart(stage), encoding="utf-8")

out = start_svg("描き方で迷いやすい4つの点と見直し", 820)
cards = [
    ("1. 行動を状態にしている", "購入する", "意外と良い", "体験の前後で、どんな気持ちに変わる？"),
    ("2. 2軸の意味が似ている", "認知度 × 理解度", "知識 × 健康への意識", "このゴールには、異なる何が必要？"),
    ("3. 斜め一直線に並ぶ", "○ → ○ → ○", "2軸の違いを見直す", "一方だけが深まる状態を省略していない？"),
    ("4. 施策が状態の箱に入る", "［お試しキット］", "状態 → お試し → 状態", "施策は、変化を支える矢印へ対応づける"),
]
for i,(heading,before,after,question) in enumerate(cards):
    y=24+i*198
    out += f'<rect x="24" y="{y}" width="752" height="178" rx="12" fill="white" stroke="#c4d9d7"/>'
    out += text(48,y+35,heading,25,anchor="start")
    out += text(205,y+86,before,23,"#875351")
    out += text(383,y+86,"→",25)
    out += text(570,y+86,after,23)
    out += text(400,y+142,question,23)
(ASSETS/"04-pitfalls.svg").write_text(out+"</g></svg>", encoding="utf-8")

data=json.loads((ROOT/"review.json").read_text(encoding="utf-8"))
article=(ROOT/"article.html").read_text(encoding="utf-8")
article=article.replace('<figure>', '<figure><p class="scroll-hint">スマートフォンでは図を横にスクロールして読めます。</p>')
related=""
for item in data["related_changes"]:
    snippet = item["html"].replace('href="/note/how-to-draw-concept-diagram/"', 'href="#finished-example"')
    related += f'<section class="related-card"><h3><a href="{html.escape(item["url"])}">{html.escape(item["url"])}</a></h3><p class="placement">{html.escape(item["placement"])}</p>{snippet}'
    if "replacement_paragraph" in item:
        related += '<p><strong>分岐の説明も元図に合わせて修正：</strong>'+item["replacement_paragraph"]+'</p><p>'+item["table_correction"]+'</p>'
    related += '</section>'
css='''
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:#f5f7f6;color:#24383b;font-family:system-ui,-apple-system,"Hiragino Kaku Gothic ProN",sans-serif;line-height:1.95}a{color:#176d70;text-underline-offset:4px}header{background:#193e42;color:#fff;padding:22px max(20px,calc((100% - 960px)/2))}header p{margin:0}header a{color:#fff}.review-bar{background:#fff0c4;padding:12px 20px;text-align:center;font-size:14px}main{max-width:960px;margin:28px auto;background:white;border-radius:16px;padding:48px}h1{font-size:34px;line-height:1.55;margin:10px 0 24px;letter-spacing:-.025em}h2{margin:58px 0 20px;font-size:26px;line-height:1.6;border-left:5px solid #38999a;padding-left:16px}h3{font-size:18px;overflow-wrap:anywhere}p{margin:18px 0}nav{background:#f2f8f7;border-radius:12px;padding:16px 24px}li{margin:8px 0}figure{margin:28px 0;overflow-x:auto}figure img{display:block;width:100%;height:auto}figcaption{font-size:14px;color:#607477;line-height:1.8;padding:12px 4px}table{border-collapse:collapse;width:100%;font-size:15px;line-height:1.8}th,td{border:1px solid #d5e1df;padding:13px;vertical-align:top;text-align:left}thead,th{background:#f2f8f7}.table-scroll{overflow-x:auto}table{min-width:640px}.scroll-hint{display:none}.meta{background:#f5f7f6;border-radius:12px;padding:20px;margin-top:40px}.related-card{padding:20px;border:1px solid #d5e1df;border-radius:12px;margin:20px 0}.placement{font-size:14px;color:#607477}.label{font-size:13px;letter-spacing:.12em;color:#537d7f}#review-notes{border-top:3px solid #193e42;margin-top:65px}footer{max-width:960px;margin:24px auto;padding:20px;color:#607477;font-size:14px}@media(max-width:700px){main{margin:12px;padding:24px 18px;border-radius:10px}h1{font-size:26px}h2{font-size:22px;margin-top:44px}figure img{min-width:680px}.scroll-hint{display:block;font-size:13px;color:#607477;margin:0 0 8px}nav{padding:12px}nav ol{padding-left:25px}body{font-size:16px}.related-card{padding:14px}}
'''
page=f'''<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>レビュー：{html.escape(data["title"])}</title><style>{css}</style></head><body>
<header><p>CONCEPT DIAGRAM · LOCAL REVIEW</p></header><div class="review-bar">未公開原稿 · 2026-10-14 · <a href="#review-notes">関連ページの変更案を見る</a></div>
<main><p class="label">公式ガイド / 清水 誠</p><h1>{html.escape(data["title"])}</h1>{article}
<section id="review-notes"><h2>公開前の確認：関連ページの導線</h2><p>以下は関連ページへ反映する文章案です。本番ページと個人サイトの原稿は、このプレビュー作成では変更していません。</p>{related}
<div class="meta"><h3>検索結果に表示する説明文の案</h3><p>{html.escape(data["description"])}</p><h3>公開後に確かめること</h3><ul>{''.join('<li>'+html.escape(x['expected'])+'</li>' for x in data['hypotheses'])}</ul></div></section></main>
<footer>作業用手順の8段階、公式の必須8項目、中間状態5〜6個を区別。途中図は説明用の再構成で、調査・効果の実証を示すものではありません。</footer></body></html>'''
(ROOT/"index.html").write_text(page, encoding="utf-8")
print("Built", ROOT/"index.html")
