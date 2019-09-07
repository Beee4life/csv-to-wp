<?php
    
    /**
     * Content for the settings page
     */
    function csv2wp_settings_page() {
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'csv2wp' ) );
        }
        ?>

        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br/></div>

            <h1>CSV to WP - <?php esc_html_e( 'Settings', 'csv2wp' ); ?></h1>
            
            <?php CSV2WP::csv2wp_show_admin_notices(); ?>
    
            <div class="csv2wp">
                
                <?php echo CSV2WP::csv2wp_admin_menu(); ?>

                <form name="settings-form" id="settings-form" action="" method="post">
                    <input name="settings_page_nonce" type="hidden" value="<?php echo wp_create_nonce( 'settings-page-nonce' ); ?>"/>
                    
                    <h2><?php esc_html_e( 'Who can import CSV data ?', 'csv2wp' ); ?></h2>
                    <p>
                        <?php esc_html_e( 'Here you can select what capability a user needs to import any data. The default setting is "manage_options" which belongs to administrator.', 'csv2wp' ); ?>
                            <br />
                        <?php esc_html_e( "The reason why it's set per capability instead of per user is because two users with the same role can have different capabilities.", "csv2wp" ); ?>
                    </p>

                    <?php
                        $all_capabilities = get_role( 'administrator' )->capabilities;
                        $import_user_role = get_option( 'csv2wp_import_role' );
                        ksort( $all_capabilities );
                    ?>
                    <label>
                        <select name="csv2wp_select_cap" id="csv2wp_select_cap">
                            <?php foreach ( $all_capabilities as $key => $value ) { ?>
                                <option value="<?php echo $key; ?>"<?php echo( $import_user_role == $key ? ' selected' : '' ); ?>><?php echo $key; ?></option>
                            <?php } ?>
                        </select>
                    </label>

                    <br/><br/>
    
                    <h2><?php esc_html_e( 'Preserve settings', 'csv2wp' ); ?></h2>

                    <p>
                        <label>
                            <input type="checkbox" class="" value="csv2wp_preserve_settings"/> <?php esc_html_e( "If you check this box NO settings will be deleted upon uninstallation.", "csv2wp" ); ?>
                        </label>
                    </p>

                    <br/>

                    <input type="submit" class="admin-button admin-button-small" value="<?php esc_html_e( 'Save settings', 'csv2wp' ); ?>"/>
                </form>

            </div>

        </div>
    <?php }
