<?php
$post_id = 266;
$order   = array( 0, 1, 5, 2, 6, 3, 4, 7, 8 );
$meta = get_post_meta( $post_id );
$groups = array();
$layout_arr = null;
foreach ( $meta as $key => $vals ) {
	$val = maybe_unserialize( $vals[0] );
	if ( 'flexible_content_blocks' === $key ) { $layout_arr = $val; continue; }
	if ( preg_match( '/^(_?)flexible_content_blocks_(\d+)_(.+)$/', $key, $m ) ) {
		$groups[ (int) $m[2] ][ $key ] = $val;
	}
}
if ( ! is_array( $layout_arr ) || count( $layout_arr ) !== count( $order ) ) { echo "ABORT mismatch\n"; exit( 1 ); }
file_put_contents( WP_CONTENT_DIR . '/uploads/snag-266-backup.json', json_encode( array( 'layouts' => $layout_arr, 'groups' => $groups ) ) );
foreach ( $groups as $idx => $keys ) { foreach ( $keys as $k => $v ) { delete_post_meta( $post_id, $k ); } }
$new_layouts = array();
foreach ( $order as $new_idx => $old_idx ) {
	$new_layouts[ $new_idx ] = $layout_arr[ $old_idx ];
	if ( empty( $groups[ $old_idx ] ) ) { continue; }
	foreach ( $groups[ $old_idx ] as $k => $v ) {
		$new_key = preg_replace( '/^(_?)flexible_content_blocks_\d+_/', '${1}flexible_content_blocks_' . $new_idx . '_', $k );
		update_post_meta( $post_id, $new_key, wp_slash( $v ) );
	}
}
update_post_meta( $post_id, 'flexible_content_blocks', wp_slash( $new_layouts ) );
clean_post_cache( $post_id );
wp_cache_flush();
$layouts = get_post_meta( $post_id, 'flexible_content_blocks', true );
foreach ( $layouts as $i => $l ) {
	$h = get_post_meta( $post_id, "flexible_content_blocks_{$i}_heading", true );
	echo "$i $l | " . trim( (string) $h ) . "\n";
}
