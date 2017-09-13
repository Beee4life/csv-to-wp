<?php
	/**
	 * Delete all race ranking data for all users
	 */
	function nuke_all_data() {

		if ( current_user_can( 'manage_options' ) && isset( $_POST[ "nuke_all_nonce" ] ) ) {
			if ( ! wp_verify_nonce( $_POST[ "nuke_all_nonce" ], 'nuke-all-nonce' ) ) {
				CSV_WP::csv2wp_errors()->add( 'error_nonce_no_match', __( 'Something went wrong. Please try again.', 'rankings-import' ) );

				return;
			} else {

				if ( 'nuke' == $_POST[ "nuke_it" ] ) {

					// get all users
					$check_user_args = array(
						'meta_query' => array(
							array(
								'key'     => 'race_rankings',
								'compare' => 'EXISTS'
							),
						),
					);
					$users_with_custom_fields = get_users( $check_user_args );
					if ( $users_with_custom_fields ) {
						foreach( $users_with_custom_fields as $user ) {
							delete_user_meta( $user->ID, 'race_rankings' );
						}
					}
					if ( class_exists( 'ActionLogger' ) ) {
						ActionLogger::al_log_user_action( 'nuke_all', 'rankings-import', ' nuked all rankings' );
					}
					CSV_WP::csv2wp_errors()->add( 'success_all_nuked', __( 'AAAAAAAAAND it\'s gone.', 'rankings-import' ) );

					return;
				}
			}
		}
	}

	/**
	 * Delete individual rankings for a user
	 */
	function delete_individual_ranking() {

		if ( current_user_can( 'manage_options' ) && isset( $_POST[ "remove_ranking_nonce" ] ) ) {
			if ( ! wp_verify_nonce( $_POST[ "remove_ranking_nonce" ], 'remove-ranking-nonce' ) ) {
				CSV_WP::csv2wp_errors()->add( 'error_nonce_no_match', __( 'Something went wrong. Please try again.', 'rankings-import' ) );

				return;
			} else {

				$user_id         = $_POST[ 'member_id' ];
				$years_to_remove = $_POST[ 'years' ];
				if ( false != $years_to_remove ) {
					$value_array = array();
					foreach( $years_to_remove as $remove_year ) {
						$csv_line = explode( ',', $remove_year );
						$year     = $csv_line[ 0 ];
						$category = $csv_line[ 1 ];
						$ranking  = $csv_line[ 2 ];
						$points   = $csv_line[ 3 ];
						$new_array = array(
							'year'          => $year,
							'category_name' => $category,
							'ranking'       => $ranking,
							'points'        => $points,
						);
						$value_array[] = $new_array;
					}
					if ( count( $value_array ) > 0 ) {
						$race_rankings = get_user_meta( $user_id, 'race_rankings', true );

						foreach( $value_array as $row ) {

							if ( false != $race_rankings ) {
								foreach( $race_rankings as $key => $ranking ) {
									if ( $ranking == $row ) {
										unset($race_rankings[$key]);
									}
								}
								$race_rankings = array_values( $race_rankings );
							}
							update_user_meta( $user_id, 'race_rankings', $race_rankings );
						}
						if ( class_exists( 'ActionLogger' ) ) {
							ActionLogger::al_log_user_action( 'individual_ranking_deleted', 'rankings-import', ' deleted ' . count( $value_array ) . ' ranking lines for ' . get_userdata( $user_id )->display_name );
						}
						CSV_WP::csv2wp_errors()->add( 'success_rankings_deleted', __( 'Ranking(s) deleted.', 'rankings-import' ) );
					}
				}
			}
		}
	}

