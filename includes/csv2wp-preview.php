<?php
    
    /**
     * Content for the settings page
     */
    function csv2wp_preview_page() {
        
        if ( ! current_user_can( get_option( 'csv2wp_import_role' ) ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'csv2wp' ) );
        }
        ?>

        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br/></div>

            <h1>CSV to WP - <?php esc_html_e( 'Preview', 'csv2wp' ); ?></h1>
            
            <?php CSV2WP::csv2wp_show_admin_notices(); ?>
    
            <div class="csv2wp">
                
                <?php echo CSV2WP::csv2wp_admin_menu(); ?>
                
                <?php
                    $delimiter = false;
                    if ( isset( $_POST[ 'csv2wp_file_name' ] ) ) {
                        $delimiter   = $_POST[ 'csv2wp_delimiter' ];
                        $file_name   = $_POST[ 'csv2wp_file_name' ];
                        $has_header  = ( isset( $_POST[ 'csv2wp_header_row' ] ) ) ? true : false;
                        $show_length = ( isset( $_POST[ 'csv2wp_show_length' ] ) ) ? true : false;
                    } else {
                        $file_name = false;
                    }
                ?>

                <?php $file_index  = csv2wp_check_if_files(); ?>
                <?php if ( $file_index ) { ?>
                    <div class="csv2wp__section">
                        <p><?php esc_html_e( 'Here you can preview any uploaded csv files.', 'csv2wp' ); ?></p>
                        <p><?php esc_html_e( 'Please keep in mind that all csv files are verified before displaying (and therefor can be deleted, when errors are encountered).', 'csv2wp' ); ?></p>
                    </div>
    
                    <div class="csv2wp__section">
        
                        <form name="select-preview-file" id="settings-form" action="" method="post">
                            <input name="select_preview_nonce" type="hidden" value="<?php echo wp_create_nonce( 'select-preview-nonce' ); ?>"/>
                            <table class="csv2wp__table">
                                <thead>
                                <tr>
                                    <th><?php esc_html_e( 'File name', 'csv2wp' ); ?></th>
                                    <th><?php esc_html_e( 'Delimiter', 'csv2wp' ); ?></th>
                                    <th><?php esc_html_e( 'Has header', 'csv2wp' ); ?></th>
                                    <th><?php esc_html_e( 'Show value length', 'csv2wp' ); ?></th>
                                    <th class="xhidden"><?php esc_html_e( 'Max. lines', 'csv2wp' ); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <label>
                                            <select name="csv2wp_file_name" id="select-preview-file">
                                                <?php $posted_file = ( isset( $_POST[ 'csv2wp_file_name' ] ) ) ? $_POST[ 'csv2wp_file_name' ] : false; ?>
                                                <?php if ( count( $file_index ) > 1 ) { ?>
                                                    <option value=""><?php esc_html_e( 'Select a file', 'csv2wp' ); ?></option>
                                                <?php } ?>
                                                <?php foreach ( $file_index as $file ) { ?>
                                                    <option value="<?php echo $file; ?>"<?php echo( $posted_file == $file ? ' selected' : false ); ?>><?php echo $file; ?></option>
                                                <?php } ?>
                                            </select>
                                        </label>
                                    </td>

                                    <td>
                                        <?php $delimiters = [ ",", ";" ]; ?>
                                        <label>
                                            <select name="csv2wp_delimiter" id="csv2wp_delimiter">
                                                <?php foreach( $delimiters as $limiter ) { ?>
                                                    <option value="<?php echo $limiter; ?>"<?php echo( $delimiter == $limiter ? ' selected' : false ); ?>><?php echo $limiter; ?></option>
                                                <?php } ?>
                                            </select>
                                        </label>
                                    </td>

                                    <td>
                                        <label>
                                            <input name="csv2wp_header_row" id="csv2wp_header_row" type="checkbox" value="true"<?php if ( isset( $has_header ) && true == $has_header ) { echo ' checked'; } ?>/> <?php esc_html_e( 'Yes', 'csv2wp' ); ?>
                                        </label>
                                    </td>
    
                                    <td>
                                        <label>
                                            <input name="csv2wp_show_length" id="csv2wp_show_length" type="checkbox" value="true"<?php if ( isset( $show_length ) && true == $show_length ) { echo ' checked'; } ?>/> <?php esc_html_e( 'Yes', 'csv2wp' ); ?>
                                        </label>
                                    </td>
    
                                    <td class="xhidden">
                                        <?php $amounts = [ 5, 10, 25, 50, 100, 250, 500, 1000 ]; ?>
                                        <label>
                                            <select name="csv2wp_max_lines" id="csv2wp_max_lines">
                                                <option value=""><?php esc_html_e( 'All', 'csv2wp' ); ?></option>
                                                <?php foreach( $amounts as $amount ) { ?>
                                                    <option value="<?php echo $amount; ?>"><?php echo $amount; ?></option>
                                                <?php } ?>
                                            </select>
                                        </label>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <input type="submit" class="admin-button admin-button-small" value="<?php esc_html_e( 'Preview this file', 'csv2wp' ); ?>"/>
                        </form>
                    </div>

                <?php } else { ?>
                    <p><?php esc_html_e( 'You have no files to preview.', 'csv2wp' ); ?></p>
                <?php } ?>

                <?php
                    // Get imported data
                    if ( $file_name ) {
                        $csv_info   = csv2wp_csv_to_array( $file_name, $delimiter, true, $has_header, true );
                        $header_row = ( isset( $csv_info[ 'column_names' ] ) ) ? $csv_info[ 'column_names' ] : [];
                        
                        echo '<div class="csv2wp__section">';
                        if ( isset( $csv_info[ 'data' ] ) && ! empty( $csv_info[ 'data' ] ) ) {
                            echo '<h2>' . __( 'CSV contents', 'csv2wp' ) . '</h2>';
                            echo '<table class="csv2wp__table csv2wp__table--preview">';
                            if ( $has_header && ! empty( $header_row ) ) {
                                echo '<thead>';
                                echo '<tr>';
                                foreach ( $header_row as $column ) {
                                    echo '<th>' . $column . '</th>';
                                    if ( $show_length ) {
                                        echo '<th>Length</th>';
                                    }
                                }
                                echo '</tr>';
                                echo '</thead>';
                            }
                            echo '<tbody>';
                            $line_number = 0;
                            foreach ( $csv_info[ 'data' ] as $line ) {
                                $line_number++;
                                echo '<tr>';
                                foreach ( $line as $column ) {
                                    echo '<td>';
                                    echo esc_html($column);
                                    echo '</td>';
                                    if ( $show_length ) {
                                        echo '<td>'.strlen($column).'</td>';
                                    }
                                }
                                echo '</tr>';
                                if ( $line_number == $_POST[ 'csv2wp_max_lines' ] ) {
                                    break;
                                }
                            }
                            echo '</tbody>';
                            echo '</table>';
                        } else {
                            echo '<p class="error_notice">';
                            echo sprintf( __( 'You either have errors in your CSV or there is no data. Verify this file on the <a href="%s">dashboard</a>.', 'csv2wp' ), admin_url( 'admin.php?page=' ) . 'csv2wp-dashboard' );
                            echo '</p>';
                        }
                        echo '</div>';
                    }
                ?>
            </div>

        </div>
        <?php
    }
