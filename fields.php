<?php

if( !class_exists( 'Tabbed_Meta_Field' ) ) :

/**
 *
 */
class Tabbed_Meta_Field {
	public static function save( $post_id, $name, $value, $post ) {
		update_post_meta( $post_id, $name, sanitize_text_field( $value ) ); 
	}
}

/**
 *
 */
class Tabbed_Meta_Text_Field extends Tabbed_Meta_Field {

	public static function render( $args ) {

		// this input can have a placeholder value
 		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';

 		return sprintf(
 			'<input type="text" name="%s" value="%s" placeholder="%s" class="widefat">',
 			esc_attr( $args['name'] ),
 			esc_attr( $args['value'] ),
 			esc_attr( $placeholder )
 		);
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

	public static function save( $post_id, $name, $value, $post = null ) {
		update_post_meta( $post_id, $name, 1 );
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
	public static function save( $post_id, $name, $value, $post = null ) {
		update_post_meta( $post_id, $name, esc_url( $value ) );
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
				$html .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
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

		$post_type = isset( $args['post_type'] ) ? $args['post_type'] : 'post';

		// set the post type as a data attribute
		$html = '<div class="post-picker" data-post-type="' . esc_attr( $post_type )  . '">';

		if( isset( $args['value'] ) ) {

			$ids = array_map( 'intval', explode( ",", $args['value'] ) );

			$posts = get_posts( array(
				'post__in' => $ids,
				'posts_per_page' => count( $ids ),
				'post_type' => $post_type,
				'orderby' => 'post__in'
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

			$html .= '<p class="notice">No posts selected.</p>';
		}

		$html .= '</ul>';

		$recent_posts = get_posts( array(
			'posts_per_page' => 20,
			'post__not_in' => $ids,
			'post_type' => $post_type
		));

		// recent posts
		if( $recent_posts ) {

			$html .= '<h4>Select Recent Posts</h4>';
			$html .= '<div class="picker-recent-posts"><select class="picker-select">';

			foreach( $recent_posts as $post ) {
				$html .= sprintf( 
					'<option value="%s">%s</option>',
					intval( $post->ID ),
					esc_html( $post->post_title )
				);
			}

			$html .= '</select></div>';
		}

		// search box
		$html .=
			'<h4>Search for Posts</h4>' .
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
	public static function get_picker_li( $post ) {
		return sprintf(
			'<li data-id="%s">' .
				'<h4>%s</h4>' .
				'<nav>' . 
					'<a href="%s" class="edit">Edit</a>' . 
					'<a href="#" class="remove">Remove</a>' .
					'<a href="%s">View</a>' .
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
	public static function save( $post_id, $name, $value, $post ) {
		
		global $wpdb;

		// save the meta data, but also setup parent child relationship
		parent::save( $post_id, $name, $value, $post );

		$old_ids = get_post_meta( $post_id, $name, true );

		$new_ids = array_map( 'intval', explode( ',', $value ) );

		if( $old_ids ) {
			
			$old_ids = array_map( 'intval', explode( ',', $old_ids ) );

			// set the post parent to 0 and menu order to nil
			$wpdb->query(
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

		$i = 1;

		// setup the new ids
		foreach( $new_ids as $id ) {

			wp_update_post( array(
				'ID' => $id,
				'post_parent' => $post->ID,
				'menu_order' => $i
			));

			$i++;
		}
	}
}

endif;