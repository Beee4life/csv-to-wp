<?php
    /*
    Plugin Name: CSV to WP
    Version: 0.5.1
    Plugin URI: https://github.com/Beee4life/csv-to-wp/
    Description: This plugin allows you to import an verify CSV data and imports it to your WordPress database.
    Author: Beee
    Author URI: https://berryplasman.com
    Text-domain: csv2wp
    License: GPL2v2
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

            public function __construct() {
                $this->settings = array(
                    'path'    => trailingslashit( dirname( __FILE__ ) ),
                    'version' => '0.5.1',
                );

                // (de)activation hooks
                register_activation_hook( __FILE__,     array( $this, 'csv2wp_plugin_activation' ) );
                register_deactivation_hook( __FILE__,   array( $this, 'csv2wp_plugin_deactivation' ) );

                // actions
                add_action( 'admin_menu',               array( $this, 'csv2wp_add_admin_pages' ) );
                add_action( 'admin_enqueue_scripts',    array( $this, 'csv2wp_enqueue_scripts' ) );
                add_action( 'admin_init',               array( $this, 'csv2wp_errors' ) );
                add_action( 'admin_init',               array( $this, 'csv2wp_admin_menu' ) );
                add_action( 'admin_init',               array( $this, 'csv2wp_settings_page_functions' ) );
                add_action( 'plugins_loaded',           array( $this, 'csv2wp_load_textdomain' ) );

                // csv actions
                add_action( 'admin_init',               array( $this, 'csv2wp_create_uploads_directory' ) );
                add_action( 'admin_init',               array( $this, 'csv2wp_upload_functions' ) );
                add_action( 'admin_init',               array( $this, 'csv2wp_handle_file_functions' ) );
                add_action( 'admin_init',               array( $this, 'csv2wp_import_raw_csv_data' ) );

                // add settings link to plugin
                add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'csv2wp_plugin_link' ) );

                include 'includes/functions.php';
                include 'includes/csv2wp-help-tabs.php';

            }

            public function csv2wp_plugin_activation() {
                if ( false == get_option( 'csv2wp_preserve_settings' ) ) {
                    update_option( 'csv2wp_import_role', 'manage_options', true );
                }
            }

            public function csv2wp_plugin_deactivation() {
                if ( false == get_option( 'csv2wp_preserve_settings' ) ) {
                    delete_option( 'csv2wp_import_role' );
                }
            }

            public function csv2wp_create_uploads_directory() {
                if ( true != is_dir( csv2wp_get_upload_folder() ) ) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    WP_Filesystem();
                    global $wp_filesystem;
                    $wp_filesystem->mkdir( $csv2wp_upload_folder, 0755 );
                }
            }

            public function csv2wp_load_textdomain() {
                load_plugin_textdomain( 'csv2wp', false, basename( dirname( plugin_basename( __FILE__ ) ) ) . '/languages' );
            }

            public static function csv2wp_errors() {
                static $wp_error; // Will hold global variable safely

                return isset( $wp_error ) ? $wp_error : ( $wp_error = new WP_Error( null, null, null ) );
            }

            public static function csv2wp_show_admin_notices() {
                if ( $codes = CSV2WP::csv2wp_errors()->get_error_codes() ) {
                    if ( is_wp_error( CSV2WP::csv2wp_errors() ) ) {

                        // Loop error codes and display errors
                        $span_class = false;
                        $prefix     = false;
                        foreach ( $codes as $code ) {
                            if ( strpos( $code, 'success' ) !== false ) {
                                $span_class = 'updated ';
                                $prefix     = false;
                            } elseif ( strpos( $code, 'warning' ) !== false ) {
                                $span_class = 'notice-warning ';
                                $prefix     = esc_html( __( 'Warning', 'csv2wp' ) );
                            } elseif ( strpos( $code, 'info' ) !== false ) {
                                $span_class = 'notice-info ';
                                $prefix     = false;
                            } else {
                                $span_class = 'notice-error ';
                                $prefix     = esc_html( __( 'Error', 'csv2wp' ) );
                            }
                        }
                        echo '<div id="message" class="notice ' . $span_class . 'csv2wp__notice is-dismissible">';
                        foreach ( $codes as $code ) {
                            $message = CSV2WP::csv2wp_errors()->get_error_message( $code );
                            echo '<div class="">';
                            if ( true == $prefix ) {
                                echo '<strong>' . $prefix . ':</strong> ';
                            }
                            echo $message;
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                }
            }

            public function csv2wp_import_raw_csv_data() {

                if ( current_user_can( 'manage_options' ) && isset( $_POST[ "import_raw_rankings_nonce" ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ "import_raw_rankings_nonce" ], 'import-raw-rankings-nonce' ) ) {
                        CSV2WP::csv2wp_errors()->add( 'error_nonce_no_match', esc_html__( 'Something went wrong. Please try again.', 'csv2wp' ) );

                        return;
                    } else {
                        // nonce ok + verified

                        $verify    = ! empty( $_POST[ 'verify' ] ) ? $_POST[ 'verify' ] : false;
                        $raw_data  = ! empty( $_POST[ 'raw_csv_import' ] ) ? $_POST[ 'raw_csv_import' ] : false;
                        $csv_array = csv2wp_verify_raw_csv_data( $raw_data );

                        if ( false != $csv_array ) {

                            if ( false != $verify ) {
                                CSV2WP::csv2wp_errors()->add( 'success_no_errors_in_csv', esc_html__( 'Congratulations, there appear to be no errors in your CSV.', 'csv2wp' ) );

                                return;
                            } else {
                                if ( count( $csv_array ) > 0 ) {
                                    $count = 0;
                                    foreach ( $csv_array as $csv_line ) {
                                        // do something with $csv_line
                                        $count++;
                                    }
                                    CSV2WP::csv2wp_errors()->add( 'success_raw_data_imported', sprintf( esc_html__( '%d lines imported through raw import.', 'csv2wp' ), $count ) );
                                }
                            }
                        }
                    }
                }
            }

            /*
             * Read uploaded file for verification or import
             * Or delete the file
             * @TODO: cut this into littler functions
             */
            public function csv2wp_handle_file_functions() {
                if ( current_user_can( 'manage_options' ) && isset( $_POST[ 'select_file_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'select_file_nonce' ], 'select-file-nonce' ) ) {
                        CSV2WP::csv2wp_errors()->add( 'error_nonce_no_match', __( 'Something went wrong. Please try again.', 'csv2wp' ) );

                        return;
                    } else {
                        if ( empty( $_POST[ 'csv2wp_file_name' ] ) ) {
                            CSV2WP::csv2wp_errors()->add( 'error_no_file_selected', __( "You didn't select a file to handle.", 'csv2wp' ) );
                            return;
                        }

                        global $wpdb;
                        $create_table   = false;
                        $delimiter      = sanitize_text_field( $_POST[ 'csv2wp_delimiter' ] );
                        $file_name      = sanitize_text_field( $_POST[ 'csv2wp_file_name' ] );
                        $has_header     = $_POST[ 'csv2wp_header' ];
                        $import_where   = sanitize_text_field( $_POST[ 'csv2wp_import_in' ] );
                        $meta_key       = ( isset( $_POST[ 'csv2wp_meta' ] ) ) ? sanitize_text_field( $_POST[ 'csv2wp_meta' ] ) : false;
                        $plugin_options = [ 'table', 'postmeta', 'usermeta' ];
                        $remove         = isset( $_POST[ 'csv2wp_remove' ] ) ? true : false;
                        $verify         = isset( $_POST[ 'csv2wp_verify' ] ) ? true : false;

                        if ( false === $remove ) {
                            $csv_array = csv2wp_csv_to_array( $file_name, $delimiter, $verify, $has_header, false, $import_where, $meta_key );

                            if ( false === $verify ) {
                                // $verify == false, so import for real
                                if ( ! in_array( $import_where, $plugin_options ) ) {
                                    // execute a custom option, set by a filter
                                    do_action( $import_where, $csv_array, $has_header, $file_name );

                                } elseif ( 'table' == $import_where ) {
                                    $table = sanitize_text_field( $_POST[ 'csv2wp_table' ] );
                                    if ( empty( $table ) || strlen( $table ) <= strlen( $wpdb->prefix ) ) {
                                        CSV2WP::csv2wp_errors()->add( "error_no_table_entered", __( "You didn't enter a table, where to import it.", 'csv2wp' ) );
                                    } elseif ( strpos( ' ', $table ) !== false ) {
                                        CSV2WP::csv2wp_errors()->add( "error_space_in_table", __( 'You have a space in your table name.', 'csv2wp' ) );
                                    } elseif ( false !== $has_header && false != $meta_key ) {
                                        CSV2WP::csv2wp_errors()->add( "error_header_meta", __( "You can't have 'has header' and 'meta key' selected at the same time. If you enter a meta key, your CSV file can't be headers.", 'csv2wp' ) );
                                    } elseif ( false == $_POST[ 'csv2wp_header' ] ) {
                                        CSV2WP::csv2wp_errors()->add( "error_no_header", esc_html__( 'You unchecked whether the file has a header row. For insert into table, you must have a header row.', 'csv2wp' ) );
                                    }

                                    if ( ! CSV2WP::csv2wp_errors()->get_error_codes() ) {
                                        $create_table = $this->csv2wp_create_custom_table( $table, $csv_array[ 'column_names' ] );
                                    }

                                } elseif ( in_array( $import_where, [ 'usermeta', 'postmeta' ] ) ) {
                                    if ( false !== $has_header && false != $meta_key ) {
                                        CSV2WP::csv2wp_errors()->add( 'error_header_meta', __( "You can't have 'has header' and 'meta key' selected at the same time. If your CSV has headers, you can't use a meta key.", 'csv2wp' ) );
                                    }
                                }

                                if ( CSV2WP::csv2wp_errors()->get_error_codes() ) {
                                    return;
                                }

                                $success = $this->csv2wp_process_data( $csv_array, $import_where, $has_header, $create_table );

                                if ( true === $success ) {
                                    $delete_result = csv2wp_delete_file( $file_name, apply_filters( 'delete_csv_after_process', true ) );
                                    if ( isset( $delete_result ) && true == $delete_result ) {
                                        CSV2WP::csv2wp_errors()->add( 'success_data_imported', sprintf( esc_html__( 'YAY ! %d lines are imported and the file is deleted.', 'csv2wp' ), $line_number ) );
                                    } else {
                                        CSV2WP::csv2wp_errors()->add( 'success_data_imported', sprintf( esc_html__( 'YAY ! %d lines are imported but the file is not deleted.', 'csv2wp' ), $line_number ) );
                                    }
                                    do_action( 'csv2wp_successful_csv_import', $line_number );

                                    return;
                                }

                            } else {
                                // verify == true, which actually happens in csv2wp_csv_to_array() already
                                // @TODO: verify table create
                                if ( ! empty( $csv_array[ 'data' ] ) ) {
                                    CSV2WP::csv2wp_errors()->add( 'success_no_errors_in_csv', esc_html__( 'Congratulations, there appear to be no errors in your CSV.', 'csv2wp' ) );

                                    return;
                                }
                            }

                        } else {
                            // delete file
                            if ( ! empty( $file_name ) ) {
                                csv2wp_delete_file( $file_name );
                                CSV2WP::csv2wp_errors()->add( 'success_file_deleted', sprintf( esc_html__( 'File "%s" successfully deleted.', 'csv2wp' ), $file_name ) );
                            }
                        }
                    }
                }
            }

            public function csv2wp_process_data( $csv_array, $import_where, $has_header = false, $create_table = false ) {
                include 'includes/csv2wp-process-data.php';
            }

            public function csv2wp_create_custom_table( $name, $columns = [] ) {
                if ( ! $name || ! $columns ) {
                    return false;
                }

                global $wpdb;
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                $charset_collate = $wpdb->get_charset_collate();

                if ( ! empty( $columns ) ) {
                    $sql = "CREATE TABLE {$name} (\n";
                    $sql .= "id int(7) unsigned NOT NULL auto_increment,";
                    foreach ( $columns as $column ) {
                        $sanitized = strtolower( str_replace( ' ', '_', sanitize_text_field( $column ) ) );
                        $sql       .= $sanitized . " varchar(255) NULL,\n";
                    }
                    $sql .= "PRIMARY KEY  (id)\n";
                    $sql .= ") {$charset_collate}";
                }

                $result = dbDelta( $sql );

                if ( ! empty( $result ) ) {
                    return true;
                } else {
                    return false;
                }
            }

            public function csv2wp_upload_functions() {
                if ( current_user_can( 'manage_options' ) && isset( $_POST[ 'csv2wp_upload_csv_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'csv2wp_upload_csv_nonce' ], 'csv2wp-upload-csv-nonce' ) ) {
                        CSV2WP::csv2wp_errors()->add( 'error_nonce_no_match', esc_html__( 'Something went wrong. Please try again.', 'csv2wp' ) );

                        return;
                    } else {
                        if ( isset( $_FILES[ 'csv2wp_upload' ][ 'name' ] ) ) {
                            $file_name   = sanitize_file_name( $_FILES[ 'csv2wp_upload' ][ 'name' ] );
                            $target_file = sprintf( '%s/%s', csv2wp_get_upload_folder(), basename( $file_name ) );

                            if ( move_uploaded_file( $_FILES[ 'csv2wp_upload' ][ 'tmp_name' ], $target_file ) ) {
                                // file uploaded succeeded
                                do_action( 'csv2wp_successful_csv_upload' );
                                $message = sprintf( __( 'File %s is successfully uploaded and now shows under %s.', 'csv2wp' ), $file_name, sprintf( '<b>%s</b>', esc_html__( 'Handle a csv file', 'csv2wp' ) ) );
                                CSV2WP::csv2wp_errors()->add( 'success_file_uploaded', $message );

                                return;

                            } else {
                                // file upload failed
                                CSV2WP::csv2wp_errors()->add( 'error_file_uploaded', esc_html( __( 'Upload failed. Please try again.', 'csv2wp' ) ) );

                                return;
                            }
                        }
                    }

                    return;
                }
            }

            public function csv2wp_settings_page_functions() {
                /*
                 * Update who can manage
                 */
                if ( isset( $_POST[ 'settings_page_nonce' ] ) ) {
                    if ( ! wp_verify_nonce( $_POST[ 'settings_page_nonce' ], 'settings-page-nonce' ) ) {
                        CSV2WP::csv2wp_errors()->add( 'error_nonce_no_match', esc_html( __( 'Something went wrong. Please try again.', 'csv2wp' ) ) );
                        return;
                    } else {
                        if ( isset( $_POST[ 'csv2wp_select_cap' ] ) ) {
                            update_option( 'csv2wp_import_role', $_POST[ 'csv2wp_select_cap' ] );
                        }

                        if ( isset( $_POST[ 'csv2wp_preserve_settings' ] ) ) {
                            update_option( 'csv2wp_preserve_settings', 1 );
                        } else {
                            delete_option( 'csv2wp_preserve_settings' );
                        }

                        CSV2WP::csv2wp_errors()->add( 'success_settings_saved', esc_html( __( 'Settings saved.', 'csv2wp' ) ) );
                    }
                }
            }

            public function csv2wp_plugin_link( $links ) {
                array_unshift( $links, '<a href="' . admin_url( 'admin.php?page=csv2wp-dashboard' ) . '">' . __( 'Import', 'csv2wp' ) . '</a>' );

                return $links;
            }

            public function csv2wp_add_admin_pages() {
                include 'includes/csv2wp-dashboard.php';
                add_menu_page( 'CSV Importer', 'CSV to WP', get_option( 'csv2wp_import_role' ), 'csv2wp-dashboard', 'csv2wp_dashboard_page', 'dashicons-grid-view' );

                include 'includes/csv2wp-preview.php'; // content for the preview page
                add_submenu_page( 'options.php', 'Preview', 'Preview', get_option( 'csv2wp_import_role' ), 'csv2wp-preview', 'csv2wp_preview_page' );

                // include 'includes/csv2wp-mapping.php'; // content for the mapping page
                if ( function_exists( 'csv2wp_mapping_page' ) ) {
                    add_submenu_page( 'options.php', 'Mapping', 'Mapping', get_option( 'csv2wp_import_role' ), 'csv2wp-mapping', 'csv2wp_mapping_page' );
                }

                include 'includes/csv2wp-settings.php'; // content for the settings page
                if ( function_exists( 'csv2wp_settings_page' ) ) {
                    add_submenu_page( 'options.php', 'Settings', 'Settings', get_option( 'csv2wp_import_role' ), 'csv2wp-settings', 'csv2wp_settings_page' );
                }

                include 'includes/csv2wp-support.php'; // content for the support page
                add_submenu_page( 'options.php', 'Support', 'Support', get_option( 'csv2wp_import_role' ), 'csv2wp-support', 'csv2wp_support_page' );
            }

            public function csv2wp_enqueue_scripts() {
                wp_register_style( 'csv2wp', plugins_url( 'assets/css/style.css', __FILE__ ), false, $this->settings[ 'version' ] );
                wp_enqueue_style( 'csv2wp' );

                $plugin_dir = plugin_dir_url( __FILE__ );
                wp_register_script( 'csv2wp', "{$plugin_dir}assets/js/csv2wp.js", array( 'jquery' ), $this->settings[ 'version' ] );
                wp_enqueue_script( 'csv2wp' );
            }

            public static function csv2wp_admin_menu() {
                $menu = sprintf( '<div class="csv2wp__menu"><a href="%s">%s</a>', admin_url( 'admin.php?page=csv2wp-dashboard' ), esc_html( __( 'Dashboard', 'csv2wp' ) ) );
                if ( ! empty( csv2wp_check_if_files() ) ) {
                    $menu .= sprintf( ' | <a href="%s">%s</a>', admin_url( 'admin.php?page=csv2wp-preview' ), esc_html( __( 'Preview file', 'csv2wp' ) ) );
                }
                if ( function_exists( 'csv2wp_mapping_page' ) ) {
                    $menu .= sprintf( ' | <a href="%s">%s</a>', admin_url( 'admin.php?page=csv2wp-mapping' ), esc_html( __( 'Mappings', 'csv2wp' ) ) );
                }
                if ( function_exists( 'csv2wp_settings_page' ) ) {
                    $menu .= sprintf( ' | <a href="%s">%s</a>', admin_url( 'admin.php?page=csv2wp-settings' ), esc_html( __( 'Settings', 'csv2wp' ) ) );
                }
                $menu .= sprintf( ' | <a href="%s">%s</a>', admin_url( 'admin.php?page=csv2wp-support' ), esc_html( __( 'Support', 'csv2wp' ) ) );
                $menu .= '</div>';

                return $menu;

            }

            public static function get_instance() {
                static $instance;

                if ( null === $instance ) {
                    $instance = new self();
                }

                return $instance;
            }
        }

        CSV2WP::get_instance();

    endif; // class_exists check
