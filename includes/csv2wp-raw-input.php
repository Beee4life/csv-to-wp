<?php if ( $show_raw ) { ?>
    <div class="csv2wp__section">
        <h2>
            <?php esc_html_e( 'Import raw CSV data', 'csv2wp' ); ?>
        </h2>
        <p>
            <?php esc_html_e( 'Make sure the cursor is ON the last line (after the last character), NOT on a new line.', 'csv2wp' ); ?>
            <br/>
            <?php esc_html_e( 'This is seen as a new entry and creates an error !!!', 'csv2wp' ); ?>
        </p>

        <?php $submitted_raw_data = false; ?>
        <?php if ( isset( $_POST[ 'raw_csv_import' ] ) ) { ?>
            <?php $submitted_raw_data = $_POST[ 'raw_csv_import' ]; ?>
        <?php } ?>

        <form method="POST">
            <input name="import_raw_rankings_nonce" type="hidden" value="<?php echo wp_create_nonce( 'import-raw-rankings-nonce' ); ?>"/>
            <label for="raw-import" class="screen-reader-text"></label>
            <textarea name="raw_csv_import" id="raw-import" type="textarea" rows="5" cols="50" placeholder=""><?php echo $submitted_raw_data; ?></textarea>
            <br/>
            <input name="verify" type="submit" class="button button-primary" value="<?php esc_html_e( 'Verify data', 'csv2wp' ); ?>"/>
            <input name="import" type="submit" class="button button-primary" value="<?php esc_html_e( 'Import data', 'csv2wp' ); ?>"/>
        </form>
    </div>
<?php } ?>
