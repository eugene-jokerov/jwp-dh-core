jQuery(function($){
	var jwp_dh = $('.jwp-dh-start');
	var ajax_data;
	var dh_levels = {
		'current' : 1,
		'requests': 0,
	};
	jwp_dh.on('click', function(){
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
	
	function dh_ajax_request(data, button){
		if ( button.data( 'dhstate' ) != 'running' ) {
			// если процесс не запущен, то запросы не выполняем
			return;
		}
		
		dh_levels.requests++;
		$(document).trigger('jwpdh.request', { 'self' : button });
		$.post(ajaxurl, data, function(response) {
			if ( data.first_request ) {
				// первый запрос бывает только один раз
				data.first_request = false;
			}
			$(document).trigger('jwpdh.responce', { 'responce' : response, 'self' : button });
			if ( button.data( 'dhstate' ) == 'stopping' ) {
				// если текущее состояние "останавливается", то переводим в "остановлен"
				button.data( 'dhstate', 'stopped' );
			}
			var old_level = dh_levels.current;
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
				// если поменли handler
				if ( response.slug != data.slug ) {
					// при смене handler идёт двойное повышение уровня
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
	
});

jQuery(function($){
	
	$(document).on('jwpdh.start', function(e, event_info) { 
		event_info.self.val( 'Остановить' );
	});
	
	$(document).on('jwpdh.stop', function(e, event_info) { 
		event_info.self.val( 'Возобновить' );
	});
	
	$(document).on('jwpdh.continue', function(e, event_info) { 
		event_info.self.val( 'Остановить' );
	});
	
	$(document).on('jwpdh.before_first_send', function(e, event_info) { 
		if ( $('.jwp-dh-process').length ) {
			$('.jwp-dh-process').show();
		}
		if ( $('.jwp-dh-total').length ) {
			$('.jwp-dh-total').text(event_info.args.total);
		}
		if ( $('.jwp-dh-offset').length ) {
			$('.jwp-dh-offset').text(event_info.args.offset);
		}
	});
	
	$(document).on('jwpdh.responce', function(e, event_info) { 
		var response = event_info.responce;
		if ( $('.jwp-dh-output').length ) {
			if ( response.output instanceof Array ) {
				$.each( response.output, function( index, value ) {
					$('.jwp-dh-output').prepend( '<p>' + value + '</p>' );
				}); 
			} else {
				$('.jwp-dh-output').prepend( '<p>' + response.output +'</p>' );
			}
		}
		if ( $('.jwp-dh-total').length ) {
			$('.jwp-dh-total').text(response.total);
		}
		if ( $('.jwp-dh-offset').length ) {
			$('.jwp-dh-offset').text(response.offset);
		}
	});
	
	$(document).on('jwpdh.first_calculate_total', function(e, event_info) { 
		var response = event_info.responce;
		if ( $('.jwp-dh-total').length ) {
			$('.jwp-dh-total').text(response.total);
		}
	});
	
	$(document).on('jwpdh.finish', function(e, event_info) { 
		event_info.self.hide();
		alert("Обработка завершена");
	});
	
	
});
