<?php

	function csv2wp_dashboard_page() {
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}

		$show_search = false;
		$show_nuke = false;

		?>

		<div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h2>CSV importer dashboard</h2>

            <?php CSV_WP::csv2wp_show_admin_notices(); ?>

            <div id="csv-importer" class="">

	            <?php echo CSV_WP::csv2wp_admin_menu(); ?>

                <p><?php esc_html_e( 'This page allows to import a csv and import it into your database.', 'csv2wp' ); ?>

                <h2><?php esc_html_e( 'Upload a file', 'csv2wp' ); ?></h2>

                <form enctype="multipart/form-data" method="POST">
                    <input name="import_rankings_nonce" type="hidden" value="<?php echo wp_create_nonce( 'import-rankings-nonce' ); ?>" />
                    <input type="hidden" name="MAX_FILE_SIZE" value="1024000" />
                    <label for="file_upload"><?php esc_html_e( 'Choose a (csv) file to upload', 'csv2wp' ); ?></label>
                    <input name="csv_upload" type="file" accept=".csv" />
                    <br /><br />
                    <input type="submit" value="Upload file" />
                </form>

                <?php
                    // @TODO: check if directory exists
                    $target_dir = plugin_dir_path( __FILE__ ) . 'uploads/';
                    $file_index = scandir( $target_dir );
                    if ( $file_index ) {
                ?>
                    <br />
                    <h2><?php esc_html_e( 'Select a file to \'handle\'', 'csv2wp' ); ?></h2>

                    <?php if ( 0 < count( $file_index ) ) { ?>
                        <form name="" method="POST">
                            <input name="select_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'select-file-nonce' ); ?>" />
                            <table class="uploaded_files">
                                <?php
                                    $has_files = false;
                                    foreach( $file_index as $file ) {
                                        if ( '.DS_Store' != $file && '.' != $file && '..' != $file ) {
                                            $has_files = true;
                                            ?>
                                            <tr>
                                                <td><input name="file_name[]" type="checkbox" value="<?php echo $file; ?>"></td>
                                                <td><?php echo $file; ?></td>
                                            </tr>
                                        <?php }
                                    }
                                ?>
                            </table>
                            <?php if ( $has_files ) { ?>
                                <br />
                                <input name="verify" type="submit" value="Verify selected file(s)" />
                                <input name="import" type="submit" value="Import selected file(s)" />
                                <input name="remove" type="submit" value="Remove selected file(s)" />
                            <?php } ?>
                        </form>

                        <?php } ?>

                        <?php if ( false == $has_files && '.DS_Store' != $file_index[0] ) { ?>
                            <ul><li>No files uploaded</li></ul>
                        <?php } ?>
                    <?php } ?>


                <?php if ( false != $show_search ) { ?>
                    <br />
                    <h2>Enter an IDF #</h2>
                    <form method="POST">
                        <input name="select_user_nonce" type="hidden" value="<?php echo wp_create_nonce( 'select-user-nonce' ); ?>" />
                        <input name="selected_user" type="number" value="" placeholder="Enter user id/ IDF #" />
                        <input type="submit" value="Select user" />
                    </form>
                <?php } ?>

                <?php
                    if ( isset( $_POST[ "select_user_nonce" ] ) ) {
                        if ( wp_verify_nonce( $_POST[ "select_user_nonce" ], 'select-user-nonce' ) ) {
                            if ( isset( $_POST[ "selected_user" ] ) || isset( $_POST[ "member_id" ] ) ) {
                                $user_id = $_POST[ "selected_user" ];
                            }
                        }
                    }
                    if ( isset( $_POST[ "member_id" ] ) ) {
                        $user_id = $_POST[ "member_id" ];
                    }

                    $race_rankings = false;
                    if ( ! empty( $user_id ) ) {
                        $race_rankings = get_user_meta( $user_id, 'race_rankings', true );
                    }

                    if ( false != $race_rankings ) { ?>
                    <br />
                    <h2>User\'s rankings</h2>
                    <p>If there are double values, you can delete them, BUT please note, both instances will be deleted !!!</p>
                    <form method="POST"">
                        <input name="remove_ranking_nonce" type="hidden" value="<?php echo wp_create_nonce( 'remove-ranking-nonce' ); ?>" />
                        <input name="member_id" type="hidden" value="<?php echo $user_id; ?>" />

                        <table class="user__race-results" cellpadding="0" cellspacing="0">
                            <thead>
                            <tr>
                                <th>Year</th>
                                <th>Category</th>
                                <th>Position</th>
                                <th>Points</th>
                                <th>Remove</th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach( $race_rankings as $category ) { ?>
                                <tr>
                                <td><?php echo $category[ 'year' ]; ?></td>
                                <td><?php echo $category[ 'category_name' ]; ?></td>
                                <td><?php echo $category[ 'ranking' ]; ?></td>
                                <td><?php echo $category[ 'points' ]; ?></td>
                                <td>
                                    <input name="years[]" type="checkbox" value="<?php echo $category[ 'year' ]; ?>,<?php echo $category[ 'category_name' ]; ?>,<?php echo $category[ 'ranking' ]; ?>,<?php echo $category[ 'points' ]; ?>" /></td>
                                </tr>
                            <?php } ?>
                        </tbody></table>
                        <input type="submit" value="Remove rankings" />
                    </form>
                <?php } ?>

                <br />
                <h2>Import raw CSV data</h2>
                <p>
                    <?php esc_html_e( 'Make sure the cursor is ON the last line (after the last character), NOT on a new line.', 'csv2wp' ); ?>
                    <br />
                    <?php esc_html_e( 'This is seen as a new entry and creates an error !!!', 'csv2wp' ); ?>
                </p>

                <?php $submitted_raw_data = false; if ( isset( $_POST[ 'raw_csv_import' ] ) ) { $submitted_raw_data = $_POST[ 'raw_csv_import' ]; } ?>
                <form method="POST">
                    <input name="import_raw_rankings_nonce" type="hidden" value="<?php echo wp_create_nonce( 'import-raw-rankings-nonce' ); ?>" />
                    <label for="raw-import"></label>
                    <textarea name="raw_csv_import" id="raw-import" type="textarea" rows="5" cols="50" placeholder=""><?php echo $submitted_raw_data; ?></textarea>
                    <br />
                    <input name="verify" type="submit" value="Verify data" />
                    <input name="import" type="submit" value="Import data" />
                </form>

                <?php if ( false != $show_nuke ) { ?>
                    <br />
                    <h2>Nuke all user rankings</h2>
                    <p>Here you can nuke ALL user rankings for all years for all categories.<br />WATCH OUT ! There\'s no confirmation, so nuke it and it\'s gone.</p>

                    <form method="POST">
                        <input name="nuke_all_nonce" type="hidden" value="<?php echo wp_create_nonce( 'nuke-all-nonce' ); ?>" />
                        <input name="nuke_it" type="checkbox" value="nuke" /></td>
                        <input name="nuke" type="submit" value="NUKE IT ALL !" />
                    </form>
                <?php } ?>
            </div>
        </div>

<?php
    } // end race_overview - content for settings page
