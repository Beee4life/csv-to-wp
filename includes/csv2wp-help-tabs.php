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
            'toplevel_page_csv2wp-preview',
            'toplevel_page_csv2wp-settings',
        ];
        
        if ( in_array( $screen_id, $screens ) ) {
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
                <li><?php esc_html_e( 'Verify', 'csv2wp' ); ?>: This option verfies if all columns are equally sized.</li>
                <li><?php esc_html_e( 'Import', 'csv2wp' ); ?>: This option imports the file (which also include a verification).</li>
                <li><?php esc_html_e( 'Remove', 'csv2wp' ); ?>: This option deletes the file.</li>
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
    
            $sidebar_content = '<p><strong>' . esc_html__( "Author's website", "csv2wp" ) . '</strong></p>';
            $sidebar_content .= '<p><a href="https://www.berryplasman.com">berryplasman.com</a></p>';
            get_current_screen()->set_help_sidebar( $sidebar_content );
            
        }

        return $old_help;
    }
    add_filter( 'contextual_help', 'csv2wp_help_tabs', 5, 3 );
