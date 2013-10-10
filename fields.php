<?php

if( !class_exists( 'Tabbed_Meta_Field' ) ) :

/**
 *
 */
class Tabbed_Meta_Field {
	public static function validate( $post_id, $name, $value, $post ) {
		return sanitize_text_field( $value ); 
	}
}

/**
 *
 */
class Tabbed_Meta_Text_Field extends Tabbed_Meta_Field {

	/**
	 *
	 */
	public static function render( $args ) {

		// this input can have a placeholder value
 		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';

 		// class is customizable
 		$class = isset( $args['class'] ) ? $args['class'] : 'widefat';

 		return sprintf(
 			'<input type="text" name="%s" value="%s" placeholder="%s" class="%s">',
 			esc_attr( $args['name'] ),
 			esc_attr( $args['value'] ),
 			esc_attr( $placeholder ),
 			esc_attr( $class )
 		);
	}
}

/**
 *
 */
class Tabbed_Meta_Date_Field extends Tabbed_Meta_Text_Field {

	public static function render( $args ) {

		$value = !empty( $value ) ? $value : time();

 		return sprintf(
 			'<input type="text" name="%s" value="%s">',
 			esc_attr( $args['name'] ),
 			esc_attr( date( 'm/d/Y', $value ) )
 		);
	}

	public static function validate( $post_id, $name, $value, $post = null ) {
		return strtotime( $value );
	}
}

/**
 *
 */
class Tabbed_Meta_Checkbox_Field extends Tabbed_Meta_Field {

	public static function render( $args ) {

		return sprintf(
 			'<input type="checkbox" name="%s" value="1" %s>',
 			esc_attr( $args['name'] ),
 			checked( 1, $args['value'], 0 )
 		);
	}

	public static function validate( $post_id, $name, $value, $post = null ) {
		return 1;
	}
}

/**
 *
 */
class Tabbed_Meta_Link_Field extends Tabbed_Meta_Field {

	/**
	 *
	 */
	public static function render( $args ) {

		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';

 		return sprintf(
 			'<input type="text" name="%s" value="%s" placeholder="%s" class="widefat">',
 			esc_attr( $args['name'] ),
 			esc_url( $args['value'] ),
 			esc_attr( $placeholder )
 		);
	}

	/**
	 *
	 */
	public static function validate( $post_id, $name, $value, $post = null ) {
		return esc_url( $value );
	}
}

/**
 *
 */
class Tabbed_Meta_Select_Field extends Tabbed_Meta_Field {

	/**
	 *
	 */
	public static function render( $args ) {

		$html = '';

		if( isset( $args['options'] ) && is_array( $args['options'] ) ) {

			foreach( $args['options'] as $key => $value ) {
				$html .= sprintf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $key ),
					selected( $key, $args['value'], 0 ),
					esc_html( $value ) 
				);
			}
		}

		return sprintf(
			'<select name="%s">%s</select>',
			esc_attr( $args['name'] ),
			$html
		);
	}
}

/**
 *
 */
class Tabbed_Meta_Post_Picker_Field extends Tabbed_Meta_Field {

	/**
	 * Saves posts as comma separted ids
	 */
	public static function render( $args ) {

		global $wp_post_types;

		// setup some defaults
		$args = wp_parse_args( $args, array(
			'post_type'      => 'post',
			'limit'          => 999,
			'enable_recent' => true
		));

		$singular_name = $wp_post_types[$args['post_type']]->labels->singular_name;
		$plural_name = $wp_post_types[$args['post_type']]->labels->name;

		// set the post type as a data attribute
		$html = '<div class="post-picker" data-post-type="' . esc_attr( $args['post_type'] )  . '" data-limit="' . intval( $args['limit'] ) . '">';

		if( isset( $args['value'] ) ) {

			$ids = array_map( 'intval', explode( ",", $args['value'] ) );

			$posts = get_posts( array(
				'post__in' => $ids,
				'posts_per_page' => count( $ids ),
				'post_type' => $args['post_type'],
				'orderby' => 'post__in',
				'post_status' => 'any'
			));
		}

		$html .= sprintf(
			'<input type="hidden" name="%s" value="%s" class="picker-ids">',
			esc_attr( $args['name'] ),
			esc_attr( $args['value'] )
		);

		$html .= '<ul class="picker-list">';

		if( !empty( $posts ) ) {
			
			foreach( $posts as $post ) {
				$html .= self::get_picker_li( $post );
			}

		} else {

			$html .= '<p class="notice">No items selected.</p>';
		}

		$html .= '</ul>';

		// enable recent posts
		if( $args['enable_recent'] ) {

			$recent_posts = static::recent_posts( $ids, $args['post_type'] );

			// recent posts
			if( $recent_posts ) {

				$html .= '<h4>Select a Recent ' . esc_html( $singular_name ) . '</h4>';
				$html .= '<div class="picker-recent-posts"><select class="picker-select">';
				$html .= '<option value="0">Choose a ' . esc_html( $singular_name ) . '</option>';

				foreach( $recent_posts as $post ) {

					$html .= sprintf( 
						'<option value="%s">%s</option>',
						intval( $post->ID ),
						esc_html( $post->post_title )
					);
				}

				$html .= '</select></div>';
			}
		}

		// search box
		$html .=
			'<h4>Search for ' . esc_html( $plural_name ) .'</h4>' .
			'<div class="picker-search">' .
				'<input type="text" name="s" class="picker-query" placeholder="Enter a term or phrase">' .
				'<button class="button">Search</button>' .
				'<div class="picker-results"></div>' .	
			'</div>';

		// close post picker div
		$html .= '</div>';

		return $html;
	}

