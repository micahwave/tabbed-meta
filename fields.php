<?php



class Tabbed_Meta_Fields {

	/**
	 *
	 */
	function __construct() {
		add_action( 'wp_ajax_get_picker_posts', array( $this, 'ajax_get_picker_posts') );
	}

	/**
	 *
	 */
	function ajax_get_picker_posts() {

		check_ajax_referer( 'tm_fields' );

		if( !empty( $_REQUEST['s'] ) ) {

			$posts = get_posts( array(
				's' => sanitize_text_field( $_REQUEST['s'] ),
				'post_type' => isset( $_REQUEST['post_type'] ) ? sanitize_text_field( $_REQUEST['post_type'] ) : 'post'
			));

			if( $posts ) {
				die( json_encode( $posts ) );
			}
		}
	}
	
	/**
	 *
	 */
	public static function text_field( $args ) {

		// this input can have a placeholder value
 		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';

 		return sprintf(
 			'<input type="text" name="%s" value="%s" placeholder="%s" class="widefat">',
 			esc_attr( $args['name'] ),
 			esc_attr( $args['value'] ),
 			esc_attr( $placeholder )
 		);
	}

	/**
	 *
	 */
	public static function checkbox_field( $args ) {

		return sprintf(
 			'<input type="checkbox" name="%s" value="1" %s>',
 			esc_attr( $args['name'] ),
 			checked( 1, $args['value'], 0 )
 		);
	}

	/**
	 *
	 */
	public static function link_field( $args ) {

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
	public static function select_field( $args ) {

		$html = '';

		if( isset( $args['options'] ) ) {
			foreach( $options as $key => $value ) {
				$html .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
			}
		}

		return sprintf(
			'<select name="%s">%s</select>',
			esc_attr( $args['name'] ),
			$html
		);
	}

	/**
	 * Saves posts as comma separted ids
	 */
	public static function post_picker_field( $args ) {

		$html = '';

		$post_type = isset( $args['post_type'] ) ? $args['post_type'] : 'post';

		if( isset( $args['value'] ) ) {

			$ids = array_map( 'intval', explode( ",", $args['value'] ) );

			$posts = get_posts( array(
				'post__in' => $ids,
				'posts_per_page' => count( $ids ),
				'post_type' => $post_type
			));
		}

		$html .= sprintf(
			'<input type="hidden" name="%s" value="%s">',
			esc_attr( $args['name'] ),
			esc_attr( $args['value'] )
		);

		if( !empty( $posts ) ) {

			$html .= '<ol>';

			foreach( $posts as $post ) {
				$html .= sprintf(
					'<li>%s'.
						'<nav>' . 
							'<a href="#" class="remove">Remove</a>' .
							'<a href="#" class="edit">Edit</a>' . 
						'</nav>' .
					'</li>',
					$post->post_title
				);
			}

			$html .= '</ol>';

		} else {

			$html .= 'No posts found.';
		}

		$html .= 
			'<div class="picker-search">' .
				'<input type="text" name="s" placeholder="Search">' .
				'<button>Search</button>' .
				'<div class="picker-results"></div>' .
			'</div>';

		return $html;
		

	}


	/**
	 * Sets child post post_parent to current post and sets menu order
	 */
	public static function child_post_picker( $args ) {

	}
}
new Tabbed_Meta_Fields();