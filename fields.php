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

endif;