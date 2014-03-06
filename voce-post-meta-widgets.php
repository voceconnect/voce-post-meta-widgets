<?php
/*
  Plugin Name: Voce Post Meta Widgets
  Plugin URI: http://vocecommunications.com
  Description: Extend Voce Post Meta with widget fields
  Version: 0.1.1
  Author: matstars, voceplatforms
  Author URI: http://vocecommunications.com
  License: GPL2
 */


add_action( 'admin_init', 'voce_meta_widgets_check_voce_meta_api' );

class Voce_Post_Meta_Widgets {
	/**
	 *
	 * Class notes:
	 * 	Filters:
	 * 	voce_post_meta_widgets_post_types - post types that have widget area added (defaults to none)
	 * 	voce_post_meta_widgets_widget_choices_location - location of metabox for widget choices (defaults to side)
	 * 	voce_post_meta_widgets_widget_choices_priority - priority of metabox for widget choices (defaults to low)
	 *
	 */

	const WIDGET_ID_PREFIX 		= "voce_post_meta_widgets_";
	const SIDEBAR_OPTION_NAME 	= "voce_post_meta_sidebars";

	public static function initialize() {
		$post_types = apply_filters( 'voce_post_meta_widgets_post_types', array( 'page' ) );
		// if no post types are specified, we don't need to run this
		if ( ! is_array( $post_types ) ) {
			return;
		}
		require_once( ABSPATH . '/wp-admin/includes/widgets.php' );
		add_action( 'wp_loaded', array( __CLASS__, 'hide_sidebars' ));
		add_filter( 'meta_type_mapping', array(__CLASS__, 'meta_type_mapping') );
		add_action( 'admin_enqueue_scripts', array(__CLASS__, 'action_admin_enqueue_scripts') );
		// add metaboxes to the post types specified
		add_action( 'add_meta_boxes', function() use ($post_types) {
			foreach ($post_types as $post_type) {
				add_meta_box( 'sidebar_admin', 'Sidebar Admin', array( __CLASS__, 'sidebar_admin_metabox' ), $post_type, apply_filters('voce_post_meta_widgets_widget_choices_location', 'side'), apply_filters('voce_post_meta_widgets_widget_choices_priority', 'low') );
			}
		});
		$sidebars = get_option( Voce_Post_Meta_Widgets::SIDEBAR_OPTION_NAME );
		if ( is_array( $sidebars )) {
			foreach( $sidebars as $sidebar ) {
			$args = array(
				'name' => $sidebar,
				'id' => $sidebar,
				'description' => 'Drag &amp; Drop Widgets from the Right Sidebar to Below',
				'before_widget' => '<li id="%1$s" class="widget %2$s">',
				'after_widget' => '</li>',
				'before_title' => '<h2 class="widgettitle">',
				'after_title' => '</h2>'
			);
				error_log($sidebar . "\n", 3, "/var/tmp/a.log");
				register_sidebar( $args );
			}
		}
		
		add_action( 'add_meta_boxes', function() use ( $post_types ) {
			global $post;
			$the_sidebars = wp_get_sidebars_widgets();
			foreach( $the_sidebars as $key=>$val ) {
				$sidebar_ids[] = $key;			
			}
			foreach( $post_types as $post_type ) {
				add_meta_box( 'voce_widgets_hidden', 'Sidebar Admin', function() use ($sidebar_ids, $post) {
					?>
					<div class="voce-hide-me">
					<?php
					foreach($sidebar_ids as $sidebar){
						if ( strpos($sidebar, "post_id_" . $post->ID) === false ) {

						?>
						<div class="sidebar widget-droppable widget-list" id="<?php echo $post->post_name; ?>_0">
							<?php Voce_Post_Meta_Widgets::get_active_widgets( $sidebar ); ?>
						</div>
						<?php
						}
					}
					?>
				</div>
					<?php
				}, $post_type, 'side', 'low' );
			}
		});
	}

	/**
	  *
	  * Hide Custom Sidebars on Widgets.php 
	  *
	  * @method hide_sidebars
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
	 *
	 * Enqueue admin JavaScripts and CSS
	 *
	 * Filters:
	 * voce_post_meta_widgets_scripts - filter what pages the scripts/styles needs (defaults to 'post-new.php' and 'post.php')
	 *
	 * @method action_admin_enqueue_scripts
	 * @param type $hook
	 * @return void
	 */

