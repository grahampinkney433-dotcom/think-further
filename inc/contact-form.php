<?php
/**
 * Native contact form handler (no plugin dependency).
 *
 * @package ThinkFurther
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the contact form.
 */
function tf_contact_form() {
	$status = isset( $_GET['tf-contact'] ) ? sanitize_key( wp_unslash( $_GET['tf-contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<?php if ( 'sent' === $status ) : ?>
		<div class="form-notice form-notice--success" role="status">
			<p><strong><?php esc_html_e( 'Message received.', 'thinkfurther' ); ?></strong> <?php esc_html_e( 'We read everything personally and will reply within one working day.', 'thinkfurther' ); ?></p>
		</div>
	<?php elseif ( 'error' === $status ) : ?>
		<div class="form-notice form-notice--error" role="alert">
			<p><?php esc_html_e( 'Something went wrong sending your message. Please email us directly instead.', 'thinkfurther' ); ?></p>
		</div>
	<?php endif; ?>

	<form class="contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<input type="hidden" name="action" value="tf_contact">
		<?php wp_nonce_field( 'tf_contact_submit', 'tf_contact_nonce' ); ?>
		<p class="contact-form__hp" aria-hidden="true">
			<label for="tf-website">Website</label>
			<input type="text" id="tf-website" name="tf_website" tabindex="-1" autocomplete="off">
		</p>

		<div class="contact-form__row">
			<div class="contact-form__field">
				<label for="tf-name"><?php esc_html_e( 'Name', 'thinkfurther' ); ?></label>
				<input type="text" id="tf-name" name="tf_name" required autocomplete="name">
			</div>
			<div class="contact-form__field">
				<label for="tf-email"><?php esc_html_e( 'Work email', 'thinkfurther' ); ?></label>
				<input type="email" id="tf-email" name="tf_email" required autocomplete="email">
			</div>
		</div>

		<div class="contact-form__row">
			<div class="contact-form__field">
				<label for="tf-company"><?php esc_html_e( 'Company', 'thinkfurther' ); ?></label>
				<input type="text" id="tf-company" name="tf_company" autocomplete="organization">
			</div>
			<div class="contact-form__field">
				<label for="tf-interest"><?php esc_html_e( 'What are you exploring?', 'thinkfurther' ); ?></label>
				<select id="tf-interest" name="tf_interest">
					<option value="AI consultancy &amp; transformation"><?php esc_html_e( 'AI consultancy & transformation', 'thinkfurther' ); ?></option>
					<option value="AI search visibility"><?php esc_html_e( 'AI search visibility', 'thinkfurther' ); ?></option>
					<option value="Both / not sure yet"><?php esc_html_e( 'Both / not sure yet', 'thinkfurther' ); ?></option>
				</select>
			</div>
		</div>

		<div class="contact-form__field">
			<label for="tf-message"><?php esc_html_e( 'Tell us where you are with AI', 'thinkfurther' ); ?></label>
			<textarea id="tf-message" name="tf_message" rows="6" required></textarea>
		</div>

		<button type="submit" class="btn btn--solid btn--lg">
			<?php esc_html_e( 'Send message', 'thinkfurther' ); ?>
			<span aria-hidden="true">&rarr;</span>
		</button>
	</form>
	<?php
}

/**
 * Handle contact form submission.
 */
function tf_handle_contact_form() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$redirect = remove_query_arg( 'tf-contact', $redirect );

	if (
		! isset( $_POST['tf_contact_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['tf_contact_nonce'] ) ), 'tf_contact_submit' )
	) {
		wp_safe_redirect( add_query_arg( 'tf-contact', 'error', $redirect ) );
		exit;
	}

	// Honeypot: silently accept bot submissions without sending.
	if ( ! empty( $_POST['tf_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'tf-contact', 'sent', $redirect ) );
		exit;
	}

	$name     = isset( $_POST['tf_name'] ) ? sanitize_text_field( wp_unslash( $_POST['tf_name'] ) ) : '';
	$email    = isset( $_POST['tf_email'] ) ? sanitize_email( wp_unslash( $_POST['tf_email'] ) ) : '';
	$company  = isset( $_POST['tf_company'] ) ? sanitize_text_field( wp_unslash( $_POST['tf_company'] ) ) : '';
	$interest = isset( $_POST['tf_interest'] ) ? sanitize_text_field( wp_unslash( $_POST['tf_interest'] ) ) : '';
	$message  = isset( $_POST['tf_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tf_message'] ) ) : '';

	if ( ! $name || ! is_email( $email ) || ! $message ) {
		wp_safe_redirect( add_query_arg( 'tf-contact', 'error', $redirect ) );
		exit;
	}

	$to      = sanitize_email( tf_option( 'tf_contact_email' ) );
	$to      = $to ? $to : get_option( 'admin_email' );
	$subject = sprintf( '[thinkfurther.ai] Enquiry from %s', $name );

	$body  = "Name: {$name}\n";
	$body .= "Email: {$email}\n";
	$body .= "Company: {$company}\n";
	$body .= "Interest: {$interest}\n\n";
	$body .= "Message:\n{$message}\n";

	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

	$sent = wp_mail( $to, $subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'tf-contact', $sent ? 'sent' : 'error', $redirect ) );
	exit;
}
add_action( 'admin_post_tf_contact', 'tf_handle_contact_form' );
add_action( 'admin_post_nopriv_tf_contact', 'tf_handle_contact_form' );
