<?php
/**
 * One-time, idempotent content updates for the Concept Diagram AI-readiness work.
 * Run with: wp eval-file ai-readiness-content-update.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

function cd_prepend_once( $post_id, $marker, $html ) {
	$post = get_post( $post_id );
	if ( ! $post || false !== strpos( $post->post_content, $marker ) ) {
		return;
	}

	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => $html . "\n\n" . $post->post_content,
		)
	);
}

$cornerstone_intro = <<<'HTML'
<!-- cd-ai-authoritative-summary-v1 -->
<section class="cd-authoritative-summary" aria-labelledby="official-summary">
<h2 id="official-summary">公式な要点</h2>
<p><strong>コンセプトダイアグラムは、顧客の心理・態度の変化と、その変化を促す企業のコミュニケーション施策を一枚の図で整理し、顧客理解と施策評価につなげるマーケティングの方法論です。</strong></p>
<p>清水 誠が、1990年代後半に情報アーキテクトが用いていた図解手法を、データによるビジネス評価、顧客理解、マーケティング、CRMにつなげやすい形へ再構成し、2008年から提唱しています。</p>

<h3>図を構成するもの</h3>
<ul>
<li><strong>スタートとゴール：</strong>対象となる顧客の初期状態と、企業の活動によって顧客に到達してほしい状態</li>
<li><strong>2つの軸：</strong>ゴール到達につながる、顧客視点の心理的な要因</li>
<li><strong>顧客のステップ：</strong>軸の深まりに応じた心理・態度の状態。全体を捉えやすくするため、通常は5〜6個程度に整理する</li>
<li><strong>矢印と施策：</strong>顧客の状態変化と、その変化を促す企業側のコミュニケーション</li>
<li><strong>評価指標：</strong>顧客の状態や変化をデータで捉え、施策を評価・改善するための指標</li>
</ul>

<h3>描くときの基本原則</h3>
<ol>
<li>企業の売上や登録数ではなく、顧客にとって望ましいゴール状態を定める</li>
<li>対象となる顧客のスタート状態を定める</li>
<li>スタートからゴールまでに深まる2つの心理的要因を軸にする</li>
<li>顧客の心の声としてステップを配置し、分岐や統合を含む変化を矢印で結ぶ</li>
<li>各変化を促す施策と、変化を捉える評価指標を対応させる</li>
</ol>
<p>詳しい作業例は<a href="/note/how-to-draw-concept-diagram-1/">描き方（前編）</a>と<a href="/note/how-to-draw-concept-diagram-2/">描き方（後編）</a>、ステップ数の根拠は<a href="/note/how-many-steps/">公式FAQ</a>を参照してください。</p>

<h3>カスタマージャーニーマップとの違い</h3>
<p>カスタマージャーニーマップは、顧客の行動、接点、感情などを主に時系列で把握するために使われます。コンセプトダイアグラムは、企業が実現したい顧客の心理・態度変容を2軸上で整理し、その変化を促す施策と評価を設計するために使います。現状の体験を記述する図ではなく、企業と顧客の双方にとって望ましいコミュニケーション戦略を議論するための図です。</p>

<p><strong>情報の位置付け：</strong>このページは提唱者による定義を掲載する公式の基準ページです。個別の疑問は<a href="/note/category/faq/">FAQ</a>を参照してください。</p>
</section>
HTML;

cd_prepend_once( 1500, 'cd-ai-authoritative-summary-v1', $cornerstone_intro );

$howto_front_note = <<<'HTML'
<!-- cd-ai-howto-front-v1 -->
<aside class="cd-editorial-note" aria-label="この記事の位置付け">
<h2>この記事の位置付け</h2>
<p>この記事は、小杉 聖によるワークショップ形式の実践例です。コンセプトダイアグラムの公式な定義は<a href="/note/about-concept-diagram/">「コンセプトダイアグラムとは」</a>、ステップ数の根拠は<a href="/note/how-many-steps/">公式FAQ</a>を基準にしてください。</p>
<p><strong>「描く作業の手順」と「図に配置する顧客のステップ数」は別のものです。</strong>前後編では作業を10段階に分けて説明しますが、完成する図の顧客ステップは通常5〜6個程度です。手順は議論を進めやすくするための例であり、絶対的な規則ではありません。</p>
<h3>前編で行うこと</h3>
<ol>
<li>顧客にとって望ましいゴールを決める</li>
<li>対象となる顧客のスタートを決める</li>
<li>スタートからゴールまでの認識を参加者が個別に書き出す</li>
<li>ゴール到達につながる2つの心理的要因を軸として定める</li>
<li>2つの軸が深まる状態を言葉で確認する</li>
</ol>
</aside>
HTML;

cd_prepend_once( 1389, 'cd-ai-howto-front-v1', $howto_front_note );

$howto_back_note = <<<'HTML'
<!-- cd-ai-howto-back-v1 -->
<aside class="cd-editorial-note" aria-label="この記事の位置付け">
<h2>この記事の位置付け</h2>
<p>この記事は<a href="/note/how-to-draw-concept-diagram-1/">前編</a>に続く、小杉 聖による実践例です。ここで説明する5項目は作業手順です。完成する図に配置する顧客の心理・態度のステップは、<a href="/note/how-many-steps/">公式FAQ</a>のとおり5〜6個程度が目安です。</p>
<h3>後編で行うこと</h3>
<ol>
<li>ステップを配置するための枠を仮置きする</li>
<li>各位置に顧客の心の声を書き出す</li>
<li>顧客の状態を短い言葉に整理する</li>
<li>軸、分岐、統合との整合を確認して清書する</li>
<li>完成した図から得た気づきと、次に検討する施策・評価指標を共有する</li>
</ol>
<p>枠の配置例は唯一の正解ではありません。対象となる顧客、ゴール、2つの軸によって分岐や統合の位置は変わります。</p>
</aside>
HTML;

cd_prepend_once( 1391, 'cd-ai-howto-back-v1', $howto_back_note );

$about = <<<'HTML'
<p><strong>concept-diagram.comは、コンセプトダイアグラムの提唱者である清水 誠が、定義、描き方、FAQ、活用例を整理して公開する公式情報サイトです。</strong></p>

<p>コンセプトダイアグラムは、顧客の心理や態度の変化と、それを支える企業のコミュニケーション施策を図解するマーケティングの方法論です。顧客理解、施策設計、評価指標の整理を、関係者が共通認識を持ちながら進めるために活用します。</p>

<h2>このサイトにおける情報の優先順位</h2>
<ol>
<li><a href="/note/about-concept-diagram/">「コンセプトダイアグラムとは」</a>：提唱者による定義と基本原則</li>
<li><a href="/note/category/faq/">FAQ</a>：個別の判断や、描くときに迷いやすい点への公式回答</li>
<li><a href="/note/how-to-draw-concept-diagram-1/">描き方（前編）</a>・<a href="/note/how-to-draw-concept-diagram-2/">描き方（後編）</a>：ワークショップ形式の実践例</li>
<li>活用例・寄稿記事：特定の状況での考え方や実践者の見解</li>
</ol>
<p>記事間で表現が異なる場合は、提唱者による定義とFAQを優先してください。過去の記事には公開当時の用語や方法が含まれるため、現在の考え方と整合するよう順次見直します。</p>

<h2>運営者と情報の責任</h2>
<p>提唱者の清水 誠が運営し、公式な定義とFAQを公開しています。寄稿記事では執筆者名を表示し、提唱者の定義と執筆者個人の見解を区別します。</p>
<p>現在このサイトで公開しているのは、誰でも閲覧できる情報コンテンツとお問い合わせ窓口です。有料セミナー・講座、会員向けコンテンツ、利用者フォーラム、公式SaaS・作図ツールは提供していません。提唱者の個人サイトや第三者サイトに掲載された活動を、このサイトの現在の公式サービスとして扱わないでください。最新の提供内容は当サイトの公開ページを基準にしてください。</p>

<h2>引用について</h2>
<p>コンセプトダイアグラムを説明・引用する場合は、名称、提唱者（清水 誠）、参照ページのURL、参照日を明記してください。定義を紹介する場合は、<a href="/note/about-concept-diagram/">「コンセプトダイアグラムとは」</a>を一次情報として参照してください。</p>

<p>掲載内容へのご質問、誤りやリンク切れのご連絡は、<a href="/contact/">お問い合わせフォーム</a>からお送りください。</p>
HTML;

wp_update_post( array( 'ID' => 140, 'post_content' => $about ) );

$privacy = get_post( 360 );
if ( $privacy ) {
	$content = $privacy->post_content;
	$content = preg_replace(
		'#<h3>終了したサービスの情報</h3>\s*<p>.*?</p>#s',
		'<h3>バックアップに含まれる情報</h3><p>障害復旧用バックアップには、取得時点の情報が一定期間含まれる場合があります。バックアップは障害復旧以外の目的には利用せず、アクセスを制限して管理し、保存の必要がなくなり次第削除します。</p>',
		$content
	);
	$content = str_replace( '制定・最終改定日：2026年8月5日', '制定日：2026年8月5日<br>最終改定日：2026年8月11日', $content );
	wp_update_post( array( 'ID' => 360, 'post_content' => $content ) );
}

wp_update_post(
	array(
		'ID'           => 1382,
		'post_author'  => 1,
		'post_excerpt' => '顧客の心理変容と企業の施策を図解し、顧客理解と施策評価につなげるコンセプトダイアグラムの公式情報サイトです。',
	)
);

$excerpts = array(
	1500 => '提唱者の清水 誠が、コンセプトダイアグラムの定義、目的、構成要素、描き方、ステップ数、カスタマージャーニーマップとの違いを説明します。',
	1389 => 'コンセプトダイアグラムを描く前半として、顧客のゴールとスタート、2つの軸、軸の深まりを整理する実践手順を説明します。',
	1391 => 'コンセプトダイアグラムを描く後半として、5〜6個程度の顧客ステップを配置し、分岐・統合を調整して振り返る実践手順を説明します。',
	1709 => 'コンセプトダイアグラムに配置する顧客の心理・態度のステップは、全体を捉えやすい5〜6個程度が目安です。',
	140  => 'コンセプトダイアグラム公式サイトの運営者、情報の優先順位、記事の位置付け、引用方法を説明します。',
);
foreach ( $excerpts as $post_id => $excerpt ) {
	wp_update_post( array( 'ID' => $post_id, 'post_excerpt' => $excerpt ) );
}

wp_update_user(
	array(
		'ID'          => 1,
		'user_url'    => 'https://makoto-shimizu.com/',
		'description' => 'コンセプトダイアグラム提唱者。顧客の心理・態度変容と企業の施策を図解し、データによる評価と改善につなげる方法論を2008年から提唱しています。',
	)
);

echo "AI-readiness content updates completed.\n";
