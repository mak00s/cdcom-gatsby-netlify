<?php
/**
 * Publish two public learning resources derived from the 2024 lecture deck.
 * Run with: wp eval-file wordpress/publish-public-guides.php
 */

$image_id  = 2442;
$image_url = wp_get_attachment_url( $image_id );

if ( ! $image_url ) {
	fwrite( STDERR, "Attachment 2442 was not found.\n" );
	exit( 1 );
}

$sample_content = <<<'HTML'
<p><strong>架空のヘアケア事業を題材にした、コンセプトダイアグラムの完成サンプルです。</strong>2024年の講義資料で使用した作例を、現在の公式定義に合わせて読み解きます。特定の企業、商品、調査結果や効果を示す事例ではありません。</p>

<p>この作例では、ヘアケアを後回しにしている人が、商品を買うこと自体ではなく、自分に合うケアを理解して続けられる状態へ変わる過程を描いています。</p>

<figure class="wp-block-image size-full"><img src="__IMAGE_URL__" alt="自分ケアの知識と健康への意識を2軸に、中間の顧客状態5個と施策を配置したヘアケア事業のコンセプトダイアグラム" width="1600" height="900" class="wp-image-2442" /><figcaption>2024年に作成した架空の作例。画像内の「ステップ」は、現在の公式用語では「顧客状態」です。</figcaption></figure>

<h2>この作例の前提</h2>

<table><thead><tr><th>項目</th><th>設定</th></tr></thead><tbody>
<tr><th>対象顧客</th><td>ヘアケアへの関心が低い、情報不足で選べない、または手間を理由に後回しにしている成人男性</td></tr>
<tr><th>事業側の課題</th><td>商品単体の訴求だけでなく、自分に合うケアを理解し、無理なく続ける価値を伝える</td></tr>
<tr><th>スタート</th><td>「何でもいい」</td></tr>
<tr><th>ゴール</th><td>「身も心も充実の毎日」</td></tr>
<tr><th>横軸</th><td>自分ケアの知識</td></tr>
<tr><th>縦軸</th><td>健康への意識</td></tr>
</tbody></table>

<p>ゴールを「商品を買う」「ブランドのファンになる」としないのがポイントです。企業や商品は顧客が望む状態へ進むための手段と考えます。</p>

<h2>5つの中間の顧客状態</h2>

<ol>
<li><strong>問題かも</strong>：これまで気にしていなかった課題を、自分にも関係があるかもしれないと認識した状態</li>
<li><strong>意外と良い</strong>：試してみることで、負担よりも手応えや心地よさを感じた状態</li>
<li><strong>しっかり納得</strong>：なぜ必要なのか、なぜ自分に合うのかを理解した状態</li>
<li><strong>他もケアしたい</strong>：一つの体験をきっかけに、食事など周辺のセルフケアにも関心が広がった状態</li>
<li><strong>ずっとキープしたい</strong>：良い状態を一時的な体験で終わらせず、習慣として続けたい状態</li>
</ol>

<p>スタートとゴールを除く中間状態は5個です。これは<a href="/note/how-many-steps/">実務上の推奨である5〜6個</a>に収まっています。</p>

<h2>分岐と統合をどう読むか</h2>

<p>「意外と良い」から先は、一つの商品への理解を深めて「しっかり納得」へ進む経路と、関心を周辺のセルフケアへ広げて「他もケアしたい」へ進む経路に分かれます。その後、どちらも「ずっとキープしたい」へ近づきます。</p>

<p>すべての人が同じ順番で進むという意味ではありません。分岐と統合は、異なる心理変化を企業がどう支援するかを考えるための仮説です。</p>

<h2>状態変化を促す施策</h2>

<table><thead><tr><th>主な状態変化</th><th>施策の例</th></tr></thead><tbody>
<tr><td>何でもいい → 問題かも</td><td>不安を過度に煽らず、自分との関係を理解できる広告、セルフチェック、インフォグラフィック</td></tr>
<tr><td>問題かも → 意外と良い</td><td>サンプル、お試しキット、使い方ガイド、適切なタイミングのフォロー</td></tr>
<tr><td>意外と良い → しっかり納得</td><td>FAQ、根拠と限界を示した知識コンテンツ、継続利用を助ける案内</td></tr>
<tr><td>意外と良い → 他もケアしたい</td><td>食事や生活習慣など、関連するセルフケア情報への案内</td></tr>
<tr><td>しっかり納得／他もケアしたい → ずっとキープしたい</td><td>習慣化支援、振り返り、利用者同士の知見共有、継続状況に応じた案内</td></tr>
</tbody></table>

