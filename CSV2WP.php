<?php
    /*
    Plugin Name: CSV to WP
    Version: 0.1
    Plugin URI: https://github.com/Beee4life/csv-to-wp/
    Description: This plugin allows you to import an verify CSV data and imports it to your WordPress database.
    Author: Beee
    Author URI: http://berryplasman.com
    Text-domain: csv2wp
    License: GPL2
       ___  ____ ____ ____
      / _ )/ __/  __/  __/
     / _  / _/   _/   _/
    /____/___/____/____/

    */
    
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    } // Exit if accessed directly
    
    if ( ! class_exists( 'CSV2WP' ) ) :
        
        class CSV2WP {
            var $settings;
            
            public function initialize() {
                $this->settings = array(
                    'path'    => trailingslashit( dirname( __FILE__ ) ),
                    'version' => '1.0.0',
                );
                
                // (de)activation hooks
                register_activation_hook( __FILE__, array( $this, 'csv2wp_plugin_activation' ) );
                register_deactivation_hook( __FILE__, array( $this, 'csv2wp_plugin_deactivation' ) );
                
                // add settings link to plugin
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array(
                    $this,
                    'csv2wp_plugin_link'
                ) );
                
                // actions
                add_action( 'admin_menu', array( $this, 'csv2wp_add_admin_pages' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'csv2wp_enqueue_css' ) );
                add_action( 'admin_init', array( $this, 'csv2wp_errors' ) );
                add_action( 'admin_init', array( $this, 'csv2wp_admin_menu' ) );
                
                // csv actions
                add_action( 'admin_init', array( $this, 'csv2wp_upload_functions' ) );
                add_action( 'admin_init', array( $this, 'csv2wp_handle_file_functions' ) );
                add_action( 'admin_init', array( $this, 'csv2wp_create_uploads_directory' ) );
                // add_action( 'admin_init',               array( $this, 'csv2wp_import_raw_csv_data' ) );
                
                
                include( 'includes/functions.php' );
                // include( 'not-in-use.php' );
                
                $this->csv2wp_create_uploads_directory();
                
            }
            
            // @TODO: preview raw imported data
            
            
            /**
             * Function which runs upon plugin deactivation
             */
            public function csv2wp_plugin_activation() {
                $this->csv2wp_store_default_values();
            }
            
            /**
             * Function which runs upon plugin deactivation
             */
            public function csv2wp_plugin_deactivation() {
                delete_option( 'csv2wp_import_role' );
            }
            
            public function csv2wp_create_uploads_directory() {
                if ( true != is_dir( plugin_dir_path( __FILE__ ) . 'uploads' ) ) {
                    mkdir( plugin_dir_path( __FILE__ ) . 'uploads', 0755 );
                }
            }
            
            /**
             * Store default values (upon activation)
             */
            public function csv2wp_store_default_values() {
                update_option( 'csv2wp_import_role', 'manage_options' );
            }
            
            /**
             * @return WP_Error
             */
            public static function csv2wp_errors() {
                static $wp_error; // Will hold global variable safely
                
                return isset( $wp_error ) ? $wp_error : ( $wp_error = new WP_Error( null, null, null ) );
            }
            
            /**
             * Displays error messages from form submissions
             */
            public static function csv2wp_show_admin_notices() {
                if ( $codes = CSV2WP::csv2wp_errors()->get_error_codes() ) {
                    if ( is_wp_error( CSV2WP::csv2wp_errors() ) ) {
                        
                        // Loop error codes and display errors
                        $error      = false;
                        $span_class = false;
                        $prefix     = false;
                        foreach ( $codes as $code ) {
                            if ( strpos( $code, 'success' ) !== false ) {
                                $span_class = 'notice-success ';
                                $prefix     = false;
                            } elseif ( strpos( $code, 'warning' ) !== false ) {
                                $span_class = 'notice-warning ';
                                $prefix     = esc_html( __( 'Warning', 'csv2wp' ) );
                            } elseif ( strpos( $code, 'info' ) !== false ) {
                                $span_class = 'notice-info ';
                                $prefix     = false;
                            } else {
                                $error      = true;
                                $span_class = 'notice-error ';
                                $prefix     = esc_html( __( 'Error', 'csv2wp' ) );
                            }
                        }
                        echo '<div class="notice ' . $span_class . 'is-dismissible">';
                        foreach ( $codes as $code ) {
                            $message = CSV2WP::csv2wp_errors()->get_error_message( $code );
                            echo '<div class="">';
                            if ( true == $prefix ) {
                                echo '<strong>' . $prefix . ':</strong> ';
                            }
                            echo $message;
                            echo '</div>';
                            echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html( __( 'Dismiss this notice', 'csv2wp' ) ) . '</span></button>';
                        }
                        echo '</div>';
                    }
                }
            }
            
            /**
             * Handle raw uploaded csv data
             */
            public function csv2wp_import_raw_csv_data() {
                
                if ( current_user_can( 'manage_options' ) && isset( $_POST[ "import_raw_rankings_nonce" ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ "import_raw_rankings_nonce" ], 'import-raw-rankings-nonce' ) ) {
                        CSV2WP::csv2wp_errors()->add( 'error_nonce_no_match', __( 'Something went wrong. Please try again.', 'csv2wp' ) );
                        
                        return;
                    } else {
                        // nonce ok + verified
    
                        $verify    = ! empty( $_POST[ 'verify' ] ) ? $_POST[ 'verify' ] : false;
                        $raw_data  = ! empty( $_POST[ 'raw_csv_import' ] ) ? $_POST[ 'raw_csv_import' ] : false;
                        $csv_array = csv2wp_verify_raw_csv_data( $raw_data );
                        
                        if ( false != $csv_array ) {
                            
                            if ( false != $verify ) {
                                CSV2WP::csv2wp_errors()->add( 'success_no_errors_in_csv', __( 'Congratulations, there appear to be no errors in your CSV.', 'csv2wp' ) );
                                
                                return;
                            } else {
                                if ( count( $csv_array ) > 0 ) {
                                    $count = 0;
                                    foreach ( $csv_array as $csv_line ) {
                                        // do something with $csv_line
                                        $count++;
                                    }
                                    CSV2WP::csv2wp_errors()->add( 'success_raw_data_imported', __( $count . ' lines imported through raw import.', 'csv2wp' ) );
                                }
                            }
                        }
                    }
                }
            }
            
            /**
             * Read uploaded file for verification or import
             * Or delete the file
             */
            public function csv2wp_handle_file_functions() {
                
                if ( current_user_can( 'manage_options' ) && isset( $_POST[ "select_file_nonce" ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ "select_file_nonce" ], 'select-file-nonce' ) ) {
                        CSV2WP::csv2wp_errors()->add( 'error_nonce_no_match', esc_html( __( 'Something went wrong. Please try again.', 'csv2wp' ) ) );
                        
                        return;
                    } else {
                        // nonce ok + verified
                        
                        if ( ! isset( $_POST[ 'csv2wp_file_name' ] ) ) {
                            CSV2WP::csv2wp_errors()->add( "error_no_file_selected", __( "You didn't select a file.", "csv2wp" ) );
                            
                            return;
                        }
                        
                        $remove    = ! empty( $_POST[ 'csv2wp_remove' ] ) ? $_POST[ 'csv2wp_remove' ] : false;
                        $verify    = ! empty( $_POST[ 'csv2wp_verify' ] ) ? $_POST[ 'csv2wp_verify' ] : false;
                        $file_name = ! empty( $_POST[ 'csv2wp_file_name' ] ) ? $_POST[ 'csv2wp_file_name' ] : false;
                        
                        if ( false == $remove ) {
                            
                            if ( count( $_POST[ 'csv2wp_file_name' ] ) > 1 ) {
                                CSV2WP::csv2wp_errors()->add( "error_too_many_files", __( "Only 1 file at a time please, otherwise the error messages can be off. And yes, it's already on the todo list.", "csv2wp" ) );
                                
                                return;
                            }
                            
                            $csv_array = csv2wp_csv_to_array( $file_name[ 0 ], $verify );
                            
                            if ( is_array( $csv_array ) ) {
                                $line_number = count( $csv_array );
                            }
                            
                            // import data or delete file
                            if ( false == $verify ) {
                                // verify = false, so this is for real, this is no verification, there are no errors, so files can be processed
                                
                                // @TODO: do something with the data ($csv_array) here
                                
                                foreach ( $_POST[ 'csv2wp_file_name' ] as $file ) {
                                    // delete file
                                    unlink( plugin_dir_path( __FILE__ ) . 'uploads/' . $file );
                                }
                                do_action( 'csv2wp_successful_csv_import', $line_number );
                                CSV2WP::csv2wp_errors()->add( 'success_data_imported', __( 'YAY ! ' . $line_number . ' lines are imported and the file is deleted.', 'csv2wp' ) );
                                
                                return;
                            } else {
                                do_action( 'csv2wp_successful_csv_validate', $file_name );
                                CSV2WP::csv2wp_errors()->add( 'success_no_errors_in_csv', esc_html( __( 'Congratulations, there are no errors in your CSV.', 'csv2wp' ) ) );
                                
                                return;
                            }
                            
                        } else {
                            
                            if ( isset( $_POST[ 'csv2wp_file_name' ] ) ) {
                                foreach ( $_POST[ 'csv2wp_file_name' ] as $file_name ) {
                                    // delete file
                                    unlink( plugin_dir_path( __FILE__ ) . 'uploads/' . $file_name );
                                }
                                if ( count( $_POST[ 'csv2wp_file_name' ] ) == 1 ) {
                                    CSV2WP::csv2wp_errors()->add( 'success_file_deleted', __( 'File "' . $file_name . '" successfully deleted.', 'csv2wp' ) );
                                } else {
                                    CSV2WP::csv2wp_errors()->add( 'success_files_deleted', esc_html( __( 'Files successfully deleted.', 'csv2wp' ) ) );
                                }
                            }
                            
                        }
                    }
                }
            }
            
            /**
             * Upload a CSV file
             */
            public function csv2wp_upload_functions() {
                
                if ( current_user_can( 'manage_options' ) && isset( $_POST[ "upload_file_nonce" ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ "upload_file_nonce" ], 'upload-file-nonce' ) ) {
                        CSV2WP::csv2wp_errors()->add( 'error_nonce_no_match', esc_html( __( 'Something went wrong. Please try again.', 'csv2wp' ) ) );
                        
                        return;
                    } else {
                        
                        if ( true != is_dir( plugin_dir_path( __FILE__ ) . 'uploads' ) ) {
                            mkdir( plugin_dir_path( __FILE__ ) . 'uploads', 0755 );
                        }
                        $target_file = plugin_dir_path( __FILE__ ) . 'uploads/' . basename( $_FILES[ 'csv_upload' ][ 'name' ] );
                        
                        if ( move_uploaded_file( $_FILES[ 'csv_upload' ][ 'tmp_name' ], '/' . $target_file ) ) {
                            // file uploaded succeeded
                            do_action( 'csv2wp_successful_csv_upload' );
                            CSV2WP::csv2wp_errors()->add( 'success_file_uploaded', __( 'File "' . $_FILES[ 'csv_upload' ][ 'name' ] . '" is successfully uploaded and now shows under \'Select files to import\'.', 'csv2wp' ) );
                            
                            return;
                            
                        } else {
                            // file upload failed
                            CSV2WP::csv2wp_errors()->add( 'error_file_uploaded', esc_html( __( 'Upload failed. Please try again.', 'csv2wp' ) ) );
                            
                            return;
                        }
                    }
                }
            }
            
            /**
             * Adds a link in the plugin menu
             *
             * @param $links
             *
             * @return array
             */
            public function csv2wp_plugin_link( $links ) {
                $add_this = array(
                    '<a href="' . admin_url( 'admin.php?page=csv2wp-settings' ) . '">Settings</a>',
                );
                
                return array_merge( $add_this, $links );
            }
            
            /**
             * Adds a menu on top of the pages
             * @return string
             */
            public static function csv2wp_admin_menu() {
                
                $menu = '<p><a href="' . admin_url() . 'admin.php?page=csv2wp-dashboard">' . esc_html( __( 'Dashboard', 'csv2wp' ) ) . '</a>';
                if ( is_array( csv2wp_check_if_files() ) ) {
                    $menu .= ' | <a href="' . admin_url() . 'admin.php?page=csv2wp-preview">' . esc_html( __( 'Preview file', 'csv2wp' ) ) . '</a>';
                }
                $menu .= ' | <a href="' . admin_url() . 'admin.php?page=csv2wp-settings">' . esc_html( __( 'Settings', 'csv2wp' ) ) . '</a>';
                $menu .= ' | <a href="' . admin_url() . 'admin.php?page=csv2wp-support">' . esc_html( __( 'Support', 'csv2wp' ) ) . '</a>';
                
                return $menu;
                
            }
            
            /**
             * Create admin pages
             */
            public function csv2wp_add_admin_pages() {
                add_menu_page( 'CSV Importer', 'CSV to WP', 'manage_options', 'csv2wp-dashboard', 'csv2wp_dashboard_page', 'dashicons-grid-view' );
                require( 'includes/csv2wp-dashboard.php' );
                add_submenu_page( 'csv2wp-dashboard', 'Preview', 'Preview', 'manage_options', 'csv2wp-preview', 'csv2wp_preview_page' );
                require( 'includes/csv2wp-preview.php' ); // content for the settings page
                add_submenu_page( 'csv2wp-dashboard', 'Settings', 'Settings', 'manage_options', 'csv2wp-settings', 'csv2wp_settings_page' );
                require( 'includes/csv2wp-settings.php' ); // content for the settings page
                add_submenu_page( 'csv2wp-dashboard', 'Support', 'Support', 'manage_options', 'csv2wp-support', 'csv2wp_support_page' );
                require( 'includes/csv2wp-support.php' ); // content for the settings page
            }
            
            /**
             * Enqueue CSS
             */
            public function csv2wp_enqueue_css() {
                wp_register_style( 'csv2wp', plugins_url( 'style.css', __FILE__ ), false, '1.0.0' );
                wp_enqueue_style( 'csv2wp' );
            }
            
        }
        
        /**
         * The main function responsible for returning the one true CSV2WP instance to functions everywhere.
         *
         * @return \CSV2WP
         */
        function init_csv_importer_plugin() {
            global $csv_importer_plugin;
            
            if ( ! isset( $csv_importer_plugin ) ) {
                $csv_importer_plugin = new CSV2WP();
                $csv_importer_plugin->initialize();
            }
            
            return $csv_importer_plugin;
        }
        
        // initialize
        // init_csv_importer_plugin();
    
    endif; // class_exists check
    
    $csv_importer_plugin = new CSV2WP();
    $csv_importer_plugin->initialize();
