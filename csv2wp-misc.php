<?php

    /**
     * Content for the settings page
     */
    function csv2wp_misc_page() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Sorry, you do not have sufficient permissions to access this page.', 'csv2wp' ) );
        }
        ?>

        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1>CSV Importer misc settings</h1>

	        <?php CSV_WP::csv2wp_show_admin_notices(); ?>

            <div id="csv-importer" class="">

	            <?php echo CSV_WP::csv2wp_admin_menu(); ?>

                <h2><?php esc_html_e( 'About the author', 'csv2wp' ); ?></h2>
                <p>This plugin is created by <a href="http://www.berryplasman.com">Beee</a>, a Wordpress developer from Amsterdam.</p>

            </div><!-- end #csv-importer -->

        <?php do_action('al_after_settings' ); ?>
        </div><!-- end .wrap -->
<?php
    }
