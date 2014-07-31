<?php

class SRP_Related_Post_Manager {

	/**
	 * Get related posts by post id and post type
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public function get_related_posts( $post_id, $limit=-1 ) {
		global $wpdb;

		$related_posts = array();

		// Build SQl
		$sql = "
		SELECT O.`word`, P.`ID`, P.`post_title`, SUM( R.`weight` ) AS `related_weight`
		FROM `" . SRP_Related_Word_Manager::get_database_table() . "` O
		INNER JOIN `" . SRP_Related_Word_Manager::get_database_table() . "` R ON R.`word` = O.`word`
		INNER JOIN `" . $wpdb->posts . "` P ON P.`ID` = R.`post_id`
		WHERE 1=1
		AND O.`post_id` = %d
		AND R.`post_type` = 'post'
		AND R.`post_id` != %d
		AND P.`post_status` = 'publish'
		GROUP BY P.`id`
		ORDER BY `related_weight` DESC
		";

		// Check & Add Limit
		if(-1 != $limit) {
			$sql .= "
			LIMIT 0,%d";
		};

		// Prepare SQL
		$sql = $wpdb->prepare( $sql , $post_id, $post_id );

		// Replace limit
		if(-1 != $limit) {
			$sql = $wpdb->prepare( $sql , $limit );
		}

		// Get post from related cache
		$rposts = $wpdb->get_results( $sql );

		if ( count( $rposts ) > 0 ) {
			foreach ( $rposts as $rpost ) {
				if ( !isset( $related_posts[$rpost->ID] ) ) {
					$related_posts[] = $rpost;
				}

			}
		}

		return $related_posts;
	}

	/**
	 * Get non auto linked posts
	 *
	 * @param $limit
	 *
	 * @return array
	 */
	public function get_not_auto_linked_posts( $limit ) {
		return get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => SRP_Constants::PM_AUTO_LINKED,
					'compare' => 'NOT EXISTS',
					'value'   => ''
				),
			)
		) );
	}

	/**
	 * Link x related posts to post
	 *
	 * @param $post_id
	 * @param $amount
	 */
	public function link_related_post( $post_id, $amount ) {
		$posts = $this->get_related_posts( $post_id, $amount );
		echo '<pre>';
		print_r($posts);
		exit;
	}

	/**
	 * Link x related posts to y not already linked posts
	 *
	 * @param int $amount
	 */
	public function link_related_posts( $rel_amount, $post_amount=-1 ) {
		global $wpdb;

		// Get uncached posts
		$posts = $this->get_not_auto_linked_posts($post_amount);

		// Check & Loop
		if ( count( $posts ) > 0 ) {
			foreach ( $posts as $post ) {
				$this->link_related_post( $post->ID, $rel_amount );
			}
		}

		// Done
		return true;
	}

}