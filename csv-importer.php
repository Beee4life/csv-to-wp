<?php
    /*
    Plugin Name: CSV Importer
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
	            add_action( 'admin_menu',               array( $this, 'csvi_add_preview_page' ) );
	            add_action( 'admin_menu',               array( $this, 'csvi_add_settings_page' ) );
	            add_action( 'admin_menu',               array( $this, 'csvi_add_faq_page' ) );
	            add_action( 'admin_menu',               array( $this, 'csvi_add_misc_page' ) );
                add_action( 'admin_enqueue_scripts',    array( $this, 'enqueue_csvi_css' ) );

                // csv actions
                add_action( 'admin_init',               array( $this, 'upload_file_functions' ) );
	            add_action( 'admin_init',               array( $this, 'read_file_functions' ) );
	            add_action( 'admin_init',               array( $this, 'csv_to_array' ) );
                add_action( 'admin_init',               array( $this, 'import_raw_csv_data' ) );

	            // misc actions
	            add_action( 'admin_init',               array( $this, 'csvi_errors' ) );
	            add_action( 'admin_init',               array( $this, 'csvi_admin_menu' ) );

	            include( 'verify-csv-data.php' );
	            include( 'not-in-use.php' );

	            // $this->csvi_store_default_values();

            }

	        // @TODO: preview raw imported data


            /**
             * Function which runs upon plugin deactivation
             */
            public function csvi_plugin_activation() {
                $this->csvi_create_uploads_directory();
                $this->csvi_store_default_values();
            }

            /**
             * Function which runs upon plugin deactivation
             */
            public function csvi_plugin_deactivation() {
	            delete_option( 'csvi_import_role' );
            }

	        public function csvi_create_uploads_directory() {
            	if ( true != is_dir( plugin_dir_path( __FILE__ ) . 'uploads' ) ) {
		            mkdir(plugin_dir_path( __FILE__ ) . 'uploads', 0755 );
	            }
	        }

	        /**
	         * Function which runs upon plugin deactivation
	         */
	        public function csvi_store_default_values() {
	        	update_option( 'csvi_import_role', 'manage_options' );
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
						        $span_class = 'notice-error ';
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
                        CSV_Importer::csvi_errors()->add( 'error_nonce_no_match', __( 'Something went wrong. Please try again.', 'csv-importer' ) );

                        return;
                    } else {
                        // nonce ok + verified

                        $raw_data      = $_POST[ 'raw_csv_import' ];
                        // verify data
                        $verified_data = verify_csv_data( $raw_data );
                        $verify        = ! empty( $_POST[ 'verify' ] ) ? $_POST[ 'verify' ] : false;

                        if ( false != $verified_data ) {

                            if ( false != $verify ) {
                                CSV_Importer::csvi_errors()->add( 'success_no_errors_in_csv', __( 'Congratulations, there are no errors in your CSV.', 'csv-importer' ) );

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
                                        ActionLogger::al_log_user_action( 'import_raw', 'csv-importer', ' uploaded ' . $count . ' lines through raw import' );
                                    }
                                    CSV_Importer::csvi_errors()->add( 'success_rankings_imported', __( $count . ' lines imported through raw import. You can check the last user which is imported <a href="' . get_author_posts_url( $idf_number ) . '">here</a>.', 'csv-importer' ) );
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
                        CSV_Importer::csvi_errors()->add( 'error_nonce_no_match', __( 'Something went wrong. Please try again.', 'csv-importer' ) );

                        return;
                    } else {
                        // nonce ok + verified

                        if ( ! isset( $_POST[ 'file_name' ] ) ) {
                            CSV_Importer::csvi_errors()->add( 'error_no_file_selected', __( 'You didn\'t select a file.', 'csv-importer' ) );

                            return;
                        }

                        $remove    = ! empty( $_POST[ 'remove' ] ) ? $_POST[ 'remove' ] : false;
                        $verify    = ! empty( $_POST[ 'verify' ] ) ? $_POST[ 'verify' ] : false;
                        $file_name = ! empty( $_POST[ 'file_name' ] ) ? $_POST[ 'file_name' ] : false;

                        if ( false == $remove ) {

	                        // echo '<pre>'; var_dump($_POST); echo '</pre>'; exit;

                            if ( count( $_POST[ 'file_name' ] ) > 1 ) {
	                            CSV_Importer::csvi_errors()->add( 'error_too_many_files', __( 'Only 1 file at a time please, otherwise the error messages can be off. And yes, it\'s already on the todo list.', 'csv-importer' ) );

                                return;
                            }

	                        // read file to create new array of data to work with
	                        if (($handle = fopen( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name[0], "r")) !== FALSE) {
		                        while (($csv_line = fgetcsv($handle, 1000, ",")) !== FALSE) {
		                        }
	                        }

	                        // read file
                            if (($handle = fopen( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name[0], "r")) !== FALSE) {
	                            $line_number      = 0;
	                            $column_benchmark = 0;
	                            $csv_array = array();
                                while (($csv_line = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                    $line_number++;

	                                // if line is 1, count columns (of header)
	                                if ( 1 == $line_number ) {
		                                // count columns to compare with other lines
		                                $column_benchmark = count($csv_line);
	                                }

                                    // check amount of columns
	                                if ( count( $csv_line ) != $column_benchmark ) {
	                                	// if column count doesn't match benchmark
                                        if ( count( $csv_line ) < $column_benchmark ) {
                                            if ( false != $verify ) {
                                                $error_message = 'Since your file is not accurate anymore, the file is deleted.';
                                            } else {
                                                $error_message = 'Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                                            }
                                            CSV_Importer::csvi_errors()->add( 'error_no_correct_columns', sprintf( __( 'There are too few columns on line %d. %s', 'csv-importer' ), $line_number, $error_message ) );

                                        } elseif ( count( $csv_line ) > $column_benchmark ) {
	                                        if ( false != $verify ) {
		                                        $error_message = 'Since your file is not accurate anymore, the file is deleted.';
	                                        } else {
		                                        $error_message = 'Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
	                                        }
	                                        CSV_Importer::csvi_errors()->add( 'error_no_correct_columns', sprintf( __( 'There are too many columns on line %d. %s', 'csv-importer' ), $line_number, $error_message ) );
                                        }
                                        foreach( $_POST[ 'file_name' ] as $file_name ) {
                                            // delete file
                                            // unlink( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name );
                                        }

                                        return;
                                    }

                                    $new_line = array();
	                                foreach( $csv_line as $item ) {
	                                	$new_line[] = $item;
	                                }
	                                $csv_array[] = $new_line;

                                }
                                fclose($handle);

                                // import data or delete file
                                if ( false == $verify ) {
                                	// verify = false, so this is for real, this is no verification, there are no errors, so files can be processed

	                                // do something with the data ($csv_array) here

                                    foreach( $_POST[ 'file_name' ] as $file_name ) {
                                        // delete file
                                        // unlink( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name );
                                    }
                                    do_action( 'csvi_successfull_csv_import' );
                                    CSV_Importer::csvi_errors()->add( 'success_rankings_imported', __( 'YAY ! ' . $line_number . ' lines are imported and the file is deleted.', 'csv-importer' ) );

                                    return;
                                } else {
	                                do_action( 'csvi_successfull_csv_validate' );
                                    CSV_Importer::csvi_errors()->add( 'success_no_errors_in_csv', __( 'Congratulations, there are no errors in your CSV.', 'csv-importer' ) );

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
                                    CSV_Importer::csvi_errors()->add( 'success_file_deleted', __( 'File "' . $file_name . '" successfully deleted.', 'csv-importer' ) );
                                } else {
                                    CSV_Importer::csvi_errors()->add( 'success_files_deleted', __( 'Files successfully deleted.', 'csv-importer' ) );
                                }

                            } else {
                                // no files selected
                                CSV_Importer::csvi_errors()->add( 'warning_no_files_selected', __( 'You didn\'t select a file to delete.', 'csv-importer' ) );
                            }

                        }
                    }
                }
            }

	        /**
	         * Change CSV into array
	         */
	        public static function csv_to_array( $file_name = false ) {

	        	// $file_name = 'test2.csv';
	            if ( false != $file_name ) {
		            // echo '<pre>'; var_dump($file_name); echo '</pre>'; exit;
			        // read file to create new array of data to work with
			        if (($handle = fopen( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name, "r")) !== FALSE) {
				        $line_number      = 0;
				        $column_benchmark = 0;
				        $csv_array = array();
				        while (($csv_line = fgetcsv($handle, 1000, ",")) !== FALSE) {
					        $line_number++;

					        // if line is 1, count columns (of header)
					        if ( 1 == $line_number ) {
						        // count columns to compare with other lines
						        $column_benchmark = count($csv_line);
					        }

					        // check amount of columns
					        if ( count( $csv_line ) != $column_benchmark ) {
						        // if column count doesn't match benchmark
						        $error_message = 'Since your file is not accurate anymore, the file is deleted.';
						        if ( count( $csv_line ) < $column_benchmark ) {
							        CSV_Importer::csvi_errors()->add( 'error_no_correct_columns', sprintf( __( 'There are too few columns on line %d. %s', 'csv-importer' ), $line_number, $error_message ) );
						        } elseif ( count( $csv_line ) > $column_benchmark ) {
							        CSV_Importer::csvi_errors()->add( 'error_no_correct_columns', sprintf( __( 'There are too many columns on line %d. %s', 'csv-importer' ), $line_number, $error_message ) );
						        }
						        // foreach( $_POST[ 'file_name' ] as $file_name ) {
							        // delete file
							        // unlink( plugin_dir_path(__FILE__ ) . 'uploads/' . $file_name );
						        // }

						        return false;
					        }

					        $new_line = array();
					        foreach( $csv_line as $item ) {
						        $new_line[] = $item;
					        }
					        $csv_array[] = $new_line;

				        }
				        fclose($handle);

				        return $csv_array;

			        }
			        return false;
		        }
	        }

	        /**
             * Upload a CSV file
             */
            public function upload_file_functions() {

                if ( current_user_can( 'manage_options' ) && isset( $_POST[ "import_rankings_nonce" ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ "import_rankings_nonce" ], 'import-rankings-nonce' ) ) {
                        CSV_Importer::csvi_errors()->add( 'error_nonce_no_match', __( 'Upload failed. Please try again.', 'csv-importer' ) );

                        return;
                    } else {

	                    if ( true != is_dir( plugin_dir_path( __FILE__ ) . 'uploads' ) ) {
		                    mkdir(plugin_dir_path( __FILE__ ) . 'uploads', 0755 );
	                    }
                        $target_file = plugin_dir_path( __FILE__ ) . 'uploads/' . basename( $_FILES[ 'csv_upload' ][ 'name' ] );

	                    if (move_uploaded_file($_FILES['csv_upload']['tmp_name'], '/' . $target_file)) {
                            // file uploaded succeeded
		                    do_action( 'csvi_successful_csv_upload' );
                            CSV_Importer::csvi_errors()->add( 'success_file_uploaded', __( 'File "' . $_FILES[ 'csv_upload' ][ 'name' ] . '" is successfully uploaded and now shows under \'Select files to import\'.', 'csv-importer' ) );
                            return;

                        } else {
                            // file upload failed
                            CSV_Importer::csvi_errors()->add( 'error_file_uploaded', __( 'Upload failed. Please try again.', 'csv-importer' ) );
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
		        return '<p><a href="' . site_url() . '/wp-admin/admin.php?page=csvi-dashboard">' . esc_html( __( 'Dashboard', 'action-logger' ) ) . '</a> | <a href="' . site_url() . '/wp-admin/admin.php?page=csvi-preview">' . esc_html( __( 'Preview data', 'action-logger' ) ) . '</a> <span class="xhidden">| <a  href="' . site_url() . '/wp-admin/admin.php?page=csvi-settings">' . esc_html( __( 'Settings', 'action-logger' ) ) . '</a> </span>| <a href="' . site_url() . '/wp-admin/admin.php?page=csvi-faq">' . esc_html( __( 'FAQ', 'action-logger' ) ) . '</a> | <a href="' . site_url() . '/wp-admin/admin.php?page=csvi-misc">' . esc_html( __( 'Misc', 'action-logger' ) ) . '</a></p>';
	        }

	        /**
             * Create admin page
             */
            public function csvi_add_dashboard_page() {
                add_menu_page( 'CSV Importer', 'CSV Importer', 'manage_options', 'csvi-dashboard', 'csv_import_dashboard', 'dashicons-grid-view' );
	            include( 'csvi-dashboard.php' );
            }

	        /**
	         * Adds a (hidden) settings page, only through the menu on top of the pages.
	         */
	        public function csvi_add_preview_page() {
		        add_submenu_page( NULL, 'Preview', 'Preview', 'manage_options', 'csvi-preview', 'csvi_preview_page' );
		        include( 'csvi-preview.php' ); // content for the settings page
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
	         * Adds a (hidden) settings page, only through the menu on top of the pages.
	         */
	        public function csvi_add_faq_page() {
		        add_submenu_page( NULL, 'FAQ', 'FAQ', 'manage_options', 'csvi-faq', 'csvi_faq_page' );
		        include( 'csvi-faq.php' ); // content for the settings page
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
