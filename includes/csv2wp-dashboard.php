<?php
    /**
     * Output for dashboard page
     */
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

                <p><?php esc_html_e( 'This page allows you to import a csv into your database.', 'csv2wp' ); ?></p>
                <p><?php esc_html_e( 'See the help tab for more explanation.', 'csv2wp' ); ?></p>
                
                <h2><?php esc_html_e( 'Upload a CSV file', 'csv2wp' ); ?></h2>

                <form enctype="multipart/form-data" method="POST">
                    <input name="upload_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'upload-file-nonce' ); ?>"/>
                    <label for="csv_uploaded_file"><?php esc_html_e( 'Choose a (csv) file to upload', 'csv2wp' ); ?></label>
                    <input id="csv_uploaded_file" name="csv_uploaded_file" type="file" accept=".csv"/>
                    <br/><br/>
                    <input type="submit" value="<?php esc_html_e( 'Upload file', 'csv2wp' ); ?>"/>
                </form>
                
                <?php $file_index = csv2wp_check_if_files(); ?>
                <?php if ( $file_index ) { ?>
                        ?>
                        <br/>
                        <h2>
                            <?php esc_html_e( "Handle a CSV file", "csv2wp" ); ?>
                        </h2>
                        <p>
                            <?php esc_html_e( 'Select a file, select where to import it, whether the file has a header row and if you want to limit the amount of lines.', 'csv2wp' ); ?>
                        </p>
                        
                        <?php if ( ! empty( $file_index ) ) { ?>
                            <?php global $wpdb; ?>
                            <form method="POST">
                                <input name="select_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'select-file-nonce' ); ?>"/>
                                <table class="uploaded_files">
                                    <thead>
                                        <tr>
                                            <th>&nbsp;</th>
                                            <th><?php echo __( 'File name', 'csv2wp' ); ?></th>
                                            <th><?php echo __( 'Import in', 'csv2wp' ); ?></th>
                                            <th><?php echo __( 'Has header', 'csv2wp' ); ?></th>
                                            <th><?php echo __( 'Table', 'csv2wp' ); ?></th>
                                            <th><?php echo __( 'Delimiter', 'csv2wp' ); ?></th>
                                            <th><?php echo __( 'Max. lines', 'csv2wp' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php $row_id = 0; ?>
                                    <?php foreach ( $file_index as $file ) { ?>
                                        <tr>
                                            <td>
                                                <label for="csv2wp_row_id" class="screen-reader-text">File name</label>
                                                <input id="csv2wp_row_id" name="csv2wp_row_id" type="radio" value="<?php echo $row_id; ?>">
                                                <input id="csv2wp_file_name-<?php echo $row_id; ?>" name="csv2wp_file_name-<?php echo $row_id; ?>" type="hidden" value="<?php echo $file; ?>">
                                            </td>
                                            <td><?php echo $file; ?></td>
                                            <td>
                                                <label for="csv2wp_import_in-<?php echo $row_id; ?>" class="screen-reader-text">Import in</label>
                                                <select name="csv2wp_import_in-<?php echo $row_id; ?>" class="csv2wp_import_in" id="csv2wp_import_in-<?php echo $row_id; ?>">
                                                    <option value="table">Database table</option>
                                                    <option value="postmeta">Post meta</option>
                                                    <option value="usermeta">User meta</option>
                                                </select>
                                            </td>
                                            <td class="header">
                                                <span class="csv2wp_header-<?php echo $row_id; ?>">
                                                    <label for="csv2wp_header-<?php echo $row_id; ?>" class="screen-reader-text"><?php esc_html_e( 'Yes', 'csv2wp' ); ?></label>
                                                    <input id="csv2wp_header-<?php echo $row_id; ?>" class="" name="csv2wp_header-<?php echo $row_id; ?>" type="checkbox" value="1" checked="checked"> Yes
                                                </span>
                                            </td>
                                            <td>
                                                <label>
                                                    <input id="csv2wp_key_table-<?php echo $row_id; ?>" class="csv2wp_key csv2wp_key-<?php echo $row_id; ?> table" name="csv2wp_table-<?php echo $row_id; ?>" type="text" size="10" value="<?php echo $wpdb->prefix; ?>" placeholder="<?php echo $wpdb->prefix; ?>">
                                                </label>
                                            </td>
                                            <td>
                                                <label for="csv2wp_delimiter-<?php echo $row_id; ?>" class="screen-reader-text">Delimiter</label>
                                                <select name="csv2wp_delimiter-<?php echo $row_id; ?>" id="csv2wp_delimiter-<?php echo $row_id; ?>">
                                                    <option value=",">,</option>
                                                    <option value=";">;</option>
                                                </select>
                                            </td>
                                            <td>
                                                <label for="csv2wp_max_lines-<?php echo $row_id; ?>" class="screen-reader-text">Limit lines</label>
                                                <select name="csv2wp_max_lines-<?php echo $row_id; ?>" id="csv2wp_max_lines-<?php echo $row_id; ?>">
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
                                            </td>
                                        </tr>
                                        <?php $row_id++; ?>
                                    <?php } ?>
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
                        <label for="raw-import"></label>
                        <textarea name="raw_csv_import" id="raw-import" type="textarea" rows="5" cols="50" placeholder=""><?php echo $submitted_raw_data; ?></textarea>
                        <br/>
                        <input name="verify" type="submit" value="<?php esc_html_e( 'Verify data', 'csv2wp' ); ?>"/>
                        <input name="import" type="submit" value="<?php esc_html_e( 'Import data', 'csv2wp' ); ?>"/>
                    </form>
                <?php } ?>

            </div>
        </div>
        
<?php } // end race_overview - content for settings page
