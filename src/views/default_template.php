<script>
jQuery(function($){
	$('.jwp-dh-start').jwpdh();
	
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

</script>
<div class="wrap">
	<h1><?php echo esc_attr( $this->get( 'handler_title' ) ); ?></h1>
	<input type="button" value="Начать" class="button button-primary jwp-dh-start" <?php echo $this->data_atts(); ?>>
	
	<div class="jwp-dh-process" style="display:none;">
		<div class="card">
			<h3 class="title">Идёт обработка</h3>
			<p>Обработано: <span class="jwp-dh-offset">0</span> из <span class="jwp-dh-total">?</span></p>
			<span>Логи:</span>
			<div class="jwp-dh-output" style="border:1px solid black; overflow-y: scroll; max-height:300px; padding-left:5px;">
			
			</div>
		</div>
	</div>
</div>
