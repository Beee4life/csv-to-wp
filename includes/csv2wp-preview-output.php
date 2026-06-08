<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    echo '<h2>' . esc_html__( 'CSV contents', 'csv-to-wp' ) . '</h2>';
    echo '<table class="csv2wp__table csv2wp__table--preview">';
    if ( $has_header && ! empty( $header_row ) ) {
        echo '<thead>';
        echo '<tr>';
        foreach ( $header_row as $column ) {
            echo '<th>' . esc_html( $column ) . '</th>';
        }
        echo '</tr>';
        echo '</thead>';
    }
    echo '<tbody>';
    $line_number = 0;
    foreach ( $csv_info[ 'data' ] as $line ) {
        $line_number++;
        echo '<tr>';
        foreach ( $line as $column ) {
            echo '<td>';
            echo esc_html($column);
            if ( $show_length ) {
                echo ' [' . (int) strlen( $column ) . ']';
            }
            echo '</td>';
        }
        echo '</tr>';
        if ( $line_number == $max_lines ) {
            break;
        }
    }
    echo '</tbody>';
    echo '</table>';
