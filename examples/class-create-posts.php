<?php
	class JWP_DH_Example_Create_Posts extends JWP\DH\Handler {
			
		public $title = 'Создание записей';
		
		public $max_process_elements = 10; // сколько создаст за 1 ajax запрос
		
		public function process( $request, $response ) {

			$post_ids = array();
			for ( $i = 0; $i < $this->max_process_elements; $i++ ) {
				
				$post_data = array(
					'post_title'    => rand( 11111, 9999999 ),
					'post_status'   => 'publish',
					'post_author'   => 1,
				);

				// Вставляем запись в базу данных
				$post_id = wp_insert_post( $post_data );
				add_post_meta( $post_id, 'jwp_dh_test', rand( 1, 9999 ) );
				$post_ids[] = $post_id;
			}
			$response->output( 'Добавлено: ' . join( ',', $post_ids ) );
			return $response;
		}
		
		public function total( $request ) {
			return 100; // всего записей нужно создать
		}
	}
