(function( $ ){
	var ajax_data;
	var dh_levels = {
		'current' : 1,
		'requests': 0,
	};
	var dh_ajax_request = function(data, button){
		if ( button.data( 'dhstate' ) != 'running' ) {
			// если процесс не запущен, то запросы не выполняем
			return;
		}
		
		dh_levels.requests++;
		$(document).trigger('jwpdh.request', { 'self' : button });
		$.post(ajaxurl, data, function(response) {
			if ( button.data( 'dhstate' ) == 'stopping' ) {
				// если текущее состояние "останавливается", то переводим в "остановлен"
				button.data( 'dhstate', 'stopped' );
			}
			var old_level = dh_levels.current;
			var slug_changed = false;
			dh_levels[ old_level ] = {
				total: response.total,
				offset: response.offset,
				slug: data.slug
			}
			dh_levels.current = response.level;
			if ( old_level < dh_levels.current ) {
				// если level повысили
				data.total = 0;
				data.offset = 0;
				// если поменяли handler
				if ( response.slug != data.slug ) {
					// при смене handler идёт двойное повышение уровня
					slug_changed = true;
					var levelup2 = old_level + 1;
					dh_levels[ levelup2 ] = {
						total: 0,
						offset: 0,
						slug: data.slug
					}
				}
				data.slug = response.slug;
			} else {
				// если level понизили или не изменяли
				var prev_level = dh_levels.current + 1;
				if ( dh_levels.hasOwnProperty( prev_level ) ) {
					delete dh_levels[ prev_level ];
				}
				data.total = dh_levels[ dh_levels.current ].total;
				data.offset = dh_levels[ dh_levels.current ].offset;
				data.slug = dh_levels[ dh_levels.current ].slug;
			}
			
			data.level = response.level;
			data.custom = response.custom;
			
			button.data( 'dh-total', data.total );
			button.data( 'dh-offset', data.offset );

			$(document).trigger('jwpdh.responce', {
				 'responce' : response, 
				 'self' : button,
				 'slug' : data.slug,
				 'is_first': data.first_request,
				 'slug_changed' : slug_changed,
			});
			if ( data.first_request ) {
				// первый запрос бывает только один раз
				data.first_request = 0;
			}
			
			if(data.offset < data.total) {
				dh_ajax_request(data, button);
			} else {
				if ( data.level > 1 ) {
					//data.level = data.level - 1;
					dh_ajax_request(data, button);
				} else {
					$(document).trigger('jwpdh.finish', { 'self' : button });
				}
			}
		});
	}

	var methods = {
		init : function( options ) {
			// если плагин инициализирован, то сразу возвращаем объект
			if ( typeof(ajax_data) !== 'undefined' ) {
				return this;
			}
			// инициализация
			return this.each(function(){
				$(this).on('click', function(){
					if ( ! $(this).data( 'dhstate' ) ) {
						// первый запуск
						$(this).data( 'dhstate', 'running' );
						$(document).trigger('jwpdh.start', { 'self' : $(this) });
					} else {
						if ( $(this).data( 'dhstate' ) == 'running' ) {
							// если процесс запущен - останавливаем
							$(this).data( 'dhstate', 'stopping' );
							$(document).trigger('jwpdh.stop', { 'self' : $(this) });
							return false;
						} else if ( $(this).data( 'dhstate' ) == 'stopping' ) {
							// если процесс останавливается - дожидаемся полной остановки и запускаем на продолжение
							$(this).data( 'dhstate', 'running' );
							$(document).trigger('jwpdh.continue', { 'self' : $(this) });
							return false; // завершаем обработку клика. Продолжение обработки начнётся после получения ответа от сервера
						} else {
							// если процесс полностью остановлен - запускаем на продолжение
							$(this).data( 'dhstate', 'running' );
							$(document).trigger('jwpdh.continue', { 'self' : $(this) });
						}
					}
					if ( typeof(ajax_data) === 'undefined' ) {
						ajax_data = {
							'action'        : $(this).data( 'dh-action' ),
							'offset'        : $(this).data( 'dh-offset' ),
							'total'         : $(this).data( 'dh-total' ),
							'slug'          : $(this).data( 'dh-handler' ),
							'_wpnonce'      : $(this).data( 'dh-wpnonce' ),
							'level'         : $(this).data( 'dh-level' ),
							'custom'        : $(this).data( 'dh-custom' ),
							'first_request' : 1,
							'total_only'    : 1,
						};
					}
					$(document).trigger('jwpdh.before_first_send', { 'args' : ajax_data, 'self' : $(this) });
					var self = $(this);
					if ( ajax_data.total_only && ! ajax_data.total ) {
						// если первый запрос и не указан total
						$.post(ajaxurl, ajax_data, function(res) {
							ajax_data.total_only = 0;
							if ( res.total ) {
								ajax_data.total = res.total;
								$(document).trigger('jwpdh.first_calculate_total', { 'responce' : res, 'self' : self });
							}
							dh_ajax_request(ajax_data, self);
						});
					} else {
						ajax_data.total_only = 0;
						dh_ajax_request(ajax_data, self);
					}
					return false;
				});
			});
		},
	};
	 
	$.fn.jwpdh = function( method ) {
		
		// логика вызова метода
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' in jQuery.jwpdh not exists' );
		}   

	};
})( jQuery );
