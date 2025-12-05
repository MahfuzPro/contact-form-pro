<?php
class CFP_Shortcode {
    public function __construct() {
        add_shortcode( 'cfp_form', array( $this, 'render_shortcode' ) );
    }


    public function render_shortcode( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts );
        
        // Check for success message
        if ( isset( $_GET['cfp_success'] ) ) {
            return '<div class="cfp-success">Thank you! Your message has been sent.</div>';
        }

        // Render Form
        ob_start();
        ?>
        <div class="cfp-form-wrapper">
            <form method="post" action="">
                <?php wp_nonce_field( 'cfp_form_action' ); ?>
                <input type="hidden" name="form_id" value="<?php echo esc_attr( $atts['id'] ); ?>">

                <div class="cfp-field">
                    <label>Name</label>
                    <input type="text" name="cfp_name" required>
                </div>
                <div class="cfp-field">
                    <label>Email</label>
                    <input type="email" name="cfp_email" required>
                </div>
                <div class="cfp-field">
                    <label>Subject</label>
                    <input type="text" name="cfp_subject">
                </div>
                <div class="cfp-field">
                    <label>Message</label>
                    <textarea name="cfp_message" required></textarea>
                </div>
                <div class="cfp-field">
                    <input type="submit" name="cfp_submit" value="Send Message">
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
new CFP_Shortcode();