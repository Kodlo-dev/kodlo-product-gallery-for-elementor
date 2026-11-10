<?php
/**
 * Metadata write failure notice for Kodlo Product Gallery for Elementor.
 *
 * Stores one user-specific warning when a shared destination write cannot finish.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'kodlo_gallery_meta_write_failed', 'kodlo_gallery_queue_meta_write_failure_notice' );
add_action( 'admin_notices', 'kodlo_gallery_render_meta_write_failure_notice', 10, 0 );

/** Returns the current site's user-meta key for write-failure feedback. */
function kodlo_gallery_get_meta_write_failure_user_meta_key() {
	return '_kodlo_gallery_meta_write_failure_' . get_current_blog_id();
}

/**
 * Queues one user-specific warning after a failed Kodlo metadata write.
 *
 * @param int $post_id Attachment or variation ID.
 */
function kodlo_gallery_queue_meta_write_failure_notice( $post_id ) {
	$user_id = get_current_user_id();
	if ( ! $user_id || ! current_user_can( 'edit_post', absint( $post_id ) ) ) {
		return;
	}

	update_user_meta(
		$user_id,
		kodlo_gallery_get_meta_write_failure_user_meta_key(),
		array(
			'created_at' => time(),
		)
	);
}

/** Displays and clears the most recent metadata write warning for this user. */
function kodlo_gallery_render_meta_write_failure_notice() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}

	$user_meta_key = kodlo_gallery_get_meta_write_failure_user_meta_key();
	$failure       = get_user_meta( $user_id, $user_meta_key, true );
	if ( ! is_array( $failure ) ) {
		return;
	}
	delete_user_meta( $user_id, $user_meta_key );

	$created_at = absint( $failure['created_at'] ?? 0 );
	if ( ! $created_at || $created_at < time() - HOUR_IN_SECONDS ) {
		return;
	}

	echo '<div class="notice notice-error is-dismissible"><p>';
	echo esc_html__( 'Kodlo could not save one gallery media change. Reopen the affected item and save it again.', 'kodlo-product-gallery-for-elementor' );
	echo '</p></div>';
}