	/**
	 *
	 */
	public static function recent_posts( $ids, $post_type ) {
		return get_posts( array(
			'posts_per_page' => 20,
			'post__not_in' => $ids,
			'post_type' => $post_type
		));
	}

	/**
	 *
	 */
	public static function get_picker_li( $post ) {
		return sprintf(
			'<li data-id="%s">' .
				'<h4>%s</h4>' .
				'<nav>' . 
					'<a href="%s" class="edit" target="_blank">Edit</a>' . 
					'<a href="#" class="remove">Remove</a>' .
					'<a href="%s" target="_blank">View</a>' .
				'</nav>' .
			'</li>',
			intval( $post->ID ),
			esc_html( $post->post_title ),
			esc_url( get_edit_post_link( $post->ID ) ),
			esc_url( get_the_guid( $post->ID ) )
		);
	}
}

class Tabbed_Meta_Child_Post_Picker_Field extends Tabbed_Meta_Post_Picker_Field {

	/**
	 *
	 */
	public static function validate( $post_id, $name, $value, $post ) {

		global $wpdb;

		// save the meta data, but also setup parent child relationship
		parent::validate( $post_id, $name, $value, $post );

		$old_ids = get_post_meta( $post_id, $name, true );

		$new_ids = array_map( 'intval', explode( ',', $value ) );

		if( $old_ids ) {
			
			$old_ids = array_map( 'intval', explode( ',', $old_ids ) );

			// set the post parent to 0 and menu order to nil
			$sql = (
				"
				UPDATE $wpdb->posts AS p
				SET p.post_parent = 0 AND p.menu_order = ''
				WHERE p.ID IN(" . implode( ',', $old_ids ) . ")
				"
			);

			// clean cache for these posts
			foreach( $old_ids as $id ) {
				clean_post_cache( $id );
			}
		}

		// do we have ids to update?
		if( is_array( $new_ids ) && count( $new_ids ) ) {

			$i = 1;

			// setup the new ids
			foreach( $new_ids as $id ) {

				wp_update_post( array(
					'ID' => $id,
					'post_parent' => $post->ID,
					'menu_order' => $i
				));

				error_log( 'Updated Post ID: ' . $id );

				$i++;
			}
		}
	}

	/**
	 *
	 */
	public static function recent_posts( $ids, $post_type ) {
		return get_posts( array(
			'posts_per_page' => 20,
			'post__not_in' => $ids,
			'post_type' => $post_type,
			'post_parent' => 0
		));
	}
}

/**
 *
 */
class Tabbed_Meta_Sorter_Field extends Tabbed_Meta_Field {

	/**
	 * Saves posts as comma separted ids
	 */
	public static function render( $args ) {

		// setup some defaults
		$args = wp_parse_args( $args, array(
			'items' => null
		));

		$html = '<div class="sorter">';

		$items = $args['items'];

		if( !empty( $args['value'] ) ) {

			$new_items = array();
			foreach( explode( ',', $args['value'] ) as $v ) {
				$new_items[$v] = $items[$v];
			}

			$items = $new_items;

		}

		$html .= sprintf(
			'<input type="hidden" name="%s" value="%s" class="sorter-ids">',
			esc_attr( $args['name'] ),
			!empty( $args['value'] ) ? esc_attr( $args['value'] ) : implode( ',', array_keys( $items ) )
		);

		$html .= '<ul class="sorter-list">';

		if( !empty( $items ) ) {
			
			foreach( $items as $key => $item ) {
				$html .= self::get_picker_li( $key, $item );
			}

		} else {

			$html .= '<p class="notice">No items selected.</p>';
		}

		$html .= '</ul>';

		

		// close div
		$html .= '</div>';

		return $html;
	}

	/**
	 *
	 */
	public static function recent_posts( $ids, $post_type ) {
		return get_posts( array(
			'posts_per_page' => 20,
			'post__not_in' => $ids,
			'post_type' => $post_type
		));
	}

	/**
	 *
	 */
	public static function get_picker_li( $key, $item ) {
		return sprintf(
			'<li data-id="%s">' .
				'<h4>%s</h4>' .
			'</li>',
			esc_attr( $key ),
			esc_html( $item )
		);
	}
}

endif;