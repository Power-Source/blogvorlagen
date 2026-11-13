<?php

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! is_multisite() || wp_get_environment_type() !== 'local' ) {
	throw new RuntimeException( 'Run this test with WP-CLI on a local Multisite installation.' );
}

require_once dirname( __DIR__ ) . '/blogtemplates.php';
require_once dirname( __DIR__ ) . '/nbt-api.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/ms.php';

$site_ids = array();
$template_id = 0;
$original_blog_id = get_current_blog_id();
$failure = null;
$assert = static function ( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
	WP_CLI::log( 'PASS: ' . $message );
};
$die_handler = static function () {
	return static function ( $message ) {
		throw new RuntimeException( wp_strip_all_tags( $message ) );
	};
};
add_filter( 'wp_die_handler', $die_handler );
add_filter( 'wp_die_cli_handler', $die_handler );

try {
	$user = wp_get_current_user();
	$assert( $user->exists() && is_super_admin( $user->ID ), 'Test runs as a network administrator (--user)' );
	nbt_activate_plugin();
	// Do not apply any existing network-wide default template to the fixtures.
	global $blog_templates;
	$initialization_priority = has_action( 'wp_initialize_site', array( $blog_templates, 'set_blog_defaults' ) );
	if ( false !== $initialization_priority ) {
		remove_action( 'wp_initialize_site', array( $blog_templates, 'set_blog_defaults' ), $initialization_priority );
	}
	$network = get_network();
	$slug = 'nbt-copy-test-' . wp_generate_password( 8, false, false );
	foreach ( array( 'source', 'target' ) as $role ) {
		$id = wpmu_create_blog(
			is_subdomain_install() ? $slug . '-' . $role . '.' . $network->domain : $network->domain,
			is_subdomain_install() ? '/' : $network->path . $slug . '-' . $role . '/',
			'NBT copy test ' . $role,
			$user->ID,
			array( 'public' => 0 ),
			$network->id
		);
		if ( is_wp_error( $id ) ) {
			throw new RuntimeException( $id->get_error_message() );
		}
		$site_ids[] = $id;
	}
	list( $source_id, $target_id ) = $site_ids;
	switch_to_blog( $source_id );
	$source_home = home_url();
	global $wpdb;
	$source_prefix = $wpdb->prefix;
	$post_id = wp_insert_post( array(
		'post_title' => 'NBT fixture post',
		'post_content' => '<a href="' . $source_home . '/fixture/">Internal link</a>',
		'post_status' => 'publish',
		'post_author' => $user->ID,
	), true );
	if ( is_wp_error( $post_id ) ) {
		throw new RuntimeException( $post_id->get_error_message() );
	}
	$page_id = wp_insert_post( array( 'post_title' => 'NBT fixture page', 'post_type' => 'page', 'post_status' => 'publish' ), true );
	if ( is_wp_error( $page_id ) ) {
		throw new RuntimeException( $page_id->get_error_message() );
	}
	$excluded_page_id = wp_insert_post( array( 'post_title' => 'NBT excluded page', 'post_type' => 'page', 'post_status' => 'publish' ), true );
	if ( is_wp_error( $excluded_page_id ) ) {
		throw new RuntimeException( $excluded_page_id->get_error_message() );
	}
	update_post_meta( $excluded_page_id, 'nbt_fixture', 'Do not copy excluded metadata' );
	update_post_meta( $page_id, 'nbt_fixture', 'Selected page metadata' );
	update_option( 'nbt_fixture', array( 'nested' => array( 'prefix' => $source_prefix . 'capabilities' ) ) );
	$upload = wp_upload_bits( 'nbt-fixture.txt', null, 'NBT media fixture' );
	if ( $upload['error'] ) {
		throw new RuntimeException( $upload['error'] );
	}
	$attachment_id = wp_insert_attachment( array(
		'post_title' => 'NBT fixture media',
		'post_mime_type' => 'text/plain',
		'guid' => $upload['url'],
	), $upload['file'], $post_id, true );
	if ( is_wp_error( $attachment_id ) ) {
		throw new RuntimeException( $attachment_id->get_error_message() );
	}
	$source_content = '<a href="' . $source_home . '/fixture/">Internal link</a><a href="' . $upload['url'] . '">Media</a>';
	wp_update_post( array( 'ID' => $post_id, 'post_content' => $source_content ) );
	$draft_id = wp_insert_post( array( 'post_title' => 'NBT fixture draft', 'post_content' => $upload['url'], 'post_status' => 'draft' ), true );
	if ( is_wp_error( $draft_id ) ) {
		throw new RuntimeException( $draft_id->get_error_message() );
	}
	$comment_id = wp_insert_comment( array( 'comment_post_ID' => $post_id, 'comment_content' => 'NBT fixture comment', 'comment_approved' => 1 ) );
	add_comment_meta( $comment_id, 'nbt_fixture', 'comment metadata' );
	$excluded_comment_id = wp_insert_comment( array( 'comment_post_ID' => $excluded_page_id, 'comment_content' => 'NBT excluded comment', 'comment_approved' => 1 ) );
	add_comment_meta( $excluded_comment_id, 'nbt_fixture', 'Do not copy excluded comment metadata' );
	$menu_id = wp_create_nav_menu( 'NBT fixture menu' );
	if ( is_wp_error( $menu_id ) ) {
		throw new RuntimeException( $menu_id->get_error_message() );
	}
	$menu_item_id = wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title' => 'NBT internal link',
		'menu-item-url' => $source_home . '/fixture/',
		'menu-item-type' => 'custom',
		'menu-item-status' => 'publish',
	) );
	if ( is_wp_error( $menu_item_id ) ) {
		throw new RuntimeException( $menu_item_id->get_error_message() );
	}
	set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu_id ) );
	set_theme_mod( 'nbt_fixture_media', $upload['url'] );
	$empty_menu_id = wp_create_nav_menu( 'NBT empty menu' );
	if ( is_wp_error( $empty_menu_id ) ) {
		throw new RuntimeException( $empty_menu_id->get_error_message() );
	}
	$settings = array(
		'to_copy' => array( 'settings', 'posts', 'pages', 'terms', 'menus', 'files' ),
		'post_category' => array( 'all-categories' ),
		'pages_ids' => array( $page_id ),
		'block_posts_pages' => false,
		'update_dates' => false,
	);
	restore_current_blog();
	$template_id = nbt_get_model()->add_template( $source_id, 'NBT isolated test', '', $settings );
	$assert( $template_id > 0, 'Test template created' );
	$copy_args = $settings;
	$copy_args['template_id'] = $template_id;
	$copy_args['to_copy'] = array_fill_keys( $settings['to_copy'], true );
	switch_to_blog( $target_id );
	update_option( 'nbt_target_only', 'Remove this cached option' );
	restore_current_blog();
	$force_filesystem_error = static function ( $skip_list ) {
		global $wp_filesystem;
		$wp_filesystem = new class( false ) extends WP_Filesystem_Direct {
			public function dirlist( $path, $include_hidden = true, $recursive = false ) {
				return false;
			}
		};
		return $skip_list;
	};
	add_filter( 'nbt_copy_files_skip_list', $force_filesystem_error );
	$file_error_reported = false;
	try {
		nbt_api_copy_contents_to_new_blog( $source_id, $target_id, $user->ID, $copy_args );
	} catch ( RuntimeException $error ) {
		$file_error_reported = $error->getMessage() === __( 'Directory listing failed.' );
	} finally {
		remove_filter( 'nbt_copy_files_skip_list', $force_filesystem_error );
	}
	$assert( $file_error_reported, 'Filesystem copy failure is reported, not ignored' );
	$assert( get_current_blog_id() === $original_blog_id, 'Failed copy restores original blog context' );
	nbt_api_copy_contents_to_new_blog( $source_id, $target_id, $user->ID, $copy_args );
	$assert( get_current_blog_id() === $original_blog_id, 'Copy restores original blog context' );
	$site_args = array( 'user_id' => $user->ID, 'options' => array( 'blog_template' => $template_id ) );
	add_filter( 'nbt_copy_files_skip_list', $force_filesystem_error );
	$file_error_reported = false;
	try {
		$blog_templates->set_blog_defaults( get_site( $target_id ), $site_args );
	} catch ( RuntimeException $error ) {
		$file_error_reported = $error->getMessage() === __( 'Directory listing failed.' );
	} finally {
		remove_filter( 'nbt_copy_files_skip_list', $force_filesystem_error );
	}
	$assert( $file_error_reported && get_current_blog_id() === $original_blog_id,
		'Site initialization callback also restores context on failure' );
	$blog_templates->set_blog_defaults( get_site( $target_id ), $site_args );
	$assert( get_current_blog_id() === $original_blog_id, 'Saved template applies through site initialization callback' );
	switch_to_blog( $target_id );
	$target_home = home_url();
	$target_upload = wp_upload_dir();
	$target_file_url = str_replace( $source_id . '/', $target_id . '/', $upload['url'] );
	$checks = array(
		'Post copied and internal/media URLs replaced' => get_post( $post_id )->post_content === '<a href="' . $target_home . '/fixture/">Internal link</a><a href="' . $target_file_url . '">Media</a>',
		'Page copied' => get_post( $page_id )->post_title === 'NBT fixture page',
		'Only selected page metadata copied' => get_post_meta( $page_id, 'nbt_fixture', true ) === 'Selected page metadata' &&
			get_post( $excluded_page_id ) === null && ! metadata_exists( 'post', $excluded_page_id, 'nbt_fixture' ),
		'Draft media URL points to target' => get_post( $draft_id )->post_content === $target_file_url,
		'Nested serialized option remains readable' => get_option( 'nbt_fixture' ) === array( 'nested' => array( 'prefix' => $wpdb->prefix . 'capabilities' ) ),
		'Removed target option does not survive in cache' => get_option( 'nbt_target_only' ) === false,
		'Menu item URL points to target' => get_post_meta( $menu_item_id, '_menu_item_url', true ) === $target_home . '/fixture/',
		'Menu location preserved' => get_nav_menu_locations()['primary'] === $menu_id,
		'Empty menu copied without invalid SQL' => wp_get_nav_menu_object( $empty_menu_id ) !== false,
		'Comment metadata copied' => get_comment_meta( $comment_id, 'nbt_fixture', true ) === 'comment metadata',
		'Comments on excluded pages are not copied' => get_comment( $excluded_comment_id ) === null &&
			! metadata_exists( 'comment', $excluded_comment_id, 'nbt_fixture' ),
		'Attachment points to target uploads' => str_starts_with( wp_get_attachment_url( $attachment_id ), $target_upload['baseurl'] . '/' ),
		'Attachment file copied' => is_file( get_attached_file( $attachment_id ) ) && file_get_contents( get_attached_file( $attachment_id ) ) === 'NBT media fixture',
		'Theme media URL points to target uploads' => str_starts_with( get_theme_mod( 'nbt_fixture_media' ), $target_upload['baseurl'] . '/' ),
		'Target site identity preserved' => get_option( 'blogname' ) === 'NBT copy test target',
	);
	foreach ( $checks as $message => $condition ) {
		WP_CLI::log( ( $condition ? 'PASS: ' : 'FAIL: ' ) . $message );
	}
	$assert( ! in_array( false, $checks, true ), 'All copied content checks pass' );
	restore_current_blog();
	switch_to_blog( $source_id );
	$assert( get_theme_mod( 'nbt_fixture_media' ) === $upload['url'] &&
		get_post( $post_id )->post_content === $source_content,
		'Source content remains unchanged' );
	restore_current_blog();
	$copier = new class( $source_id, $target_id, $user->ID, array() ) extends NBT_Template_copier {
		public function replace_fixture( $value ) {
			return $this->replace_option_prefix( $value, 'wp_9_', 'wp_10_' );
		}
	};
	$serialized = maybe_serialize( $copier->replace_fixture( array( 'wp_9_key' => array( 'wp_9_capabilities', false ) ) ) );
	$assert( maybe_unserialize( $serialized ) === array( 'wp_10_key' => array( 'wp_10_capabilities', false ) ),
		'Different-length prefixes preserve nested serialized values and keys' );
} catch ( Throwable $error ) {
	$failure = $error;
} finally {
	while ( ms_is_switched() ) {
		restore_current_blog();
	}
	if ( $template_id ) {
		nbt_get_model()->delete_template( $template_id );
	}
	foreach ( array_reverse( $site_ids ) as $id ) {
		wpmu_delete_blog( $id, true );
	}
	if ( isset( $initialization_priority ) && false !== $initialization_priority ) {
		add_action( 'wp_initialize_site', array( $blog_templates, 'set_blog_defaults' ), $initialization_priority, 2 );
	}
	remove_filter( 'wp_die_handler', $die_handler );
	remove_filter( 'wp_die_cli_handler', $die_handler );
}
if ( $failure ) {
	WP_CLI::error( $failure->getMessage() );
}
WP_CLI::success( 'Copy integration test passed; test websites and template removed.' );
