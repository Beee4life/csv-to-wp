<?php

    /**
     * Content for the settings page
     */
    function csvi_misc_page() {

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

                <h2><?php esc_html_e( 'Export data to CSV', 'csv-importer' ); ?></h2>
                <p><?php esc_html_e( 'By clicking this button you will trigger a download for a CSV (comma separated value) file.', 'csv-importer' ); ?></p>
                <form name="export-form" action="" method="post">
                    <input name="export_csv" type="hidden" value="1" />
                    <input name="export_csv_nonce" type="hidden" value="<?php echo wp_create_nonce( 'export-csv-nonce' ); ?>"/>
                    <input name="" type="submit" class="admin-button admin-button-small" value="Export to CSV"/>
                </form>

                <h2><?php esc_html_e( 'Preserve data when uninstalling', 'csv-importer' ); ?></h2>
                <?php $checked = get_option( 'al_preserve_settings' ); ?>
                <form name="preserve-form" action="" method="post">
                    <input name="preserve_settings_nonce" type="hidden" value="<?php echo wp_create_nonce( 'preserve-settings-nonce' ); ?>"/>
                    <label for="preserve-settings" class="screen-reader-text">Preserve settings</label>
                    <input name="preserve_settings" id="preserve-settings" type="checkbox" value="1" <?php if ( false != $checked ) { echo 'checked '; } ?>/> <?php esc_html_e( 'If you uninstall the plugin, all data is removed as well. If you check this box, your logs won\'t be deleted upon uninstall.', 'csv-importer' ); ?>
                    <br />
                    <br />
                    <input name="" type="submit" class="admin-button admin-button-small" value="Save settings"/>
                </form>

                <h2><?php esc_html_e( 'Support', 'csv-importer' ); ?></h2>
                <p><?php echo sprintf( __( 'If you know about this plugin, you probably know me and know where to reach me. If not, please report it on GitHub in the %s.', 'csv-importer' ), '<a href="https://github.com/Beee4life/csv-importer/issues">issues section</a>' ); ?></p>
                <p><?php esc_html_e( 'Find more info about the plugin on', 'csv-importer' ); ?> <a href="https://github.com/Beee4life/csv-importer/">GitHub</a>.</p>

                <h2><?php esc_html_e( 'About the author', 'csv-importer' ); ?></h2>
                <p>This plugin is created by <a href="http://www.berryplasman.com">Beee</a>, a Wordpress developer from Amsterdam.</p>

            </div><!-- end #csv-importer -->

        <?php do_action('al_after_settings' ); ?>
        </div><!-- end .wrap -->
<?php
    }