<h2>評価指標を追加する</h2>

<p>元の講義用図には評価指標を書き込んでいません。現在の公式定義では、次のような指標を加えると、顧客状態と施策の仮説を検証しやすくなります。</p>

<table><thead><tr><th>顧客状態</th><th>観測候補</th></tr></thead><tbody>
<tr><td>問題かも</td><td>課題の自覚度、セルフチェック完了、関連情報への関心</td></tr>
<tr><td>意外と良い</td><td>試用後の実感、再使用意向、一定期間内の再使用</td></tr>
<tr><td>しっかり納得</td><td>理解度、自分に合う理由の説明、継続意向</td></tr>
<tr><td>他もケアしたい</td><td>関連テーマへの関心、周辺コンテンツの利用、別のセルフケアの試行</td></tr>
<tr><td>ずっとキープしたい</td><td>習慣化の自己評価、継続期間、再利用間隔、離脱理由</td></tr>
</tbody></table>

<p>閲覧、クリック、申込み、購入などの行動だけで顧客状態を断定しないことが重要です。行動データは代理指標として使い、アンケート、インタビュー、問い合わせ内容などと組み合わせます。</p>

<h2>現在の必須8項目との対応</h2>

<ol>
<li>対象顧客：ヘアケアを後回しにしている成人男性</li>
<li>スタート：「何でもいい」</li>
<li>ゴール：「身も心も充実の毎日」</li>
<li>2つの心理軸：自分ケアの知識、健康への意識</li>
<li>顧客状態：5つの中間状態</li>
<li>状態変化：分岐と統合を含む矢印</li>
<li>施策：各状態変化を促すコミュニケーション</li>
<li>評価指標：心理と行動を組み合わせた観測候補</li>
</ol>

<p>必須項目の定義は<a href="/note/concept-diagram-elements/">構成要素の公式FAQ</a>、実際に描く手順は<a href="/note/concept-diagram-workshop-guide/">自分たちで描くワークショップガイド</a>を参照してください。</p>
HTML;

$sample_content = str_replace( '__IMAGE_URL__', esc_url( $image_url ), $sample_content );

$guide_content = <<<'HTML'
<p><strong>コンセプトダイアグラムを、セミナーを受けなくても自分たちで描けるようにするための実践ガイドです。</strong>2024年まで講義で使っていたワークショップ内容を、現在の公式定義に合わせて公開します。</p>

<p>最初から正解を当てることが目的ではありません。顧客についての仮説を図にし、チームで違いを発見し、データや調査で検証できる状態にすることが目的です。</p>

<h2>始める前の準備</h2>

<h3>参加者</h3>
<p>顧客、商品、営業、サポート、データなど、異なる視点を持つ3〜5人程度で行うと議論が広がります。一人で下書きしてからチームで見直す方法でも構いません。</p>

<h3>用意するもの</h3>
<ul>
<li>大きな紙、ホワイトボード、または共同編集できる作図ツール</li>
<li>太いペン、付箋</li>
<li>顧客調査、問い合わせ、商談、利用状況、アクセス解析などの既存資料</li>
<li><a href="/note/concept-diagram-elements/">必須8項目の一覧</a></li>
</ul>

<h3>進め方の原則</h3>
<ul>
<li>最初は個人で複数案を出し、その後に共通点と差分を話す</li>
<li>誰か一人の案を選ぶのではなく、良い部分を集約する</li>
<li>完成度よりも、顧客についての前提の違いを可視化する</li>
<li>企業の都合、顧客の心理、観測できる行動を混同しない</li>
</ul>

<h2>1. 対象顧客と事業の前提をそろえる</h2>

<p>最初に「誰の変化を描くのか」を決めます。年齢や性別だけでなく、置かれている状況、困りごと、現在の心理・態度を含めてください。</p>

