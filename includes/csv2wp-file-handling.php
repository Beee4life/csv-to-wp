<?php $file_index = csv2wp_check_if_files(); ?>
<?php if ( ! empty( $file_index ) ) { ?>
    <div class="csv2wp__section">
        <h2>
            <?php esc_html_e( 'Handle a CSV file', 'csv2wp' ); ?>
        </h2>
        <p>
            <?php esc_html_e( 'Select a file, select where to import it, whether the file has a header row and if you want to limit the amount of lines.', 'csv2wp' ); ?>
        </p>

        <?php global $wpdb; ?>
        <form method="POST">
            <input name="select_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'select-file-nonce' ); ?>"/>
            <input name="csv2wp_header" type="hidden" value="<?php echo apply_filters( 'csv2wp_has_header', true ); ?>"/>

            <table class="csv2wp__table">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'File name', 'csv2wp' ); ?></th>
                    <th><?php esc_html_e( 'Import in', 'csv2wp' ); ?></th>
                    <th><?php esc_html_e( 'Delimiter', 'csv2wp' ); ?></th>
                    <th><span class="csv2wp__th csv2wp__th--table"><?php esc_html_e( 'Table', 'csv2wp' ); ?></span><span class="csv2wp__th csv2wp__th--meta hidden"><?php esc_html_e( 'Meta key', 'csv2wp' ); ?></span></th>
                    <th class="hidden"><?php esc_html_e( 'Max. lines', 'csv2wp' ); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <label>
                            <select id="csv2wp_row_id" name="csv2wp_file_name">
                                <?php if ( count( $file_index ) > 1 ) { ?>
                                    <option value=""><?php esc_html_e( 'Select a file', 'csv2wp' ); ?></option>
                                <?php } ?>
                                <?php foreach ( $file_index as $file ) { ?>
                                    <option value="<?php echo $file; ?>"><?php echo $file; ?></option>
                                <?php } ?>
                            </select>
                        </label>
                    </td>
                    <td>
                        <label>
                            <select name="csv2wp_import_in" class="csv2wp_import_in" id="csv2wp_import_in">
                                <?php foreach ( apply_filters( 'csv2wp_import_options', $import_options ) as $import_key => $import_label ) { ?>
                                    <option value="<?php echo $import_key; ?>"><?php echo $import_label; ?></option>
                                <?php } ?>
                            </select>
                        </label>
                    </td>
                    <td>
                        <?php $delimiters = [ ';', ',', '|' ]; ?>
                        <label>
                            <select name="csv2wp_delimiter" id="csv2wp_delimiter">
                                <?php foreach( $delimiters as $delimiter ) { ?>
                                    <?php $selected_delimiter = ( $delimiter == apply_filters( 'csv2wp_delimiter', ( false != $posted_delimiter ) ? $posted_delimiter : ';' ) ) ? ' selected' : false; ?>
                                    <option value="<?php echo $delimiter; ?>"<?php echo $selected_delimiter; ?>>
                                        <?php echo $delimiter; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </label>
                    </td>
                    <td>
                        <label>
                            <input id="csv2wp_key_table" class="csv2wp__key csv2wp__key--table" name="csv2wp_table" type="text" size="10" value="" placeholder="<?php echo $wpdb->prefix; ?>">
                            <input id="csv2wp_key_meta" class="csv2wp__key csv2wp__key--meta hidden" name="csv2wp_meta" type="text" size="10" value="" placeholder="meta key">
                        </label>
                    </td>
                    <td class="hidden">
                        <label>
                            <input type="number" name="csv2wp_max_lines" value="" />
                        </label>
                    </td>
                </tr>
                </tbody>
            </table>

            <input name="csv2wp_verify" type="submit" class="button button-primary" value="<?php esc_html_e( 'Verify', 'csv2wp' ); ?>"/>
            <input name="csv2wp_import" type="submit" class="button button-primary" value="<?php esc_html_e( 'Import', 'csv2wp' ); ?>"/>
            <input name="csv2wp_remove" type="submit" class="button button-primary" value="<?php esc_html_e( 'Remove', 'csv2wp' ); ?>"/>
        </form>
    </div>
<?php } ?>
