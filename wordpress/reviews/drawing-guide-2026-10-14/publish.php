<?php
/** Reviewed publication package. Run explicitly with wp eval-file. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "WP CLI required.\n" );
}
$root = __DIR__;
$review = json_decode( file_get_contents( $root . '/review.json' ), true );
$body = file_get_contents( $root . '/article.html' );
$ids = array( 2448, 1389, 1391, 2444, 2443 );
$current = array();
foreach ( $ids as $id ) {
	$post = get_post( $id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		WP_CLI::error( 'Published post missing: ' . $id );
	}
	$current[ $id ] = array( 'post' => $post, 'meta' => get_post_meta( $id ) );
}
$backup = '/home/mak-s/backups/drawing-guide-20261014';
if ( ! is_dir( $backup ) ) {
	WP_CLI::error( 'Create backup directory before publication.' );
}
$snapshot = $backup . '/posts-before-' . gmdate( 'Ymd-His' ) . '.json';
if ( false === file_put_contents( $snapshot, wp_json_encode( $current, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) ) ) {
	WP_CLI::error( 'Could not save publication backup.' );
}
chmod( $snapshot, 0600 );

// Prepare the related replacements before changing any post.
$contents = array();
foreach ( array( 1389, 1391, 2444 ) as $index => $id ) {
	$pattern = '/<p\b[^>]*data-version="cd-drawing-hub-link-v1"[^>]*>.*?<\/p>/s';
	$replacement = $review['related_changes'][ $index ]['html'];
	$replacement = str_replace( '<p>', '<p data-version="cd-drawing-hub-link-v2">', $replacement );
	$contents[ $id ] = preg_replace( $pattern, $replacement, $current[ $id ]['post']->post_content, 1, $count );
	if ( 1 !== $count ) {
		WP_CLI::error( 'Expected one existing guide link in post ' . $id . '; found ' . $count );
	}
}
$sample = $current[2443]['post']->post_content;
$old = '「意外と良い」から先は、一つの商品への理解を深めて「しっかり納得」へ進む経路と、関心を周辺のセルフケアへ広げて「他もケアしたい」へ進む経路に分かれます。その後、どちらも「ずっとキープしたい」へ近づきます。';
if ( 1 !== substr_count( $sample, $old ) || 1 !== substr_count( $sample, '意外と良い → 他もケアしたい' ) ) {
	WP_CLI::error( 'Sample branch text changed since review; inspect before publication.' );
}
$sample = str_replace( $old, $review['related_changes'][3]['replacement_paragraph'], $sample );
$sample = str_replace( '意外と良い → 他もケアしたい', 'しっかり納得 → 他もケアしたい', $sample );
$sample = preg_replace( '/(<p>この作例では、.*?<\/p>)/s', '$1' . "\n" . $review['related_changes'][3]['html'], $sample, 1, $count );
if ( 1 !== $count ) {
	WP_CLI::error( 'Sample introduction changed since review.' );
}
$contents[2443] = $sample;

// SVGs are generated locally from reviewed source; no third-party SVG upload.
add_filter( 'upload_mimes', function ( $mimes ) { $mimes['svg'] = 'image/svg+xml'; return $mimes; } );
$uploaded = array();
foreach ( array( '01-axes.svg', '02-states.svg', '03-support.svg', '04-pitfalls.svg' ) as $name ) {
	$svg = file_get_contents( $root . '/assets/' . $name );
	$result = wp_upload_bits( 'cd-drawing-guide-20261014-' . $name, null, $svg );
	if ( ! empty( $result['error'] ) ) {
		WP_CLI::error( $result['error'] );
	}
	$attachment = wp_insert_attachment( array(
		'post_mime_type' => 'image/svg+xml',
		'post_title' => '描き方ガイド ' . $name,
		'post_status' => 'inherit',
	), $result['file'], 2448, true );
	if ( is_wp_error( $attachment ) ) {
		WP_CLI::error( $attachment );
	}
	$body = str_replace( 'assets/' . $name, esc_url( $result['url'] ), $body );
	$uploaded[ $name ] = array( 'id' => $attachment, 'url' => $result['url'] );
}
$css = '<style>.cd-drawing-guide figure{margin:1.8em 0;overflow-x:auto}.cd-drawing-guide figure img{display:block;width:100%;height:auto}.cd-drawing-guide figcaption{font-size:.875em;line-height:1.8;padding-top:.6em}.cd-drawing-guide .table-scroll{overflow-x:auto}.cd-drawing-guide table{min-width:640px}.cd-drawing-guide .scroll-hint{display:none}@media(max-width:700px){.cd-drawing-guide figure img{min-width:680px}.cd-drawing-guide .scroll-hint{display:block;font-size:.85em}}</style>';
$body = str_replace( '<figure>', '<figure><p class="scroll-hint">スマートフォンでは図を横にスクロールして読めます。</p>', $body );
$contents[2448] = $css . '<div class="cd-drawing-guide">' . $body . '</div>';
foreach ( $contents as $id => $content ) {
	$data = array( 'ID' => $id, 'post_content' => $content );
	if ( 2448 === $id ) {
		$data['post_title'] = $review['title'];
		$data['post_excerpt'] = $review['description'];
	}
	$result = wp_update_post( $data, true );
	if ( is_wp_error( $result ) ) {
		WP_CLI::error( $result );
	}
	if ( 2448 === $id ) {
		update_post_meta( $id, 'wp-seo-meta-description', $review['description'] );
	}
	clean_post_cache( $id );
	WP_CLI::log( 'Updated ' . $id . ' ' . get_permalink( $id ) );
}
$record = array( 'published_at' => current_time( 'c' ), 'snapshot' => $snapshot, 'assets' => $uploaded );
file_put_contents( $backup . '/publication.json', wp_json_encode( $record, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
chmod( $backup . '/publication.json', 0600 );
WP_CLI::success( 'Published reviewed drawing guide and four related WordPress pages.' );
