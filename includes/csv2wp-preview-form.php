<div class="csv2wp__section">
    <form name="select-preview-file" id="settings-form" action="" method="post">
        <input name="select_preview_nonce" type="hidden" value="<?php echo wp_create_nonce( 'select-preview-nonce' ); ?>"/>
        <input name="csv2wp_has_header" type="hidden" value="1"/>
        <table class="csv2wp__table">
            <thead>
            <tr>
                <th><?php esc_html_e( 'File name', 'csv2wp' ); ?></th>
                <th><?php esc_html_e( 'Delimiter', 'csv2wp' ); ?></th>
                <th><?php esc_html_e( 'Show value length', 'csv2wp' ); ?></th>
                <th><?php esc_html_e( 'Max. lines', 'csv2wp' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <label>
                        <select name="csv2wp_file_name" id="select-preview-file">
                            <?php $posted_file = ( isset( $_POST[ 'csv2wp_file_name' ] ) ) ? $_POST[ 'csv2wp_file_name' ] : false; ?>
                            <?php if ( count( $file_index ) > 1 ) { ?>
                                <option value=""><?php esc_html_e( 'Select a file', 'csv2wp' ); ?></option>
                            <?php } ?>
                            <?php foreach ( $file_index as $file ) { ?>
                                <option value="<?php echo $file; ?>"<?php echo( $posted_file == $file ? ' selected' : false ); ?>><?php echo $file; ?></option>
                            <?php } ?>
                        </select>
                    </label>
                </td>

                <td>
                    <?php $delimiters = [ ';', ',', '|' ]; ?>
                    <label>
                        <select name="csv2wp_delimiter" id="csv2wp_delimiter">
                            <?php foreach( $delimiters as $delimiter ) { ?>
                                <?php $selected_delimiter = ( $delimiter == apply_filters( 'csv2wp_delimiter', ';' ) ) ? ' selected' : false; ?>
                                <option value="<?php echo $delimiter; ?>"<?php echo $selected_delimiter; ?>>
                                    <?php echo $delimiter; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </label>
                </td>

                <td>
                    <label>
                        <input name="csv2wp_show_length" id="csv2wp_show_length" type="checkbox" value="1"<?php if ( isset( $show_length ) && true == $show_length ) { echo ' checked'; } ?>/> <?php esc_html_e( 'Yes', 'csv2wp' ); ?>
                    </label>
                </td>

                <td>
                    <label>
                        <input type="number" name="csv2wp_max_lines" id="csv2wp_max_lines" value="<?php echo $max_lines; ?>" />
                    </label>
                </td>
            </tr>
            </tbody>
        </table>

        <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Preview this file', 'csv2wp' ); ?>"/>
    </form>
</div>
