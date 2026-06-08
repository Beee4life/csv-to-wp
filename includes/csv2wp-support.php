<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    /**
     * Content for the settings page
     */
    function csv2wp_support_page() {

        if ( ! current_user_can( get_option( 'csv2wp_import_role' ) ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'csv-to-wp' ) );
        }
        ?>

        <div class="wrap csv2wp">
            <div id="icon-options-general" class="icon32"><br/></div>

            <h1>CSV to WP - <?php esc_html_e( 'Support', 'csv-to-wp' ); ?></h1>

            <?php CSV2WP::csv2wp_show_admin_notices(); ?>

            <div class="">

                <?php echo wp_kses_post( CSV2WP::csv2wp_admin_menu() ); ?>

                <h2><?php esc_html_e( 'Support', 'csv-to-wp' ); ?></h2>
                <?php // translators: link to github issues ?>
                <p><?php echo sprintf( esc_html( __( 'If you know about this plugin, you probably know me and know where to reach me. If not, please report it on GitHub in the %s.', 'csv-to-wp' ) ), sprintf( '<a href="%s">issues section</a>', esc_url( 'https://github.com/Beee4life/csv-to-wp/issues' ) ) ); ?></p>
                <p>
                    <?php esc_html_e( 'Find more info about the plugin on', 'csv-to-wp' ); ?> <a href="https://github.com/Beee4life/csv-to-wp/">GitHub</a>.
                </p>

            </div>

        </div>
        <?php
    }
