<?php

    /**
     * Check if files are uploaded
     *
     * @return array
     */
    function csv2wp_check_if_files() {

        $target_dir = csv2wp_get_upload_folder();
        $file_index = scandir( $target_dir );

        if ( is_array( $file_index ) ) {
            $actual_files = [];
            foreach ( $file_index as $file ) {
                if ( '.DS_Store' != $file && '.' != $file && '..' != $file ) {
                    $actual_files[] = $file;
                }
            }
            if ( ! empty( $actual_files ) ) {
                return $actual_files;
            }
        }

        return [];

    }


    /**
     * Read a CSV file, check for correct amount of columns and returns it as an array
     *
     * @param        $file_name
     * @param string $delimiter
     * @param bool   $verify
     * @param bool   $has_header
     * @param bool   $preview
     * @param bool   $import_where
     *
     * @return array|void
     */
    function csv2wp_csv_to_array( $file_name, $delimiter = ';', $verify = false, $has_header = false, $preview = false, $import_where = false, $meta_key = false ) {

        // read file
        $csv_array   = [];
        $empty_array = false;
        $new_array   = [];
        if ( ( $handle = fopen( csv2wp_get_upload_folder( '/' ) . $file_name, "r" ) ) !== false ) {
            $line_number  = 0;
            $value_length = apply_filters( 'csv2wp_line_length', 1000 );

            while ( ( $csv_line = fgetcsv( $handle, $value_length, "{$delimiter}" ) ) !== false ) {
                $line_number++;
                $csv_array[ 'delimiter' ] = $delimiter;

                // if line is 1 and has header == true, count columns (to set benchmark)
                if ( 1 == $line_number ) {
                    if ( $has_header ) {
                        foreach ( $csv_line as $column ) {
                            $csv_array[ 'column_names' ][] = $column;
                        }
                    } else {
                        // @TODO: check if meta is imported
                    }
                    $csv_array[ 'column_count' ] = count( $csv_line );
                }

                // if column count doesn't match benchmark
                if ( in_array( $import_where, [ 'usermeta', 'postmeta' ] ) ) {
                    // if headers, check if cols are min 2
                    if ( false != $has_header ) {
                        if ( false == $meta_key ) {
                            // if ! has key must be 3
                            if ( count( $csv_line ) != 3 ) {
                                CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', esc_html( __( "You don't have the right amount of columns.", 'csv2wp' ) ) );

                                return;
                            }
                        } else {
                            // if has key must be 2
                            if ( count( $csv_line ) != 2 ) {
                                CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', esc_html( __( "You don't have the right amount of columns.", 'csv2wp' ) ) );

                                return;
                            }
                        }
                    } else {
                        // if no headers, check if cols are == 3
                        if ( count( $csv_line ) != 3 ) {
                            CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', esc_html( __( "You don't have the right amount of columns.", 'csv2wp' ) ) );

                            return;
                        }
                    }
                }

                // if column count doesn't match benchmark
                if ( count( $csv_line ) != $csv_array[ 'column_count' ] ) {
                    // if column count < benchmark
                    if ( count( $csv_line ) < $csv_array[ 'column_count' ] ) {
                        $error_message = esc_html( __( 'Since your file is not accurate anymore, the file is deleted.', 'csv2wp' ) );
                        if ( true == $verify ) {
                        } elseif ( true != $preview ) {
                            // for real
                            $error_message = 'Lines 1-' . ( $line_number ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                        }
                        CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', sprintf( __( 'There are too few columns on line %d. %s', 'csv2wp' ), $line_number, $error_message ) );

                    } elseif ( count( $csv_line ) > $csv_array[ 'column_count' ] ) {
                        // if column count > benchmark
                        $error_message = esc_html( __( 'Since your file is not accurate anymore, the file is deleted.', 'csv2wp' ) );
                        if ( true == $verify ) {
                        } elseif ( true != $preview ) {
                            // for real
                            $error_message = 'Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                        }
                        CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', sprintf( esc_html( __( 'There are too many columns on line %d. %s', 'csv2wp' ) ), $line_number, $error_message ) );
                    }
                    // delete file
                    unlink( csv2wp_get_upload_folder( '/' ) . $file_name );

                }

                if ( CSV2WP::csv2wp_errors()->get_error_codes() ) {

                    $empty_array = true;
                    $new_array   = [];

                } else {

                    // create a new array for each row
                    $new_line   = [];
                    $item_count = 0;
                    foreach ( $csv_line as $item ) {

                        if ( strlen( $item ) > $value_length ) {
                            CSV2WP::csv2wp_errors()->add( 'error_too_long_value', esc_html( sprintf( __( "The value '%s' is too long.", 'csv2wp' ), $item ) ) );

                            return;
                        }

                        if ( true == $has_header ) {
                            if ( 1 == $line_number ) {
                                // do nothing, headers don't need to be added
                            } else {
                                $new_line[ $csv_array[ 'column_names' ][ $item_count ] ] = $item;
                            }
                        } else {
                            $new_line[] = $item;
                        }
                        $item_count++;
                    }
                    if ( ! empty( $new_line ) ) {
                        $new_array[] = $new_line;
                    }
                }
            }
            fclose( $handle );

            /**
             * Don't add data if there are any errors. This to prevent rows which had no error from outputting
             * on the preview page.
             */
            if ( ! empty( $new_array ) && false == $empty_array ) {
                $csv_array[ 'data' ] = array_values( $new_array );
            }
        }

        return $csv_array;
    }

    /**
     * Verify raw CSV data
     *
     * @param bool $csv_data
     *
     * @return array|bool
     */
    function csv2wp_verify_raw_csv_data( $csv_data = false ) {

        if ( false != $csv_data ) {
            $validated_csv = [];
            $lines         = explode( "\n", $csv_data );
            $line_number   = 0;
            foreach ( $lines as $csv_line ) {
                $line_number++;

                if ( strlen( $csv_line ) < 2 ) {
                    CSV2WP::csv2wp_errors()->add( 'error_in_data', esc_html( __( 'There is an empty line on line ' . $line_number . '.', 'csv2wp' ) ) );

                    return false;

                } else {

                    // if line is 1, count columns (to set benchmark)
                    if ( 1 == $line_number ) {
                        // count columns to compare with other lines
                        $column_count = count( $csv_line );
                    }

                    $line_array = str_getcsv( $csv_line );
                    if ( count( $line_array ) != $column_count ) {
                        // length of a line if not correct
                        if ( count( $line_array ) < $column_count ) {
                            CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', esc_html( __( 'There are too few columns on line ' . $line_number . '.', 'csv2wp' ) ) );
                        } elseif ( count( $line_array ) > $column_count ) {
                            CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', esc_html( __( 'There are too many columns on line ' . $line_number . '.', 'csv2wp' ) ) );
                        }

                        return false;

                    } else {

                        // why did I need this again ???
                        $element_counter = 0;
                        foreach ( $line_array as $element ) {
                            $element_counter++;
                        }

                        // all good
                        $validated_csv[] = $line_array;
                    }
                }
            }

            return $validated_csv;
        }

        return false;
    }

    /**
     * Get upload folder for plugin, can be overriden with filter
     *
     * @param false $suffix
     *
     * @return mixed|void
     */
    function csv2wp_get_upload_folder( $suffix = false ) {

        $upload_folder = apply_filters( 'acfcs_upload_folder', wp_upload_dir()[ 'basedir' ] . '/csv2wp' . $suffix );

        return $upload_folder;
    }
