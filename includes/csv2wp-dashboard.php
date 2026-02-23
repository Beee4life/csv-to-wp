<?php
    /**
     * Output for dashboard page
     */
    function csv2wp_dashboard_page() {
        if ( ! current_user_can( get_option( 'csv2wp_import_role' ) ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'csv2wp' ) );
        }

        $posted_delimiter = ( isset( $_POST[ 'csv2wp_delimiter' ] ) ) ? $_POST[ 'csv2wp_delimiter' ] : false;
        $show_raw         = ( defined( 'LOCALHOST' ) && LOCALHOST == 1 ) ? true : false;
        $import_options   = [
            'table'    => 'Database table',
            'postmeta' => 'Post meta',
            'usermeta' => 'User meta',
        ];

        ?>

        <div class="wrap csv2wp">
            <div id="icon-options-general" class="icon32"><br/></div>

            <h2>CSV to WP - <?php esc_html_e( 'Dashboard', 'csv2wp' ); ?></h2>

            <?php CSV2WP::csv2wp_show_admin_notices(); ?>

            <?php echo CSV2WP::csv2wp_admin_menu(); ?>

            <div class="admin_left">
                <div class="content">
                    <?php include 'csv2wp-file-upload.php'; ?>
                    <?php include 'csv2wp-file-handling.php'; ?>
                    <?php include 'csv2wp-raw-input.php'; ?>
                </div>
            </div>
        </div>

<?php }
