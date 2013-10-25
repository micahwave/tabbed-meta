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
 * Builds a data field that uses the jquery date picker
 */
class Tabbed_Meta_Date_Field extends Tabbed_Meta_Text_Field {

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
class Tabbed_Meta_Sorter_Field extends Tabbed_Meta_Field {

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