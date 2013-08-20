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
	 */

	public static function initialize() {
		global $pagenow;
		require_once( ABSPATH . '/wp-admin/includes/widgets.php' );
		add_action( 'init', array( __CLASS__, 'initialize' ) );
		add_action( 'wp_ajax_get-active-widgets', array( __CLASS__, 'ajax_get_active_widgets' ) );
		add_action( 'wp_ajax_register-sidebar', array( __CLASS__, 'ajax_register_sidebar' ) );
		add_filter( 'meta_type_mapping', array(__CLASS__, 'meta_type_mapping') );
		add_action( 'admin_enqueue_scripts', array(__CLASS__, 'action_admin_enqueue_scripts') );
		$post_types = array_unique( apply_filters( 'voce_post_meta_widget_area_post_types', array( 'page' ) ) );
		add_action( 'add_meta_boxes', function() use ($post_types) {
			foreach ($post_types as $post_type) {
				add_meta_box( 'sidebar_admin', 'Sidebar Admin', array( __CLASS__, 'sidebar_admin_metabox' ), $post_type, apply_filters('voce_post_meta_widget_area_widget_choices_location', 'side'), apply_filters('voce_post_meta_widget_area_widget_choices_priority', 'low') );
			}
		});
	}

	/**
	 * Enqueue admin JavaScripts
	 * @return void
	 */

	public static function action_admin_enqueue_scripts( $hook ) {
		$pages = apply_filters( 'voce_post_meta_widget_area_scripts', array('post-new.php', 'post.php', 'widgets.php') );

		if( !in_array( $hook, $pages ) ) {
			return;
		}
		
		wp_enqueue_script( 'voce-post-widget-area', self::plugins_url( '/js/voce-post-widget-area.js', __FILE__ ), array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ) );
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
			<p class="description"><?php _e( 'Drag widgets from here widget areas to activate them.' ); ?></p>
			<?php wp_list_widgets(); ?>
		</div>

		
		<?php wp_nonce_field( 'save-sidebar-widgets', '_wpnonce_widgets', false ); ?>

		<div class="clear"></div>

		<?php
	}	


		/**
		 * Retrieve sidebars and output HTML for metabox
		 * 
		 * @global Object $post
		 * @global Array $wp_registered_sidebars 
		 * @return Void
		 */

		public static function get_sidebars() {
			global $post, $wp_registered_sidebars;

			$i = 0;

			?><select class="sidebar-list"><?php 

			foreach ($wp_registered_sidebars as $sidebar) {
				// Ignore sidebars registered by this plugin.
				if ( strpos( $sidebar['id'], self::WIDGET_ID_PREFIX ) === 0 ) {
					continue;
				}

				?><option id="<?php echo self::get_sidebar_id( $post->post_name, $i ); ?>" data-sidebar="<?php echo $sidebar['id']; ?>"><?php echo $sidebar['name']; ?></option><?php
				$i++;
			}

			?></select><?php 
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
	$url_class = '';
	?>
	<br />
	<hr />
	<p>Widget Area balh</p>

	<?php
}
