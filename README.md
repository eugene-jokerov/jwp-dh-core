# JWP DH Core
Библиотека JWP DH Core предназначена для последовательной обработки данных в CMS WordPress. С помощью неё можно создавать, модифицировать и удалять произвольное кол-во записей, рубрик, опций, строк в базе данных и других сущностей.

Использование AJAX даёт возможность обрабатывать выборку данных по частям. Это избавит от ошибок, связанных с нехваткой времени выполнения и памяти для PHP скрипта.

Механизм сборки позволяет создать несколько независимых экземпляров библиотеки. Может быть полезно для встраивания в плагины и темы.

## Возможности
* Обработка определённого кол-ва сущностей(записей, рубрик, опций и тд)
* Обработка изменяемого кол-ва сущностей
* Рекурсивная обработка
* Обработка нескольких типов сущностей по цепочке
* Циклическое выполнений действий
* Обработка большого кол-ва данных
* Кастомизация внешнего вида и js
* Работает только в админке
* Поддерживается PHP 5.3 и выше. Wordpress 4.2+

## Установка
### Проблема совместного использования ###
Представьте ситуацию, когда в разные плагины встраивается одна библиотека. 
Второе подключение вызовет фатальнаую ошибкау PHP т.к. нельзя объявить два класса с одним именем. 
Это можно решить проверкой на наличие класса, но тогда оба плагина будут использовать один и тот-же код библиотеки. 
А вдруг им нужны разные версии или код модифицирован? Проблему совместного использования полностью решает сборка.

Сборка позволяет получить независимый экземпляр библиотеки за счёт уникализации имён классов. Это нужно для безопасного встравиания в плагины или тему. 

### Сборка ###
Для выполнения сборки нужно перейти в директорию `builder`, найти файл `build.php` и следовать инструкциям внутри. 
После запуска `build.php` в этой-же директории появится новая директория `src` с уникализированным исходным кодом библиотеки.

### Использование библиотеки без сборки ###
В директории `src` находится оригинальный исходный код библиотеки. В тестовых целях его можно использовать без сборки.

### Подключение ###
Скопировать содержимое директории `src` к себе в проект и подключить в нужном месте файл `jwp-dh.php`
```php
include_once '/path/to/jwp-dh.php';
```
Потом можно создавать классы обработчики и выводить их на странице в админке. 
Класс обработчик обязательно должен быть наследован от абстрактного класса `JWP\DH\Handler`. 
При сборке, namespace `JWP\DH\`, следует заменить на свой вариант. Например: `JWP\DH\Test\Handler`.
В классе необходимо реализовать два метода: `public function process( $request, $response )` и `public function total( $request )`

## Примеры использования
### Создание пользовательского обработчика
```php
class JWP_DH_Example_Process_Posts extends JWP\DH\Handler {
	
	public $title = 'Обработка записей';
	
	public $max_process_elements = 10;
	
	public function process( $request, $response ) {
		$posts = get_posts( array(
            'post_type'   => 'post',
            'numberposts' => $this->max_process_elements,
            'offset'      => $request->get( 'offset' ),
        ) );
        if ( $posts ) {
			$post_ids = array();
			foreach ( $posts as $post ) {
				$post_ids[] = $post->ID;
				// тут можно
			}
			$response->output( 'Обработаны записи: ' . join( ',', $post_ids ) );
		}
		return $response;
	}
	
	public function total( $request ) {
		$posts_args = array( 
			'post_type'      => 'post',
			'posts_per_page' => 1,
		);
		$total = new WP_Query( $posts_args );
		return $total->found_posts;
	}
}
```

### Создание страницы обработчика в админке
```php
add_action( 'admin_menu', function() {
	$page = add_menu_page(
        'DH Test page',
        'DH Test page',
        'manage_options',
        'jwp-dh-test',
        'jwp_dh_render_test'
    );
    $dh = JWP\DH\Core::instance();
    $dh->register_work_page( $page ); // регистрируем страницу, на которой будет подключен js
} );

function jwp_dh_render_test() {
    $dh = JWP\DH\Core::instance();
    $dh->render_handler_page( 'JWP_DH_Example_Process_Posts' ); // выводим содержимое страници обработчика
}
```

### Примеры пользовательских обработчиков

* [Создание N записей](https://github.com/eugene-jokerov/jwp-dh-core/blob/master/examples/class-create-posts.php)
* [Удаление записей по произвольному полю](https://github.com/eugene-jokerov/jwp-dh-core/blob/master/examples/class-delete-posts.php)
* [Обработка строк в таблице базы данных](https://github.com/eugene-jokerov/jwp-dh-core/blob/master/examples/class-process-dbtable.php)
* [Обработка записей](https://github.com/eugene-jokerov/jwp-dh-core/blob/master/examples/class-process-posts.php)
* [Обработка изменяемого кол-ва записей](https://github.com/eugene-jokerov/jwp-dh-core/blob/master/examples/class-process-posts-meta.php)
* [Рекурсивная обработка рубрик](https://github.com/eugene-jokerov/jwp-dh-core/blob/master/examples/class-recursive-rubrics.php)
* [Рекурсивная обработка с передачей управления в другой обработчик](https://github.com/eugene-jokerov/jwp-dh-core/blob/master/examples/class-multi.php)

