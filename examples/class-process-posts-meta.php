<?php
	
	// тут общее кол-во записей в выборке уменьшается после каждого запроса
	class JWP_DH_Example_Process_Posts_Meta extends JWP\DH\Handler {
		
		public $title = 'Обработка записей по meta';
		
		public $max_process_elements = 10;
		
		public function process( $request, $response ) {
			$posts = get_posts( array(
				'post_type'      => 'post',
				'numberposts'    => $this->max_process_elements,
				'meta_query'     => array(
					array(
						'key'     => 'jwp_dh_test',
						'value'   => 5000,
						'compare' => '>',
						'type'    => 'NUMERIC'
					)
				)
			) );
			if ( $posts ) {
				$post_ids = array();
				foreach ( $posts as $post ) {
					$post_ids[] = $post->ID;
					update_post_meta( $post->ID, 'jwp_dh_test', 1 );
				}
				$response->output( 'Обработано: ' . join( ',', $post_ids ) );
			} else {
				$response->force_end();
			}
			return $response;
		}
		
		public function total( $request ) {
			$posts_args = array( 
				'post_type'      => 'post',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => 'jwp_dh_test',
						'value'   => 5000,
						'compare' => '>',
						'type'    => 'NUMERIC'
					)
				)
			);
			$total = new WP_Query( $posts_args );
			return $total->found_posts;
		}
	}
