<?php
    function csv2wp_check_if_files() {
        $target_dir = csv2wp_get_upload_folder();
        if ( file_exists( $target_dir ) ) {
            $file_index = scandir( $target_dir );

            if ( is_array( $file_index ) ) {
                $actual_files = [];
                foreach ( $file_index as $file ) {
                    if ( '.DS_Store' !== $file && '.' !== $file && '..' !== $file ) {
                        $actual_files[] = $file;
                    }
                }
                if ( ! empty( $actual_files ) ) {
                    return $actual_files;
                }
            }
        }

        return [];
    }

    /*
     * Read a CSV file, check for correct amount of columns and returns it as an array
     */
    function csv2wp_csv_to_array( string $file_name, string $delimiter = ';', bool $verify = false, bool $has_header = false, bool $preview = false, string $import_where = '', $meta_key = false ) {
        $csv_array[ 'delimiter' ] = $delimiter;
        $empty_array              = false;
        $line_number              = 0;
        $new_array                = [];
        $handle                   = fopen( csv2wp_get_upload_folder( '/' ) . $file_name, "r" );

        if ( $handle ) {
            $value_length = apply_filters( 'csv2wp_line_length', 1000 );

            while ( ( $csv_line = fgetcsv( $handle, $value_length, "{$delimiter}" ) ) !== false ) {
                $line_number++;

                // if line is 1 and has header == true, count columns (to set benchmark)
                $header_data = csv2wp_get_header_data( $csv_line, $line_number, $has_header );

                if ( is_array( $header_data ) ) {
                    $csv_array = array_merge( $csv_array, $header_data );
                }

                // header is ok, so we proceed to check each row, to see if column count doesn't match the benchmark
                csv2wp_check_column_amount_line( $csv_line, $line_number, $csv_array[ 'column_count' ], $verify, $preview );

                if ( CSV2WP::csv2wp_errors()->get_error_codes() ) {
                    // there are errors, so we return an empty array
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

                        if ( true === $has_header ) {
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
            if ( CSV2WP::csv2wp_errors()->get_error_codes() ) {
                csv2wp_delete_file( $file_name );
            }

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

    function csv2wp_get_header_data( $csv_line, $line_number, $has_header = false ) {
        if ( ! $csv_line || ! $line_number || ! $import_where ) {
            return false;
        }

        $csv_data = [];

        if ( 1 == $line_number ) {
            if ( $has_header ) {
                foreach ( $csv_line as $column ) {
                    $csv_data[ 'column_names' ][] = $column;
                }
            } else {
                // @TODO: check if meta is imported (why)
            }
            $csv_data[ 'column_count' ] = count( $csv_line );
        }

        if ( in_array( $import_where, [ 'usermeta', 'postmeta' ] ) ) {
            if ( 1 == $line_number ) {
                csv2wp_check_column_amount_header( $csv_line, $file_name, $has_header, $meta_key );

                if ( CSV2WP::csv2wp_errors()->get_error_codes() ) {
                    return;
                }
            }
        }


        return $csv_data;
    }

    function csv2wp_check_column_amount_header( $csv_line, $file_name, $has_header = false, $meta_key = false ) {
        if ( ! $csv_line ) {
            $message = esc_html( __( "You have an empty header line.", 'csv2wp' ) );
            return $message;
        }

        // if headers, check if cols are min 2
        if ( false !== $has_header ) {
            if ( false == $meta_key ) {
                // if there is no meta_key, there must be 3 columns
                if ( count( $csv_line ) !== 3 ) {
                    $error = true;
                }
            } else {
                // if there is no meta_key, there must be 2 columns
                if ( count( $csv_line ) !== 2 ) {
                    $error = true;
                }
            }
        } else {
            // if there are no headers, check if there are 3 columns
            if ( count( $csv_line ) !== 3 ) {
                $error = true;
            }
        }

        if ( isset( $error ) && true == $error ) {
            $message1 = esc_html( __( "You don't have the right amount of columns in your header line.", 'csv2wp' ) );
            $message2 = esc_html__( 'Since your file is not accurate anymore, the file is deleted.', 'csv2wp' );
            csv2wp_delete_file( $file_name );
            CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', sprintf( '%s %s', $message1, $message2 ) );
        }
    }

    function csv2wp_check_column_amount_line( $csv_line, $line_number, $column_count, $verify = false, $preview = false ) {
        if ( ! $csv_line || ! $column_count ) {
            $message = esc_html( __( "No data or no column count", 'csv2wp' ) );
            CSV2WP::csv2wp_errors()->add( 'error_no_data_count', $message );
        }

        if ( count( $csv_line ) != $column_count ) {
            // if column count < benchmark
            $error_message = esc_html( __( 'Since your file is not accurate anymore, the file is deleted.', 'csv2wp' ) );
            if ( count( $csv_line ) < $column_count ) {
                if ( true == $verify ) {
                    // no lines will be imported in preview mode, so no message needed
                } elseif ( true != $preview ) {
                    // for real
                    $error_message = 'Lines 1-' . ( $line_number ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                }
                CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns_' . $line_number, sprintf( __( 'There are too few columns on line %d. %s', 'csv2wp' ), $line_number, $error_message ) );

            } elseif ( count( $csv_line ) > $column_count ) {
                // if column count > benchmark
                if ( true == $verify ) {
                } elseif ( true != $preview ) {
                    // for real
                    $error_message = 'Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                }
                CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns_' . $line_number, sprintf( esc_html( __( 'There are too many columns on line %d. %s', 'csv2wp' ) ), $line_number, $error_message ) );
            }
        }



        return true;
    }

    function csv2wp_verify_raw_csv_data( string $csv_data = '' ) : array|false {
        if ( ! empty( $csv_data ) ) {
            $validated_csv = [];
            $line_number   = 0;
            $lines         = explode( "\n", $csv_data );

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
                    if ( count( $line_array ) !== $column_count ) {
                        // length of a line if not correct
                        if ( count( $line_array ) < $column_count ) {
                            CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', esc_html( __( sprintf( 'There are too few columns on line %d.', $line_number ), 'csv2wp' ) ) );
                        } elseif ( count( $line_array ) > $column_count ) {
                            CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', esc_html( __( sprintf( 'There are too many columns on line %d.', $line_number ), 'csv2wp' ) ) );
                        }

                        return false;

                    } else {
                        // all good
                        $validated_csv[] = $line_array;
                    }
                }
            }

            return $validated_csv;
        }

        return false;
    }

    function csv2wp_get_upload_folder( $suffix = false ) {
        $folder = wp_upload_dir()[ 'basedir' ] . '/csv2wp' . $suffix;
        return apply_filters( 'csv2wp_upload_folder', $folder );
    }

    function csv2wp_delete_file( $file_name, $delete = true ) {
        if ( ! empty( $file_name ) && $delete ) {
            if ( file_exists( csv2wp_get_upload_folder( '/' ) . $file_name ) ) {
                unlink( csv2wp_get_upload_folder( '/' ) . $file_name );
            }
        }
    }
