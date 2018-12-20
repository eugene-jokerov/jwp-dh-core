<?php
	namespace JWP\DH;
	defined( 'ABSPATH' ) || exit;

	/**
	 * JWP\DH\View - отвечает за генерацию и вывод стрнаниц админки
	 * 
	 * Генерирует отдельные элементы и собирает их в страницу
	 */
	class View {
		
		/**
		 * Параметры
		 *
		 * @var array
		 */
		protected $params = array();
		
		/**
		 * Инициализация view
		 *
		 * @return void
		 */
		public function __construct( $params = array() ) {
			$this->params = array(
				'offset'  => 0,
				'total'   => 0,
				'level'   => 1,
				'custom'  => array(),
			);
			$this->setup( $params );
		}
		
		/**
		 * Установка начальных параметров
		 *
		 * @param array $params начальные параметры
		 * @return void
		 */
		public function setup( $params = array() ) {
			$this->params = wp_parse_args( $params, $this->params );
		}
		
		/**
		 * Возвращает параметр
		 *
		 * @param string $param_name имя параметра
		 * @return string
		 */
		public function get( $param_name ) {
			if ( $param_name and isset( $this->params[ $param_name ] ) ) {
				return $this->params[ $param_name ];
			}
			return '';
		}
		
		/**
		 * Подключает и выводит файл, отвечающий за содержимое страницы обработчика
		 *
		 * @return void
		 */
		public function render() {
			$admin_page_template = $this->get_admin_page_template();
			if ( is_file( $admin_page_template ) ) {
				include_once( $admin_page_template );
			}
		}
		
		/**
		 * Возвращает путь к файлу, отвечающему за содержимое страницы
		 *
		 * @return void
		 */
		public function get_admin_page_template() {
			// путь не ниже wp_content
			if ( isset( $this->params['template_file'] ) and $this->params['template_file'] ) {
				return $this->params['template_file'];
			}
			return $this->params['base_path'] . '/views/default_template.php';
		}
		
		/**
		 * Генерирует защитное поле nonce
		 *
		 * @return string wpnonce
		 */
		public function nonce() {
			return wp_create_nonce( 'jwp-dh-' . $this->params['hash'] );
		}
		
		/**
		 * Выводит data параметры для html элемента
		 * Используются в js для работы системы
		 *
		 * @return string
		 */
		public function data_atts() {
			$atts = array(
				'action'  => $this->params['ajax_action'],
				'handler' => $this->params['handler_name'],
				'wpnonce' => $this->nonce(),
				'offset'  => $this->params['offset'],
				'total'   => $this->params['total'],
				'level'   => $this->params['level'],
				'custom'  => json_encode( $this->params['custom'] ),
			);
			$data_atts = '';
			foreach ( $atts as $key => $value ) {
				$data_atts .= 'data-dh-' . $key . '="' . $value . '" ';
			}
			return $data_atts;
		}
	}
