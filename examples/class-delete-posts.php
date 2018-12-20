<?php

	class JWP_DH_Example_Delete_Posts extends JWP\DH\Handler {

		public $title = 'Удаление записей';
		
		public $max_process_elements = 10;
		
		public function process( $request, $response ) {

			$posts = get_posts( array( 
				'post_type'      => 'post',
				'meta_key'       => 'jwp_dh_test',
				'numberposts'    => $this->max_process_elements,
			) );
			if ( ! $posts ) {
				$response->force_end();
				return $response;
			}
			$post_ids = array();
			foreach ( $posts as $post ) {
				$post_ids[] = $post->ID;
				wp_delete_post( $post->ID, true );
			}
			$response->output( 'Удалены: ' . join( ',', $post_ids ) );
			return $response;
		}
		
		public function total( $request ) {
			$args = array( 
				'post_type'      => 'post',
				'meta_key'       => 'jwp_dh_test',
				'posts_per_page' => 1,
			);
			$total = new WP_Query( $args );
			return $total->found_posts;
		}
	}
