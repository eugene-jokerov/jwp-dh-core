<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	if ( ! class_exists( 'JWP\DH\Core' ) ) {
		$jwp_dh_settings = array(
			'path' => plugin_dir_path( __FILE__ ),
			'url'  => home_url( str_replace( ABSPATH, '', __DIR__ ) . '/' ), // универсальный способ получения url
		);
		$jwp_dh_core_class_path = $jwp_dh_settings['path'] . '/includes/classes/class-core.php';
		if ( file_exists( $jwp_dh_core_class_path ) ) {
			include_once $jwp_dh_core_class_path; // подключаем основной класс
			JWP\DH\Core::init( $jwp_dh_settings );
		}
	}
