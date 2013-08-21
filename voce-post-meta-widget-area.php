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
	 * 
	 * 
	 */

	const WIDGET_ID_PREFIX 		= "voce_post_meta_widget_area_";
	const SIDEBAR_OPTION_NAME 	= "voce_post_meta_sidebars";

	public static function initialize() {
		$post_types = apply_filters( 'voce_post_meta_widget_area_post_types', array( ) );
		// if no post types are specified, we don't need to run this
		if ( ! is_array( $post_types ) ) {
			return;
		}
		require_once( ABSPATH . '/wp-admin/includes/widgets.php' );
		add_action( 'init', array( __CLASS__, 'hide_sidebars' ));
		add_filter( 'meta_type_mapping', array( __CLASS__, 'meta_type_mapping') );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'action_admin_enqueue_scripts' ) );
		// add metaboxes to the post types specified
		add_action( 'add_meta_boxes', function() use ($post_types) {
			foreach ($post_types as $post_type) {
				add_meta_box( 'sidebar_admin', 'Sidebar Admin', array( __CLASS__, 'sidebar_admin_metabox' ), $post_type, apply_filters('voce_post_meta_widget_area_widget_choices_location', 'side'), apply_filters('voce_post_meta_widget_area_widget_choices_priority', 'low') );
			}
		});
		$sidebars = get_option( Voce_Post_Meta_Widget_Area::SIDEBAR_OPTION_NAME );
		if ( is_array( $sidebars )) {
			foreach( $sidebars as $sidebar ) {
			$args = array(
				'name' => $sidebar,
				'id' => $sidebar,
				'description' => 'Voce Post Meta Widget Area for Posts',
				'before_widget' => '<li id="%1$s" class="widget %2$s">',
				'after_widget' => '</li>',
				'before_title' => '<h2 class="widgettitle">',
				'after_title' => '</h2>'
			);
				register_sidebar( $args );
			}
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
	 * Enqueue admin JavaScripts and CSS
	 *
	 * Filters:
	 * voce_post_meta_widget_area_scripts - filter what pages the scripts/styles needs 
	 *
	 * @return void
	 */

	public static function action_admin_enqueue_scripts( $hook ) {
		$pages = apply_filters( 'voce_post_meta_widget_area_scripts', array('post-new.php', 'post.php', 'widgets.php') );
		if( !in_array( $hook, $pages ) ) {
			return;
		}
		
		wp_enqueue_script( 'voce-post-widget-area', self::plugins_url( '/js/voce-post-widgets.js', __FILE__ ), array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ) );
		wp_enqueue_style( 'voce-post-widget-area', self::plugins_url( '/css/voce-post-widgets.css', __FILE__ ) );

		//@todo figure this out vvvv !

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
				'display_callbacks' => array( 'voce_widget_area_field_display' ),
				'sanitize_callbacks' => array( 'voce_widget_area_field_submit' )
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

		<div id="widget-list" class="voce-post-meta-widget column-1">
			<strong><?php _e( 'Available Widgets' ); ?></strong>
			<p class="description"><?php _e( 'Drag widgets from here to widget areas to activate them.' ); ?></p>
			<?php wp_list_widgets(); ?>
		</div>

		
		<?php wp_nonce_field( 'save-sidebar-widgets', '_wpnonce_widgets', false ); ?>

		<div class="clear"></div>

		<?php
	}	

	/*
	 * Clean sidebars, removes any sidebars that are empty
	 * 
	 * @method clean_sidebars
	 * @return void
	 *
	 */

	public static function clean_sidebars( $sidebars ) {
		$the_sidebars = wp_get_sidebars_widgets();
		$sidebar_return = array();
		$sidebars = (array)$sidebars;
		foreach( $sidebars as $sidebar ) {
			if ( count( $the_sidebars[$sidebar] ) ) {
				$sidebar_return[] = $sidebar;
			}
		}
		return $sidebar_return;
	}

	public static function get_active_widgets( $sidebar ) {
			global $sidebars_widgets;
			$temp = $sidebars_widgets;
			$sidebars_widgets = array( $sidebar => array( ) );
			wp_list_widget_controls( $sidebar );
			$sidebars_widgets = $temp;
	}		


		

}


add_action("init", function(){
	Voce_Post_Meta_Widget_Area::initialize();
});

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
	$sidebar_id = Voce_Post_Meta_Widget_Area::WIDGET_ID_PREFIX . $field->get_input_id();
	
	?>
	<input type="hidden" id="voce_post_widgets_exist" value="true" />
	<br />
	<hr />
	<div class="column-2 voce-post-meta-widget-drop">
		<p>Widget Area ID = <?php echo $sidebar_id ?> </p>
		<div class="sidebar" style="min-height:200px;height:100%;width:90%;background:#FFF;padding-left:10px;padding-right:10px">
				<div class="column-2-widgets">
					<strong><?php _e( 'Active Widgets' ); ?></strong>
					<div class="sidebar widget-droppable widget-list" id="<?php echo $post->post_name; ?>_0">
						<?php Voce_Post_Meta_Widget_Area::get_active_widgets( $sidebar_id ); ?>
					</div>
				</div>
		</div>

	<?php
}

function voce_widget_area_field_submit( $field, $value, $post_id ) {
	$sidebar = Voce_Post_Meta_Widget_Area::WIDGET_ID_PREFIX . $field->get_input_id();
	$sidebars = get_option( Voce_Post_Meta_Widget_Area::SIDEBAR_OPTION_NAME );
	$sidebars[] = $sidebar;
	$sidebars = array_unique( $sidebars );
	$sidebars = Voce_Post_Meta_Widget_Area::clean_sidebars( $sidebars );
	update_option( Voce_Post_Meta_Widget_Area::SIDEBAR_OPTION_NAME, $sidebars );
	
}
