<?php
    /**
     * Output for dashboard page
     */
    function csv2wp_dashboard_page() {
        if ( ! current_user_can( get_option( 'csv2wp_import_role' ) ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'csv2wp' ) );
        }
        ?>

        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br/></div>

            <h2>CSV to WP - <?php esc_html_e( 'Dashboard', 'csv2wp' ); ?></h2>
            
            <?php CSV2WP::csv2wp_show_admin_notices(); ?>

            <div class="csv2wp">
                
                <?php echo CSV2WP::csv2wp_admin_menu(); ?>

                <div class="csv2wp__section">
                    <p><?php esc_html_e( 'This page allows you to import a csv into your database.', 'csv2wp' ); ?> <?php esc_html_e( 'See the help tab for more explanation.', 'csv2wp' ); ?></p>
                </div>
                
                <div class="csv2wp__section">
                    <h2><?php esc_html_e( 'Upload a CSV file', 'csv2wp' ); ?></h2>
    
                    <form enctype="multipart/form-data" method="POST">
                        <input name="upload_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'upload-file-nonce' ); ?>"/>
                        <label for="csv_uploaded_file"><?php esc_html_e( 'Choose a (*.csv) file to upload', 'csv2wp' ); ?></label>
                        <input id="csv_uploaded_file" name="csv_uploaded_file" type="file" accept=".csv"/>
                        <br/><br/>
                        <input type="submit" value="<?php esc_html_e( 'Upload file', 'csv2wp' ); ?>"/>
                    </form>
                </div>

                <div class="csv2wp__section">
                    <?php $file_index = csv2wp_check_if_files(); ?>
                    <?php if ( ! empty( $file_index ) ) { ?>
                        <h2>
                            <?php esc_html_e( "Handle a CSV file", "csv2wp" ); ?>
                        </h2>
                        <p>
                            <?php esc_html_e( 'Select a file, select where to import it, whether the file has a header row and if you want to limit the amount of lines.', 'csv2wp' ); ?>
                        </p>
        
                        <?php global $wpdb; ?>
                        <form method="POST">
                            <input name="select_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'select-file-nonce' ); ?>"/>
                            <table class="csv2wp__table">
                                <thead>
                                <tr>
                                    <th><?php esc_html_e( 'File name', 'csv2wp' ); ?></th>
                                    <th><?php esc_html_e( 'Import in', 'csv2wp' ); ?></th>
                                    <th><?php esc_html_e( 'Delimiter', 'csv2wp' ); ?></th>
                                    <th><?php esc_html_e( 'Has header', 'csv2wp' ); ?></th>
                                    <th><span class="csv2wp__th csv2wp__th--table"><?php esc_html_e( 'Table', 'csv2wp' ); ?></span><span class="csv2wp__th csv2wp__th--meta hidden"><?php esc_html_e( 'Meta key', 'csv2wp' ); ?></span></th>
                                    <th class="hidden"><?php esc_html_e( 'Max. lines', 'csv2wp' ); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <label>
                                            <select id="csv2wp_row_id" name="csv2wp_file_name">
                                                <?php if ( count( $file_index ) > 1 ) { ?>
                                                    <option value=""><?php esc_html_e( 'Select a file', 'csv2wp' ); ?></option>
                                                <?php } ?>
                                                <?php foreach ( $file_index as $file ) { ?>
                                                    <option value="<?php echo $file; ?>"><?php echo $file; ?></option>
                                                <?php } ?>
                                            </select>
                                        </label>
                                    </td>
                                    <td>
                                        <label>
                                            <select name="csv2wp_import_in" class="csv2wp_import_in" id="csv2wp_import_in">
                                                <option value="table"><?php esc_html_e( 'Database table', 'csv2wp' ); ?></option>
                                                <option value="postmeta"><?php esc_html_e( 'Post meta', 'csv2wp' ); ?></option>
                                                <option value="usermeta"><?php esc_html_e( 'User meta', 'csv2wp' ); ?></option>
                                            </select>
                                        </label>
                                    </td>
                                    <td>
                                        <label>
                                            <select name="csv2wp_delimiter" id="csv2wp_delimiter">
                                                <option value=",">,</option>
                                                <option value=";">;</option>
                                                <option value="|">|</option>
                                            </select>
                                        </label>
                                    </td>
                                    <td class="header">
                                        <span class="csv2wp_header">
                                            <label>
                                                <input id="csv2wp-header" class="csv2wp__header" name="csv2wp_header" type="checkbox" value="1" checked="checked"> <?php esc_html_e( 'Yes', 'csv2wp' ); ?>
                                            </label>
                                        </span>
                                    </td>
                                    <td>
                                        <label>
                                            <input id="csv2wp_key_table" class="csv2wp__key csv2wp__key--table" name="csv2wp_table" type="text" size="10" value="" placeholder="<?php echo $wpdb->prefix; ?>">
                                            <input id="csv2wp_key_meta" class="csv2wp__key csv2wp__key--meta hidden" name="csv2wp_meta" type="text" size="10" value="" placeholder="meta key">
                                        </label>
                                    </td>
                                    <td class="hidden">
                                        <label>
                                            <select name="csv2wp_max_lines" id="csv2wp_max_lines">
                                                <option value=""><?php esc_html_e( 'All', 'csv2wp' ); ?></option>
                                                <option value="5">5</option>
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                                <option value="250">250</option>
                                                <option value="500">500</option>
                                                <option value="1000">1000</option>
                                            </select>
                                        </label>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
            
                            <input name="csv2wp_verify" type="submit" class="button csv2wp__button" value="<?php esc_html_e( 'Verify', 'csv2wp' ); ?>"/>
                            <input name="csv2wp_import" type="submit" class="button csv2wp__button" value="<?php esc_html_e( 'Import', 'csv2wp' ); ?>"/>
                            <input name="csv2wp_remove" type="submit" class="button csv2wp__button" value="<?php esc_html_e( 'Remove', 'csv2wp' ); ?>"/>
                        </form>
                        
                    <?php } else { ?>
                        <p><?php esc_html_e( 'No files uploaded.', 'csv2wp' ); ?></p>
                    <?php } ?>
                </div>

                <?php if ( defined( 'WP_TESTING' ) && WP_TESTING == 1 ) { ?>
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
                            <input name="verify" type="submit" value="<?php esc_html_e( 'Verify data', 'csv2wp' ); ?>"/>
                            <input name="import" type="submit" value="<?php esc_html_e( 'Import data', 'csv2wp' ); ?>"/>
                        </form>
                    </div>
                <?php } ?>

            </div>
        </div>
        
<?php } // end race_overview - content for settings page
