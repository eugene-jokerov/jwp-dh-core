<?php
	namespace JWP\DH; 
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * JWP\DH\Core - основной класс библиотеки
	 * 
	 * Основной класс системы JWP Data Handler. Реализует паттерн Singleton для удобного вызова из кода темы или других плагинов.
	 */
	final class Core {
		
		/**
		 * Версия библиотеки
		 *
		 * @var string
		 */
		private $version = '1.2.7';
		
		/**
		 * Единственный экземпляр класса
		 *
		 * @var Core
		 */
		private static $_instance = null;
		
		/**
		 * Базовый путь
		 *
		 * @var string
		 */
		protected $path;
		
		/**
		 * Базовый URL
		 *
		 * @var string
		 */
		protected $url;
		
		/**
		 * Объект для хранения пользовательского обработчика
		 *
		 * @var object
		 */	
		protected $handler;
		
		/**
		 * Массив идентификаторов рабочих страниц
		 * Только на этих страницах подключается js скрипт и другие ресурсы
		 *
		 * @var array
		 */
		protected $work_pages = array();
		
		/**
		 * Условно уникальный идентификатор
		 * Используется для уникализации имён в рамках текущего сайта. 
		 * Актуально при использовании нескольких сборок библиотеки на одном сайте.
		 *
		 * @var string
		 */
		protected $hash;
		
		/**
		 * Action для обработки AJAX запросов
		 *
		 * @var string
		 */
		protected $ajax_action;
		
		/**
		 * Ограничивает клонирование объекта
		 *
		 * @return void
		 */
		protected function __clone() {
			
		}
		
		/**
		 * Ограничивает создание другого экземпляра класса через сериализацию
		 *
		 * @return void
		 */
		protected function __wakeup() {
			
		}
		
		/**
		 * Возвращает единственный экземпляр класса.
		 *
		 * @return JWP\DH\Core
		 */
		static public function instance() {
			return self::$_instance;
		}
		
		/**
		 * Начальная инициализация библиотеки
		 *
		 * @param array $settings Базовые настройки библиотеки
		 * @return void
		 */
		static public function init( $settings ) {
			if( is_null( self::$_instance ) ) {
				self::$_instance = new self( $settings );
			}
		}
		
		/**
		 * Возвращает базовый УРЛ.
		 *
		 * @return string
		 */
		public function base_url() {
			return $this->url;
		}
		
		/**
		 * Возвращает базовый путь.
		 *
		 * @return string
		 */
		public function base_path() {
			return $this->path;
		}
		
		/**
		 * Инициализация библиотеки.
		 *
		 * @return void
		 */
		private function __construct( $settings ) {
			$this->url = $settings['url'];
			$this->path = $settings['path'];
			$this->hash = substr( md5( __NAMESPACE__ ), 0, 5 );
			$this->ajax_action = 'jwp_dh_' . $this->hash;
			
			include_once $this->path . '/includes/classes/class-request.php';
			include_once $this->path . '/includes/classes/class-response.php';
			include_once $this->path . '/includes/classes/class-view.php';
			include_once $this->path . '/includes/classes/class-handler-base.php';
			include_once $this->path . '/includes/classes/class-handler.php';
		
			add_action( "wp_ajax_{$this->ajax_action}", array( $this, 'ajax_callback' ) ); 
			add_action( 'admin_init', array( $this, 'admin_register_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}
		
		/**
		 * Регистрация js скриптов для админки
		 *
		 * @return void
		 */
		public function admin_register_scripts() {
			wp_register_script( "jwp-dh-main-{$this->hash}", $this->url . 'assets/js/data-handler.js', array( 'jquery' ), $this->version );
		}
		
		/**
		 * Подключение главного js скрипта
		 * Срабатывает по событию admin_init
		 *
		 * @param string $hook
		 * @return void
		 */
		public function admin_enqueue_scripts( $hook ) {
			if ( ! in_array( $hook, $this->work_pages ) ) {
				return false;
			}
			wp_enqueue_script( "jwp-dh-main-{$this->hash}" );
		}
		
		/**
		 * Возвращает обработчик по его уникальному идентификатору
		 * При первом обращении к обработчику инициализирует его
		 *
		 * @param string $handler_class
		 * @return JWP\DH\Handler
		 */
		protected function get_handler( $handler_class ) {
			if ( ! class_exists( $handler_class ) ) {
				return new \WP_Error( 'class_not_found', 'Не объявлен пользовательский класс: ' . $handler_class );
			}
			if ( ! isset( $this->handler ) ) {
				$this->handler = new $handler_class;
			}
			if ( $this->is_valid_handler( $this->handler ) ) {
				return $this->handler;
			}
			return new \WP_Error( 'class_not_valid', 'Пользовательский класс ' . esc_attr( $handler_class ) . ' не соответствует спецификации ' );
		}
		
		/**
		 * Проверяет объект $handler на соответствие спецификации
		 *
		 * @param JWP\DH\Handler $handler
		 * @return bool
		 */
		protected function is_valid_handler( $handler ) {
			return true; // реализовать функционал проверки handler
		}
		
		/**
		 * Обработчик ajax запросов
		 *
		 * @return void
		 */
		public function ajax_callback() {
			check_ajax_referer( 'jwp-dh-' . $this->hash ); // проверка nonce
			if( ! current_user_can( 'manage_options' ) ) { 
				wp_die(); // у текущего пользователя недостаточно прав
			}
			
			// получаем параметры запроса
			$request      = new Request; // JWP\DH\Request
			$response     = new Response; // JWP\DH\Response
			$handler_slug = $request->get( 'slug' ); // уникальный идентификатор обработчика
			$handler      = $this->get_handler( $handler_slug ); 
			if ( is_wp_error( $handler ) ) {
				wp_die( $handler->get_error_message() ); // запрашиваемый обработчик не найден
			}
			
			$this->process_user_handler( $handler, $request, $response );

			// возвращаем данные в json формате
			wp_send_json( $response->prepare_to_send() );
		}
		
		/**
		 * Обработка данных с использованием пользовательского обработчика
		 *
		 * @param JWP\DH\Handler $handler Объект пользовательского обработчика
		 * @param JWP\DH\Request $request Объект запроса
		 * @param JWP\DH\Response $response Объект ответа
		 * @return JWP\DH\Response $response
		 */
		protected function process_user_handler( $handler, $request, $response ) {
			$offset       = $request->get( 'offset' ); // отступ от первого элемента
			$total        = $request->get( 'total' ); // всего элементов
			$total_only   = $request->is_total_only();
			
			$response->set( 'level', $request->get( 'level' ) );
			$response->set( 'custom', $request->get( 'custom' ) );
			$response->set( 'slug', $request->get( 'slug' ) );

			// вычисляем общее кол-во элементов в выборке
			if ( ! $total ) {
				$total = $handler->total( $request );
			}
			$total = intval( $total );
			$response->set( 'total', $total );
			
			if ( $total_only ) {
				// если нужно получить только общее кол-во
				$response->set( 'offset', 0 );
				return $response;
			}

			$max_process_elements = $handler->get_max_process_elements( $total, $offset );
			$offset = $offset + $max_process_elements;
			if ( $offset >= $total ) {
				// это последний запрос
				$request->set( 'last_request', true );
				$response->set( 'offset', $total );
			} else {
				$response->set( 'offset', $offset );
			}

			$handler->process( $request, $response ); // передаём управление в пользовательский обработчик
			
			$response->set( 'total', $total );
			return $response;
		}
		
		/**
		 * Выводит содержимое страницы обработчика
		 *
		 * @param string $handler_class_name Имя класса обработчика
		 * @param array $params настройки обработчика и страницы
		 * @return void
		 */
		public function render_handler_page( $handler_class_name, $params = array() ) {
			if ( ! $handler_class_name ) {
				return false;
			}
			$handler = $this->get_handler( $handler_class_name );
			if ( ! is_wp_error( $handler ) ) {
				$defaults = array(
					'handler_name'  => $handler_class_name,
					'handler_title' => $handler->get_title(),
					'base_path'     => $this->base_path(),
					'ajax_action'   => $this->ajax_action,
					'hash'          => $this->hash,
				);
				$params = wp_parse_args( $params, $defaults );
				$view = $handler->view( $params );
				$view->render();
			} else {
				echo esc_attr( $handler->get_error_message() );
			}
		}
		
		/**
		 * Регистрирует страницу админки, на которой будет работать handler
		 * Пока используется только для подключения js скрипта
		 *
		 * @param string $pagename Название страницы в админке. Например: toplevel_page_process-attachments
		 * @return bool
		 */
		public function register_work_page( $pagename ) {
			if ( in_array( $pagename, $this->work_pages ) ) {
				return false;
			}
			$this->work_pages[] = $pagename;
			return true;
		}
		
		/**
		 * Массово регистрирует страницы админки, на которых будут работать handlers
		 *
		 * @param array $pages Названия страниц в админке.
		 * @return bool
		 */
		public function register_work_pages( $pages ) {
			if ( ! $pages or ! is_array( $pages ) ) {
				return false;
			}
			foreach ( $pages as $page ) {
				$this->register_work_page( $page );
			}
		}
	}
