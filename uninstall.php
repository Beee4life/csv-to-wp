<?php

    // If uninstall.php is not called by WordPress, die
    if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
        die;
    }

    // Remove options
	delete_option( 'csv2wp_import_role' );
    // $options   = get_option( 'csv2wp_' );
    // foreach ( $options as $key ) {
    //     delete_option( 'csv2wp_' . $key );
    // }
