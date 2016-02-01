<li class="rp4wp-col<?php echo RP4WP_Manager_Frontend::get_column_class( $row_counter ); ?>">
	<?php

	// load configuration
	$component_manager = new RP4WP_Manager_Component();
	$components        = $component_manager->get_components();

	// check
	if ( null !== $components ) {

		// Load the content template
		$manager_template = new RP4WP_Manager_Template();

		foreach ( $components as $component_key => $component ) {

			if ( 'wrapper' == $component->type ) {
				echo '<div class="rp4wp_component_wrapper rp4wp_component_wrapper_' . $component->pos . ' rp4wp_component_' . $component->id . '">';
				foreach ( $component->components as $inner_component ) {
					$component_manager->load_component_template( $inner_component, $related_post, $excerpt_length, $parent );
				}
				echo '</div>';
			} else {
				$component_manager->load_component_template( $component, $related_post, $excerpt_length, $parent );
			}

		}

	}
	?>
</li>