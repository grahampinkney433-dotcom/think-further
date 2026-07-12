<?php
/**
 * Search form.
 *
 * @package ThinkFurther
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="tf-search-field"><?php esc_html_e( 'Search for:', 'thinkfurther' ); ?></label>
	<input type="search" id="tf-search-field" class="search-form__input" placeholder="<?php esc_attr_e( 'Search insights…', 'thinkfurther' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
	<button type="submit" class="btn btn--solid"><?php esc_html_e( 'Search', 'thinkfurther' ); ?></button>
</form>
