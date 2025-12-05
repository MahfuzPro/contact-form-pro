<?php
/* *
 * Plugin Name: Contact Form Pro
 * Plugin URI: https://example.com/contact-form-pro
 * Description: Assignment 1 - Contact Form with submission as CPT.
 * Version: 1.0.0
 * Author: Mahfuz Khan
 * License: GPL v2 or later
 * Text Domain: wp-secure-forms-demo
 */


// Prevent direct access
defined('ABSPATH') or exit();

// Define plugin constants
define( 'CFP_PATH', plugin_dir_path( __FILE__ ) );
define( 'CFP_URL', plugin_dir_url( __FILE__ ) );

// Define the main class to manage the plugin
class CFP_Plugin {

    /**
     * Constructor. Initializes the plugin by setting up hooks and loading components.
     */
    public function __construct() {
        // Load dependencies/components
        $this->load_dependencies();

        // Setup asset enqueuing hooks
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Includes all necessary component classes.
     * The require_once calls remain procedural, but they load classes
     * which will then be instantiated elsewhere (or could be instantiated here).
     */
    private function load_dependencies() {
        // The paths CFP_PATH and CFP_URL are assumed to be defined elsewhere (e.g., in the main plugin file)
        require_once CFP_PATH . 'includes/class-cfp-cpt.php';
        require_once CFP_PATH . 'includes/class-cfp-shortcode.php';
        require_once CFP_PATH . 'includes/class-cfp-admin.php';
        require_once CFP_PATH . 'includes/class-cfp-submission.php';

        // Optional: Instantiate component classes here if they don't auto-run on include
        // new CFP_CPT();
        // new CFP_Shortcode();
        // new CFP_Admin();
        // new CFP_Submission();
    }

    /**
     * Enqueues frontend scripts and styles.
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style( 'cfp-frontend', CFP_URL . 'assets/css/frontend.css' );
    }

    /**
     * Enqueues admin scripts and styles.
     */
    public function enqueue_admin_assets() {
        wp_enqueue_style( 'cfp-admin', CFP_URL . 'assets/css/admin.css' );
    }
}

// Instantiate the main class to run the plugin
new CFP_Plugin();