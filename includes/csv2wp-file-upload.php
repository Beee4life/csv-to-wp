<div class="csv2wp__section">
    <h2><?php esc_html_e( 'Upload a CSV file', 'csv2wp' ); ?></h2>

    <form enctype="multipart/form-data" method="post">
        <input name="csv2wp_upload_csv_nonce" type="hidden" value="<?php echo wp_create_nonce( 'csv2wp-upload-csv-nonce' ); ?>" />

        <div class="csv2wp__upload-element">
            <label for="csv2wp_upload">
                <?php esc_html_e( 'Choose a (CSV) file to upload', 'acf-city-selector' ); ?>
            </label>
            <div class="form--upload form--csv_upload">
                <input type="file" name="csv2wp_upload" id="csv2wp_upload" accept=".csv" />
                <span class="val"></span>
                <span class="upload_button button-primary" data-type="csv2wp_upload">
                    <?php _e( 'Select file', 'csv2wp' ); ?>
                </span>
            </div>
        </div>
        <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Upload CSV', 'csv2wp' ); ?>" />
    </form>
</div>
