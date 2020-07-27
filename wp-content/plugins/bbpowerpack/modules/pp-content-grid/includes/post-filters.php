<?php
$all_label			= empty( $settings->all_filter_label ) ? __('All', 'bb-powerpack') : $settings->all_filter_label;
$post_type_slug 	= $settings->post_type;
$post_filter_tax 	= ! empty( $settings->post_grid_filters ) && 'none' !== $settings->post_grid_filters ? $settings->post_grid_filters : '';
$default_filter		= isset( $settings->post_grid_filters_default ) ? $settings->post_grid_filters_default : '';
$terms_to_show		= isset( $settings->post_grid_filters_terms ) ? $settings->post_grid_filters_terms : '';

if ( empty( $post_filter_tax ) ) {
	return;
}

$post_filter_field 	= 'tax_' . $post_type_slug . '_' . $post_filter_tax;
$post_filter_terms	= array();
$taxonomy 			= get_taxonomy( $post_filter_tax );

if ( isset( $settings->{$post_filter_field} ) ) :

	$post_filter_value	= $settings->{$post_filter_field};
	$post_filter_matching = $settings->{$post_filter_field . '_matching'};

	if ( $post_filter_value ) {
		$post_filter_term_ids = explode( ",", $post_filter_value );
		if ( ! $post_filter_matching ) {
			$post_filter_terms = get_terms( $post_filter_tax, array( 'exclude' => $post_filter_term_ids ) );
		} else {
			foreach ( $post_filter_term_ids as $post_filter_term_id ) {
				$post_filter_terms[] = get_term_by('id', $post_filter_term_id, $post_filter_tax);
			}
		}
	}

endif;

$terms = ( count( $post_filter_terms ) > 0 ) ? $post_filter_terms : get_terms( $post_filter_tax );
$count = is_array( $terms ) ? count( $terms ) : 0;
?>
<div class="pp-post-filters-wrapper">
	<div class="pp-post-filters-toggle">
		<span class="toggle-text"><?php echo $all_label; ?></span>
	</div>
	<ul class="pp-post-filters">
		<?php
			if ( empty( $default_filter ) ) {
				echo apply_filters( 'pp_cg_filters_all', '<li class="pp-post-filter pp-filter-active" data-filter="*" tabindex="0" aria-label="'. strip_tags( $all_label ) .'">' . $all_label . '</li>', $settings );
			} else {
				echo apply_filters( 'pp_cg_filters_all', '<li class="pp-post-filter" data-filter="*" tabindex="0" aria-label="'. strip_tags( $all_label ) .'">' . $all_label . '</li>', $settings );
			}
			if ( $count > 0 ) {
				$terms = apply_filters( 'pp_cg_filter_terms', $terms, $settings );
				$filter_terms = array();
				foreach ( $terms as $term ) {
					if ( ! empty( $terms_to_show ) ) {
						if ( 'parent' === $terms_to_show ) {
							if ( $term->parent ) {
								$filter_terms[] = $term->parent;
								continue;
							} else {
								$filter_terms[] = $term->term_id;
							}
						} elseif ( 'children' === $terms_to_show ) {
							if ( ! $term->parent ) {
								$filter_terms = get_term_children( $term->term_id, $term->taxonomy );
								continue;	
							} else {
								$filter_terms[] = $term->term_id;
							}
						}
					} else {
						$filter_terms[] = $term->term_id;
					}
				}
				if ( ! is_wp_error( $filter_terms ) && count( $filter_terms ) ) {
					$filter_terms = array_unique( $filter_terms );
					foreach ( $filter_terms as $term_id ) {
						$term = get_term( $term_id );
						$slug = $term->slug;
						$filter_active_class = '';
						if ( $slug === $default_filter ) {
							$filter_active_class = ' pp-filter-active';
						}
						if ( $post_filter_tax == 'post_tag' ) {
							echo '<li class="pp-post-filter' . $filter_active_class . '" data-filter=".tag-'.$slug.'" data-term="'.$slug.'" tabindex="0" aria-label="'. strip_tags( $term->name ) .'">'.$term->name.'</li>';
						} else {
							echo '<li class="pp-post-filter' . $filter_active_class . '" data-filter=".'.$taxonomy->name.'-'.$slug.'" data-term="'.$slug.'" tabindex="0" aria-label="'. strip_tags( $term->name ) .'">'.$term->name.'</li>';
						}
					}
				}
			}
		?>
	</ul>
</div>

<?php do_action( 'pp_cg_after_post_filters', $settings ); ?>