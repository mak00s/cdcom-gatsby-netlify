<?php
/** Explicit publication of the user-approved overview revision. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit( "WP CLI required.\n" );
}
$root = __DIR__;
$data = json_decode( file_get_contents( $root . '/review.json' ), true );
$post = get_post( 1500 );
if ( ! $post || 'publish' !== $post->post_status || $post->post_title !== $data['title'] ) {
	WP_CLI::error( 'Published overview/title does not match the reviewed target.' );
}
$expected_hash = 'e3cf0a161515a30dc78844bbf6361303d1bdace766e7382e1d812fd10f5260ea';
if ( hash( 'sha256', $post->post_content ) !== $expected_hash ) {
	WP_CLI::error( 'Overview body changed since the reviewed baseline. Inspect the diff before publishing.' );
}
$backup = '/home/mak-s/backups/overview-20261014';
if ( ! is_dir( $backup ) ) {
	WP_CLI::error( 'Create the backup directory first.' );
}
$snapshot = $backup . '/post-before-' . gmdate( 'Ymd-His' ) . '.json';
if ( false === file_put_contents( $snapshot, wp_json_encode( array( 'post' => $post, 'meta' => get_post_meta(1500) ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) ) ) {
	WP_CLI::error( 'Could not save the post backup.' );
}
chmod( $snapshot, 0600 );
$body = file_get_contents( $root . '/article.html' );
$definition = 'コンセプトダイアグラムは、顧客の心理・態度の変化を2つの軸で図解し、その変化を促す体験・施策と評価指標を結びつける図解手法です。';
if ( 1 !== substr_count( $body, $definition ) || false === strpos( $body, 'id="official-summary"' ) || false === strpos( $body, 'id="concept-diagram-elements"' ) ) {
	WP_CLI::error( 'Reviewed definition or stable anchors are missing.' );
}
add_filter( 'upload_mimes', function ( $mimes ) { $mimes['svg'] = 'image/svg+xml'; return $mimes; } );
$svg = file_get_contents( $root . '/assets/overview.svg' );
$upload = wp_upload_bits( 'cd-overview-20261014.svg', null, $svg );
if ( ! empty( $upload['error'] ) ) {
	WP_CLI::error( $upload['error'] );
}
$attachment = wp_insert_attachment( array(
	'post_mime_type' => 'image/svg+xml',
	'post_title' => 'コンセプトダイアグラムの読み方：ヘアケア事業',
	'post_status' => 'inherit',
), $upload['file'], 1500, true );
if ( is_wp_error( $attachment ) ) {
	WP_CLI::error( $attachment );
}
update_post_meta( $attachment, '_wp_attachment_image_alt', 'ヘアケア事業を題材に、2軸、5つの中間状態、分岐と統合、施策と評価指標の対応を示す説明用のコンセプトダイアグラム' );
$body = str_replace( 'assets/overview.svg', esc_url( $upload['url'] ), $body );
$body = str_replace( '<figure>', '<figure><p class="scroll-hint">スマートフォンでは図を横にスクロールして読めます。</p>', $body );
$css = '<style>.cd-overview-guide figure{margin:1.8em 0;overflow-x:auto}.cd-overview-guide figure img{display:block;width:100%;height:auto}.cd-overview-guide figcaption{font-size:.875em;line-height:1.8;padding-top:.6em}.cd-overview-guide .table-scroll{overflow-x:auto}.cd-overview-guide table{min-width:640px}.cd-overview-guide .scroll-hint{display:none}@media(max-width:700px){.cd-overview-guide figure img{min-width:680px}.cd-overview-guide .scroll-hint{display:block;font-size:.85em}}</style>';
$result = wp_update_post( array(
	'ID' => 1500,
	'post_content' => $css . '<div class="cd-overview-guide">' . $body . '</div>',
	'post_excerpt' => $data['description'],
), true );
if ( is_wp_error( $result ) ) {
	WP_CLI::error( $result );
}
update_post_meta( 1500, 'wp-seo-meta-description', $data['description'] );
clean_post_cache(1500);
if ( get_post(1500)->post_title !== $data['title'] ) {
	WP_CLI::error( 'The preserved title failed verification.' );
}
$record = array(
	'published_at' => current_time('c'),
	'url' => get_permalink(1500),
	'title' => get_post(1500)->post_title,
	'previous_body_sha256' => $expected_hash,
	'published_body_sha256' => hash('sha256', get_post_field('post_content',1500)),
	'snapshot' => $snapshot,
	'attachment_id' => $attachment,
	'image_url' => $upload['url'],
);
file_put_contents( $backup . '/publication.json', wp_json_encode( $record, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
chmod( $backup . '/publication.json', 0600 );
WP_CLI::success( 'Published overview with the existing title and reviewed definition.' );
