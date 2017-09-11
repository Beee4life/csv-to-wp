<?php
    /*
    Plugin Name: CSV Importer (Beee)
    Version: 0.1
    Plugin URI: http://www.berryplasman.com
    Description: This plugin allows you to import an verify CSV data and import it wherever you want in the database.
    Author: Beee
    Author URI: http://berryplasman.com
    Text-domain: csv-importer

            http://www.berryplasman.com
               ___  ____ ____ ____
              / _ )/ __/  __/  __/
             / _  / _/   _/   _/
            /____/___/____/____/

    */

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    if ( ! class_exists( 'CSV_Importer' ) ) :

        class CSV_Importer {
            var $settings;

            function __construct() {}

            function initialize() {
                $this->settings = array(
                    'path'      => trailingslashit( dirname( __FILE__ ) ),
                    'version'   => '1.0.0',
                );

	            // (de)activation hooks
	            register_activation_hook( __FILE__,     array( $this, 'csvi_plugin_activation' ) );
	            register_deactivation_hook( __FILE__,   array( $this, 'csvi_plugin_deactivation' ) );

                // actions
                add_action( 'admin_menu',               array( $this, 'csvi_add_dashboard_page' ) );
	            add_action( 'admin_menu',               array( $this, 'csvi_add_settings_page' ) );
	            add_action( 'admin_menu',               array( $this, 'csvi_add_misc_page' ) );
                add_action( 'admin_enqueue_scripts',    array( $this, 'enqueue_csvi_css' ) );

                // csv actions
                add_action( 'admin_init',               array( $this, 'upload_file_functions' ) );
                add_action( 'admin_init',               array( $this, 'read_file_functions' ) );
                add_action( 'admin_init',               array( $this, 'import_raw_csv_data' ) );
                // add_action( 'admin_init',               array( $this, 'delete_individual_ranking' ) );
                // add_action( 'admin_init',               array( $this, 'nuke_all_data' ) );

	            // misc actions
	            add_action( 'admin_init',               array( $this, 'csvi_errors' ) );
	            add_action( 'admin_init',               array( $this, 'csvi_admin_menu' ) );

	            include( 'verify-csv-data.php' );
	            include( 'not-in-use.php' );

            }
            /**
             * Function which runs upon plugin deactivation
             */
            public function csvi_plugin_activation() {
                $this->csvi_create_uploads_directory();
                // $this->csvi_store_default_values();
            }

            /**
             * Function which runs upon plugin deactivation
             */
            public function csvi_plugin_deactivation() {
            }

	        public function csvi_create_uploads_directory() {
            	if ( true != is_dir( plugin_dir_path( __FILE__ ) . 'uploads' ) ) {
		            mkdir(plugin_dir_path( __FILE__ ) . 'uploads', 0755 );
	            }
	        }

	        /**
	         * @return WP_Error
	         */
	        public static function csvi_errors() {
		        static $wp_error; // Will hold global variable safely
		        return isset( $wp_error ) ? $wp_error : ( $wp_error = new WP_Error( null, null, null ) );
	        }

	        /**
	         * Displays error messages from form submissions
	         */
	        public static function csvi_show_admin_notices() {
		        if ( $codes = CSV_Importer::csvi_errors()->get_error_codes() ) {
			        if ( is_wp_error( CSV_Importer::csvi_errors() ) ) {

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
						        $prefix     = esc_html( __( 'Warning', 'action-logger' ) );
					        } elseif ( strpos( $code, 'info' ) !== false ) {
						        $span_class = 'notice-info ';
						        $prefix     = false;
					        } else {
						        $error  = true;
						        $prefix = esc_html( __( 'Error', 'action-logger' ) );
					        }
				        }
				        echo '<div class="notice ' . $span_class . 'is-dismissible">';
				        foreach( $codes as $code ) {
					        $message = CSV_Importer::csvi_errors()->get_error_message( $code );
					        echo '<div class="">';
					        if ( true == $prefix ) {
						        echo '<strong>' . $prefix . ':</strong> ';
					        }
					        echo $message;
					        echo '</div>';
					        echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html( __( 'Dismiss this notice', 'action-logger' ) ) . '</span></button>';
				        }
				        echo '</div>';
			        }
		        }
	        }

            /**
             * Import raw uploaded csv data
             */
            public function import_raw_csv_data() {

                if ( current_user_can( 'manage_options' ) && isset( $_POST[ "import_raw_rankings_nonce" ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ "import_raw_rankings_nonce" ], 'import-raw-rankings-nonce' ) ) {
                        CSV_Importer::csvi_errors()->add( 'error_nonce_no_match', __( 'Something went wrong. Please try again.', 'cvs-importer' ) );

                        return;
                    } else {
                        // nonce ok + verified

                        $raw_data      = $_POST[ 'raw_csv_import' ];
                        $verified_data = verify_csv_data( $raw_data );
                        $verify        = ! empty( $_POST[ 'verify' ] ) ? $_POST[ 'verify' ] : false;

                        if ( false != $verified_data ) {

                            if ( false != $verify ) {
                                CSV_Importer::csvi_errors()->add( 'success_no_errors_in_csv', __( 'Congratulations, there are no errors in your CSV.', 'cvs-importer' ) );

                                return;
                            } else {
                                if ( count( $verified_data ) > 0 ) {
                                    $count = 0;
                                    foreach ( $verified_data as $csv_line ) {
                                        $idf_number = $csv_line[ 0 ];
                                        $year       = $csv_line[ 1 ];
                                        $category   = $csv_line[ 2 ];
                                        $ranking    = $csv_line[ 3 ];
                                        $points     = $csv_line[ 4 ];
                                        $new_value  = array(
                                            $year => array(
                                                'year'          => $year,
                                                'category_name' => $category,
                                                'ranking'       => $ranking,
                                                'points'        => $points,
                                            ),
                                        );

                                        $race_rankings = get_user_meta( $idf_number, 'race_rankings', true );
                                        $user_meta     = ! empty( $race_rankings ) ? $race_rankings : false;
                                        if ( false != $user_meta ) {
                                            $new_value = array_merge( $new_value, $user_meta );
                                            asort( $new_value );
                                        }
                                        update_user_meta( $idf_number, 'race_rankings', $new_value );
                                        $count++;
                                    }
                                    if ( class_exists( 'ActionLogger' ) ) {
                                        ActionLogger::al_log_user_action( 'import_raw', 'cvs-importer', ' uploaded ' . $count . ' lines through raw import' );
                                    }
                                    CSV_Importer::csvi_errors()->add( 'success_rankings_imported', __( $count . ' lines imported through raw import. You can check the last user which is imported <a href="' . get_author_posts_url( $idf_number ) . '">here</a>.', 'cvs-importer' ) );
                                }
                            }
                        }
                    }
                }
            }

            /**
             * Read uploaded file for verification or import
             * Delete file is also included in this function
             */
            public function read_file_functions() {

                if ( current_user_can( 'manage_options' ) && isset( $_POST[ "select_file_nonce" ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ "select_file_nonce" ], 'select-file-nonce' ) ) {
                        CSV_Importer::csvi_errors()->add( 'error_nonce_no_match', __( 'Something went wrong. Please try again.', 'cvs-importer' ) );

                        return;
                    } else {
                        // nonce ok + verified

                        if ( ! isset( $_POST[ 'file_name' ] ) ) {
                            CSV_Importer::csvi_errors()->add( 'error_no_file_selected', __( 'You didn\'t select a file.', 'cvs-importer' ) );

                            return;
                        }

                        $remove    = ! empty( $_POST[ 'remove' ] ) ? $_POST[ 'remove' ] : false;
                        $verify    = ! empty( $_POST[ 'verify' ] ) ? $_POST[ 'verify' ] : false;
                        $file_name = ! empty( $_POST[ 'file_name' ] ) ? $_POST[ 'file_name' ] : false;

                        if ( false == $remove ) {

                            if ( count( $_POST[ 'file_name' ] ) != 1 ) {
                                CSV_Importer::csvi_errors()->add( 'error_too_many_files', __( 'Only 1 file at a time please, otherwise the error messages can be off. And yes, it\'s already on the todo list.', 'cvs-importer' ) );

                                return;
                            }

                            // read file
                            if (($handle = fopen( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name[0], "r")) !== FALSE) {
                                $line_number = 0;

                                while (($csv_line = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                    $line_number++;

                                    if ( count( $csv_line ) != 5 ) {
                                        if ( count( $csv_line ) < 5 ) {
                                            if ( false != $verify ) {
                                                $error_message = 'Since your file is not accurate anymore, the file is deleted.';
                                            } else {
                                                $error_message = 'Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                                            }
                                            CSV_Importer::csvi_errors()->add( 'error_no_correct_columns', sprintf( __( 'There are too few columns on line %d. %s', 'cvs-importer' ), $line_number, $error_message ) );

                                        } elseif ( count( $csv_line ) > 5 ) {
                                            if ( false != $verify ) {
                                                CSV_Importer::csvi_errors()->add( 'error_no_correct_columns', __( 'There are too many columns on line ' . $line_number . '. Lines 0-' . ( $line_number - 1 ) . ' Since your file is not accurate anymore, the file is deleted.', 'cvs-importer' ) );
                                            } else {
                                                CSV_Importer::csvi_errors()->add( 'error_no_correct_columns', __( 'There are too many columns on line ' . $line_number . '. Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted.', 'cvs-importer' ) );
                                            }
                                        }
                                        foreach( $_POST[ 'file_name' ] as $file_name ) {
                                            // delete file
                                            unlink( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name );
                                        }

                                        return;
                                    }

                                    foreach( $csv_line as $line ) {
                                        if ( $line == '0' ) {
                                            if ( false != $verify ) {
                                                CSV_Importer::csvi_errors()->add( 'error_zero_value', __( 'There\'s a zero value on line ' . $line_number . '. Since your file is not accurate anymore, it\'s deleted. ', 'cvs-importer' ) );
                                            } else {
                                                CSV_Importer::csvi_errors()->add( 'error_zero_value', __( 'There\'s a zero value on line ' . $line_number . '. Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, therefor it\'s deleted.', 'cvs-importer' ) );
                                            }
                                            foreach( $_POST[ 'file_name' ] as $file_name ) {
                                                // delete file
                                                unlink( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name );
                                            }

                                            return;
                                        }
                                    }

                                    $idf_number = $csv_line[ 0 ];
                                    $user_data  = get_userdata( intval( $idf_number ) );
                                    if ( false == $user_data ) {
                                        // user does not exist, so check for old ids
                                    }

                                    $year = $csv_line[ 1 ];
                                    if ( strlen( $year ) != 4 ) {
                                        if ( false != $verify ) {
                                            CSV_Importer::csvi_errors()->add( 'error_zero_value', __( 'There\'s a year which is not 4 characters long on line ' . $line_number . '. Since your file is not accurate anymore, it\'s deleted. ', 'cvs-importer' ) );
                                        } else {
                                            CSV_Importer::csvi_errors()->add( 'error_zero_value', __( 'There\'s a year which is not 4 characters long on line ' . $line_number . '. Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, therefor it\'s deleted.', 'cvs-importer' ) );
                                        }
                                        foreach( $_POST[ 'file_name' ] as $file_name ) {
                                            // delete file
                                            unlink( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name );
                                        }

                                        return;
                                    }

                                    $category = $csv_line[ 2 ];
                                    if ( ! in_array( $category, [
                                        'Open',
                                        'Women',
                                        'Juniors',
                                        'Masters',
                                        'Luge',
                                        'Street luge',
                                        'Classic luge'
                                    ] ) ) {
                                        if ( false != $verify ) {
                                            CSV_Importer::csvi_errors()->add( 'error_zero_value', __( 'There\'s a non-approved category name on line ' . $line_number . '.', 'cvs-importer' ) );
                                        } else {
                                            CSV_Importer::csvi_errors()->add( 'error_zero_value', __( 'There\'s a non-approved category name on line ' . $line_number . '. Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, therefor it\'s deleted.', 'cvs-importer' ) );
                                        }
                                        foreach( $_POST[ 'file_name' ] as $file_name ) {
                                            // delete file
                                            unlink( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name );
                                        }

                                        return;
                                    }

                                    $ranking    = $csv_line[ 3 ];
                                    $points     = $csv_line[ 4 ];

                                    $new_value = array(
                                        $year => array(
                                            'year'          => $year,
                                            'category_name' => $category,
                                            'ranking'       => $ranking,
                                            'points'        => $points,
                                        ),
                                    );

                                    if ( false == $verify ) {

                                        $race_rankings = get_user_meta( $idf_number, 'race_rankings', true );
                                        $user_meta     = ! empty( $race_rankings ) ? $race_rankings : false;
                                        if ( false != $user_meta ) {
                                            $new_value = array_merge( $new_value, $user_meta );
                                            asort( $new_value );
                                        }
                                        update_user_meta( $idf_number, 'race_rankings', $new_value );
                                    }

                                }
                                fclose($handle);


                                // delete file
                                if ( false == $verify ) {
                                    foreach( $_POST[ 'file_name' ] as $file_name ) {
                                        // delete file
                                        unlink( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name );
                                    }
                                    if ( class_exists( 'ActionLogger' ) ) {
                                        ActionLogger::al_log_user_action( 'rankings_imported', 'cvs-importer', ' successfully imported ' . $line_number . ' lines from file' );
                                    }
                                    CSV_Importer::csvi_errors()->add( 'success_rankings_imported', __( 'YAY ! ' . $line_number . ' lines are imported and the file is deleted. You can check the last user which is imported <a href="' . get_author_posts_url( $idf_number ) . '">here</a>.', 'cvs-importer' ) );

                                    return;
                                } else {
                                    if ( class_exists( 'ActionLogger' ) ) {
                                        ActionLogger::al_log_user_action( 'verify_csv', 'cvs-importer', ' successfully verified ' . $_POST[ 'file_name' ][0] );
                                    }
                                    CSV_Importer::csvi_errors()->add( 'success_no_errors_in_csv', __( 'Congratulations, there are no errors in your CSV.', 'cvs-importer' ) );

                                    return;
                                }
                            }

                        } else {

                            if ( isset( $_POST[ 'file_name' ] ) ) {
                                foreach( $_POST[ 'file_name' ] as $file_name ) {
                                    // delete file
                                    unlink( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name );
                                }
                                if ( count( $_POST[ 'file_name' ] ) == 1 ) {
                                    CSV_Importer::csvi_errors()->add( 'success_file_deleted', __( 'File "' . $file_name . '" successfully deleted.', 'cvs-importer' ) );
                                } else {
                                    CSV_Importer::csvi_errors()->add( 'success_files_deleted', __( 'Files successfully deleted.', 'cvs-importer' ) );
                                }

                            } else {
                                // no files selected
                                CSV_Importer::csvi_errors()->add( 'warning_no_files_selected', __( 'You didn\'t select a file to delete.', 'cvs-importer' ) );
                            }

                        }
                    }
                }
            }

            /**
             * Upload a CSV file
             */
            public function upload_file_functions() {

                if ( current_user_can( 'manage_options' ) && isset( $_POST[ "import_rankings_nonce" ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ "import_rankings_nonce" ], 'import-rankings-nonce' ) ) {
                        CSV_Importer::csvi_errors()->add( 'error_nonce_no_match', __( 'Upload failed. Please try again.', 'cvs-importer' ) );

                        return;
                    } else {

                        $target_dir  = plugin_dir_path( __FILE__ ) . 'uploads/';
                        $target_file = $target_dir . basename( $_FILES[ 'csv_upload' ][ 'name' ] );

                        // @TODO: check if file exists

                        if (move_uploaded_file($_FILES['csv_upload']['tmp_name'], '/' . $target_file)) {
                            // file uploaded succeeded
                            if ( class_exists( 'ActionLogger' ) ) {
                                // ActionLogger::al_log_user_action( 'upload_rankings_csv', 'cvs-importer', ' uploaded a file named ' . $_FILES[ 'csv_upload' ][ 'name' ], false );
                            }
                            CSV_Importer::csvi_errors()->add( 'success_file_uploaded', __( 'File "' . $_FILES[ 'csv_upload' ][ 'name' ] . '" is successfully uploaded and now shows under \'Select files to import\'.', 'cvs-importer' ) );
                            return;

                        } else {
                            // file upload failed
                            CSV_Importer::csvi_errors()->add( 'error_file_uploaded', __( 'Upload failed. Please try again.', 'cvs-importer' ) );
                            return;
                        }
                    }
                }

            }

	        /**
	         * Adds a menu on top of the pages
	         * @return string
	         */
            public static function csvi_admin_menu() {
		        return '<p><a href="' . site_url() . '/wp-admin/admin.php?page=csv-import">' . esc_html( __( 'CSV Importer', 'action-logger' ) ) . '</a> | <a href="' . site_url() . '/wp-admin/admin.php?page=csvi-settings">' . esc_html( __( 'Settings', 'action-logger' ) ) . '</a> | <a href="' . site_url() . '/wp-admin/admin.php?page=csvi-misc">' . esc_html( __( 'Misc', 'action-logger' ) ) . '</a></p>';
	        }

	        /**
             * Create admin page
             */
            public function csvi_add_dashboard_page() {
                add_menu_page( 'CSV Importer', 'CSV Importer', 'manage_options', 'csv-import', 'csv_import_dashboard', 'dashicons-grid-view' );
	            include( 'csvi-dashboard.php' );
            }

	        /**
	         * Adds a (hidden) settings page, only through the menu on top of the pages.
	         */
	        public function csvi_add_settings_page() {
		        add_submenu_page( NULL, 'Settings', 'Settings', 'manage_options', 'csvi-settings', 'csvi_settings_page' );
		        include( 'csvi-settings.php' ); // content for the settings page
	        }

	        /**
	         * Adds a (hidden) settings page, only through the menu on top of the pages.
	         */
	        public function csvi_add_misc_page() {
		        add_submenu_page( NULL, 'Misc', 'Misc', 'manage_options', 'csvi-misc', 'csvi_misc_page' );
		        include( 'csvi-misc.php' ); // content for the settings page
	        }

            /**
             * Enqueue CSS
             */
            public function enqueue_csvi_css() {
                wp_register_style( 'csvi', plugins_url( 'style.css', __FILE__ ), false, '1.0.0' );
                wp_enqueue_style( 'csvi' );
            }

        }

        /**
         * The main function responsible for returning the one true CSV_Importer instance to functions everywhere.
         *
         * @return \CSV_Importer
         */
        function init_csv_importer_plugin() {
            global $csv_importer_plugin;

            if ( ! isset( $csv_importer_plugin ) ) {
                $csv_importer_plugin = new CSV_Importer();
                $csv_importer_plugin->initialize();
            }

            return $csv_importer_plugin;
        }

        // initialize
        init_csv_importer_plugin();

    endif; // class_exists check
