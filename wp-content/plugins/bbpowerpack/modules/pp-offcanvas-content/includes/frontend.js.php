var pp_offcanvas_<?php echo $id; ?> = '';
;(function($){

	$(document).ready(function() {
		pp_offcanvas_<?php echo $id; ?> = new PPOffcanvasContent({
			id:                '<?php echo $id; ?>',
			direction:         '<?php echo $settings->direction; ?>',
			contentTransition: '<?php echo $settings->content_transition; ?>',
			closeButton:       '<?php echo $settings->close_button; ?>',
			escClose:          '<?php echo $settings->esc_close; ?>',
			closeButton:       '<?php echo $settings->close_button; ?>',
			bodyClickClose:    '<?php echo $settings->body_click_close; ?>',
			toggleSource:      '<?php echo $settings->toggle_source; ?>',
			toggle_class:      '<?php echo $settings->toggle_class; ?>',
			toggle_id:         '<?php echo $settings->toggle_id; ?>',

			isBuilderActive:    <?php echo FLBuilderModel::is_builder_active() ? 'true' : 'false'; ?>,
		});
	});

})(jQuery);
