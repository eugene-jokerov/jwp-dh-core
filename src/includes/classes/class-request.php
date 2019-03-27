<?php
	namespace JWP\DH;
	defined( 'ABSPATH' ) || exit;

	/**
	 * JWP\DH\Request
	 * Отвечает за входные данные
	 */
	class Request {
		
		/**
		 * Имя класса обработчика
		 *
		 * @var string
		 */
		protected $slug = '';
		
		/**
		 * Отступ для выборки
		 *
		 * @var int
		 */
		protected $offset = 0;
		
		/**
		 * Общее кол-во элементов в выборке
		 *
		 * @var int
		 */
		protected $total = 0;
		
		/**
		 * Только посчитать общее кол-во элементов в выборке
		 *
		 * @var bool
		 */
		protected $total_only = false;
		
		/**
		 * Уровень вложенности
		 *
		 * @var int
		 */
		protected $level = 1;
		
		/**
		 * Пользовательские данные
		 *
		 * @var array
		 */
		protected $custom = array();
		
		/**
		 * Первая ли это итерация обработки данных
		 * Запрос на просчёт общего кол-ва элементов не считается за итерацию
		 *
		 * @var bool
		 */
		protected $first_request = false;

		/**
		 * Последний запрос
		 *
		 * @var bool
		 */
		protected $last_request = false;
		
		/**
		 * Инициализация
		 *
		 * @return void
		 */
		public function __construct() {
			$this->slug = isset( $_POST['slug'] ) ? str_replace( '\\\\', '\\', $_POST['slug'] ) : ''; // удаляем экранирование слэшей для классов в namespace формате
			$this->offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
			$this->total = isset( $_POST['total'] ) ? intval( $_POST['total'] ) : 0;
			$this->total_only = ( isset( $_POST['total_only'] ) and (bool)$_POST['total_only'] ) ? true : false;
			$this->level = isset( $_POST['level'] ) ? intval( $_POST['level'] ) : 1;
			$this->custom = isset( $_POST['custom'] ) ? (array) $_POST['custom'] : array();
			$this->first_request = ( isset( $_POST['first_request'] ) and (bool)$_POST['first_request'] ) ? true : false;
		}
		
		/**
		 * Возвращает параметр запроса
		 *
		 * @param $property_name
		 * @return string
		 */
		public function get( $property_name ) {
			if ( ! $property_name ) {
				return false;
			}
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
		 * Только посчитать общее кол-во элементов?
		 *
		 * @return bool
		 */
		public function is_total_only() {
			return $this->total_only;
		}
		
		/**
		 * Первая ли это итерация?
		 *
		 * @return bool
		 */
		public function is_first_request() {
			return $this->first_request;
		}

		/**
		 * Последняя ли это итерация?
		 *
		 * @return bool
		 */
		public function is_last_request() {
			return $this->last_request;
		}
		
		/**
		 * Устанавливает пользовательские данные
		 *
		 * @param string $property_name
		 * @return bool|string
		 */
		public function get_custom_data( $property_name ) {
			if ( ! $property_name ) {
				return false;
			}
			if ( isset( $this->custom[ $property_name ] ) ) {
				return $this->custom[ $property_name ];
			}
			return false;
		}
		
	}
