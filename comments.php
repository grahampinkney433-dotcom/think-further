<?php
/**
 * Comments template.
 *
 * @package ThinkFurther
 */

if ( post_password_required() ) {
	return;
}
?>

<section class="comments" id="comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments__title">
			<?php
			$tf_count = get_comments_number();
			/* translators: %s: comment count. */
			echo esc_html( sprintf( _n( '%s response', '%s responses', $tf_count, 'thinkfurther' ), number_format_i18n( $tf_count ) ) );
			?>
		</h2>

		<ol class="comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'avatar_size' => 44,
					'short_ping'  => true,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => '&larr; ' . __( 'Older', 'thinkfurther' ),
				'next_text' => __( 'Newer', 'thinkfurther' ) . ' &rarr;',
			)
		);
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="comments__closed"><?php esc_html_e( 'Comments are closed.', 'thinkfurther' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</section>
