<?php
	// тут будет обработано всё дерево рубрик и для каждой рубрики
	// будет передано управление в другой обработчик, который обработает каждую запись из этой рубрики
	class JWP_DH_Example_Multi extends JWP\DH\Handler {
		
		public $title = 'Рекурсивная обработка рубрик';
		
		public $max_process_elements = 1;
		
		public function process( $request, $response ) {
			$parent_id = 0;
			if ( $request->get( 'level' ) > 1 ) {
				$parent_id = $request->get_custom_data( 'parent_id' );
			}
			$terms = get_terms( 'category', array(
				'hide_empty' => false,
				'parent'     => $parent_id,
				'number'     => $this->max_process_elements,
				'offset'     => $request->get( 'offset' ),
			) );
			if ( $terms ) {
				$post_ids = array();
				$key = array_keys( $terms );
				$term = $terms[ $key[0] ];
				
				$response->level_up();
				$response->set_custom_data( 'parent_id', $term->term_id );
				$response->output( 'Рубрика: ' . $term->name );
				
				$response->change_handler( 'JWP_DH_Example_Multi_Step2' ); // передаём управление в другой обработчик
				$response->set_custom_data( 'term_id', $term->term_id );
				$response->level_up();
			} else {
				$term = get_term( $parent_id, 'category' );
				$response->set_custom_data( 'parent_id', $term->parent );
				$response->level_down();
			}
			return $response;
		}
		
		public function total( $request ) {
			$parent_id = 0;
			if ( $request->get( 'level' ) > 1 ) {
				$parent_id = $request->get_custom_data( 'parent_id' );
			}
			
			$terms = get_terms( 'category', array(
				'hide_empty' => false,
				'parent'     => $parent_id,
			) );
			return count( $terms );
		}
	}

	class JWP_DH_Example_Multi_Step2 extends JWP\DH\Handler {
		
		public $title = 'Обработка записей';
		
		public $max_process_elements = 10;
		
		public function process( $request, $response ) {
			$attachments = get_posts( array(
				'post_type'      => 'post',
				'category__in'   => $request->get_custom_data( 'term_id' ),
				'numberposts'    => $this->max_process_elements,
				'offset'         => $request->get( 'offset' ),
			) );
			if ( $attachments ) {
				$post_ids = array();
				foreach ( $attachments as $attachment ) {
					$post_ids[] = $attachment->ID;
				}
				$response->output( 'Посты: ' . join( ',', $post_ids ) );
			} else {
				$response->change_handler( 'JWP_DH_Example_Multi' ); // возвращаем управление в первый обработчик
				$response->set_custom_data( 'term_id', 0 );
				$response->level_down();
			}
			return $response;
		}
		
		public function total( $request ) {
			$attachments_args = array( 
				'post_type'      => 'post',
				'category__in'   => $request->get_custom_data( 'term_id' ),
				'posts_per_page' => 1,
			);
			$total = new WP_Query( $attachments_args );
			return $total->found_posts;
		}
	}
