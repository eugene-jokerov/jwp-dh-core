<?php
	namespace JWP\DH;
	defined( 'ABSPATH' ) || exit;

	/**
	 * JWP\DH\Handler - базовый класс пользовательских обработчиков
	 * 
	 * Базовый класс для пользовательских обработчиков. Необходима реализация абстрактных методов
	 */
	abstract class Handler extends Handler_Base {
		/**
		 * В этом методе необходимо реализовать механизм обработки данных
		 *
		 * @param JWP\DH\Request $request
		 * @param JWP\DH\Response $response
		 * @return JWP\DH\Response
		 */
		abstract public function process( $request, $response );
		
		/**
		 * Вычисляет и возвращает максимальное кол-во элементов в выборке. Необходима реализация.
		 *
		 * @param JWP\DH\Request $request
		 * @return int
		 */
		abstract public function total( $request );
	}

