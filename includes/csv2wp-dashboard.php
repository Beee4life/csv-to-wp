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
                if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                }

                if ( isset( $_FILES[ 'csv2wp_upload' ] ) && ! empty( $_FILES[ 'csv2wp_upload' ][ 'name' ] ) ) {
                    $file_name = sanitize_file_name( $_FILES[ 'csv2wp_upload' ][ 'name' ] );
                    $file_ext  = pathinfo( $file_name, PATHINFO_EXTENSION );

                    if ( 'csv' !== strtolower( $file_ext ) ) {
                        wp_die( esc_html__( 'Only CSV files are allowed.', 'csv-to-wp' ) );
                    }

                    $static_custom_dir  = csv2wp_get_upload_folder();
                    $static_folder_name = basename( $static_custom_dir );

                    $custom_dir_callback = function( $uploads ) use ( $static_custom_dir, $static_folder_name ) {
                        $uploads[ 'path' ]    = $static_custom_dir;
                        $uploads[ 'basedir' ] = $static_custom_dir;
                        $uploads[ 'url' ]     = $uploads[ 'baseurl' ] . '/' . $static_folder_name;
                        $uploads[ 'baseurl' ] = $uploads[ 'baseurl' ] . '/' . $static_folder_name;
                        $uploads[ 'subdir' ]  = '';

                        return $uploads;
                    };

                    add_filter( 'upload_dir', $custom_dir_callback );

                    $upload_overrides = array(
                        'test_form' => false,
                        'mimes'     => array( 'csv' => 'text/csv' ),
                    );

                    $movefile = wp_handle_upload( $uploaded_file, $upload_overrides );

                    remove_filter( 'upload_dir', $custom_dir_callback );

                    if ( $movefile && ! isset( $movefile['error'] ) ) {
                        // SUCCESS: Path inside your custom folder
                        $target_file = $movefile[ 'file' ];

                        do_action( 'csv2wp_successful_csv_upload', $target_file );

                        $message = sprintf(
                            // translators: 1. file name, 2. header where to find file
                            esc_html__( 'File %1$s is successfully uploaded and now shows under "%2$s."', 'csv-to-wp' ),
                            '<strong>' . esc_html( $file_name ) . '</strong>',
                            esc_html__( 'Handle a csv file', 'csv-to-wp' )
                        );
                        CSV2WP::csv2wp_errors()->add( 'success_file_uploaded', $message );

                    } else {
                        CSV2WP::csv2wp_errors()->add( 'upload_error', esc_html( $movefile[ 'error' ] ) );
                    }
                }

                $posted_delimiter = ( isset( $_POST[ 'csv2wp_delimiter' ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'csv2wp_delimiter' ] ) ) : false;
            }
        }

        $show_raw         = false;
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

            <?php echo wp_kses_post( CSV2WP::csv2wp_admin_menu() ); ?>

            <div class="admin_left">
                <div class="content">
                    <?php include 'csv2wp-file-upload.php'; ?>
                    <?php include 'csv2wp-file-handling.php'; ?>
                    <?php include 'csv2wp-raw-input.php'; ?>
                </div>
            </div>
        </div>

<?php }
