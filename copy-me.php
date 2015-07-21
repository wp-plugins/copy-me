<?php

/*
 * Plugin Name: copy-me
 * Description: copy-me allows you to copy a post, page or custom post type to another site in a Wordpress multisite
 * Author: Alan Cesarini
 * Version: 1.0.0
 * Author URI: http://alancesarini.com
 * License: GPL2+
 */

if( !class_exists( 'CopyMe' ) ) {

	class CopyMe {

		private static $_this;

		private static $_version;

		private static $s2s_copier;

		function __construct() {
		
			if( isset( self::$_this ) )
				wp_die( sprintf( '%s is a singleton class and you cannot create a second instance.', get_class( $this ) ) );
			self::$_this = $this;

			self::$_version = '1.0.0';

			require( 'includes/class_copier.php' );

			self::$s2s_copier = new S2S_copier();

			add_action( 'wp_loaded', array( $this, 'register_assets' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

			// Maybe the current theme registers a custom post type, so we add the custom column after WP is fully loaded
			add_action( 'wp_loaded', array( $this, 'add_column' ) );

			add_action( 'wp_ajax_copyme_copy_item', array( $this, 'copy_item' ) );			
			
		}

		function copy_item() {

			$args = array(
				'post_id' => intval( $_POST[ 'id' ] ),
				'origin_site' => get_current_blog_id(),
				'target_site' => intval( $_POST[ 'target' ] ),
				'author_option' => 1,
				'selected_author' => -1
			);

			$response = self::$s2s_copier->copy_item( $args );

			if( $response ) {
				die( json_encode(array( 'response' => 'OK' )));
			} else {
				die( json_encode(array( 'response' => 'KO' )));
			}

		}

		function register_assets() {

			wp_register_script( 'copyme-admin-js', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), self::$_version );
			wp_register_style( 'copyme-admin-style', plugins_url( 'assets/css/admin.css', __FILE__ ), false, self::$_version );

		}

		function enqueue_assets() {

			wp_enqueue_script( 'copyme-admin-js' );
			wp_enqueue_style( 'copyme-admin-style' );

		}	

		function add_column() {

			add_filter( 'manage_posts_columns', array( $this, 'add_column_header' ) );

			add_filter( 'manage_pages_columns', array( $this, 'add_column_header' ) );

			add_action( 'manage_pages_custom_column', array( $this, 'add_column_content' ), 10, 2 );

			add_action( 'manage_posts_custom_column', array( $this, 'add_column_content' ), 10, 2 );

		}

		function add_column_header( $defaults ) {
		    
		    $defaults[ 'copyme' ] = __( 'Copy Me', 'copyme' );
		    return $defaults;
		
		}
		 
		function add_column_content( $column_name, $post_ID ) {

		    if( $column_name == 'copyme' ) {
		        echo '<a href="#" id="copyme-link-' . $post_ID . '" class="copyme-link" data-post="' . $post_ID . '">' . __( 'Copy to...', 'copyme' ) . '</a>';	
		        echo '<div id="copyme-box-' . $post_ID . '" style="display:none" class="copyme-box"><select id="copyme-target-site-' . $post_ID . '">';
		        self::$s2s_copier->render_combo_sites();
		        echo '</select><br/><a href="#" class="button button-primary copyme-button" data-origin="' . get_current_blog_id() . '" data-post="' . $post_ID . '">' . __( 'copy now', 'copyme' ) . '</a></div>';
		    }

		}

		static function this() {
		
			return self::$_this;
		
		}

	}

}	

new CopyMe();
