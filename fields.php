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

		if( !class_exists( 'Post_Finder' ) )
			return;

		// setup options for Post Finder
		$options = array();

		if( isset( $args['limit'] ) ) {
			$options['limit'] = $args['limit'];
		}

		ob_start();

		Post_Finder::render( $args['name'], $args['value'], $options );

		$html = ob_get_contents();

		ob_clean();

		return $html;
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

endif;