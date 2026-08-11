<?php
/**
 * Plugin Name: kosgis add function
 * Version: 1.1
*/
function prnt($arg) {
	if(!is_user_logged_in()) return;
	echo "<pre>";
	print_r($arg);
	echo "</pre";
}


// 翻訳の調整
function custom_gettext( $translated, $text, $domain ) {
	$custom_translates = array(
		'woocommerce' => array(
			'商品' => 'セミナー',
			'カートに入れる' => '申し込む',
			'カートを更新' => '申込内容を更新',
			'カートを表示' => "申込内容を確認",
			'カートの合計' => "参加費用の合計",
			'在庫%s個' => "あと %s 名",
			'カートに「%s」をさらに追加することはできません。' => "「%s」はすでにお申し込み済みです。お支払いに進んでください。",
			'購入手続き' => "お支払い手続き",
			'checkout' => "お支払い手続き",
			'商品カテゴリー:' => 'カテゴリー:',
			'商品タグ:' => 'タグ:',
			'注文する' => '支払いに進む',
			'注文メモ' => '備考',
			'注文に関するメモ（例： 配達のための特別注意事項。' => '当日の質問や、請求書や領収書についての宛名などがあれば',
			'%sをカートに追加しました。' => '%sに申し込む場合は、お支払いに進んでください。',
			'%sを削除しました。' => '%sをキャンセルしました。',
			'カートは空です。' => 'お申込みはありません。',
			'ショップに戻る' => 'セミナー一覧に戻る',
			'買い物を続ける' => '他のセミナーも申し込む',
			'Billing details' => 'ご請求内容',
		)
	);
	if ( isset( $custom_translates[$domain][$translated] ) ) {
		$translated = $custom_translates[$domain][$translated];
	}
	return $translated;
}
add_filter( 'gettext', 'custom_gettext', 10, 3 );

function trans_custom_gettext() {
	$args = func_get_args();
	$translated = $args[0];
	$text = $args[1];
	$domain = array_pop( $args );
	$translated = custom_gettext( $translated, $text, $domain );
	return $translated;
}
add_filter( 'gettext_with_context', 'trans_custom_gettext', 10, 4 );
add_filter( 'ngettext', 'trans_custom_gettext', 10, 5 );
add_filter( 'ngettext_with_context', 'trans_custom_gettext', 10, 6 );

/**
 * Return the best available site logo for structured data.
 * Snow Monkey's default JSON-LD emits `false` when no custom logo is set.
 */
function cd_structured_data_logo_url() {
	$custom_logo_id = (int) get_theme_mod( 'custom_logo' );
	$site_icon_id   = (int) get_option( 'site_icon' );
	$attachment_id  = $custom_logo_id ?: $site_icon_id;

	return $attachment_id
		? wp_get_attachment_image_url( $attachment_id, 'full' )
		: home_url( '/wp-content/uploads/2018/10/what-is-concept-diagram.png' );
}

/**
 * Return the eight required Concept Diagram design items as an ItemList.
 */
function cd_concept_diagram_elements_item_list( $url ) {
	$elements = array(
		array( 'name' => '対象顧客', 'description' => 'この図で心理・態度変容を考える顧客の範囲' ),
		array( 'name' => 'スタート', 'description' => '対象顧客の初期の心理・態度状態' ),
		array( 'name' => 'ゴール', 'description' => '企業と顧客の双方にとって望ましい、持続的な到達状態' ),
		array( 'name' => '2つの心理軸', 'description' => 'ゴール到達につながる、顧客視点の異なる心理的要因' ),
		array( 'name' => '顧客状態', 'description' => '軸の深まりに応じた中間の心理・態度状態。スタートとゴールを除き、実務上は5〜6個を推奨。標準構造の理論上限は7個' ),
		array( 'name' => '状態変化', 'description' => '顧客状態間の変化、分岐、統合を示す矢印' ),
		array( 'name' => '施策', 'description' => '状態変化を促す企業側のコミュニケーション' ),
		array( 'name' => '評価指標', 'description' => '顧客状態や状態変化を観測し、施策を評価・改善するための指標' ),
	);

	$list_items = array();
	foreach ( $elements as $index => $element ) {
		$list_items[] = array(
			'@type'       => 'ListItem',
			'position'    => $index + 1,
			'name'        => $element['name'],
			'description' => $element['description'],
		);
	}

	return array(
		'@type'           => 'ItemList',
		'@id'             => $url . '#concept-diagram-elements',
		'name'            => 'コンセプトダイアグラムの必須8項目',
		'numberOfItems'   => count( $list_items ),
		'itemListOrder'   => 'https://schema.org/ItemListOrderAscending',
		'itemListElement' => $list_items,
	);
}

/**
 * Return the customer states used in the fictional hair-care example.
 */
