<?php

    /**
     * Content for the settings page
     */
    function csvi_settings_page() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Sorry, you do not have sufficient permissions to access this page.', 'csv-importer' ) );
        }
        ?>

        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1>CSV Importer settings</h1>

	        <?php CSV_Importer::csvi_show_admin_notices(); ?>

            <div id="csv-importer" class="">

	            <?php echo CSV_Importer::csvi_admin_menu(); ?>

                <h2><?php esc_html_e( 'Select what to log', 'csv-importer' ); ?></h2>
                <p><?php esc_html_e( 'Here you can select which actions you want to log/ignore.', 'csv-importer' ); ?></p>
                <?php
                    $available_log_actions = get_option( 'al_available_log_actions' );
                    if ( $available_log_actions ) {
                        ?>
                        <form name="settings-form" id="settings-form" action="" method="post">
                            <input name="active_logs_nonce" type="hidden" value="<?php echo wp_create_nonce( 'active-logs-nonce' ); ?>"/>
                            <table>
                                <thead>
                                    <tr>
                                        <th class="hidden">Action name</th>
                                        <th class="">Action</th>
                                        <th class="">Generator</th>
                                        <th class="">Description</th>
                                        <th class="checkbox">Active</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $action_counter = 0; ?>
                                    <?php foreach( $available_log_actions as $action ) { ?>
                                        <?php $action_counter++; ?>
                                        <tr>
                                            <td class="hidden"><?php echo $action[ 'action_name' ]; ?></td>
                                            <td class=""><?php echo $action[ 'action_title' ]; ?></td>
                                            <td class=""><?php echo $action[ 'action_generator' ]; ?></td>
                                            <td class=""><?php echo $action[ 'action_description' ]; ?></td>
                                            <td class="checkbox">
                                                <?php
                                                    $active    = 0;
                                                    $checked   = false;
                                                    $is_active = get_option( 'al_' . $action[ 'action_name' ] );
                                                    if ( $is_active ) {
                                                        $checked = 'checked';
                                                        $active  = 1;
                                                    }
                                                ?>
                                                <label for="action-status" class="screen-reader-text">
                                                    <?php esc_html_e( 'Active', 'csv-importer' ); ?>
                                                </label>
                                                <input name="<?php echo $action[ 'action_name' ]; ?>" id="action-status" type="checkbox" value="<?php echo $active; ?>" <?php echo $checked; ?>/>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <h2><?php esc_html_e( 'Who can do what', 'csv-importer' ); ?></h2>
                            <p>
                                <?php esc_html_e( 'Here you can select what capability a user needs to see the logs. The default setting is "manage_options" which belongs to administrator.', 'csv-importer' ); ?>
                                <?php esc_html_e( 'The reason why it\'s set per capability instead of per user is because two users with the same role can have different capabilities.', 'csv-importer' ); ?>
                            </p>
                            <p>
                                <?php
                                    $all_capabilities = get_role( 'administrator' )->capabilities;
                                    $logs_user_role   = get_option( 'al_log_user_role' );
                                    ksort( $all_capabilities );
                                ?>
                                <select name="select_cap" id="select_cap">
                                    <?php foreach ( $all_capabilities as $key => $value ) { ?>
                                        <option value="<?php echo $key; ?>"<?php echo ( $logs_user_role == $key ? ' selected' : '' ); ?>><?php echo $key; ?></option>';
                                    <?php } ?>
                                </select>
                            </p>
                            <br />
                            <input type="submit" class="admin-button admin-button-small" value="Save settings" />
                        </form>
                <?php } ?>

            </div><!-- end #csv-importer -->

        <?php do_action('al_after_settings' ); ?>
        </div><!-- end .wrap -->
<?php
    }