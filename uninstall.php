<?php
    
    // if uninstall.php is not called by WordPress, die
    if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
        die;
    }
    
    // drop table
    if ( false == get_option( 'csv2wp_preserve_settings' ) ) {
        $target_folder = wp_upload_dir()[ 'basedir' ] . '/csv2wp';
        rmdir( $target_folder );
    }

?>
