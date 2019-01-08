<?php
    
    /**
     * Add help tabs
     *
     * @param $old_help  string
     * @param $screen_id int
     * @param $screen    object
     */
    function csv2wp_help_tabs( $old_help, $screen_id, $screen ) {
        
        ob_start();
        ?>
        <h5>Upload a CSV file</h5>
        <p><?php esc_html_e( 'On this page you can import a CSV file which contains cities to import.', 'csv2wp' ); ?></p>
        <p><?php esc_html_e( 'You can only upload *.csv files.', 'csv2wp' ); ?></p>
        <p><?php esc_html_e( 'The first column MUST be either a post or user id.', 'csv2wp' ); ?></p>
        <?php
        $tab_content = ob_get_clean();

        get_current_screen()->add_help_tab( array(
            'id'      => 'import-file',
            'title'   => esc_html__( 'Upload a CSV file', 'csv2wp' ),
            'content' => $tab_content
        ) );
        

        ob_start();
        ?>
        <h5>Handle a CSV file</h5>
        <p><?php esc_html_e( 'When a file is uploaded, there are 3 things you can do with it.', 'csv2wp' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'Verify', 'csv2wp' ); ?>: This option verfies if all columns are equally sized.</li>
            <li><?php esc_html_e( 'Import', 'csv2wp' ); ?>: This option imports the file (which also include a verification).</li>
            <li><?php esc_html_e( 'Remove', 'csv2wp' ); ?>: This option deletes the file.</li>
        </ul>
        <h5>Verify a CSV file</h5>
        <p><?php esc_html_e( 'Upon verification, the csv file is checked if every row/line has the same amount of columns.', 'csv2wp' ); ?></p>
        <p><?php esc_html_e( "If not, the file is deleted because it can't be used anymore due to the encounterd errors.", "csv2wp" ); ?></p>
        <h5>Import a CSV file</h5>
        <p><?php esc_html_e( 'If you want to import a file, you must provide a meta field (key).', 'csv2wp' ); ?></p>
        <h5>Delete a CSV file</h5>
        <p><?php esc_html_e( 'Goes without saying, removes a file (from the uploads directory).', 'csv2wp' ); ?></p>
        <?php
        $tab_content = ob_get_clean();

        get_current_screen()->add_help_tab( array(
            'id'      => 'import-handle',
            'title'   => esc_html__( 'Handle a CSV file', 'csv2wp' ),
            'content' => $tab_content
        ) );

        get_current_screen()->set_help_sidebar( '<p><strong>' . esc_html__( "Author's website", "csv2wp" ) . '</strong></p>
			<p><a href="http://www.berryplasman.com">berryplasman.com</a></p>' );
        
        return $old_help;
    }
    add_filter( 'contextual_help', 'csv2wp_help_tabs', 5, 3 );
