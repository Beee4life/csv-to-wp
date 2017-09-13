<?php

    /**
     * Content for the settings page
     */
    function csvi_settings_page() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Sorry, you do not have sufficient permissions to access this page.', 'csv2wp' ) );
        }
        ?>

        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1>CSV Importer settings</h1>

	        <?php CSV_Importer::csvi_show_admin_notices(); ?>

            <div id="csv-importer" class="">

	            <?php echo CSV_Importer::csvi_admin_menu(); ?>

                <h2><?php esc_html_e( 'Who can import csv data ?', 'csv2wp' ); ?></h2>
                <p>
		            <?php esc_html_e( 'Here you can select what capability a user needs to import any data. The default setting is "manage_options" which belongs to administrator.', 'csv2wp' ); ?>
		            <?php esc_html_e( 'The reason why it\'s set per capability instead of per user is because two users with the same role can have different capabilities.', 'csv2wp' ); ?>
                </p>

                <form name="settings-form" id="settings-form" action="" method="post">
                    <input name="active_logs_nonce" type="hidden" value="<?php echo wp_create_nonce( 'active-logs-nonce' ); ?>"/>
                    <?php
                        $all_capabilities = get_role( 'administrator' )->capabilities;
                        $logs_user_role   = get_option( 'al_log_user_role' );
                        ksort( $all_capabilities );
                    ?>
                    <label for="select_cap" class="screen-reader-text"></label>
                    <select name="select_cap" id="select_cap">
                        <?php foreach ( $all_capabilities as $key => $value ) { ?>
                            <option value="<?php echo $key; ?>"<?php echo ( $logs_user_role == $key ? ' selected' : '' ); ?>><?php echo $key; ?></option>';
                        <?php } ?>
                    </select>
                    <br /><br />
                    <input type="submit" class="admin-button admin-button-small" value="Save settings" />
                </form>

            </div><!-- end #csv-importer -->

        <?php do_action('al_after_settings' ); ?>
        </div><!-- end .wrap -->
<?php
    }
