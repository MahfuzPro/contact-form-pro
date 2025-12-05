<?php
class CFP_Admin {
    public function __construct() {
        // Add columns to admin list
        add_filter( 'manage_cfp_submission_posts_columns', array( $this, 'add_columns' ) );
        add_action( 'manage_cfp_submission_posts_custom_column', array( $this, 'render_columns' ), 10, 2 );
        
        // Filter by Form ID
        add_action( 'restrict_manage_posts', array( $this, 'filter_dropdown' ) );
        add_filter( 'parse_query', array( $this, 'filter_query' ) );

        // CSV Export
        add_action( 'manage_posts_extra_tablenav', array( $this, 'add_export_button' ) );
        add_action( 'admin_init', array( $this, 'handle_csv_export' ) );

        // Add Meta Box for viewing all details
        add_action( 'add_meta_boxes', array( $this, 'add_submission_meta_box' ) );
    }

    // 1. Add Columns (Name, Email, Form ID)
    public function add_columns( $columns ) {
        $columns['cfp_name'] = 'Name';
        $columns['cfp_email'] = 'Email';
        $columns['cfp_form'] = 'Form Source';
        return $columns;
    }

    public function render_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'cfp_name': echo esc_html( get_post_meta( $post_id, 'cfp_name', true ) ); break;
            case 'cfp_email': echo esc_html( get_post_meta( $post_id, 'cfp_email', true ) ); break;
            case 'cfp_form': 
                $form_id = get_post_meta( $post_id, 'cfp_form_id', true );
                echo $form_id ? get_the_title( $form_id ) . " (ID: $form_id)" : 'N/A';
                break;
        }
    }

    // 2. Filter Dropdown
    public function filter_dropdown() {
        global $typenow;
        if ( $typenow === 'cfp_submission' ) {
            $forms = get_posts( array( 'post_type' => 'cfp_form', 'numberposts' => -1 ) );
            $current = isset( $_GET['filter_form_id'] ) ? $_GET['filter_form_id'] : '';
            ?>
            <select name="filter_form_id">
                <option value="">All Forms</option>
                <?php foreach ( $forms as $form ) : ?>
                    <option value="<?php echo $form->ID; ?>" <?php selected( $current, $form->ID ); ?>>
                        <?php echo esc_html( $form->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php
        }
    }

    public function filter_query( $query ) {
        global $pagenow;
        if ( $pagenow === 'edit.php' && isset( $_GET['filter_form_id'] ) && $_GET['filter_form_id'] != '' && $query->query['post_type'] == 'cfp_submission' ) {
            $query->query_vars['meta_key'] = 'cfp_form_id';
            $query->query_vars['meta_value'] = $_GET['filter_form_id'];
        }
    }

    // 3. CSV Export
    public function add_export_button( $which ) {
        global $typenow;
        if ( 'cfp_submission' === $typenow && 'top' === $which ) {
            ?>
            <div class="alignleft actions">
                <form method="post" action="">
                    <input type="hidden" name="cfp_csv_export" value="1">
                    <?php wp_nonce_field( 'cfp_export_nonce', 'cfp_export_nonce_field' ); ?>
                    <input type="submit" class="button button-primary" value="Export CSV">
                </form>
            </div>
            <?php
        }
    }

    public function handle_csv_export() {
        if ( isset( $_POST['cfp_csv_export'] ) && check_admin_referer( 'cfp_export_nonce', 'cfp_export_nonce_field' ) ) {
            if ( ! current_user_can( 'manage_options' ) ) return;

            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment; filename="submissions.csv"' );
            header( 'Pragma: no-cache' );
            header( 'Expires: 0' );

            $output = fopen( 'php://output', 'w' );
            fputcsv( $output, array( 'Submission ID', 'Date', 'Name', 'Email', 'Subject', 'Message', 'Form ID' ) );

            $args = array( 'post_type' => 'cfp_submission', 'posts_per_page' => -1 );
            
            // Check if filter is active during export
            if( isset($_GET['filter_form_id']) && !empty($_GET['filter_form_id']) ) {
                $args['meta_key'] = 'cfp_form_id';
                $args['meta_value'] = $_GET['filter_form_id'];
            }

            $submissions = get_posts( $args );

            foreach ( $submissions as $sub ) {
                fputcsv( $output, array(
                    $sub->ID,
                    $sub->post_date,
                    get_post_meta( $sub->ID, 'cfp_name', true ),
                    get_post_meta( $sub->ID, 'cfp_email', true ),
                    $sub->post_title,
                    get_post_meta( $sub->ID, 'cfp_message', true ),
                    get_post_meta( $sub->ID, 'cfp_form_id', true ),
                ));
            }
            fclose( $output );
            exit;
        }
    }

    
    // 4. Add Meta Box to the single submission view
    public function add_submission_meta_box() {
        add_meta_box(
            'cfp_submission_details',
            'Submission Details',
            array( $this, 'render_submission_meta_box' ),
            'cfp_submission', // Only show on cfp_submission post type
            'normal',
            'high'
        );
    }
    // Render the content of the Submission Details Meta Box
    public function render_submission_meta_box( $post ) {
        // Retrieve all necessary meta fields
        $name    = get_post_meta( $post->ID, 'cfp_name', true );
        $email   = get_post_meta( $post->ID, 'cfp_email', true );
        $message = get_post_meta( $post->ID, 'cfp_message', true );
        $form_id = get_post_meta( $post->ID, 'cfp_form_id', true );
        $subject = get_the_title( $post->ID ); // The title is the subject

        $form_title = $form_id ? get_the_title( $form_id ) : 'N/A';
        ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th><label>Name</label></th>
                    <td><?php echo esc_html( $name ); ?></td>
                </tr>
                <tr>
                    <th><label>Email</label></th>
                    <td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td>
                </tr>
                <tr>
                    <th><label>Subject</label></th>
                    <td><?php echo esc_html( $subject ); ?></td>
                </tr>
                <tr>
                    <th><label>Message</label></th>
                    <td><?php echo esc_textarea( $message ); ?></td>
                </tr>
                <tr>
                    <th><label>Form Source</label></th>
                    <td><?php echo esc_html( $form_title ); ?> (ID: <?php echo esc_html( $form_id ); ?>)</td>
                </tr>
            </tbody>
        </table>
        <?php
    }

}
new CFP_Admin();