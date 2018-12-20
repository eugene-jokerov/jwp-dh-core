<?php
	namespace JWP\DH;
	defined( 'ABSPATH' ) || exit;

	/**
	 * JWP\DH\Handler - базовый класс пользовательских обработчиков
	 * 
	 * Базовый класс для пользовательских обработчиков. 
	 */
	class Handler_Base {
		
		/**
		 * Заголовок на странице обработчика
		 *
		 * @var string
		 */
		public $title = 'JWP Data Handler';
		
		/**
		 * Максимальное кол-во элементов, обрабатываемое за 1 запрос
		 * 
		 * Это свойство можно использовать в пользовательских обработчиках для создания выборки данных
		 *
		 * @var int
		 */
		public $max_process_elements = 10;
		
		/**
		 * Объект управления отображением
		 *
		 * @var JWP\DH\View 
		 */
		protected $view;
		
		/**
		 * Возвращает объект управления отображением
		 *
		 * @return JWP\DH\View
		 */
		public function view( $params = array() ) {
			if ( $this->view and $params ) {
				$this->view->setup( $params );
				return $this->view;
			}
			if ( ! $this->view ) {
				$this->view = new View( $params ); // JWP\DH\View
			}
			return $this->view;
		}
		
		/**
		 * Вычисляет и возвращает максимальное кол-во элементов, обрабатываемое за 1 запрос
		 *
		 * @param string $total
		 * @param string $offset
		 * @return int
		 */
		public function get_max_process_elements( $total = 0, $offset = 0 ) {
			if ( ! $total ) {
				return $this->max_process_elements;
			}
			if ( ( $total - $offset ) < $this->max_process_elements ) {
				return $total - $offset;
			}
			return $this->max_process_elements;
		}
		
		/**
		 * Возвращает заголовок пользовательского обработчика
		 *
		 * @return string
		 */
		public function get_title() {
			return $this->title;
		}
	}