<p>次の項目を短く整理します。</p>
<ul>
<li>対象顧客と、対象に含めない人</li>
<li>企業や事業の社会的な役割</li>
<li>顧客から見た強みと、選ばれない理由</li>
<li>顧客ニーズや市場の変化</li>
<li>現在多い顧客と、今後支援したい顧客</li>
</ul>

<h2>2. ゴールを決める</h2>

<p>商品やサービスを通じて、対象顧客にどのような持続的な変化が起きると、顧客と企業の双方にとって望ましいかを考えます。</p>

<p><strong>売上、会員数、購入、登録、ブランドのファンになることはゴールにしません。</strong>これらは企業側の成果や手段です。顧客の悩みや潜在ニーズが満たされ、ある程度持続する心理・態度状態をゴールにします。</p>

<h2>3. スタートを決める</h2>

<p>ゴールにまだ近づいていないものの、企業が支援できる可能性がある顧客の初期状態を決めます。課題を自覚している人だけに限定せず、受け身、無関心、迷い、諦めなども候補になります。</p>

<p>具体的な状況が複数ある場合は、共通する心理状態をスタートとして一つにまとめ、個別の声を吹き出しとして添えます。</p>

<h2>4. 2つの心理軸を決める</h2>

<p>スタートからゴールへ進むために、顧客の心や頭の中で何と何が深まる必要があるかを考えます。</p>

<ul>
<li>互いに意味が重ならない2軸にする</li>
<li>行動回数、売上、購入、満足度などの結果を軸にしない</li>
<li>「知識」「意欲」のように広すぎる場合は、対象に合わせて具体化する</li>
<li>一方だけが深まってもゴールへ到達しない組み合わせにする</li>
</ul>

<p>顧客状態が斜め一直線にしか並ばない場合は、2つの軸が似すぎていないか確認します。</p>

<h2>5. 中間の顧客状態を置く</h2>

<p>スタートとゴールの間に、心理・態度が意味のある単位で変わった状態を置きます。</p>

<ul>
<li>主語を顧客にする</li>
<li>「訪問」「登録」「購入」のようなタスクを顧客状態にしない</li>
<li>短い心の声として読める表現にする</li>
<li>抽象的な状態名に、具体的な発言や行動例を吹き出しで添える</li>
<li>スタートとゴールを除き、実務上は5〜6個を推奨する</li>
</ul>

<p>標準構造の理論上限は7個です。7個すべてを使う前に、似た状態を統合できないか、行動や施策が混ざっていないか確認してください。根拠は<a href="/note/how-many-steps/">顧客状態数の公式FAQ</a>で説明しています。</p>

<h2>6. 状態変化を矢印で結ぶ</h2>

<p>顧客状態の間を矢印で結び、どのような心理変化が起きるのかを確認します。全員が同じ順番で進むとは限らないため、必要に応じて分岐と統合を使います。</p>

<p>分岐を形式的に入れる必要はありません。一本道になった場合も即座に誤りとはせず、顧客の違いを省略していないか、2軸が似すぎていないかを点検します。停滞や離脱が重要な事業では、主要経路と区別して補助的に記載します。</p>

<h2>7. 状態変化を促す施策を置く</h2>

<p>施策は顧客状態の箱ではなく、状態間の矢印に置きます。「誰に、何をしてもらうか」だけでなく、「どの状態の人に、どのような変化を起こすためか」を明確にします。</p>

<p>現在実施している施策と新しい案の両方を置くと、施策が集中している箇所、支援できていない変化、目的が曖昧な施策を発見できます。</p>

<h2>8. 評価指標を決める</h2>

<p>各顧客状態と状態変化を、どの情報で観測するかを決めます。</p>

<ul>
<li>アンケートやインタビューによる心理・態度</li>
<li>問い合わせ、商談、レビューなどに現れる言葉</li>
<li>コンテンツ利用、試用、継続などの行動</li>
<li>顧客状態間の移行を示す複数の代理指標</li>
</ul>

<p>一つのクリックや購入だけで心理状態を断定しないでください。異なるデータを組み合わせ、図を検証可能な仮説として運用します。</p>

<h2>9. 全体を見直す</h2>

