<?php
    if ( ! defined( 'ABSPATH' ) ) exit;

    if ( $show_raw ) {
        $submitted_raw_data = false;

        if ( isset( $_POST[ 'import_raw_input_nonce' ] ) ) {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'import_raw_input_nonce' ] ) ), 'import-raw-input-nonce' ) ) {
                CSV2WP::csv2wp_errors()->add( 'error_nonce_no_match', __( 'Something went wrong. Please try again.', 'csv-to-wp' ) );

                return;
            } else {
                if ( isset( $_POST[ 'raw_csv_import' ] ) ) {
                    $submitted_raw_data = sanitize_textarea_field( wp_unslash( $_POST[ 'raw_csv_import' ] ) );
                }
            }
        }
        ?>
        <div class="csv2wp__section">
            <h2>
                <?php esc_html_e( 'Import raw CSV data', 'csv-to-wp' ); ?>
            </h2>
            <p>
                <?php esc_html_e( 'Make sure the cursor is ON the last line (after the last character), NOT on a new line.', 'csv-to-wp' ); ?>
                <br/>
                <?php esc_html_e( 'This is seen as a new entry and creates an error !!!', 'csv-to-wp' ); ?>
            </p>

            <form method="POST">
                <input name="import_raw_input_nonce" type="hidden" value="<?php echo esc_attr( wp_create_nonce( 'import-raw-input-nonce' ) ); ?>"/>
                <label for="raw-import" class="screen-reader-text"></label>
                <textarea name="raw_csv_import" id="raw-import" type="textarea" rows="5" cols="50" placeholder=""><?php echo esc_textarea( $submitted_raw_data ); ?></textarea>
                <br/>
                <input name="verify" type="submit" class="button button-primary" value="<?php esc_html_e( 'Verify data', 'csv-to-wp' ); ?>"/>
                <input name="import" type="submit" class="button button-primary" value="<?php esc_html_e( 'Import data', 'csv-to-wp' ); ?>"/>
            </form>
        </div>
<?php } ?>
