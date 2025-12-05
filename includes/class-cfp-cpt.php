<?php
class CFP_CPT {
    public function __construct() {
        add_action( 'init', array( $this, 'register_cpts' ) );
    }

    public function register_cpts() {
        // 1. Form CPT
        register_post_type( 'cfp_form', array(
            'labels' => array( 'name' => 'Forms', 'singular_name' => 'Form' ),
            'public' => false,
            'show_ui' => true,
            'supports' => array( 'title' ), // Title is the Form Name
            'menu_icon' => 'dashicons-feedback',
        ));

        // 2. Submission CPT
        register_post_type( 'cfp_submission', array(
            'labels' => array( 'name' => 'Submissions', 'singular_name' => 'Submission' ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=cfp_form', // Makes it a submenu of Forms
            'supports' => array( 'title' ), // Title will be the Subject
            'capabilities' => array( 'create_posts' => 'do_not_allow' ), // Users can't create submissions in Admin
            'map_meta_cap' => true,
        ));
    }
}
new CFP_CPT();