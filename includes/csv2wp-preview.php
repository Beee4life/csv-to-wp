<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    /**
     * Content for the settings page
     */
    function csv2wp_preview_page() {

        if ( ! current_user_can( get_option( 'csv2wp_import_role' ) ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'csv-to-wp' ) );
        }

        $file_index       = csv2wp_check_if_files();
        $posted_file      = false;
        $has_header       = false;
        $max_lines        = 100;
        $posted_delimiter = ',';
        $show_length      = false;

        if ( isset( $_POST[ 'select_preview_nonce' ] ) ) {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'select_preview_nonce' ] ) ), 'select-preview-nonce' ) ) {
                CSV2WP::csv2wp_errors()->add( 'error_nonce_no_match', __( 'Something went wrong. Please try again.', 'csv-to-wp' ) );
            } else {
                $posted_file      = isset( $_POST[ 'csv2wp_file_name' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'csv2wp_file_name' ] ) ) : false;
                $has_header       = true;
                $max_lines        = isset( $_POST[ 'csv2wp_max_lines' ] ) ? (int) $_POST[ 'csv2wp_max_lines' ] : 100;
                $posted_delimiter = isset( $_POST[ 'csv2wp_delimiter' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'csv2wp_delimiter' ] ) ) : ',';
                $show_length      = ( isset( $_POST[ 'csv2wp_show_length' ] ) ) ? true : false;
            }
        }
        ?>

        <div class="wrap csv2wp">
            <div id="icon-options-general" class="icon32"><br/></div>

            <h1>CSV to WP - <?php esc_html_e( 'Preview', 'csv-to-wp' ); ?></h1>

            <?php CSV2WP::csv2wp_show_admin_notices(); ?>

            <?php echo wp_kses_post( CSV2WP::csv2wp_admin_menu() ); ?>

            <p><?php esc_html_e( 'Here you can preview any uploaded csv files.', 'csv-to-wp' ); ?></p>

            <p><?php esc_html_e( 'Please keep in mind that all csv files are verified before displaying (and therefor can be deleted, when errors are encountered).', 'csv-to-wp' ); ?></p>

            <div class="admin_left">
                <div class="content">
                    <?php
                        if ( $file_index ) {
                            include 'csv2wp-preview-form.php';
                        } else { ?>
                            <div class="csv2wp__section">
                                <?php esc_html_e( 'You have no files to preview.', 'csv-to-wp' ); ?>
                                <?php // translators: dashboard ?>
                                <?php echo sprintf( esc_html__( 'Upload a csv file from your %s.', 'csv-to-wp' ), sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=csv2wp-dashboard' ) ), esc_html__( 'dashboard', 'csv-to-wp' ) ) ); ?>
                            </div>
                    <?php } ?>

                    <?php
                        // Get imported data
                        if ( $posted_file ) {
                            $csv_info   = csv2wp_csv_to_array( $posted_file, $posted_delimiter, true, $has_header, true );
                            $header_row = ( isset( $csv_info[ 'column_names' ] ) ) ? $csv_info[ 'column_names' ] : [];

                            echo '<div class="csv2wp__section">';
                            if ( isset( $csv_info[ 'data' ] ) && ! empty( $csv_info[ 'data' ] ) ) {
                                include 'csv2wp-preview-output.php';
                            } else {
                                $message = __( 'You either have errors in your CSV or there is no data.', 'csv-to-wp' );
                                $message .= '<br />';
                                $message .= __( 'If there are errors the file was deleted.', 'csv-to-wp' );
                                $message .= '<br />';
                                // translators: dashboard
                                $message .= sprintf( __( 'Verify this file on the %s.', 'csv-to-wp' ), sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=csv2wp-dashboard' ) ), esc_html__( 'dashboard', 'csv-to-wp' ) ) );
                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                echo sprintf( '<p class="error_notice">%s</p>', $message);
                            }
                            echo '</div>';
                        }
                    ?>
                </div>
            </div>

        </div>
        <?php
    }