<ul>
<li>対象顧客が具体的で、図の途中で変わっていないか</li>
<li>ゴール、スタート、心理軸、顧客状態の主語が顧客になっているか</li>
<li>2つの軸は異なる心理的要因になっているか</li>
<li>中間の顧客状態はタスクではなく、5〜6個程度に整理されているか</li>
<li>状態の違いを、施策と評価指標の違いとして説明できるか</li>
<li>状態変化の矢印と、分岐・統合の意味を説明できるか</li>
<li>企業名や商品名を外しても、顧客の変化として意味が通じるか</li>
<li>既存データで確認できることと、新たに調査すべきことが分かれているか</li>
</ul>

<h2>完成後は仮説として検証する</h2>

<p>ワークショップで描いた図は完成品ではなく、共有できる仮説です。顧客インタビュー、アンケート、利用データ、問い合わせ内容などと照らし合わせ、状態名、分岐、施策、評価指標を更新します。</p>

<p>完成形の読み方は<a href="/note/concept-diagram-example-hair-care/">ヘアケア事業のサンプル</a>、短い実践例は<a href="/note/how-to-draw-concept-diagram-1/">描き方（前編）</a>と<a href="/note/how-to-draw-concept-diagram-2/">描き方（後編）</a>を参照してください。</p>
HTML;

$posts = array(
	array(
		'post_title'    => 'コンセプトダイアグラム完成サンプル：ヘアケア事業',
		'post_name'     => 'concept-diagram-example-hair-care',
		'post_excerpt'  => '架空のヘアケア事業を題材に、対象顧客、スタート、ゴール、2軸、5つの顧客状態、分岐・統合、施策、評価指標を読み解く完成サンプルです。',
		'post_content'  => $sample_content,
		'post_category' => array( 26 ),
		'thumbnail_id'  => $image_id,
	),
	array(
		'post_title'    => 'コンセプトダイアグラムを自分たちで描くワークショップ',
		'post_name'     => 'concept-diagram-workshop-guide',
		'post_excerpt'  => 'セミナーを受けなくても、チームや個人でコンセプトダイアグラムを描けるように、準備、9段階の進め方、現在の公式チェック項目を公開します。',
		'post_content'  => $guide_content,
		'post_category' => array( 14 ),
	),
);

foreach ( $posts as $definition ) {
	$existing = get_page_by_path( $definition['post_name'], OBJECT, 'post' );
	$data     = array(
		'post_type'     => 'post',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_title'    => $definition['post_title'],
		'post_name'     => $definition['post_name'],
		'post_excerpt'  => $definition['post_excerpt'],
		'post_content'  => $definition['post_content'],
		'post_category' => $definition['post_category'],
	);

	if ( $existing ) {
		$data['ID'] = $existing->ID;
		$post_id    = wp_update_post( $data, true );
	} else {
		$post_id = wp_insert_post( $data, true );
	}

	if ( is_wp_error( $post_id ) ) {
		fwrite( STDERR, $definition['post_name'] . ': ' . $post_id->get_error_message() . "\n" );
		exit( 1 );
	}

	if ( ! empty( $definition['thumbnail_id'] ) ) {
		set_post_thumbnail( $post_id, $definition['thumbnail_id'] );
	}

	clean_post_cache( $post_id );
	echo $post_id . "\t" . get_permalink( $post_id ) . "\n";
}

$guide = get_page_by_path( 'concept-diagram-workshop-guide', OBJECT, 'post' );
if ( $guide ) {
	update_post_meta( 2410, '_menu_item_object_id', $guide->ID );
	clean_post_cache( 2410 );
}

$widgets = get_option( 'widget_custom_html', array() );
if ( isset( $widgets[3]['content'] ) ) {
	$widgets[3]['content'] = str_replace(
		array(
			'https://concept-diagram.com/note/how-to-draw-concept-diagram-1/',
			'ゴールとスタート、軸、ステップを順に整理します。',
		),
		array(
			'https://concept-diagram.com/note/concept-diagram-workshop-guide/',
			'対象顧客、ゴールとスタート、2軸、顧客状態、施策、評価指標を順に整理します。',
		),
		$widgets[3]['content']
	);
	update_option( 'widget_custom_html', $widgets );
}
