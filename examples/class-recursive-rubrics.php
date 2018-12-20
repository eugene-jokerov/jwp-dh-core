<?php
	
	// будет пройдено всё дерево рубрик и каждую можно обработать
	class JWP_DH_Example_Recursive_Rubrics extends JWP\DH\Handler {
		
		public $title = 'Рекурсивная обработка рубрик';
		
		public $max_process_elements = 1; // при рекурсивной обработке в выборке по 1 элементу
		
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
				// тут можно обработать рубрику
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
