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
