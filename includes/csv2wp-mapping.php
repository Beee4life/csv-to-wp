<?php
    
    /**
     * Content for the settings page
     */
    function csv2wp_mapping_page() {
        
        if ( ! current_user_can( get_option( 'csv2wp_import_role' ) ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'csv2wp' ) );
        }
        ?>

        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br/></div>

            <h1>CSV to WP Mapping</h1>
            
            <?php CSV2WP::csv2wp_show_admin_notices(); ?>
    
            <div class="csv2wp">
                
                <?php echo CSV2WP::csv2wp_admin_menu(); ?>

            </div>

        </div>
        <?php
    }
