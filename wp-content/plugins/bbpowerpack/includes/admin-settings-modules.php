<?php
/**
 * PowerPack admin settings modules tab.
 *
 * @since 1.0.0
 * @package bb-powerpack
 */
?>

<?php
	$enabled_modules = BB_PowerPack_Modules::get_enabled_modules();
	$enabled_categories = BB_PowerPack_Modules::get_enabled_categories();
	$current_filter = isset( $_GET['show'] ) ? $_GET['show'] : '';
	$used_modules = array();
	if ( ! empty( $current_filter ) ) {
		$used_modules = BB_PowerPack_Modules::get_used_modules();
	}
?>
<?php if ( ! is_network_admin() && is_multisite() ) : ?>

<div class="notice notice-info">
	<p><?php esc_html_e( 'You can manage the modules for your site from this page. By activating / deactivating any module will override the network settings.', 'bb-powerpack' ); ?></p>
</div>

<?php endif; ?>
<div class="pp-modules-manager">
	<div class="pp-modules-manager-search">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'bb-powerpack' ); ?>" style="min-width: 100px;" />
		<div class="pp-modules-manager-filters">
			<select class="pp-modules-manager-filter">
				<option value=""><?php esc_html_e( 'Filter: All Modules', 'bb-powerpack' ); ?></option>
				<option value="used"<?php echo 'used' == $current_filter ? ' selected' : ''; ?>><?php esc_html_e( 'Filter: Used Modules', 'bb-powerpack' ); ?></option>
				<option value="notused"<?php echo 'notused' == $current_filter ? ' selected' : ''; ?>><?php esc_html_e( 'Filter: Not Used Modules', 'bb-powerpack' ); ?></option>
			</select>
			<input type="search" placeholder="<?php esc_html_e( 'Search', 'bb-powerpack' ); ?>" />
		</div>
	</div>
	<div class="pp-modules-manager-sections">
		<?php foreach ( BB_PowerPack_Modules::get_categorized_modules() as $cat => $data ) {
			$is_cat_enabled = in_array( $cat, $enabled_categories );
		?>
		<div class="pp-modules-manager-section">
			<div class="pp-modules-manager-section-header">
				<h3>
					<label for="bb_powerpack_<?php echo $cat; ?>"><?php echo $data['category']; ?></label>
					<label class="pp-admin-field-toggle">
						<input id="bb_powerpack_<?php echo $cat; ?>" name="bb_powerpack_module_categories[]" type="checkbox" value="<?php echo $cat; ?>"<?php echo $is_cat_enabled ? ' checked="checked"' : '' ?> />
						<span class="pp-admin-field-toggle-slider"></span>
					</label>
				</h3>
			</div>
			<div class="pp-modules-manager-section-content">
				<table class="form-table pp-flex-table pp-modules" data-category="<?php echo $cat; ?>">
					<?php foreach ( $data['modules'] as $module ) {
						$is_enabled = in_array( $module['slug'], $enabled_modules ) && $module['enabled'];
						if ( 'used' === $current_filter && ! isset( $used_modules[ $module['slug'] ] ) ) {
							continue;
						}
						if ( 'notused' === $current_filter && isset( $used_modules[ $module['slug'] ] ) ) {
							continue;
						}
						$used_on = isset( $used_modules[ $module['slug'] ] ) ? $used_modules[ $module['slug'] ] : false;
						$used_on_text = array();
						if ( $used_on ) {
							foreach ( $used_on as $type => $used ) {
								$type  = str_replace( 'fl-theme-layout', 'Themer Layout', $type );
								$type  = str_replace( 'fl-builder-template', 'Builder Template', $type );
								$used_on_text[] = sprintf( '%s times on %s %ss', $used['total'], count( $used ) - 1, ucfirst( $type ) );
							}
							$used_on_text = implode( ', ', $used_on_text );
						}
						?>
						<tr valign="top" class="<?php echo ! $is_enabled ? 'pp-module-inactive' : ''; ?><?php echo $used_on ? ' pp-module-used' : ''; ?>">
							<th scope="row" valign="top">
								<label for="bb_powerpack_modules_<?php echo $module['slug']; ?>"><?php echo $module['name']; ?></label>
								<?php if ( ! empty( $used_on_text ) ) { ?>
								<span class="pp-module-used-description"><?php echo $used_on_text; ?></span>
								<?php } ?>
							</th>
							<td>
								<label class="pp-admin-field-toggle">
									<input
										id="bb_powerpack_modules_<?php echo $module['slug']; ?>" 
										name="bb_powerpack_modules[]" 
										type="checkbox" 
										value="<?php echo $module['slug']; ?>"
										<?php echo $is_enabled ? ' checked="checked"' : '' ?> 
									/>
									<span class="pp-admin-field-toggle-slider"></span>
								</label>
							</td>
						</tr>
					<?php } ?>
				</table>
			</div>
		</div>
		<?php } // End foreach(). ?>
	</div>
</div>

<?php wp_nonce_field( 'pp-modules', 'pp-modules-nonce' ); ?>
<?php submit_button(); ?>

<script>
	(function($) {
		// Toggle inactive class.
		$('input[name="bb_powerpack_modules[]"').on('change', function() {
			if ( $(this).is(':checked') ) {
				$(this).parents('tr').removeClass('pp-module-inactive');
			} else {
				$(this).parents('tr').addClass('pp-module-inactive');
			}
		});

		// Toggle all modules in a category.
		$('input[name="bb_powerpack_module_categories[]"').on('change', function() {
			var active = $(this).is(':checked');
			var category = $(this).val();
			var $table = $('.pp-modules-manager').find('table[data-category="' + category + '"]');
			$table.find('input[name="bb_powerpack_modules[]"]').each(function() {
				if ( active ) {
					$(this).prop('checked', true);
				} else {
					$(this).prop('checked', false);
				}
				$(this).trigger('change');
			});
		});

		// Toggle content.
		$('.pp-modules-manager-section-header').on('click', function(e) {
			var selector = $(e.target),
				tagName = selector.prop('tagName').toLowerCase();
			if ( selector.is( $(this) ) || 'h3' === tagName ) {
				$(this).next().slideToggle();
			}
		});

		// Search.
		$('.pp-modules-manager-search input[type="search"]').on('keyup', function(e) {
			e.stopPropagation();
			var value = $(this).val();

			setTimeout(function() {
				if ( value.length < 2 ) {
					$('.pp-modules tr').show();
					return;
				}
				if ( value.length === 0 ) {
					$('.pp-modules tr').show();
					return;
				}
				$('.pp-modules tr').hide();
				$('.pp-modules tr').each(function() {
					var label = $(this).find('label').text().toLowerCase();
					if ( label.search( value ) !== -1 ) {
						$(this).show();
					}
				});
			}, 500);
		});

		// Filter.
		$('.pp-modules-manager-filter').on('change', function() {
			var currentUrl = location.href;
			currentUrl = currentUrl.replace( /&show=.*/g, '' );
			if ( $(this).val() !== '' ) {
				currentUrl = currentUrl + '&show=' + $(this).val();
			}
			location.href = currentUrl;
		});
	})(jQuery);
</script>