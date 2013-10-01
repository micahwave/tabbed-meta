<?php

if( !class_exists( 'Tabbed_Meta_Fields' ) ) :

class Tabbed_Meta_Fields {

	/**
	 * Enable some hooks that our fields will need
	 */
	function __construct() {
		add_action( 'wp_ajax_get_picker_posts', array( $this, 'ajax_get_picker_posts') );
		add_action( 'wp_ajax_get_picker_item', array( $this, 'ajax_get_picker_item' ) );
	}

	/**
	 *
	 */
	function ajax_get_picker_item() {

		check_ajax_referer( 'tm_fields' );

		if( !empty( $_REQUEST['id'] ) ) {

			$post = get_post( intval( $_REQUEST['id'] ) );

			if( $post ) {
				die( self::get_picker_li( $post ) );
			}
		} 
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

	/**
	 * Saves posts as comma separted ids
	 */
	public static function post_picker_field( $args ) {

		$html = '<div class="post-picker">';

		$post_type = isset( $args['post_type'] ) ? $args['post_type'] : 'post';

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

			$html .= '<p class="notice">No posts found.</p>';
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

			$html .= '</select><div>';
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


	/**
	 * Sets child post post_parent to current post and sets menu order
	 */
	public static function child_post_picker( $args ) {

	}
}

endif;

new Tabbed_Meta_Fields();