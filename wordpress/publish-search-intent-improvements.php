<?php
/**
 * Publish the 2026-09 search-intent and archive improvements.
 *
 * The body of post 1500 (the official "what is" article) is deliberately
 * protected: only its title, excerpt and SEO description may change here.
 * Run with: wp eval-file wordpress/publish-search-intent-improvements.php
 */

$official_overview = get_post( 1500 );
if ( ! $official_overview ) {
	fwrite( STDERR, "Official overview post 1500 was not found.\n" );
	exit( 1 );
}
$official_overview_content = $official_overview->post_content;

$hub_content = <<<'HTML'
<p><strong>コンセプトダイアグラムを描く手順の全体像を、8段階で確認できる公式ガイドです。</strong>詳しい作業例、チームで進める方法、完成サンプルへ迷わず進めるように整理しました。</p>

<p>最初から正解を当てる必要はありません。顧客について分かっていることと想像していることを図にし、調査やデータで確かめながら更新できる仮説にすることが目的です。定義と必須項目は<a href="/note/about-concept-diagram/">「コンセプトダイアグラムとは」</a>で確認できます。</p>

<h2>1. ゴールとスタートを決める</h2>
<p>誰の変化を描くのかを定め、顧客と企業の双方にとって望ましい持続的なゴールを考えます。購入や登録ではなく、顧客の心理・態度で表してください。次に、企業が支援できる顧客の初期状態をスタートとして決めます。</p>

<h2>2. 2つの心理軸を決める</h2>
<p>スタートからゴールへ近づくために、顧客の心や頭の中で何と何が深まる必要があるかを考えます。行動回数や売上ではなく、互いに意味が重ならない2つの心理的要因を選びます。</p>

<h2>3. 軸の深まりを言葉にする</h2>
<p>それぞれの軸について、浅い状態から深い状態へ何が変わるのかを書き出します。顧客が実際に口にしそうな短い言葉にすると、2軸の違いや粒度のずれを見つけやすくなります。</p>

<h2>4. 中間の顧客状態を置く</h2>
<p>スタートとゴールの間に、意味のある心理・態度の変化を配置します。中間の顧客状態は、理論上限7個、実務上は5〜6個を推奨します。詳しい理由は<a href="/note/how-many-steps/">顧客状態数の公式FAQ</a>で説明しています。</p>

<h2>5. 分岐と統合を整える</h2>
<p>すべての顧客が同じ順序で進むとは限りません。異なる変化が起こるところは分岐させ、別の経路が同じ状態へ近づくところは統合します。形式的に分岐を増やさず、説明できる違いだけを残します。</p>

<h2>6. 体験と施策を結びつける</h2>
<p>各状態変化を支える体験やコミュニケーション施策を、顧客状態の箱ではなく矢印へ対応づけます。「何を実施するか」ではなく、「誰の、どの変化を支えるためか」を明確にします。</p>

<h2>7. 評価指標を決める</h2>
<p>本人の声、心理・態度、行動などを組み合わせ、顧客状態と状態変化をどう観測するかを決めます。一つのクリックや購入だけで心理を断定せず、複数の証拠で仮説を確かめます。</p>

<h2>8. サンプルと照らして検証する</h2>
<p>図を完成品として固定せず、顧客調査、問い合わせ、利用データなどと照らし合わせて更新します。<a href="/note/concept-diagram-example-hair-care/">ヘアケア事業の完成サンプル</a>では、5つの中間状態、分岐と統合、施策、評価指標まで確認できます。</p>

<h2>次に読むガイドを選ぶ</h2>
<ul>
<li><strong>短い作業例を追う：</strong><a href="/note/how-to-draw-concept-diagram-1/">描き方（前編）</a>から<a href="/note/how-to-draw-concept-diagram-2/">描き方（後編）</a>へ進みます。</li>
<li><strong>自分たちで実践する：</strong><a href="/note/concept-diagram-workshop-guide/">ワークショップガイド</a>で準備、進め方、見直し項目を確認します。</li>
<li><strong>迷った点を確かめる：</strong><a href="/note/category/faq/">公式FAQ</a>で構成要素、顧客状態数、2軸、CJMとの違いなどを確認します。</li>
</ul>
HTML;

$hub = get_page_by_path( 'how-to-draw-concept-diagram', OBJECT, 'post' );
$hub_data = array(
	'post_type'     => 'post',
	'post_status'   => 'publish',
	'post_author'   => 1,
	'post_title'    => 'コンセプトダイアグラムの描き方｜8つの手順と完成サンプル',
	'post_name'     => 'how-to-draw-concept-diagram',
	'post_excerpt'  => 'コンセプトダイアグラムの描き方を8つの手順で整理した公式ガイドです。ゴールとスタート、2つの心理軸、顧客状態、分岐と統合、施策、評価指標を説明し、詳しい実践ガイドと完成サンプルへ案内します。',
	'post_content'  => $hub_content,
	'post_category' => array( 14 ),
);
if ( $hub ) {
	$hub_data['ID'] = $hub->ID;
	$hub_id = wp_update_post( $hub_data, true );
} else {
	$hub_id = wp_insert_post( $hub_data, true );
}
if ( is_wp_error( $hub_id ) ) {
	fwrite( STDERR, 'Drawing hub: ' . $hub_id->get_error_message() . "\n" );
	exit( 1 );
}