function cd_hair_care_example_item_list( $url ) {
	$states = array(
		array( 'name' => '問題かも', 'description' => 'これまで気にしていなかった課題を、自分にも関係があるかもしれないと認識した状態' ),
		array( 'name' => '意外と良い', 'description' => '試してみることで、負担よりも手応えや心地よさを感じた状態' ),
		array( 'name' => 'しっかり納得', 'description' => 'なぜ必要なのか、なぜ自分に合うのかを理解した状態' ),
		array( 'name' => '他もケアしたい', 'description' => '一つの体験をきっかけに、周辺のセルフケアにも関心が広がった状態' ),
		array( 'name' => 'ずっとキープしたい', 'description' => '良い状態を一時的な体験で終わらせず、習慣として続けたい状態' ),
	);

	$list_items = array();
	foreach ( $states as $index => $state ) {
		$list_items[] = array(
			'@type'       => 'ListItem',
			'position'    => $index + 1,
			'name'        => $state['name'],
			'description' => $state['description'],
		);
	}

	return array(
		'@type'           => 'ItemList',
		'@id'             => $url . '#customer-states',
		'name'            => 'ヘアケア事業サンプルの5つの中間の顧客状態',
		'numberOfItems'   => count( $list_items ),
		'itemListOrder'   => 'https://schema.org/ItemListOrderAscending',
		'itemListElement' => $list_items,
	);
}

/**
 * Return the public self-guided workshop as HowTo structured data.
 */
function cd_workshop_howto( $url ) {
	$steps = array(
		array( 'name' => '対象顧客と事業の前提をそろえる', 'text' => '誰の変化を描くのかを決め、事業の役割、顧客から見た強みと弱み、顧客ニーズの変化を整理します。' ),
		array( 'name' => 'ゴールを決める', 'text' => '対象顧客の悩みや潜在ニーズが満たされた、顧客と企業の双方にとって望ましい持続的な状態を決めます。' ),
		array( 'name' => 'スタートを決める', 'text' => '企業が支援できる可能性がある顧客の初期の心理・態度状態を決めます。' ),
		array( 'name' => '2つの心理軸を決める', 'text' => 'スタートからゴールへ進むために、顧客の心や頭の中で深まる必要がある、互いに異なる2つの心理的要因を決めます。' ),
		array( 'name' => '中間の顧客状態を置く', 'text' => 'スタートとゴールの間に、実務上は5〜6個を目安として、意味のある心理・態度状態を配置します。' ),
		array( 'name' => '状態変化を矢印で結ぶ', 'text' => '顧客状態の間を矢印で結び、必要に応じて分岐と統合を使います。' ),
		array( 'name' => '状態変化を促す施策を置く', 'text' => '各状態変化を促す企業側のコミュニケーション施策を矢印に対応づけます。' ),
		array( 'name' => '評価指標を決める', 'text' => '心理・態度、顧客の言葉、行動などを組み合わせ、各状態と状態変化を観測する方法を決めます。' ),
		array( 'name' => '全体を見直す', 'text' => '顧客視点、2軸の違い、顧客状態数、分岐・統合、施策、評価指標を点検し、検証可能な仮説に整えます。' ),
	);

	$howto_steps = array();
	foreach ( $steps as $index => $step ) {
		$howto_steps[] = array(
			'@type'    => 'HowToStep',
			'position' => $index + 1,
			'name'     => $step['name'],
			'text'     => $step['text'],
		);
	}

	return array(
		'@type'       => 'HowTo',
		'@id'         => $url . '#howto',
		'name'        => 'コンセプトダイアグラムを自分たちで描く方法',
		'description' => '対象顧客、スタート、ゴール、2つの心理軸、顧客状態、状態変化、施策、評価指標をチームまたは個人で整理するワークショップです。',
		'inLanguage'  => 'ja-JP',
		'step'        => $howto_steps,
	);
}

/**
 * Make the official site, proposer, publisher and article relationships explicit.
 */
