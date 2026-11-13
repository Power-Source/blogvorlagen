<?php


function nbt_get_sites_search() {
	global $wpdb, $current_site;

	if ( ! current_user_can( 'manage_network' ) ) {
		wp_send_json_error( __( 'Keine Berechtigung für die Seitensuche.', 'blog_templates' ), 403 );
	}
	check_ajax_referer( 'nbt_get_sites_search', 'nonce' );
	if ( ! isset( $_POST['term'] ) || ! is_string( $_POST['term'] ) ) {
		wp_send_json_error( __( 'Ungültiger Suchbegriff.', 'blog_templates' ), 400 );
	}
	$s = trim( wp_unslash( $_POST['term'] ) );
	if ( '' === $s ) {
		wp_send_json( array() );
	}
	$wild = '%';
	if ( false !== strpos($s, '*') ) {
		$wild = '%';
		$s = trim($s, '*');
	}

	$like_s = $wpdb->esc_like( $s );
	if ( is_subdomain_install() ) {
		$blog_s = $wild . $like_s . $wild;
		$column = 'domain';
	}
	else {
		if ( $s != trim( $current_site->path, '/' ) )
			$blog_s = $current_site->path . $like_s . $wild . '/';
		else
			$blog_s = $like_s;	

		$column = 'path';
	}
	

	
	$results = $wpdb->get_results( $wpdb->prepare(
		"SELECT blog_id, domain, path FROM {$wpdb->blogs} WHERE site_id = %d AND $column LIKE %s LIMIT 10",
		$wpdb->siteid,
		$blog_s
	) );
	if ( $wpdb->last_error ) {
		error_log( 'Neue Blog-Vorlagen site search: ' . $wpdb->last_error );
		wp_send_json_error( __( 'Die Seitensuche ist fehlgeschlagen.', 'blog_templates' ), 500 );
	}

	$returning = array();
	if ( ! empty( $results ) ) {
		foreach ( $results as $row ) {
			$details = get_blog_details( $row->blog_id );
			$returning[] = array( 
				'blog_name' => $details->blogname,
				'path' => is_subdomain_install() ? $row->domain : $row->path, 
				'blog_id' => $row->blog_id 
			);
		}
	}

	wp_send_json( $returning );
}
add_action( 'wp_ajax_nbt_get_sites_search', 'nbt_get_sites_search' );