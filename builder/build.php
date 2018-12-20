<?php

// после запуска скрипта, в этой папке появится папка src с собранной версией библиотеки
$work_namespace = 'JWP\DH\Test'; // тут указываем свой namespace и больше ничего редактировать не надо

$builder = new JWP_DH_Builder( $work_namespace );
$builder->build();

class JWP_DH_Builder {
	
	private $namespace_dev = 'JWP\DH';
	
	private $namespace_work = 'JWP\DH\Test';
	
	public function __construct( $namespace_work = '' ) {
		$this->namespace_dev = $this->namespace_dev;
		if ( $namespace_work ) {
			$this->namespace_work = $namespace_work;
		}
	}
	
	public function build() {
		$source = './../src/';
		$target = './src/';
		if ( ! is_writable( './' ) ) {
			echo 'current dir not writable!';
			die();
		}
		
		if ( ! is_dir( $source ) ) {
			echo 'source not found';
			die();
		}
		// удалить старую папку
		if ( is_dir( $target ) ) {
			$this->remove( $target );
		}
		$this->copy( $source, $target );
		echo 'Complete' .PHP_EOL;
	}
	
	private function copy( $source, $target ) {
		if ( is_dir( $source ) ) {
			@mkdir( $target );
			$d = dir( $source );
			while ( FALSE !== ( $entry = $d->read() ) ) {
				if ( $entry == '.' || $entry == '..' ) {
					continue;
				}
				$this->copy( "{$source}/{$entry}", "{$target}/{$entry}" );
			}
			$d->close();
		} else {
			$content = file_get_contents( $source );
			// не текстовые файлы просто копировать
			$content = str_replace( $this->namespace_dev, $this->namespace_work, $content );
			file_put_contents( $target, $content );
			//copy($source, $target);
		}
	}

	private function remove( $path ) {
		if ( is_file( $path ) ) {
			return unlink( $path );
		}
		if ( is_dir( $path ) ) {
			foreach( scandir( $path ) as $p ) {
				if ( $p != '.' and $p != '..' ) {
					$this->remove( $path . DIRECTORY_SEPARATOR . $p );
				}
			}
			return rmdir( $path ); 
		}
		return false;
	}
	
}
