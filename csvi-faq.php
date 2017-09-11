<?php

    /**
     * Content for the settings page
     */
    function csvi_faq_page() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Sorry, you do not have sufficient permissions to access this page.', 'csv-importer' ) );
        }
        ?>

        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1>CSV Importer misc settings</h1>

	        <?php CSV_Importer::csvi_show_admin_notices(); ?>

            <div id="csv-importer" class="">

	            <?php echo CSV_Importer::csvi_admin_menu(); ?>

                <h2><?php esc_html_e( 'Support', 'csv-importer' ); ?></h2>
                <p><?php echo sprintf( __( 'If you know about this plugin, you probably know me and know where to reach me. If not, please report it on GitHub in the %s.', 'csv-importer' ), '<a href="https://github.com/Beee4life/csv-importer/issues">issues section</a>' ); ?></p>
                <p><?php esc_html_e( 'Find more info about the plugin on', 'csv-importer' ); ?> <a href="https://github.com/Beee4life/csv-importer/">GitHub</a>.</p>

            </div><!-- end #csv-importer -->

        <?php do_action('al_after_settings' ); ?>
        </div><!-- end .wrap -->
<?php
    }
