<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    /**
     * Output for dashboard page
     */
    function csv2wp_dashboard_page() {
        if ( ! current_user_can( get_option( 'csv2wp_import_role' ) ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'csv-to-wp' ) );
        }

        $posted_delimiter = false;

        if ( current_user_can( get_option( 'csv2wp_import_role' ) ) && isset( $_POST[ 'csv2wp_upload_csv_nonce' ] ) ) {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'csv2wp_upload_csv_nonce' ] ) ), 'csv2wp-upload-csv-nonce' ) ) {
                CSV2WP::csv2wp_errors()->add( 'error_nonce_no_match', esc_html__( 'Something went wrong. Please try again.', 'csv-to-wp' ) );
            } else {
                // echo '<pre>'; var_dump($_FILES); echo '</pre>'; exit;
                if ( isset( $_FILES[ 'csv2wp_upload' ][ 'name' ] ) ) {
                    $file_name   = sanitize_file_name( $_FILES[ 'csv2wp_upload' ][ 'name' ] );
                    $target_file = sprintf( '%s/%s', esc_html( csv2wp_get_upload_folder() ), basename( $file_name ) );
                    // echo '<pre>'; var_dump($target_file); echo '</pre>'; exit;

                    // phpcs:ignore Generic.PHP.ForbiddenFunctions.Found
                    if ( isset( $_FILES[ 'csv2wp_upload' ][ 'tmp_name' ] ) ) {
                        if ( move_uploaded_file( sanitize_text_field( wp_unslash( $_FILES[ 'csv2wp_upload' ][ 'tmp_name' ] ) ), $target_file ) ) {
                            // file uploaded succeeded
                            do_action( 'csv2wp_successful_csv_upload' );
                            $message = sprintf( __( 'File %1$s is successfully uploaded and now shows under "%2$s."', 'csv-to-wp' ), $file_name, esc_html__( 'Handle a csv file', 'csv-to-wp' ) );
                            CSV2WP::csv2wp_errors()->add( 'success_file_uploaded', $message );

                        } else {
                            // file upload failed
                            CSV2WP::csv2wp_errors()->add( 'error_file_uploaded', esc_html( __( 'Upload failed. Please try again.', 'csv-to-wp' ) ) );
                        }
                    }
                }

                $posted_delimiter = ( isset( $_POST[ 'csv2wp_delimiter' ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'csv2wp_delimiter' ] ) ) : false;
            }
        }

        $show_raw         = ( defined( 'LOCALHOST' ) && LOCALHOST == 1 ) ? true : false;
        $import_options   = [
            'table'    => 'Database table',
            'postmeta' => 'Post meta',
            'usermeta' => 'User meta',
        ];

        ?>

        <div class="wrap csv2wp">
            <div id="icon-options-general" class="icon32"><br/></div>

            <h2>CSV to WP - <?php esc_html_e( 'Dashboard', 'csv-to-wp' ); ?></h2>

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
