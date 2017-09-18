<?php

    /**
     * Verify CSV data
     *
     * @param bool $csv_data
     * @return array|bool
     */
    function verify_csv_data( $csv_data = false ) {

        if ( false != $csv_data ) {
	        $validated_csv = array();
	        $lines         = explode( "\n", $csv_data );
	        $line_number   = 0;
            foreach ( $lines as $line ) {
                $line_number++;

                if ( strlen( $line ) < 2 ) {
                    CSV_WP::csv2wp_errors()->add( 'error_in_data', esc_html( __( 'There is an empty line on line ' . $line_number . '.', 'csv2wp' ) ) );

                    return false;
                } else {

                    $line_array = str_getcsv( $line );

                    if ( count( $line_array ) != 5 ) {
                        // length of a line if not correct
                        if ( count( $line_array ) < 5 ) {
                            CSV_WP::csv2wp_errors()->add( 'error_no_correct_columns', esc_html( __( 'There are too few columns on line ' . $line_number . '.', 'csv2wp' ) ) );
                        } elseif ( count( $line_array ) > 5 ) {
                            CSV_WP::csv2wp_errors()->add( 'error_no_correct_columns', esc_html( __( 'There are too many columns on line ' . $line_number . '.', 'csv2wp' ) ) );
                        }
                        return false;
                    } else {

                        $element_counter = 0;
                        foreach( $line_array as $element ) {
                            $element_counter++;

                        }

                        // all good
                        $validated_csv[] = str_getcsv( $line );
                    }
                }
            }
            return $validated_csv;
        }

        return false;
    }
