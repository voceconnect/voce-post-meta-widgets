<?php
/*
  Plugin Name: Voce Post Meta Widgets
  Plugin URI: http://vocecommunications.com
  Description: Extend Voce Post Meta with widget fields
  Version: 1.0
  Author: matstars, voceplatforms
  Author URI: http://vocecommunications.com
  License: GPL2
 */

class Voce_Post_Meta_Widget_Area {
	/**
	 * Setup plugin
	 *
	 * Filters:
	 * voce_post_meta_widget_area_post_types - post types that have widget area added (defaults to none)
	 * voce_post_meta_widget_area_widget_choices_location - location of metabox for widget choices (defaults to side)
	 * voce_post_meta_widget_area_widget_choices_priority - priority of metabox for widget choices (defaults to low)
	 * 
	 * @todo remove comments surrounding the add_action to hide_sidebars
	 * 
	 * 
	 */

	const WIDGET_ID_PREFIX = "voce_post_meta_widget_area_";

	public static function initialize() {
		global $pagenow;
		require_once( ABSPATH . '/wp-admin/includes/widgets.php' );
		add_action( 'init', array( __CLASS__, 'initialize' ) );
		//add_action( 'init', array( __CLASS__, 'hide_sidebars' ));
		add_filter( 'meta_type_mapping', array(__CLASS__, 'meta_type_mapping') );
		add_action( 'admin_enqueue_scripts', array(__CLASS__, 'action_admin_enqueue_scripts') );
		$post_types = apply_filters( 'voce_post_meta_widget_area_post_types', array( ) );
		add_action( 'add_meta_boxes', function() use ($post_types) {
			foreach ($post_types as $post_type) {
				add_meta_box( 'sidebar_admin', 'Sidebar Admin', array( __CLASS__, 'sidebar_admin_metabox' ), $post_type, apply_filters('voce_post_meta_widget_area_widget_choices_location', 'side'), apply_filters('voce_post_meta_widget_area_widget_choices_priority', 'low') );
			}
		});
		foreach($post_types as $post_type) {
			add_action( 'save_post', function($post_id) use ($post_types) {
				if ( in_array( get_post_type($post_id), $post_types ) ) {
					
				}
			});
		}
	}
	/**
	  *
	  * Hide Custom Sidebars on Widgets.php 
	  * @return void
	  *
	  */

	public static function hide_sidebars(){
		global $pagenow, $wp_registered_sidebars;
		if ( is_admin() && 'widgets.php' == $pagenow ) {
			foreach ( $wp_registered_sidebars as $sidebar ) {
				if ( false !== strpos( $sidebar['id'], self::WIDGET_ID_PREFIX ) ) {
					unregister_sidebar( $sidebar['id'] );
				}
			}
		}
	}

	/**
	 * Enqueue admin JavaScripts
	 * @return void
	 */

	public static function action_admin_enqueue_scripts( $hook ) {
		global $post;
		$pages = apply_filters( 'voce_post_meta_widget_area_scripts', array('post-new.php', 'post.php', 'widgets.php') );

		if( !in_array( $hook, $pages ) ) {
			return;
		}
		
		wp_enqueue_script( 'voce-post-widget-area', self::plugins_url( '/js/voce-post-widget-area.js', __FILE__ ), array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ) );
		$sidebars_widgets = get_option( 'sidebars_widgets', array( ) );
		if ( $sidebars_widgets == "" ){
			return;
		}

		foreach ($sidebars_widgets as $key => &$sidebar) {
			// WordPress adds an 'array_version' key for internal use.
			if ( 'array_version' === $key || count( $sidebar ) == 0 )
			continue;
			array_walk( $sidebar, create_function( '&$v', '$v = "widget-_".$v;' ) );
		}

		$args = array(
			'post_name' => $post->post_name,
			'sidebars_widgets' => json_encode( $sidebars_widgets )
		);
		wp_localize_script( 'voce-post-widget-area', 'widgetsAdmin', $args );
	}

	/**
	 * Allow this plugin to live either in the plugins directory or inside
	 * the themes directory.
	 *
	 * @method plugins_url
	 * @param type $relative_path
	 * @param type $plugin_path
	 * @return string
	 */

	public static function plugins_url( $relative_path, $plugin_path ) {
		$template_dir = get_template_directory();

		foreach( array('template_dir', 'plugin_path') as $var ) {
			$$var = str_replace( '\\', '/', $$var ); // sanitize for Win32 installs
			$$var = preg_replace( '|/+|', '/', $$var );
		}
		if( 0 === strpos( $plugin_path, $template_dir ) ) {
			$url = get_template_directory_uri();
			$folder = str_replace( $template_dir, '', dirname( $plugin_path ) );
			if( '.' != $folder ) {
				$url .= '/' . ltrim( $folder, '/' );
			}
			if( !empty( $relative_path ) && is_string( $relative_path ) && strpos( $relative_path, '..' ) === false ) {
				$url .= '/' . ltrim( $relative_path, '/' );
			}
			return $url;
		} else {
			return plugins_url( $relative_path, $plugin_path );
		}
	}


		
	/**
	 * @method meta_type_mapping
	 * @param type $mapping
	 * @return array
	 */

	public static function meta_type_mapping( $mapping ) {
		$mapping['widget_area'] = array(
			'class' => 'Voce_Meta_Field',
			'args' => array(
				'display_callbacks' => array('voce_widget_area_field_display')
			)
		);
		return $mapping;
	}

	/**
	 * Generate HTML for meta box
	 * 
	 * @method sidebar_admin_metabox
	 * @global Object $post 
	 * @return Void
	 */

	public static function sidebar_admin_metabox() {
		global $post;
		?>

		<div id="widget-list" class="column-1">
			<strong><?php _e( 'Available Widgets' ); ?></strong>
			<p class="description"><?php _e( 'Drag widgets from here to widget areas to activate them.' ); ?></p>
			<?php wp_list_widgets(); ?>
		</div>

		
		<?php wp_nonce_field( 'save-sidebar-widgets', '_wpnonce_widgets', false ); ?>

		<div class="clear"></div>

		<?php
	}	


		

}


Voce_Post_Meta_Widget_Area::initialize();

/**
 * @param type $field
 * @param type $value
 * @param type $post_id
 * @return type
 */
function voce_widget_area_field_display( $field, $value, $post_id ) {
	if ( ! class_exists( 'Voce_Meta_API' ) ) {
		return;
	}
	
	
	global $post;

	$post_type = get_post_type( $post_id );
	$value_post = get_post( $value );
	
	?>
	<br />
	<hr />
	<div class="column-2">
		<p>Widget Area ID = <?php echo $field->get_input_id() ?> </p>
		<div class="sidebar" style="min-height:200px;height:100%;width:100%;background:#ccc;">
		</div>


	<?php
}
