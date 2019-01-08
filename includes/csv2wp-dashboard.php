<?php
    
    function csv2wp_dashboard_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html( __( 'Sorry, you do not have sufficient permissions to access this page.', 'csv2wp' ) ) );
        }
        
        $show_search = false;
        $show_raw    = false;
        $show_nuke   = false;
        
        ?>

        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br/></div>

            <h2>CSV importer dashboard</h2>
            
            <?php CSV2WP::csv2wp_show_admin_notices(); ?>

            <div id="csv-importer" class="">
                
                <?php echo CSV2WP::csv2wp_admin_menu(); ?>

                <p><?php esc_html_e( 'This page allows to import a csv and import it into your database.', 'csv2wp' ); ?></p>
                <p><?php esc_html_e( 'Make sure the first column in your CSV file is either a POST or USER ID.', 'csv2wp' ); ?></p>
                <p><?php esc_html_e( 'For preview reasons a header row is recommended.', 'csv2wp' ); ?></p>
                <h2><?php esc_html_e( 'Upload a CSV file', 'csv2wp' ); ?></h2>

                <form enctype="multipart/form-data" method="POST">
                    <input name="upload_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'upload-file-nonce' ); ?>"/>
                    <label for="csv_uploaded_file"><?php esc_html_e( 'Choose a (csv) file to upload', 'csv2wp' ); ?></label>
                    <input id="csv_uploaded_file" name="csv_uploaded_file" type="file" accept=".csv"/>
                    <br/><br/>
                    <input type="submit" value="<?php esc_html_e( 'Upload file', 'csv2wp' ); ?>"/>
                </form>
                
                <?php
                    $file_index = csv2wp_check_if_files();
                    if ( $file_index ) {
                        ?>
                        <br/>
                        <h2>
                            <?php esc_html_e( "Handle a CSV file", "csv2wp" ); ?>
                        </h2>
                        <p>
                            <?php esc_html_e( 'Select a file, select in which meta to import it, choose a meta field key, whether the file has a header row and if you want to limit the amount of lines.', 'csv2wp' ); ?>
                        </p>
                        
                        <?php if ( ! empty( $file_index ) ) { ?>
                            <form method="POST">
                                <input name="select_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'select-file-nonce' ); ?>"/>
                                <table class="uploaded_files" cellpadding="0" cellspacing="0" border="0">
                                    <thead>
                                        <tr>
                                            <th>&nbsp;</th>
                                            <th>File name</th>
                                            <th>Import in post meta</th>
                                            <th>Import in user meta</th>
                                            <th>Meta field</th>
                                            <th>CV has header row ?</th>
                                            <th>Max # lines</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $has_files = false;
                                        foreach ( $file_index as $file ) {
                                        ?>
                                        <tr>
                                            <td>
                                                <label for="csv2wp_file_name[]" class="screen-reader-text">File name</label>
                                                <input id="csv2wp_file_name[]" name="csv2wp_file_name[]" type="radio" value="<?php echo $file; ?>">
                                            </td>
                                            <td><?php echo $file; ?></td>
                                            <td>
                                                <label for="import_in_post_meta" class="screen-reader-text">Import in post_meta</label>
                                                <input id="import_in_post_meta" name="csv2wp_import_in" type="radio" value="post">
                                            </td>
                                            <td>
                                                <label for="import_in_user_meta" class="screen-reader-text">Import in user_meta</label>
                                                <input id="import_in_user_meta" name="csv2wp_import_in" type="radio" value="user">
                                            </td>
                                            <td>
                                                <label for="csv2wp_meta_key" class="screen-reader-text">Meta key</label>
                                                <input id="csv2wp_meta_key" name="csv2wp_meta_key" type="text">
                                            </td>
                                            <td>
                                                <label for="csv2wp_has_header" class="screen-reader-text">Has header row</label>
                                                <input id="csv2wp_has_header" name="csv2wp_has_header" type="checkbox" value="1">
                                            </td>
                                            <td>
                                                <label for="csv2wp_max_lines" class="screen-reader-text">Amount of lines</label>
                                                <input id="csv2wp_max_lines" name="csv2wp_max_lines" type="text" size="5">
                                            </td>
                                        </tr>
                                    <?php }
                                    ?>
                                    </tbody>
                                </table>
                                <br/>
                                <input name="csv2wp_verify" type="submit" value="<?php esc_html_e( 'Verify selected file(s)', 'csv2wp' ); ?>"/>
                                <input name="csv2wp_import" type="submit" value="<?php esc_html_e( 'Import selected file(s)', 'csv2wp' ); ?>"/>
                                <input name="csv2wp_remove" type="submit" value="<?php esc_html_e( 'Remove selected file(s)', 'csv2wp' ); ?>"/>
                            </form>
                        
                        <?php } ?>
                        
                    <?php } else { ?>
                        <p><?php esc_html_e( 'No files uploaded', 'csv2wp' ); ?></p>
                    <?php } ?>
                
                <?php if ( $show_raw ) { ?>
                    <br/>
                    <h2>Import raw CSV data</h2>
                    <p>
                        <?php esc_html_e( 'Make sure the cursor is ON the last line (after the last character), NOT on a new line.', 'csv2wp' ); ?>
                        <br/>
                        <?php esc_html_e( 'This is seen as a new entry and creates an error !!!', 'csv2wp' ); ?>
                    </p>
    
                    <?php $submitted_raw_data = false;
                    if ( isset( $_POST[ 'raw_csv_import' ] ) ) {
                        $submitted_raw_data = $_POST[ 'raw_csv_import' ];
                    } ?>
                    <form method="POST">
                        <input name="import_raw_rankings_nonce" type="hidden" value="<?php echo wp_create_nonce( 'import-raw-rankings-nonce' ); ?>"/>
                        <label for="raw-import"></label>
                        <textarea name="raw_csv_import" id="raw-import" type="textarea" rows="5" cols="50" placeholder=""><?php echo $submitted_raw_data; ?></textarea>
                        <br/>
                        <input name="verify" type="submit" value="<?php esc_html_e( 'Verify data', 'csv2wp' ); ?>"/>
                        <input name="import" type="submit" value="<?php esc_html_e( 'Import data', 'csv2wp' ); ?>"/>
                    </form>
                <?php } ?>

            </div>
        </div>
        
        <?php
    } // end race_overview - content for settings page
