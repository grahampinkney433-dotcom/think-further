<?php
/**
 * Case study card for grids.
 *
 * @package ThinkFurther
 */

$tf_client = get_post_meta( get_the_ID(), '_tf_client', true );
$tf_sector = get_post_meta( get_the_ID(), '_tf_sector', true );
$tf_result = get_post_meta( get_the_ID(), '_tf_result', true );
?>
<article <?php post_class( 'case-card reveal' ); ?>>
	<a class="case-card__link" href="<?php the_permalink(); ?>">
		<div class="case-card__meta">
			<?php if ( $tf_client ) : ?>
				<span class="case-card__client"><?php echo esc_html( $tf_client ); ?></span>
			<?php endif; ?>
			<?php if ( $tf_sector ) : ?>
				<span class="case-card__sector"><?php echo esc_html( $tf_sector ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( $tf_result ) : ?>
			<p class="case-card__result"><?php echo esc_html( $tf_result ); ?></p>
		<?php endif; ?>

		<h3 class="case-card__title"><?php the_title(); ?></h3>

		<?php if ( has_excerpt() ) : ?>
			<p class="case-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
		<?php endif; ?>

		<span class="case-card__more"><?php esc_html_e( 'Read the case study', 'thinkfurther' ); ?> <span aria-hidden="true">&rarr;</span></span>
	</a>
</article>
