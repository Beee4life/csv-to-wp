<?php
    
    /**
     * Verify CSV data
     *
     * @param bool $csv_data
     *
     * @return array|bool
     */
    function csv2wp_verify_raw_csv_data( $csv_data = false ) {
        
        if ( false != $csv_data ) {
            $validated_csv = array();
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
     * Check if files are uploaded
     *
     * @return array
     */
    function csv2wp_check_if_files() {
        
        $target_dir = wp_upload_dir()[ 'basedir' ] . '/csv2wp';
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
     */
    function csv2wp_csv_to_array( $file_name, $delimiter = ",", $verify = false, $has_header = false, $preview = false ) {

        // read file
        $csv_array   = array();
        $delete_file = false;
        $empty_array = false;
        $new_array   = array();
        if ( ( $handle = fopen( wp_upload_dir()[ 'basedir' ] . '/csv2wp/' . $file_name, "r" ) ) !== false ) {
            $line_number = 0;
            while ( ( $csv_line = fgetcsv( $handle, 1000, "{$delimiter}" ) ) !== false ) {
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
                if ( count( $csv_line ) != $csv_array[ 'column_count' ] ) {
                    // if column count < benchmark
                    if ( count( $csv_line ) < $csv_array[ 'column_count' ] ) {
                        $delete_file   = true;
                        $error_message = esc_html( __( 'Since your file is not accurate anymore, the file is deleted.', 'csv2wp' ) );
                        if ( true == $verify ) {
                        } elseif ( true != $preview ) {
                            // for real
                            $error_message = 'Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                        }
                        CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', sprintf( __( 'There are too few columns on line %d. %s', 'csv2wp' ), $line_number, $error_message ) );

                    } elseif ( count( $csv_line ) > $csv_array[ 'column_count' ] ) {
                        // if column count > benchmark
                        $delete_file   = true;
                        $error_message = esc_html( __( 'Since your file is not accurate anymore, the file is deleted.', 'csv2wp' ) );
                        if ( true == $verify ) {
                        } elseif ( true != $preview ) {
                            // for real
                            $error_message = 'Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                        }
                        CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', sprintf( esc_html( __( 'There are too many columns on line %d. %s', 'csv2wp' ) ), $line_number, $error_message ) );
                    }
                    if ( true == $delete_file ) {
                        // delete file
                        unlink( wp_upload_dir()[ 'basedir' ] . '/csv2wp/' . $file_name );
                    }
    
                }
    
                if ( CSV2WP::csv2wp_errors()->get_error_codes() ) {

                    $empty_array = true;
                    $new_array[] = false;

                } else {
    
                    // create a new array for each row
                    $new_line   = array();
                    $item_count = 0;
                    foreach ( $csv_line as $item ) {
        
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
     * Get pagination for
     *
     * @param $pages
     * @param $page_number
     *
     * @return bool|string
     */
    function csv2wp_get_pagination( $pages, $page_number ) {
        
        if ( $pages == 1 ) {
            return false;
        }
        
        $big = 999999999; // need an unlikely integer
        if ( $page_number <= 1 ) {
            $page_number = 1;
        }
        $pagination_args = array(
            'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format'    => '/page/',
            'total'     => $pages,
            'current'   => max( 1, $page_number ),
            'show_all'  => false,
            'end_size'  => 3,
            'mid_size'  => 2,
            'prev_next' => true,
            'prev_text' => __( '&laquo; Previous', 'csv2wp' ),
            'next_text' => __( 'Next &raquo;', 'csv2wp' ),
            'type'      => 'list',
        );
        $pagination      = sprintf( '<div class="paginator">%s</div>', paginate_links( $pagination_args ) );
        
        return $pagination;
        
    }