function cd_filter_structured_data( $json_ld ) {
	$site_url        = home_url( '/' );
	$organization_id = $site_url . '#organization';
	$website_id      = $site_url . '#website';
	$proposer_id     = $site_url . '#makoto-shimizu';
	$language        = 'ja-JP';
	$logo_url        = cd_structured_data_logo_url();

	$organization = array(
		'@type' => 'Organization',
		'@id'   => $organization_id,
		'name'  => 'コンセプトダイアグラム公式サイト',
		'url'   => $site_url,
		'logo'  => array(
			'@type' => 'ImageObject',
			'url'   => $logo_url,
		),
	);

	$proposer = array(
		'@type'       => 'Person',
		'@id'         => $proposer_id,
		'name'        => '清水 誠',
		'url'         => home_url( '/about/' ),
		'sameAs'      => array( 'https://makoto-shimizu.com/' ),
		'knowsAbout'  => array( 'コンセプトダイアグラム', '顧客理解', 'マーケティング', 'アクセス解析' ),
	);

	$website = array(
		'@type'       => 'WebSite',
		'@id'         => $website_id,
		'name'        => 'コンセプトダイアグラム公式サイト',
		'url'         => $site_url,
		'inLanguage'  => $language,
		'publisher'   => array( '@id' => $organization_id ),
		'about'       => array( '@id' => $proposer_id ),
	);

	if ( is_front_page() ) {
		$page = array(
			'@type'       => 'WebPage',
			'@id'         => $site_url . '#webpage',
			'url'         => $site_url,
			'name'        => 'コンセプトダイアグラム公式サイト',
			'description' => '顧客の心理変容と企業の施策を図解し、顧客理解と施策評価につなげるコンセプトダイアグラムの公式情報サイトです。',
			'inLanguage'  => $language,
			'isPartOf'     => array( '@id' => $website_id ),
			'about'        => array( '@id' => $proposer_id ),
			'mainEntity'   => array( '@id' => home_url( '/note/about-concept-diagram/' ) . '#article' ),
			'dateModified' => get_the_modified_time( 'c', get_queried_object_id() ),
		);

		return array(
			'@context' => 'https://schema.org',
			'@graph'   => array( $organization, $proposer, $website, $page ),
		);
	}

	if ( is_singular() ) {
		$post_id      = get_queried_object_id();
		$url          = get_permalink( $post_id );
		$post_type    = get_post_type( $post_id );
		$author_id    = (int) get_post_field( 'post_author', $post_id );
		$author_name  = get_the_author_meta( 'display_name', $author_id );
		$author_url   = get_author_posts_url( $author_id );
		$author       = array(
			'@type' => 'Person',
			'name'  => $author_name,
			'url'   => $author_url,
		);

		if ( 1 === $author_id ) {
			$author = array( '@id' => $proposer_id );
		}

		$document = array(
			'@type'         => 'post' === $post_type ? 'BlogPosting' : 'WebPage',
			'@id'           => $url . ( 'post' === $post_type ? '#article' : '#webpage' ),
			'url'           => $url,
			'name'          => get_the_title( $post_id ),
			'headline'      => get_the_title( $post_id ),
			'description'   => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
			'inLanguage'    => $language,
			'isPartOf'       => array( '@id' => $website_id ),
			'publisher'      => array( '@id' => $organization_id ),
			'author'         => $author,
			'datePublished'  => get_the_time( 'c', $post_id ),
			'dateModified'   => get_the_modified_time( 'c', $post_id ),
			'about'          => array( '@id' => $proposer_id ),
			'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
		);

		$thumbnail = get_the_post_thumbnail_url( $post_id, 'full' );
		if ( $thumbnail ) {
			$document['image'] = array( '@type' => 'ImageObject', 'url' => $thumbnail );
		}

		$graph = array( $organization, $proposer, $website, $document );

		if ( 'post' === $post_type && has_category( 'faq', $post_id ) ) {
			$answer = wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $post_id ) ) );
			$answer = wp_trim_words( preg_replace( '/\s+/', ' ', $answer ), 180, '…' );
			$graph[] = array(
				'@type'      => 'FAQPage',
				'@id'        => $url . '#faq',
				'url'        => $url,
				'inLanguage' => $language,
				'mainEntity' => array(
					'@type'          => 'Question',
					'name'           => get_the_title( $post_id ),
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $answer,
					),
				),
			);
		}

		if ( in_array( $post_id, array( 1500, 2432 ), true ) ) {
			$graph[] = cd_concept_diagram_elements_item_list( $url );
		}

		$post_name = get_post_field( 'post_name', $post_id );
		if ( 'concept-diagram-example-hair-care' === $post_name ) {
			$graph[] = cd_hair_care_example_item_list( $url );
		}
		if ( 'concept-diagram-workshop-guide' === $post_name ) {
			$graph[] = cd_workshop_howto( $url );
		}

		return array( '@context' => 'https://schema.org', '@graph' => $graph );
	}

	if ( is_category( 'faq' ) ) {
		$items = array();
		foreach ( get_posts( array( 'category_name' => 'faq', 'numberposts' => 20, 'post_status' => 'publish' ) ) as $faq_post ) {
			$items[] = array( '@type' => 'Article', 'name' => get_the_title( $faq_post ), 'url' => get_permalink( $faq_post ) );
		}
		$collection = array(
			'@type'       => 'CollectionPage',
			'@id'         => home_url( '/note/category/faq/' ) . '#collection',
			'name'        => 'コンセプトダイアグラム FAQ',
			'url'         => home_url( '/note/category/faq/' ),
			'inLanguage'  => $language,
			'isPartOf'     => array( '@id' => $website_id ),
			'hasPart'      => $items,
		);

		return array( '@context' => 'https://schema.org', '@graph' => array( $organization, $proposer, $website, $collection ) );
	}

	return array( '@context' => 'https://schema.org', '@graph' => array( $organization, $proposer, $website ) );
}
add_filter( 'inc2734_wp_seo_json_ld', 'cd_filter_structured_data', 20 );
