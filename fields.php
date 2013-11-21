<?php

if( !class_exists( 'NS_Tabbed_Meta_Field' ) ) :

/**
 *
 */
class NS_Tabbed_Meta_Field {

	public static function save( $post_id, $name, $post, $args ) {

		// dont save blanks by default
		if( !empty( $_POST[$name] ) ) {

			if( isset( $args['sanitize_callback'] ) && is_callable( $args['sanitize_callback'] ) ) {
				$value = call_user_func_array( $args['sanitize_callback'], array( $_POST[$name] ) );
			} else {
				$value = sanitize_text_field( $_POST[$name] );
			}

			update_post_meta( $post_id, $name, $value );

		} else {

			delete_post_meta( $post_id, $name );
		}	 
	}
}

/**
 *
 */
class NS_Tabbed_Meta_Text_Field extends NS_Tabbed_Meta_Field {

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
 * Builds a data field that uses the jquery date picker
 */
class NS_Tabbed_Meta_Date_Field extends NS_Tabbed_Meta_Text_Field {

	/**
	 * Output the field markup
	 */
	public static function render( $args ) {

		$value = !empty( $args['value'] ) ? $args['value'] : time();

 		return sprintf(
 			'<input type="text" name="%s" value="%s">',
 			esc_attr( $args['name'] ),
 			esc_attr( date( 'm/d/Y', $value ) )
 		);
	}

	/**
	 * Save the data
	 *
	 * @return string
	 */
	public static function save( $post_id, $name, $post, $args ) {
		if( isset( $_POST[$name] ) )
			update_post_meta( $post_id, $name, strtotime( $_POST[$name] ) );
		else
			delete_post_meta( $post_id, $name );
	}
}

/**
 *
 */
class NS_Tabbed_Meta_Checkbox_Field extends NS_Tabbed_Meta_Field {

	public static function render( $args ) {

		return sprintf(
 			'<input type="checkbox" name="%s" value="1" %s>',
 			esc_attr( $args['name'] ),
 			checked( 1, $args['value'], 0 )
 		);
	}

	/**
	 * Save either a 1 or 0
	 */
	public static function save( $post_id, $name, $post, $args ) {

		if( isset( $_POST[$name] ) )
			update_post_meta( $post_id, $name, 1 );
		else
			update_post_meta( $post_id, $name, 0 );
	}
}

/**
 * URL/Link field
 */
class NS_Tabbed_Meta_Link_Field extends NS_Tabbed_Meta_Field {

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
	 * Use esc_url
	 */
	public static function save( $post_id, $name, $post, $args ) {
		$args['sanitize_callback'] = 'esc_url';
		parent::save( $post_id, $name, $post, $args );
	}
}

/**
 * Textarea field
 */
class NS_Tabbed_Meta_Textarea_Field extends NS_Tabbed_Meta_Field {

	/**
	 *
	 */
	public static function render( $args ) {

		// this input can have a placeholder value
 		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';

 		// class is customizable
 		$class = isset( $args['class'] ) ? $args['class'] : 'widefat';

 		return sprintf(
 			'<textarea name="%s" class="widefat" rows="4">%s</textarea>',
 			esc_attr( $args['name'] ),
 			esc_textarea( $args['value'] )
 		);
	}
}

/**
 *
 */
class NS_Tabbed_Meta_Select_Field extends NS_Tabbed_Meta_Field {

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
class NS_Tabbed_Meta_Sorter_Field extends NS_Tabbed_Meta_Field {

	/**
	 *
	 */
	public static function render( $args ) {

		// setup some defaults
		$args = wp_parse_args( $args, array(
			'items' => array()
		));

		$html = '<div>';

		$items = $args['items'];

		// if we have a value, determine the order of the items
		if( !empty( $args['value'] ) && is_array( $args['items'] ) ) {
			$existing = array();
			foreach( explode( ',', $args['value'] ) as $v ) {
				$existing[$v] = $args['items'][$v];
			}
			$items = $existing;
		}

		$html .= sprintf(
			'<input type="hidden" name="%s" value="%s" class="tm-field-sorter-ids">',
			esc_attr( $args['name'] ),
			!empty( $args['value'] ) ? esc_attr( $args['value'] ) : implode( ',', array_keys( $items ) )
		);

		// output items in order
		if( count( $items ) ) {

			$html .= '<ul>';
			
			foreach( $items as $key => $item ) {
				$html .= sprintf(
					'<li data-id="%s">' .
						'<h4>%s</h4>' .
					'</li>',
					esc_attr( $key ),
					esc_html( $item )
				);
			}

			$html .= '</ul>';

		}

		// close div
		$html .= '</div>';

		return $html;
	}
}

endif;