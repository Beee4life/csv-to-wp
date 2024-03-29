<?php

    /**
     * Content for the settings page
     */
    function csv2wp_support_page() {

        if ( ! current_user_can( get_option( 'csv2wp_import_role' ) ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'csv2wp' ) );
        }
        ?>

        <div class="wrap csv2wp">
            <div id="icon-options-general" class="icon32"><br/></div>

            <h1>CSV to WP - <?php esc_html_e( 'Support', 'csv2wp' ); ?></h1>

            <?php CSV2WP::csv2wp_show_admin_notices(); ?>

            <div class="">

                <?php echo CSV2WP::csv2wp_admin_menu(); ?>

                <h2><?php esc_html_e( 'Support', 'csv2wp' ); ?></h2>
                <p><?php echo sprintf( __( 'If you know about this plugin, you probably know me and know where to reach me. If not, please report it on GitHub in the <a href="%s">issues section</a>.', 'csv2wp' ), esc_url( 'https://github.com/Beee4life/csv-to-wp/issues' ) ); ?></p>
                <p>
                    <?php esc_html_e( 'Find more info about the plugin on', 'csv2wp' ); ?> <a href="https://github.com/Beee4life/csv-to-wp/">GitHub</a>.
                </p>

            </div>

        </div>
        <?php
    }
