<?php

    /**
     * Content for the settings page
     */
    function csvi_preview_page() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Sorry, you do not have sufficient permissions to access this page.', 'csv-importer' ) );
        }
        ?>

        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1>CSV Importer overview</h1>

	        <?php CSV_Importer::csvi_show_admin_notices(); ?>

            <div id="csv-importer" class="">

	            <?php echo CSV_Importer::csvi_admin_menu(); ?>

                <!--<h2>--><?php //esc_html_e( 'CSM Import results', 'csv-importer' ); ?><!--</h2>-->
                <p>Here you can preview your uploaded csv files.</p>

                <?php //echo '<pre>'; var_dump($_POST); echo '</pre>'; exit; ?>

	            <?php
                    if ( isset( $_POST ) && isset( $_POST['file_name'] ) ) {
	                    $file_name = $_POST['file_name'];
                    } else {
	                    $file_name = false;
                    }
	            ?>

                <!--Get imported data-->
	            <?php $target_dir = plugin_dir_path( __FILE__ ) . 'uploads/'; ?>
	            <?php $file_index = scandir( $target_dir ); ?>
	            <?php //echo '<pre>'; var_dump($file_index); echo '</pre>'; exit; ?>
                <form name="select-preview-file" id="settings-form" action="" method="post">
                    <input name="select_preview_nonce" type="hidden" value="<?php echo wp_create_nonce( 'select-preview-nonce' ); ?>"/>
                    <label for="select-preview-file" class="screen-reader-text"></label>
                    <select name="file_name" id="select-preview-file">
	                    <?php if ( $file_index ) { ?>
                            <?php $posted_file = false;
                                if ( isset( $_POST[ 'file_name' ] ) ) {
                                    $posted_file = $_POST[ 'file_name' ];
                                }
                            ?>
                            <option value="">Choose a file</option>
                            <?php foreach( $file_index as $file ) { ?>
			                    <?php if ( '.DS_Store' != $file && '.' != $file && '..' != $file ) { ?>
                                    <option value="<?php echo $file; ?>"<?php echo ( $posted_file == $file ? ' selected' : '' ); ?>><?php echo $file; ?></option>
			                    <?php } ?>
		                    <?php } ?>
                        <?php } ?>
                    </select>
                    <input type="submit" class="admin-button admin-button-small" value="Preview this file" />
                </form>

                <!--Get imported data-->
                <?php if ( $file_name ) { ?>
                    <?php
                        $lines = CSV_Importer::csv_to_array( $file_name );
                        if ( false != $lines ) {
	                        $header_row = array_shift($lines);
	                        $column_count = count( $header_row );
	                        echo '<h2>CSV contents</h2>';
	                        echo '<table class="csv-preview" cellpadding="0" cellspacing="0">';
	                        echo '<thead>';
	                        echo '<tr>';
	                        foreach( $header_row as $column ) {
	                            echo '<th>' . $column . '</th>';
                            }
	                        // echo '<th>&nbsp;</th>';
	                        echo '</tr>';
	                        echo '</thead>';
	                        echo '<tbody>';
                            $line_number = 0;
                            foreach( $lines as $line ) {
                                $line_number++;
	                            echo '<tr>';
	                            foreach( $line as $column ) {
		                            echo '<td>' . $column . '</td>';
                                }
	                            // echo '<td>X</td>';
	                            echo '</tr>';
                            }
	                        echo '</tbody>';
	                        echo '</table>';
                        }
                    ?>
                <?php } ?>

            </div><!-- end #csv-importer -->

        </div><!-- end .wrap -->
<?php
    }
