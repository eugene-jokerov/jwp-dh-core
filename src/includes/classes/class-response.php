<?php
	namespace JWP\DH;
	defined( 'ABSPATH' ) || exit;

	/**
	 * JWP\DH\Response
	 * 
	 * Хранит состояние системы и используется для передачи его между backend и frontend
	 */
	class Response {
		/**
		 * Отступ от первого элемента выборки
		 *
		 * @var int
		 */
		protected $offset = 0;
		
		/**
		 * Всего элементов в выборке
		 *
		 * @var int
		 */
		protected $total = 0;
		
		/**
		 * Массив сообщений, которые надо передать на frontend
		 *
		 * @var array
		 */
		protected $output = array();
		
		/**
		 * Дополнительные пользовательские данные
		 *
		 * @var array
		 */
		protected $custom = array();
		
		/**
		 * Уровень вложенности
		 *
		 * @var int
		 */
		protected $level = 1;
		
		/**
		 * Имя класса обработчика
		 *
		 * @var string
		 */
		protected $slug = '';
		
		/**
		 * Возвращает указанный параметр
		 *
		 * @param string $property_name
		 * @return mixed
		 */
		public function get( $property_name ) {
			if ( ! $property_name ) return false;
			if ( isset( $this->$property_name ) ) {
				return $this->$property_name;
			}
			return false;
		}
		
		/**
		 * Устанавливает значение параметра
		 *
		 * @param string $property_name
		 * @param mixed $value
		 * @return void
		 */
		public function set( $property_name, $value ) {
			if ( isset( $this->$property_name ) ) {
				$this->$property_name = $value;
			}
		}
		
		/**
		 * Добавляет новое сообщение в массив сообщений
		 *
		 * @param string $value
		 * @return void
		 */
		public function output( $value ) {
			$this->output[] = $value;
		}
		
		/**
		 * Принудительно завершает обработку данных
		 * Путём уравнивания общего кол-ва объектов с кол-вом обработанных
		 *
		 * @return void
		 */
		public function force_end() {
			$this->total = $this->offset;
			$this->level = 1;
		}
		
		/**
		 * Подготавливает параметры для отправки на frontend
		 *
		 * @return void
		 */
		public function prepare_to_send() {
			return array(
				'slug'   => $this->get( 'slug' ),
				'offset' => $this->get( 'offset' ),
				'total'  => $this->get( 'total' ),
				'output' => $this->get( 'output' ),
				'level'  => $this->get( 'level' ),
				'custom' => $this->get( 'custom' ),
			);
		}
		
		/**
		 * Повышает уровень вложенности
		 *
		 * @return void
		 */
		public function level_up() {
			$this->level++;
		}
		
		/**
		 * Понижает уровень вложенности
		 *
		 * @return void
		 */
		public function level_down() {
			$this->level--;
			if ( $this->level < 1 ) {
				$this->level = 1;
			}
		}
		
		/**
		 * Устанавливает пользовательские данные
		 *
		 * @param string $property_name
		 * @param string $value
		 * @return void
		 */
		public function set_custom_data( $property_name, $value ) {
			if ( ! $property_name ) {
				return false;
			}
			$this->custom[ $property_name ] = $value;
		}
		
		/**
		 * Устанавливает новый обработчик
		 *
		 * @param string $slug имя класса обработчика
		 * @return void
		 */
		public function change_handler( $slug ) {
			$this->slug = $slug;
		}

	}

