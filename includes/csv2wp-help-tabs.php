<?php

    /*
     * Add help tabs
     */
    function csv2wp_help_tabs( $screen ) {

        if ( 'toplevel_page_csv2wp-dashboard' == $screen->id ) {
            ob_start();
            ?>
            <h5><?php esc_html_e( 'Upload a CSV file', 'csv-to-wp' ); ?></h5>
            <p><?php esc_html_e( 'On this page you can import a CSV fil and process it.', 'csv-to-wp' ); ?></p>
            <p><?php esc_html_e( 'You can only upload *.csv files.', 'csv-to-wp' ); ?></p>
            <?php
                $tab_content = ob_get_clean();

                get_current_screen()->add_help_tab( array(
                    'id'      => 'import-file',
                    'title'   => esc_html__( 'Upload a CSV file', 'csv-to-wp' ),
                    'content' => $tab_content
                ) );

                ob_start();
                ?>
            <h5><?php esc_html_e( 'Handle a CSV file', 'csv-to-wp' ); ?></h5>
            <p><?php esc_html_e( 'When a file is uploaded, there are 3 things you can do with it.', 'csv-to-wp' ); ?></p>
            <ul>
                <li><?php esc_html_e( 'Verify', 'csv-to-wp' ); ?>: <?php esc_html_e( 'This option verfies if all columns are equally sized.', 'csv-to-wp' ); ?></li>
                <li><?php esc_html_e( 'Import', 'csv-to-wp' ); ?>: <?php esc_html_e( 'This option imports the file (which also include a verification).', 'csv-to-wp' ); ?></li>
                <li><?php esc_html_e( 'Remove', 'csv-to-wp' ); ?>: <?php esc_html_e( 'This option deletes the file.', 'csv-to-wp' ); ?></li>
            </ul>
            <h5><?php esc_html_e( 'Verify a CSV file', 'csv-to-wp' ); ?></h5>
            <p><?php esc_html_e( 'Upon verification, the csv file is checked if every row/line has the same amount of columns.', 'csv-to-wp' ); ?></p>
            <p><?php esc_html_e( "If not, the file is deleted because it can't be used anymore due to the encounterd errors.", 'csv-to-wp' ); ?></p>
            <h5><?php esc_html_e( 'Import a CSV file', 'csv-to-wp' ); ?></h5>
            <p><?php esc_html_e( 'If you want to import int a table, your CSV must have a header row which is equal to your (table) column names.', 'csv-to-wp' ); ?></p>
            <p><?php esc_html_e( 'If you want to import post or user meta without a header row, you must use the following format: "post/user ID,meta_key,meta_value".', 'csv-to-wp' ); ?></p>
            <h5><?php esc_html_e( 'Delete a CSV file', 'csv-to-wp' ); ?></h5>
            <p><?php esc_html_e( 'Goes without saying, removes a file (from the uploads directory).', 'csv-to-wp' ); ?></p>
            <?php
            $tab_content = ob_get_clean();

            get_current_screen()->add_help_tab( array(
                'id'      => 'import-handle',
                'title'   => esc_html__( 'Handle a CSV file', 'csv-to-wp' ),
                'content' => $tab_content
            ) );

            ob_start();
            ?>
            <h5><?php esc_html_e( 'Import into table', 'csv-to-wp' ); ?></h5>
            <p><?php esc_html_e( 'A header row is obligated when you want to import into a table.', 'csv-to-wp' ); ?></p>
            <p><?php esc_html_e( "If you select 'import into table', a table is created with the column names you have in your CSV.", 'csv-to-wp' ); ?></p>
            <p><?php esc_html_e( "If a table already exists with this name, any new columns which exsts in the CSV but not in the table will be appended.", 'csv-to-wp' ); ?></p>
            <p><?php esc_html_e( "All values are inserted as 'text' since the plugin can't tell what type it is, based on the value alone. We will look into trying to 'set this' later on.", 'csv-to-wp' ); ?></p>
            <p><?php esc_html_e( "The maximum length of a variable is 254 characters.", 'csv-to-wp' ); ?></p>
            <?php
            $tab_content = ob_get_clean();

            get_current_screen()->add_help_tab( array(
                'id'      => 'import-table',
                'title'   => esc_html__( 'Import table', 'csv-to-wp' ),
                'content' => $tab_content
            ) );

            ob_start();
            ?>
            <h5><?php esc_html_e( 'Import into meta', 'csv-to-wp' ); ?></h5>
            <p><?php esc_html_e( 'There are 3 different ways to import post/user meta.', 'csv-to-wp' ); ?></p>

            <p>
                <b>1. <?php esc_html_e( 'With table headers', 'csv-to-wp' ); ?></b>
                <br />
                <?php esc_html_e( 'Header must be in the following format: `user id, meta key`.', 'csv-to-wp' ); ?>
                <br />
                <?php esc_html_e( 'Values must be in the following format: `user id, meta value`.', 'csv-to-wp' ); ?>
            </p>

            <p>
                <b>2. <?php esc_html_e( 'Without table headers', 'csv-to-wp' ); ?></b>
                <br />
                <?php esc_html_e( 'Must be in the following format: `user id, meta key, meta value`.', 'csv-to-wp' ); ?>
            </p>

            <p>
                <b>3. <?php esc_html_e( 'Without table headers but with a meta key', 'csv-to-wp' ); ?></b>
                <br />
                <?php esc_html_e( 'Must be in the following format: `user id, meta value`.', 'csv-to-wp' ); ?>
            </p>

            <?php
            $tab_content = ob_get_clean();

            get_current_screen()->add_help_tab( array(
                'id'      => 'import-meta',
                'title'   => esc_html__( 'Import meta', 'csv-to-wp' ),
                'content' => $tab_content
            ) );

            ob_start();
            ?>
            <h5><?php esc_html_e( 'Support', 'csv-to-wp' ); ?></h5>
            <p><?php echo sprintf( __( 'If you need support, please go to %s.', 'csv-to-wp' ), '<a href="' . esc_url( 'https://github.com/Beee4life/csv-to-wp/issues' ) . '">Github</a>' ); ?></p>
            <?php
            $tab_content = ob_get_clean();

            get_current_screen()->add_help_tab( array(
                'id'      => 'support',
                'title'   => esc_html__( 'Support', 'csv-to-wp' ),
                'content' => $tab_content
            ) );

        } elseif ( 'admin_page_csv2wp-preview' == $screen->id ) {

            ob_start();
            ?>
            <h5><?php esc_html_e( 'Preview data', 'csv-to-wp' ); ?></h5>
            <p><?php esc_html_e( 'On this page you can preview a CSV file before importing it.', 'csv-to-wp' ); ?></p>
            <p><?php esc_html_e( 'Please keep in mind that all csv files are verified before displaying (and therefor can be deleted, when errors are encountered).', 'csv-to-wp' ); ?></p>
            <p><?php esc_html_e( "If you select 'has header', the first table row will be bolded.", 'csv-to-wp' ); ?></p>
            <p><?php esc_html_e( 'You can limit the amount of lines you want to preview if you have a very large file.', 'csv-to-wp' ); ?></p>

            <?php
            $tab_content = ob_get_clean();

            get_current_screen()->add_help_tab( array(
                'id'      => 'preview-data',
                'title'   => esc_html__( 'Preview data', 'csv-to-wp' ),
                'content' => $tab_content
            ) );

            $sidebar_content = '<p><strong>' . esc_html__( "Author's website", 'csv-to-wp' ) . '</strong></p>';
            $sidebar_content .= '<p><a href="https://berryplasman.com">berryplasman.com</a></p>';
            get_current_screen()->set_help_sidebar( $sidebar_content );
        }

    }
    add_action( 'current_screen', 'csv2wp_help_tabs', 5 );