	public static function action_admin_enqueue_scripts( $hook ) {
		global $post;
		$pages = apply_filters( 'voce_post_meta_widgets_scripts', array( 'post-new.php', 'post.php', 'edit.php' ) );

		if( !in_array( $hook, $pages ) ) {
			return;
		}
		wp_enqueue_script( 'voce-post-meta-widgets', self::plugins_url( '/js/voce-post-meta-widgets.min.js', __FILE__ ), array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ) );
		wp_enqueue_style( 'voce-post-meta-widgets', self::plugins_url( '/css/voce-post-meta-widgets.min.css', __FILE__ ) );
	}

	/**
	 *
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
	 *
	 * @method meta_type_mapping
	 * @param type $mapping
	 * @return array
	 */

	public static function meta_type_mapping( $mapping ) {
		$mapping['widgets'] = array(
			'class' => 'Voce_Meta_Field',
			'args' => array(
				'display_callbacks' => array( 'voce_widgets_field_display' ),
				'sanitize_callbacks' => array( 'voce_widgets_field_submit' )
			)
		);
		return $mapping;
	}

	/**
	 *
	 * Generate HTML for meta box
	 * 
	 * @method sidebar_admin_metabox
	 * @global Object $post 
	 * @return void
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

	/**
	 *
	 * Clean sidebars, removes any sidebars that are empty
	 * 
	 * @method clean_sidebars
	 * @param type $sidebars
	 * @return array
	 *
	 */

	public static function clean_sidebars( $sidebars ) {
		$the_sidebars = wp_get_sidebars_widgets();
		$sidebar_return = array();
		if ( is_string( $sidebars ) ) $sidebars = array( $sidebars );
		foreach( $sidebars as $sidebar ) {
			if ( count( $the_sidebars[$sidebar] ) > 0 ) {
				$sidebar_return[] = $sidebar;
			}
		}
		return $sidebar_return;
	}

	/**
	 *
	 * Get Active Widgets, renders widget list
	 * 
	 * @method get_active_widgets
	 * @param type $sidebar
	 * @return void
	 *
	 */

	public static function get_active_widgets( $sidebar ) {
			wp_list_widget_controls( $sidebar );
	}		
}

/**
 * 
 * @method voce_widgets_field_display
 * @param type $field
 * @param type $value
 * @param type $post_id
 * @return void
 */

function voce_widgets_field_display( $field, $value, $post_id ) {
	if ( ! class_exists( 'Voce_Meta_API' ) ) {
		return;
	}
	$post_type 	= get_post_type( $post_id );
	$value_post = get_post( $value );
	$sidebar_id = Voce_Post_Meta_Widgets::WIDGET_ID_PREFIX . $field->get_input_id() . '_post_id_' . $post_id;
	?>
	<input type="hidden" id="voce-post-widgets-exist" value="true" />
	<div class="column-2 voce-post-meta-widget-drop">
		<label for="meta_elements_description"><strong><?php _e( 'Active Widgets' ); ?></strong></label>
		<div class="sidebar voce-post-meta-widget-wrap">
				<div class="column-2-widgets">
					<div class="sidebar widget-droppable widget-list" id="<?php echo $value_post->post_name; ?>_0">
						<?php Voce_Post_Meta_Widgets::get_active_widgets( $sidebar_id ); ?>
					</div>
				</div>
		</div>
	</div>
	<?php
}


/**
 * Callback before post is submitted
 * 
 * @method voce_widgets_field_submit
 * @param type $field
 * @param type $value
 * @param type $post_id
 * @return void
 *
 */

function voce_widgets_field_submit( $field, $value, $post_id ) {
	global $post;
	$sidebar_id	= Voce_Post_Meta_Widgets::WIDGET_ID_PREFIX . $field->get_input_id() . '_post_id_' . $post->ID;
	$sidebars 	= get_option( Voce_Post_Meta_Widgets::SIDEBAR_OPTION_NAME );
	$sidebars[] = $sidebar_id;
	$sidebars 	= array_unique( $sidebars );
	$sidebars 	= Voce_Post_Meta_Widgets::clean_sidebars( $sidebars );
	update_option( Voce_Post_Meta_Widgets::SIDEBAR_OPTION_NAME, $sidebars );
}


add_action('init', function(){
	if ( class_exists( 'Voce_Meta_API' ) ) {
		Voce_Post_Meta_Widgets::initialize();	
	}
}, 1);



/**
 * Check if Voce Post Meta is loaded
 * @method check_voce_meta_api
 * @return void
 */
function voce_meta_widgets_check_voce_meta_api() {
	if ( !class_exists('Voce_Meta_API')) {
  		add_action('admin_notices', 'voce_meta_widgets_voce_meta_api_not_loaded' );
  	}
}

/**
 * Display message if Voce_Meta_API class (or Voce Post Meta plugin, more likely) is not available
 * @method voce_meta_api_not_loaded
 * @return void
 */
function voce_meta_widgets_voce_meta_api_not_loaded() {
    printf(
      '<div class="error"><p>%s</p></div>',
      __('Voce Post Meta Widgets Plugin cannot be utilized without the <a href="https://github.com/voceconnect/voce-post-meta" target="_BLANK">Voce Post Meta</a> plugin.')
    );
}		

