<?php
$offcanvas_class  = 'pp-offcanvas-content';
$offcanvas_class .= ' pp-offcanvas-content-' . $id;
$offcanvas_class .= ' pp-offcanvas-content-' . $settings->direction;
$offcanvas_class .= ' pp-offcanvas-' . $settings->content_transition;
?>
<div class="pp-offcanvas-content-wrap">
	<div class="<?php echo $offcanvas_class; ?>" data-id="<?php echo $id; ?>">
		<?php if ( 'yes' === $settings->close_button ) { ?>
			<div class="pp-offcanvas-header">
				<div class="pp-offcanvas-close" role="button" aria-label="<?php _e( 'Close Off Canvas Content', 'bb-powerpack' ); ?>">
					<span class="<?php echo ( '' != $settings->close_button_icon ) ? $settings->close_button_icon : 'fa fa-times'; ?>"></span>
				</div>
			</div>
		<?php } ?>
		<div class="pp-offcanvas-body">
			<div class="pp-offcanvas-content-inner">
				<?php echo $module->render_content( $settings ); ?>
			</div>
		</div>
	</div>

	<div class="pp-offcanvas-toggle-wrap">
		<?php if ( 'hamburger' === $settings->toggle_source ) { ?>
			<div class="pp-offcanvas-toggle pp-offcanvas-toggle-hamburger<?php echo 'none' !== $settings->toggle_animation ? ' pp-hamburger--' . $settings->toggle_animation : ''; ?> pp-hamburger-<?php echo $settings->toggle_text_align; ?>">
				<span class="pp-hamburger-box">
					<span class="pp-hamburger-inner"></span>
				</span>
				<?php if ( '' !== $settings->burger_label ) { ?>
				<span class="pp-hamburger-label pp-toggle-label">
					<?php echo $settings->burger_label; ?>
				</span>
				<?php } ?>
			</div>
		<?php } elseif ( 'button' === $settings->toggle_source ) { ?>
			<div class="pp-offcanvas-toggle pp-offcanvas-toggle-button pp-offcanvas-icon-<?php echo $settings->toggle_text_align; ?>">
				<?php if ( '' != $settings->button_icon ) { ?>
					<span class="pp-offcanvas-toggle-icon <?php echo $settings->button_icon; ?>" aria-hidden="true"></span>
				<?php } ?>
				<span class="pp-offcanvas-toggle-text pp-toggle-label">
					<?php echo $settings->button_text; ?>
				</span>
			</div>
		<?php } elseif ( pp_is_builder_active() && ( 'class' === $settings->toggle_source || 'id' === $settings->toggle_source ) ) { ?>
			<div class="pp-editor-placeholder">
				<h4 class="pp-editor-placeholder-title"><?php echo __( 'Off-Canvas Content', 'bb-powerpack' ); ?></h4>
				<div class="pp-editor-placeholder-content">
					<?php echo __( 'You have selected to open off-canvas bar using another element. This placeholder will not be shown on the live page.', 'bb-powerpack' ); ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
