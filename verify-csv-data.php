<?php

    /**
     * Verify CSV data
     *
     * @param bool $csv_data
     * @return bool|void
     */
    function verify_csv_data( $csv_data = false ) {

        if ( false != $csv_data ) {
            $array_csv = '';

            $lines     = explode( "\n", $csv_data );
            $line_number = 0;
            foreach ( $lines as $line ) {
                $line_number++;

                if ( strlen( $line ) < 2 ) {
                    idf_errors()->add( 'error_in_data', __( 'There is an empty line on line ' . $line_number . '.', 'freerides' ) );

                    return;
                } else {

                    $line_array = str_getcsv( $line );

                    if ( count( $line_array ) != 5 ) {
                        // length of a line if not correct
                        if ( count( $line_array ) < 5 ) {
                            idf_errors()->add( 'error_no_correct_columns', __( 'There are too few columns on line ' . $line_number . '.', 'freerides' ) );
                        } elseif ( count( $line_array ) > 5 ) {
                            idf_errors()->add( 'error_no_correct_columns', __( 'There are too many columns on line ' . $line_number . '.', 'freerides' ) );
                        }
                        return;
                    } else {

                        $element_counter = 0;
                        foreach( $line_array as $element ) {
                            $element_counter++;
                            // check if an element is 0
                            if ( '0' == $element ) {
                                idf_errors()->add( 'error_0_in_data', __( 'There is a zero on line ' . $line_number . '.', 'freerides' ) );

                                return;
                            }

                            if ( $element_counter == 1 ) {
                                // check if current user exists
                                $user_data = get_userdata( $element );
                                if ( false == $user_data ) {

                                    $invalid_idf_id = $element;
                                    $new_idf_id     = false;
                                    $new_idf_id     = get_new_idf_id( $invalid_idf_id );
                                    if ( false == $new_idf_id ) {
                                        idf_errors()->add( 'error_zero_value', __( 'There\'s a user id for a non-existing member on line ' . $line_number . '.', 'freerides' ) );

                                        return;
                                    }
                                    $line = str_replace( $invalid_idf_id, $new_idf_id, $line );
                                }
                            }
                            if ( $element_counter == 2 ) {
                                if ( in_array( $element, [ 'Open', 'Women', 'Juniors', 'Masters', 'Luge', 'Street luge', 'Classic luge' ] ) ) {
                                    idf_errors()->add( 'error_wrong_category', __( 'There is a non-accepted category name on line ' . $line_number . '.', 'freerides' ) );

                                    return;
                                }
                            }
                        }

                        // all good
                        $array_csv[] = str_getcsv( $line );
                    }
                }
            }
            return $array_csv;
        }

        return false;
    }
