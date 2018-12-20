<?php

	class JWP_DH_Example_Process_DBTable extends JWP\DH\Handler {
		
		public $title = 'Обработка таблицы в базе данных';
		
		public $max_process_elements = 10;
		
		public function process( $request, $response ) {
			global $wpdb;
			$offset = $request->get( 'offset' );
			$posts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'post' LIMIT {$this->max_process_elements} OFFSET {$offset}" );
			if ( $posts ) {
				$post_ids = array();
				foreach ( $posts as $post ) {
					$post_ids[] = $post->ID;
					// тут можно обработать каждую строку
				}
				$response->output( 'Обработано: ' . join( ',', $post_ids ) );
			}
			return $response;
		}
		
		public function total( $request ) {
			global $wpdb;
			$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type = 'post' " );
			return $total;
		}
	}

