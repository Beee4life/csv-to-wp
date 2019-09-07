<?php
    
    /**
     * Add help tabs
     *
     * @param $old_help  string
     * @param $screen_id int
     * @param $screen    object
     */
    function csv2wp_help_tabs( $old_help, $screen_id, $screen ) {
        
        $screens = [
            'toplevel_page_csv2wp-dashboard',
            'admin_page_csv2wp-preview',
            'admin_page_csv2wp-settings',
            'admin_page_csv2wp-support',
        ];
        
        if ( 'toplevel_page_csv2wp-dashboard' == $screen_id ) {
            ob_start();
            ?>
            <h5><?php esc_html_e( 'Upload a CSV file', 'csv2wp' ); ?></h5>
            <p><?php esc_html_e( 'On this page you can import a CSV fil and process it.', 'csv2wp' ); ?></p>
            <p><?php esc_html_e( 'You can only upload *.csv files.', 'csv2wp' ); ?></p>
            <?php
                $tab_content = ob_get_clean();
    
                get_current_screen()->add_help_tab( array(
                    'id'      => 'import-file',
                    'title'   => esc_html__( 'Upload a CSV file', 'csv2wp' ),
                    'content' => $tab_content
                ) );

                ob_start();
                ?>
            <h5><?php esc_html_e( 'Handle a CSV file', 'csv2wp' ); ?></h5>
            <p><?php esc_html_e( 'When a file is uploaded, there are 3 things you can do with it.', 'csv2wp' ); ?></p>
            <ul>
                <li><?php esc_html_e( 'Verify', 'csv2wp' ); ?>: <?php esc_html_e( 'This option verfies if all columns are equally sized.', 'csv2wp' ); ?></li>
                <li><?php esc_html_e( 'Import', 'csv2wp' ); ?>: <?php esc_html_e( 'This option imports the file (which also include a verification).', 'csv2wp' ); ?></li>
                <li><?php esc_html_e( 'Remove', 'csv2wp' ); ?>: <?php esc_html_e( 'This option deletes the file.', 'csv2wp' ); ?></li>
            </ul>
            <h5><?php esc_html_e( 'Verify a CSV file', 'csv2wp' ); ?></h5>
            <p><?php esc_html_e( 'Upon verification, the csv file is checked if every row/line has the same amount of columns.', 'csv2wp' ); ?></p>
            <p><?php esc_html_e( "If not, the file is deleted because it can't be used anymore due to the encounterd errors.", "csv2wp" ); ?></p>
            <h5><?php esc_html_e( 'Import a CSV file', 'csv2wp' ); ?></h5>
            <p><?php esc_html_e( 'If you want to import int a table, your CSV must have a header row which is equal to your (table) column names.', 'csv2wp' ); ?></p>
            <p><?php esc_html_e( 'If you want to import post or user meta without a header row, you must use the following format: "post/user ID,meta_key,meta_value".', 'csv2wp' ); ?></p>
            <h5><?php esc_html_e( 'Delete a CSV file', 'csv2wp' ); ?></h5>
            <p><?php esc_html_e( 'Goes without saying, removes a file (from the uploads directory).', 'csv2wp' ); ?></p>
            <?php
            $tab_content = ob_get_clean();

            get_current_screen()->add_help_tab( array(
                'id'      => 'import-handle',
                'title'   => esc_html__( 'Handle a CSV file', 'csv2wp' ),
                'content' => $tab_content
            ) );
    
            ob_start();
            ?>
            <h5><?php esc_html_e( 'Import into table', 'csv2wp' ); ?></h5>
            <p><?php esc_html_e( 'A header row is obligated when you want to import into a table.', 'csv2wp' ); ?></p>
            <p><?php esc_html_e( "If you select 'import into table', a table is created with the column names you have in your CSV.", "csv2wp" ); ?></p>
            <p><?php esc_html_e( "If a table already exists with this name, any new columns which exsts in the CSV but not in the table will be appended.", "csv2wp" ); ?></p>
            <p><?php esc_html_e( "All values are inserted as 'text' since the plugin can't tell what type it is, based on the value alone. We will look into trying to 'set this' later on.", "csv2wp" ); ?></p>
            <p><?php esc_html_e( "The maximum length of a variable is 254 characters.", "csv2wp" ); ?></p>
            <?php
            $tab_content = ob_get_clean();

            get_current_screen()->add_help_tab( array(
                'id'      => 'import-table',
                'title'   => esc_html__( 'Import table', 'csv2wp' ),
                'content' => $tab_content
            ) );
    
            ob_start();
            ?>
            <h5><?php esc_html_e( 'Import into meta', 'csv2wp' ); ?></h5>
            <p><?php esc_html_e( 'There are 3 different ways to import post/user meta.', 'csv2wp' ); ?></p>
            
            <p>
                <b>1. <?php esc_html_e( 'With table headers', 'csv2wp' ); ?></b>
                <br />
                <?php esc_html_e( 'Header must be in the following format: `user id, meta key`.', 'csv2wp' ); ?>
                <br />
                <?php esc_html_e( 'Values must be in the following format: `user id, meta value`.', 'csv2wp' ); ?>
            </p>
            
            <p>
                <b>2. <?php esc_html_e( 'Without table headers', 'csv2wp' ); ?></b>
                <br />
                <?php esc_html_e( 'Must be in the following format: `user id, meta key, meta value`.', 'csv2wp' ); ?>
            </p>
            
            <p>
                <b>3. <?php esc_html_e( 'Without table headers but with a meta key', 'csv2wp' ); ?></b>
                <br />
                <?php esc_html_e( 'Must be in the following format: `user id, meta value`.', 'csv2wp' ); ?>
            </p>
            
            <?php
            $tab_content = ob_get_clean();

            get_current_screen()->add_help_tab( array(
                'id'      => 'import-meta',
                'title'   => esc_html__( 'Import meta', 'csv2wp' ),
                'content' => $tab_content
            ) );
    
        } elseif ( 'admin_page_csv2wp-preview' == $screen_id ) {
    
            ob_start();
            ?>
            <h5><?php esc_html_e( 'Preview data', 'csv2wp' ); ?></h5>
            <p><?php esc_html_e( 'On this page you can preview a CSV file before importing it.', 'csv2wp' ); ?></p>
            <p><?php esc_html_e( 'Please keep in mind that all csv files are verified before displaying (and therefor can be deleted, when errors are encountered).', 'csv2wp' ); ?></p>
            <p><?php esc_html_e( "If you select 'has header', the first table row will be bolded.", "csv2wp" ); ?></p>
            <p><?php esc_html_e( 'You can limit the amount of lines you want to preview if you have a very large file.', 'csv2wp' ); ?></p>
    
            <?php
            $tab_content = ob_get_clean();
    
            get_current_screen()->add_help_tab( array(
                'id'      => 'preview-data',
                'title'   => esc_html__( 'Preview data', 'csv2wp' ),
                'content' => $tab_content
            ) );
    
        }
    
        if ( in_array( $screen_id, $screens ) ) {
            $sidebar_content = '<p><strong>' . esc_html__( "Author's website", "csv2wp" ) . '</strong></p>';
            $sidebar_content .= '<p><a href="https://www.berryplasman.com">berryplasman.com</a></p>';
            get_current_screen()->set_help_sidebar( $sidebar_content );
        }
        
        return $old_help;
    }
    add_filter( 'contextual_help', 'csv2wp_help_tabs', 5, 3 );