$front_description = 'コンセプトダイアグラムは、顧客の心理・行動の変化を2軸で描き、提供する体験と評価指標をつなぐフレームワークです。提唱者の清水誠が、公式定義、描き方、事例、FAQを公開します。';
wp_update_post(
	array(
		'ID'           => 1382,
		'post_excerpt' => $front_description,
	)
);
update_post_meta( 1382, 'wp-seo-meta-description', $front_description );

$overview_description = 'コンセプトダイアグラムとは、顧客の心理・行動の変化を2軸で描き、体験、施策、評価指標をつなぐフレームワークです。提唱者の清水誠が、公式定義、8つの構成要素、カスタマージャーニーマップとの違いを解説します。';
wp_update_post(
	array(
		'ID'           => 1500,
		'post_title'   => 'コンセプトダイアグラムとは｜目的・8つの構成要素・CJMとの違い',
		'post_excerpt' => $overview_description,
	)
);
update_post_meta( 1500, 'wp-seo-meta-description', $overview_description );
update_post_meta( $hub_id, 'wp-seo-meta-description', $hub_data['post_excerpt'] );

$post_updates = array(
	1389 => array(
		'title'       => 'コンセプトダイアグラムの描き方（前編）｜ゴールと2軸を決める',
		'description' => 'コンセプトダイアグラムを描く前半として、顧客のゴールとスタート、2つの心理軸、軸の深まりを整理する実践例を図付きで説明します。',
	),
	1391 => array(
		'title'       => 'コンセプトダイアグラムの描き方（後編）｜顧客状態を配置する',
		'description' => 'コンセプトダイアグラムを描く後半として、中間の顧客状態を配置し、分岐と統合を調整する実践例を図付きで説明します。顧客状態は5〜6個を推奨します。',
	),
);
foreach ( $post_updates as $post_id => $definition ) {
	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_title'   => $definition['title'],
			'post_excerpt' => $definition['description'],
		)
	);
	update_post_meta( $post_id, 'wp-seo-meta-description', $definition['description'] );
}

foreach ( array( 1389, 1391 ) as $post_id ) {
	$content = get_post_field( 'post_content', $post_id );
	if ( false === strpos( $content, 'cd-drawing-hub-link-v1' ) ) {
		$link   = '<p class="cd-drawing-hub-link" data-version="cd-drawing-hub-link-v1"><a href="/note/how-to-draw-concept-diagram/">描き方の全体像と、目的に合う詳しいガイドを確認する</a></p>';
		$content = preg_replace( '/<\/aside>/', '</aside>' . "\n\n" . $link, $content, 1 );
		wp_update_post( array( 'ID' => $post_id, 'post_content' => $content ) );
	}
}

$workshop_id = 2444;
$workshop_content = get_post_field( 'post_content', $workshop_id );
if ( false === strpos( $workshop_content, 'cd-drawing-hub-link-v1' ) ) {
	$workshop_link = '<p data-version="cd-drawing-hub-link-v1">先に流れをつかみたい場合は、<a href="/note/how-to-draw-concept-diagram/">8つの手順をまとめた「コンセプトダイアグラムの描き方」</a>から確認してください。</p>';
	$workshop_content = preg_replace( '/(<p>最初から正解を当てることが目的ではありません.*?<\/p>)/s', '$1' . "\n\n" . $workshop_link, $workshop_content, 1 );
	wp_update_post( array( 'ID' => $workshop_id, 'post_content' => $workshop_content ) );
}

$widgets = get_option( 'widget_inc2734_wp_awesome_widgets_showcase', array() );
if ( isset( $widgets[2] ) ) {
	$widgets[2]['title'] = '顧客の変化を描き、施策と指標をつなぐ';
	update_option( 'widget_inc2734_wp_awesome_widgets_showcase', $widgets );
}

$custom_widgets = get_option( 'widget_custom_html', array() );
if ( isset( $custom_widgets[3]['content'] ) ) {
	$custom_widgets[3]['content'] = str_replace(
		'https://concept-diagram.com/note/concept-diagram-workshop-guide/',
		'https://concept-diagram.com/note/how-to-draw-concept-diagram/',
		$custom_widgets[3]['content']
	);
	update_option( 'widget_custom_html', $custom_widgets );
}

update_post_meta( 2410, '_menu_item_object_id', $hub_id );
clean_post_cache( 2410 );
wp_update_post(
	array(
		'ID'         => 1750,
		'post_title' => 'コンセプトダイアグラムとは',
	)
);

// Archive and taxonomy collection pages are discoverable through navigation;
// keep thin date/category indexes out of the generated sitemap.
$sitemap_options = get_option( 'sm_options', array() );
$sitemap_options['sm_in_arch']        = false;
$sitemap_options['sm_in_auth']        = false;
$sitemap_options['sm_in_tags']        = false;
$sitemap_options['sm_in_cats']        = false;
$sitemap_options['sm_in_customtypes'] = array();
update_option( 'sm_options', $sitemap_options );

if ( get_post_field( 'post_content', 1500 ) !== $official_overview_content ) {
	fwrite( STDERR, "Protected body of post 1500 changed; aborting.\n" );
	exit( 1 );
}

clean_post_cache( 1382 );
clean_post_cache( 1500 );
clean_post_cache( $hub_id );
echo $hub_id . "\t" . get_permalink( $hub_id ) . "\n";
echo "Protected overview body: unchanged\n";
