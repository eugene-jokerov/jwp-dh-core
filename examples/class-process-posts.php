<?php

	class JWP_DH_Example_Process_Posts extends JWP\DH\Handler {
		
		public $title = 'Обработка записей';
		
		public $max_process_elements = 10;
		
		public function process( $request, $response ) {
			$posts = get_posts( array(
				'post_type'   => 'post',
				'numberposts' => $this->max_process_elements,
				'offset'      => $request->get( 'offset' ),
			) );
			if ( $posts ) {
				$post_ids = array();
				foreach ( $posts as $post ) {
					$post_ids[] = $post->ID;
					// тут можно обработать каждую запись
				}
				$response->output( 'Обработаны записи: ' . join( ',', $post_ids ) );
			}
			return $response;
		}
		
		public function total( $request ) {
			$posts_args = array( 
				'post_type'      => 'post',
				'posts_per_page' => 1,
			);
			$total = new WP_Query( $posts_args );
			return $total->found_posts;
		}
	}
