<?php

    /**
     * Content for the settings page
     */
    function csv2wp_preview_page() {

        if ( ! current_user_can( get_option( 'csv2wp_import_role' ) ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'csv2wp' ) );
        }
        ?>

        <div class="wrap csv2wp">
            <div id="icon-options-general" class="icon32"><br/></div>

            <h1>CSV to WP - <?php esc_html_e( 'Preview', 'csv2wp' ); ?></h1>

            <?php CSV2WP::csv2wp_show_admin_notices(); ?>

            <?php echo CSV2WP::csv2wp_admin_menu(); ?>

            <p><?php esc_html_e( 'Here you can preview any uploaded csv files.', 'csv2wp' ); ?></p>

            <p><?php esc_html_e( 'Please keep in mind that all csv files are verified before displaying (and therefor can be deleted, when errors are encountered).', 'csv2wp' ); ?></p>

            <div class="admin_left">
                <div class="content">
                    <?php
                        $file_index       = csv2wp_check_if_files();
                        $file_name        = isset( $_POST[ 'csv2wp_file_name' ] ) ? sanitize_text_field( $_POST[ 'csv2wp_file_name' ] ) : false;
                        $has_header       = isset( $_POST[ 'csv2wp_header_row' ] ) ? true : false;
                        $max_lines        = isset( $_POST[ 'csv2wp_max_lines' ] ) ? (int) $_POST[ 'csv2wp_max_lines' ] : 100;
                        $posted_delimiter = isset( $_POST[ 'csv2wp_delimiter' ] ) ? sanitize_text_field( $_POST[ 'csv2wp_delimiter' ] ) : ',';
                        $show_length      = ( isset( $_POST[ 'csv2wp_show_length' ] ) ) ? true : false;

                        if ( $file_index ) {
                            include 'csv2wp-preview-form.php';
                        } else { ?>
                        <div class="csv2wp__section">
                            <?php esc_html_e( 'You have no files to preview.', 'csv2wp' ); ?>
                            <?php echo sprintf( __( 'Upload a csv file from your <a href="%s">dashboard</a>.', 'csv2wp' ), esc_url( admin_url( '/admin.php?page=csv2wp-dashboard' ) ) ); ?>
                        </div>
                    <?php } ?>

                    <?php
                        // Get imported data
                        if ( $file_name ) {
                            $csv_info   = csv2wp_csv_to_array( $file_name, $posted_delimiter, true, $has_header, true );
                            $header_row = ( isset( $csv_info[ 'column_names' ] ) ) ? $csv_info[ 'column_names' ] : [];

                            echo '<div class="csv2wp__section">';
                            if ( isset( $csv_info[ 'data' ] ) && ! empty( $csv_info[ 'data' ] ) ) {
                                include 'csv2wp-preview-output.php';
                            } else {
                                $message = __( 'You either have errors in your CSV or there is no data.', 'csv2wp' );
                                $message .= '<br />';
                                $message .= __( 'If there are errors the file was deleted.', 'csv2wp' );
                                $message .= '<br />';
                                $message .= sprintf( __( 'Verify this file on the %s.', 'csv2wp' ), sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=csv2wp-dashboard' ), esc_html__( 'dashboard', 'csv2wp' ) ) );
                                echo sprintf( '<p class="error_notice"></p>', $message);
                            }
                            echo '</div>';
                        }
                    ?>
                </div>
            </div>

        </div>
        <?php
    }
