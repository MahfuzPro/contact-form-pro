<?php
class CFP_Submission {
    public function __construct() {
        add_action( 'init', array( $this, 'handle_submission' ) );
    }

    public function handle_submission() {
        if ( ! isset( $_POST['cfp_submit'] ) ) return;

        // 1. Verify Nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'cfp_form_action' ) ) {
            wp_die( 'Security check failed' );
        }

        // 2. Validate & Sanitize
        $form_id = intval( $_POST['form_id'] );
        $name    = sanitize_text_field( $_POST['cfp_name'] );
        $email   = sanitize_email( $_POST['cfp_email'] );
        $subject = sanitize_text_field( $_POST['cfp_subject'] );
        $message = sanitize_textarea_field( $_POST['cfp_message'] );

        $errors = array();
        if ( empty( $name ) ) $errors[] = "Name is required.";
        if ( ! is_email( $email ) ) $errors[] = "Invalid email.";
        if ( empty( $message ) ) $errors[] = "Message is required.";

        if ( empty( $errors ) ) {
            // 3. Create Post
            $post_id = wp_insert_post( array(
                'post_type'   => 'cfp_submission',
                'post_title'  => $subject ? $subject : 'No Subject',
                'post_status' => 'publish',
            ));

            if ( $post_id ) {
                // 4. Store Meta
                update_post_meta( $post_id, 'cfp_name', $name );
                update_post_meta( $post_id, 'cfp_email', $email );
                update_post_meta( $post_id, 'cfp_message', $message );
                update_post_meta( $post_id, 'cfp_form_id', $form_id );

                // Redirect to avoid resubmission
                $redirect_url = add_query_arg( 'cfp_success', '1', wp_get_referer() );
                wp_safe_redirect( $redirect_url );
                exit;
            }
        }
    }


}
new CFP_Submission();