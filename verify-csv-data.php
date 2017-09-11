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
                    CSV_Importer::csvi_errors()->add( 'error_in_data', __( 'There is an empty line on line ' . $line_number . '.', 'csv-importer' ) );

                    return;
                } else {

                    $line_array = str_getcsv( $line );

                    if ( count( $line_array ) != 5 ) {
                        // length of a line if not correct
                        if ( count( $line_array ) < 5 ) {
                            CSV_Importer::csvi_errors()->add( 'error_no_correct_columns', __( 'There are too few columns on line ' . $line_number . '.', 'csv-importer' ) );
                        } elseif ( count( $line_array ) > 5 ) {
                            CSV_Importer::csvi_errors()->add( 'error_no_correct_columns', __( 'There are too many columns on line ' . $line_number . '.', 'csv-importer' ) );
                        }
                        return;
                    } else {

                        $element_counter = 0;
                        foreach( $line_array as $element ) {
                            $element_counter++;

                        }

                        // all good
                        $array_csv[] = str_getcsv( $line );
                    }
                }
            }
            return true;
        }

        return false;
    }
