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
        
        $target_dir = plugin_dir_path( __FILE__ ) . '../uploads/';
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
     * @param $handle
     * @param $verify
     *
     * @return array|bool
     */
    function csv2wp_csv_to_array( $file_name, $verify = false ) {
        
        // read file
        $csv_array = array();
        if ( ( $handle = fopen( plugin_dir_path( __FILE__ ) . '../uploads/' . $file_name, "r" ) ) !== false ) {
            $line_number      = 0;
            $column_benchmark = 0;
            // @TODO: set 1000 from setting
            while ( ( $csv_line = fgetcsv( $handle, 1000, "," ) ) !== false ) {
                $line_number++;
                
                // if line is 1, count columns (to set benchmark)
                if ( 1 == $line_number ) {
                    // count columns to compare with other lines
                    $column_benchmark = count( $csv_line );
                    
                    foreach ( $csv_line as $column ) {
                        $column_names[] = $column;
                    }
                }
                
                // if column count doesn't match benchmark
                if ( count( $csv_line ) != $column_benchmark ) {
                    // if column count < benchmark
                    if ( count( $csv_line ) < $column_benchmark ) {
                        if ( false != $verify ) {
                            $error_message = esc_html( __( 'Since your file is not accurate anymore, the file is deleted.', 'csv2wp' ) );
                        } else {
                            $error_message = 'Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                        }
                        CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', sprintf( __( 'There are too few columns on line %d. %s', 'csv2wp' ), $line_number, $error_message ) );
                        
                    } elseif ( count( $csv_line ) > $column_benchmark ) {
                        // if column count > benchmark
                        if ( false != $verify ) {
                            $error_message = esc_html( __( 'Since your file is not accurate anymore, the file is deleted.', 'csv2wp' ) );
                        } else {
                            $error_message = 'Lines 0-' . ( $line_number - 1 ) . ' are correctly imported but since your file is not accurate anymore, the file is deleted';
                        }
                        CSV2WP::csv2wp_errors()->add( 'error_no_correct_columns', sprintf( esc_html( __( 'There are too many columns on line %d. %s', 'csv2wp' ) ), $line_number, $error_message ) );
                    }
                    foreach ( $_POST[ 'csv2wp_file_name' ] as $file_name ) {
                        // delete file
                        unlink( plugin_dir_path( __FILE__ ) . 'uploads/' . $file_name );
                    }
                    
                    return false;
                }
                
                // create a new array for each row
                $new_line   = array();
                $item_count = 0;
                foreach ( $csv_line as $item ) {
                    if ( 1 == $line_number ) {
                        // headers don't need an array index
                        $new_line[] = $item;
                    } else {
                        $new_line[ $column_names[ $item_count ] ] = $item;
                    }
                    $item_count++;
                }
                $csv_array[] = $new_line;
                
            }
            fclose( $handle );
            
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
