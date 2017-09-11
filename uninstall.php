<?php

    // If uninstall.php is not called by WordPress, die
    if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
        die;
    }

    // Remove CSV Importer options
    // $options   = get_option( 'csvi_' );
    // foreach ( $options as $key ) {
    //     delete_option( 'csvi_' . $key );
    // }
