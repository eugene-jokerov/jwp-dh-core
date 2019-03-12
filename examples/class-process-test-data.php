<?php

	class JWP_DH_Example_Process_Test_Data extends JWP\DH\Handler {
		
		public $title = 'Обработка тестовых данных';
		
		public $max_process_elements = 10;
		
		public $test_data_count = 1362; // кол-во тестовых данных
		
		public function process( $request, $response ) {
			$range_start = $request->get( 'offset' ) + 1;
			$range_end = $request->get( 'offset' ) + $this->max_process_elements;
			if ( $range_end > $this->test_data_count ) {
				$range_end = $this->test_data_count;
			}
			$demo_elements = range( $range_start, $range_end );
			if ( $demo_elements ) {
				$post_ids = array();
				foreach ( $demo_elements as $element ) {
					$post_ids[] = $element;
					// тут можно обработать каждый элемент
				}
				$response->output( 'Обработаны элементы: ' . join( ',', $post_ids ) );
			}
			return $response;
		}
		
		public function total( $request ) {
			return $this->test_data_count;
		}
	}
