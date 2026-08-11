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
