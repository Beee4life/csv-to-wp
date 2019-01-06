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

                <h2><?php esc_html_e( 'Upload a file', 'csv2wp' ); ?></h2>

                <form enctype="multipart/form-data" method="POST">
                    <input name="upload_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'upload-file-nonce' ); ?>"/>
                    <input type="hidden" name="MAX_FILE_SIZE" value=""/>
                    <label for="file_upload"><?php esc_html_e( 'Choose a (csv) file to upload', 'csv2wp' ); ?></label>
                    <input name="csv_upload" type="file" accept=".csv"/>
                    <br/><br/>
                    <input type="submit" value="<?php esc_html_e( 'Upload file', 'csv2wp' ); ?>"/>
                </form>
                
                <?php
                    // @TODO: check if directory exists
                    $file_index = csv2wp_check_if_files();
                    if ( $file_index ) {
                        ?>
                        <br/>
                        <h2><?php esc_html_e( "Select a file to 'handle'", "csv2wp" ); ?></h2>
                        <p>
                            <small>First select a file, then select where to import it.</small>
                        </p>
                        
                        <?php if ( 0 < count( $file_index ) ) { ?>
                            <form name="" method="POST">
                                <input name="select_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'select-file-nonce' ); ?>"/>
                                <table class="uploaded_files">
                                    <?php
                                        $has_files = false;
                                        foreach ( $file_index as $file ) {
                                            if ( '.DS_Store' != $file && '.' != $file && '..' != $file ) {
                                                $has_files = true;
                                                ?>
                                                <tr>
                                                    <td>
                                                        <label for="csv2wp_file_name[]" class="screen-reader-text">File name</label>
                                                        <input id="csv2wp_file_name[]" name="csv2wp_file_name[]" type="radio" value="<?php echo $file; ?>">
                                                    </td>
                                                    <td><?php echo $file; ?></td>
                                                    <td>
<!--                                                        <label for="import_in_post_meta" class="screen-reader-text">Import in post_meta</label>-->
<!--                                                        <input id="import_in_post_meta" name="import_in[]" type="radio" value="1">Import in post_meta-->
                                                    </td>
                                                    <td>
<!--                                                        <label for="import_in_user_meta" class="screen-reader-text">Import in user_meta</label>-->
<!--                                                        <input id="import_in_user_meta" name="import_in[]" type="radio" value="1">Import in user_meta-->
                                                    </td>
                                                </tr>
                                            <?php }
                                        }
                                    ?>
                                </table>
                                <?php if ( $has_files ) { ?>
                                    <br/>
                                    <input name="csv2wp_verify" type="submit" value="<?php esc_html_e( 'Verify selected file(s)', 'csv2wp' ); ?>"/>
                                    <input name="csv2wp_import" type="submit" value="<?php esc_html_e( 'Import selected file(s)', 'csv2wp' ); ?>"/>
                                    <input name="csv2wp_remove" type="submit" value="<?php esc_html_e( 'Remove selected file(s)', 'csv2wp' ); ?>"/>
                                <?php } ?>
                            </form>
                        
                        <?php } ?>
                        
                        <?php if ( false == $has_files && '.DS_Store' != $file_index[ 0 ] ) { ?>
                            <ul>
                                <li><?php esc_html_e( 'No files uploaded', 'csv2wp' ); ?></li>
                            </ul>
                        <?php } ?>
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
