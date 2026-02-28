<?php
    // There are no (more) errors, so file can be processed
    // $verify = false, so this is for real
    $max_lines = ( ! empty( $_POST[ 'csv2wp_max_lines' ] ) ) ? $_POST[ 'csv2wp_max_lines' ] : false;
    $success   = false;

    if ( is_array( $csv_array[ 'data' ] ) ) {
        $line_number = 0;

        if ( 'table' == $import_where ) {
            $table = isset( $_POST[ 'csv2wp_table' ] ) ? $_POST[ 'csv2wp_table' ] : false;

            if ( $create_table === true ) {
                foreach( $csv_array[ 'data' ] as $line ) {
                    $data_line = [];
                    foreach( $line as $column_name => $value ) {
                        $data_line[ strtolower( $column_name ) ] = $value;
                    }
                    $result = $wpdb->insert( $table, $data_line );
                    if ( 1 == $result ) {
                        $line_number++;
                    }
                    if ( false !== $max_lines && $max_lines == $line_number ) {
                        break;
                    }
                }
                $success = true;
            }

        } elseif ( in_array( $import_where, [ 'usermeta', 'postmeta' ] ) ) {
            foreach( $csv_array[ 'data' ] as $line ) {
                $header_row = ( true === $has_header ) ? $csv_array[ 'column_names' ] : [];
                $post_id    = false;
                $user_id    = false;

                // make function from this, so it's 1 line
                if ( 'postmeta' == $import_where ) {
                    if ( ! empty( $header_row ) ) {
                        if ( ! in_array( 'post_id', $header_row ) ) {
                            CSV2WP::csv2wp_errors()->add( "error_no_postid", sprintf( esc_html__( "%s has no column 'post_id'.", 'csv2wp' ), $file_name ) );

                            return;
                        } else {
                            // get key from post id
                            $post_id = $line[ 'post_id' ];
                            unset( $line[ 'post_id' ] );
                        }
                    }
                } elseif ( 'usermeta' == $import_where ) {
                    if ( ! empty( $header_row ) ) {
                        if ( ! in_array( 'user_id', $header_row ) ) {
                            CSV2WP::csv2wp_errors()->add( "error_no_userid", sprintf( esc_html__( "%s has no column 'user_id'.", 'csv2wp' ), $file_name ) );

                            return;
                        } else {
                            // get key from user id
                            $user_id = $line[ 'user_id' ];
                            unset( $line[ 'user_id' ] );
                        }
                    }
                }

                $result = false;
                if ( ! empty( $header_row ) ) {
                    $meta_key   = $line[ 'meta_key' ];
                    $meta_value = $line[ 'meta_value' ];

                    if ( 'postmeta' == $import_where && false != $post_id ) {
                        $result = update_post_meta( $post_id, $meta_key, $meta_value );
                    } elseif ( 'usermeta' == $import_where && false != $user_id) {
                        $result = update_user_meta( $user_id, $meta_key, $meta_value );
                    }

                    if ( false != $result ) {
                        $line_number++;
                        $success = true;
                    }

                } else {
                    // prepare data for update_*_meta
                    $id = $line[ 0 ];

                    if ( false != $entered_meta_key ) {
                        $meta_key   = $entered_meta_key;
                        $meta_value = $line[ 1 ];
                    } else {
                        $meta_key   = $line[ 1 ];
                        $meta_value = $line[ 2 ];
                    }

                    if ( false != $id && false != $meta_key && false != $meta_value ) {
                        if ( 'postmeta' == $import_where ) {
                            $result = update_post_meta( $id, $meta_key, $meta_value );
                        } elseif ( 'usermeta' == $import_where ) {
                            $result = update_user_meta( $id, $meta_key, $meta_value );
                        }
                        if ( false != $result ) {
                            $line_number++;
                            $success = true;
                        }
                    }
                }
            }
        }
    }
