<?php
/*
  Plugin Name: Voce Post Meta Widgets
  Plugin URI: http://vocecommunications.com
  Description: Extend Voce Post Meta with widget fields
  Version: 0.3
  Author: matstars, markparolisi, banderon, voceplatforms
  Author URI: http://vocecommunications.com
  License: GPL2
 */

/**
 *
 * @class Voce_Post_Meta_Widgets
 * Class notes:
 *    Filters:
 *    voce_post_meta_widgets_post_types - post types that have widget area added (defaults to none)
 *    voce_post_meta_widgets_widget_choices_location - location of metabox for widget choices (defaults to side)
 *    voce_post_meta_widgets_widget_choices_priority - priority of metabox for widget choices (defaults to low)
 *
 */
class Voce_Post_Meta_Widgets {

	/**
	 * Prefix for the sidebar name
	 * e.g. WIDGET_ID_PREFIX . meta_name . _post_id_ . $post_id
	 */
	const WIDGET_ID_PREFIX = "voce_post_meta_widgets_";
	/**
	 * Holds all of the custom sidebar data
	 */
	const SIDEBAR_OPTION_NAME = "voce_post_meta_sidebars";

	/**
	 * @method initialize
	 */
	public static function initialize() {
		add_action( 'init', array( __CLASS__, 'check_voce_meta_api' ) );
	}

	/**
	 * Check if Voce Post Meta is loaded
	 * If it is, bootstrap the plugin
	 * @method check_voce_meta_api
	 * @return void
	 */
	public static function check_voce_meta_api() {
		if ( class_exists( 'Voce_Meta_API' ) ) {
			self::bootstrap();
		} else {
			add_action( 'admin_notices', function () {
				printf( '<div class="error"><p>%s</p></div>', __( 'Voce Post Meta Widgets Plugin cannot be utilized without the <a href="https://github.com/voceconnect/voce-post-meta" target="_BLANK">Voce Post Meta</a> plugin.' ) );
			} );
		}
	}

	/**
	 * Register all of our action and filter callbacks
	 *
	 * @method bootstrap
	 */
	protected static function bootstrap() {
		require_once( ABSPATH . '/wp-admin/includes/widgets.php' );
		add_action( 'widgets_init', array( __CLASS__, 'register_sidebars' ) );
		add_action( 'load-widgets.php', array( __CLASS__, 'hide_sidebars' ) );
		add_filter( 'meta_type_mapping', array( __CLASS__, 'meta_type_mapping' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'action_add_meta_boxes' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'action_admin_enqueue_scripts' ) );
	}

	/**
	 * Register all of our custom sidebars
	 * @method register_sidebars
	 */
	public static function register_sidebars() {
		$sidebars = get_option( Voce_Post_Meta_Widgets::SIDEBAR_OPTION_NAME );
		if ( is_array($sidebars) )
			$sidebars = array_filter($sidebars);

		if ( is_array( $sidebars ) && !empty( $sidebars ) ) {
			foreach ( $sidebars as $sidebar ) {
				$args = array(
					'name'          => $sidebar,
					'id'            => $sidebar,
					'description'   => 'Drag &amp; Drop Widgets from the Right Sidebar to Below',
					'before_widget' => '<li id="%1$s" class="widget %2$s">',
					'after_widget'  => '</li>',
					'before_title'  => '<h2 class="widgettitle">',
					'after_title'   => '</h2>'
				);
				register_sidebar( $args );
			}
		}
	}

	/**
	 *
	 * Set the callbacks for the display and save methods on Voce Post Meta API
	 * @method meta_type_mapping
	 *
	 * @param type $mapping
	 *
	 * @return array
	 */
	public static function meta_type_mapping( $mapping ) {
		$mapping[ 'widgets' ] = array(
			'class' => 'Voce_Meta_Field',
			'args'  => array(
				'display_callbacks'  => array( 'voce_widgets_field_display' ),
				'sanitize_callbacks' => array( 'voce_widgets_field_submit' )
			)
		);
		return $mapping;
	}

	/**
	 * Create the metaboxes for the available widgets
	 *
	 * @param $post_type
	 * @param $post
	 */
	public static function action_add_meta_boxes( $post_type, $post ) {
		$post_types = apply_filters( 'voce_post_meta_widgets_post_types', array() );
		if ( in_array( $post_type, $post_types ) ) {
			$location = apply_filters( 'voce_post_meta_widgets_widget_choices_location', 'side' );
			$priority = apply_filters( 'voce_post_meta_widgets_widget_choices_priority', 'low' );
			$callback = array(
				__CLASS__,
				'sidebar_admin_metabox'
			);
			add_meta_box( 'sidebar_admin', 'Sidebar Admin', $callback, $post_type, $location, $priority );

			$callback = array(
				__CLASS__,
				'hidden_widgets_metabox'
			);
			add_meta_box( 'voce_widgets_hidden', 'Widgets Hidden', $callback, $post_type, $location, $priority, $post );
		}
	}

	/**
	 * Generate HTML for meta box
	 *
	 * @method sidebar_admin_metabox
	 * @global Object $post
	 * @return void
	 */
	public static function sidebar_admin_metabox() {
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
	 * Display all of the widgets in a hidden metabox
	 *
	 * @method hidden_widgets_metabox
	 */
	public static function hidden_widgets_metabox( $post ) {
		$widgets = wp_get_sidebars_widgets();
		$sidebar_ids = array_keys( $widgets );
		?>
		<div class="hidden-field">
			<?php
			foreach ( $sidebar_ids as $sidebar ) :
				if ( strpos( $sidebar, "post_id_" . $post->ID ) === false ) :
					?>
					<div class="sidebar widget-droppable widget-list" id="<?php echo $post->post_name; ?>_0">
						<?php Voce_Post_Meta_Widgets::get_active_widgets( $sidebar ); ?>
					</div>
				<?php
				endif;
			endforeach;
			?>
		</div>
		<?php
		add_filter( 'postbox_classes_post_voce_widgets_hidden', function ( $classes ) {
			return array_merge( $classes, array( 'hidden-field' ) );
		} );
	}

	/**
	 * Hide Custom Sidebars on Widgets.php
	 *
	 * @method hide_sidebars
	 * @return void
	 */
	public static function hide_sidebars() {
		global $pagenow, $wp_registered_sidebars;
		if ( is_admin() && 'widgets.php' === $pagenow ) {
			foreach ( $wp_registered_sidebars as $sidebar ) {
				if ( false !== strpos( $sidebar[ 'id' ], self::WIDGET_ID_PREFIX ) ) {
					unregister_sidebar( $sidebar[ 'id' ] );
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
	 *
	 * @return void
	 */
	public static function action_admin_enqueue_scripts( $hook ) {
		$pages = apply_filters( 'voce_post_meta_widgets_scripts', array( 'post-new.php', 'post.php', 'edit.php' ) );
		if ( in_array( $hook, $pages ) ) {
			wp_enqueue_script( 'voce-post-meta-widgets', self::plugins_url( '/js/voce-post-meta-widgets.min.js', __FILE__ ), array(
				'jquery-ui-sortable',
				'jquery-ui-draggable',
				'jquery-ui-droppable'
			) );
			wp_enqueue_style( 'voce-post-meta-widgets', self::plugins_url( '/css/voce-post-meta-widgets.min.css', __FILE__ ) );
		}
	}

	/**
	 *
	 * Allow this plugin to live either in the plugins directory or inside
	 * the themes directory.
	 *
	 * @method plugins_url
	 * @param type $relative_path
	 * @param type $plugin_path
	 *
	 * @return string
	 */
	public static function plugins_url( $relative_path, $plugin_path ) {
		$template_dir = get_template_directory();
		foreach ( array( 'template_dir', 'plugin_path' ) as $var ) {
			$$var = str_replace( '\\', '/', $$var ); // sanitize for Win32 installs
			$$var = preg_replace( '|/+|', '/', $$var );
		}
		if ( 0 === strpos( $plugin_path, $template_dir ) ) {
			$url = get_template_directory_uri();
			$folder = str_replace( $template_dir, '', dirname( $plugin_path ) );
			if ( '.' != $folder ) {
				$url .= '/' . ltrim( $folder, '/' );
			}
			if ( !empty( $relative_path ) && is_string( $relative_path ) && strpos( $relative_path, '..' ) === false ) {
				$url .= '/' . ltrim( $relative_path, '/' );
			}
			return $url;
		} else {
			return plugins_url( $relative_path, $plugin_path );
		}
	}

	/**
	 *
	 * Clean sidebars, removes any sidebars that are empty
	 *
	 * @method clean_sidebars
	 * @param type $plugin_sidebars
	 *
	 * @return array
	 *
	 */
	public static function clean_sidebars( $plugin_sidebars ) {
		if ( !empty( $plugin_sidebars ) ) {
			$all_widgets = wp_get_sidebars_widgets();
			foreach ( $all_widgets as $sidebar_name => $widgets ) {
				if ( in_array( $sidebar_name, $plugin_sidebars ) && empty( $widgets ) ) {
					unset( $plugin_sidebars[ $sidebar_name ] );
				}
			}
		}
		return $plugin_sidebars;
	}

	/**
	 *
	 * Get Active Widgets, renders widget list
	 *
	 * @method get_active_widgets
	 * @param type $sidebar
	 *
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
 *
 * @return void
 */
if ( !function_exists( 'voce_widgets_field_display' ) ) {
	function voce_widgets_field_display( $field, $value, $post_id ) {
		$value_post = get_post( $value );
		$sidebar_id = Voce_Post_Meta_Widgets::WIDGET_ID_PREFIX . $field->get_input_id() . '_post_id_' . $post_id;
		?>
		<input type="hidden" id="voce-post-widgets-exist" value="true"/>
		<div class="column-2 voce-post-meta-widget-drop">
			<?php voce_field_label_display( $field ); ?>
			<div class="sidebar voce-post-meta-widget-wrap">
				<div class="column-2-widgets">
					<div class="sidebar widget-droppable widget-list" id="<?php echo $value_post->post_name; ?>_0">
						<?php Voce_Post_Meta_Widgets::get_active_widgets( $sidebar_id ); ?>
					</div>
				</div>
			</div>
			<?php echo !empty( $field->description ) ? ('<span class="description">' . wp_kses( $field->description, Voce_Meta_API::GetInstance()->description_allowed_html ) . '</span>') : ''; ?>
		</div>
	<?php
	}
}

/**
 * Callback before post is submitted
 *
 * @method voce_widgets_field_submit
 * @param type $field
 * @param type $value
 * @param type $post_id
 *
 * @return void
 *
 */
if ( !function_exists( 'voce_widgets_field_submit' ) ) {
	function voce_widgets_field_submit( $field, $value, $post_id ) {
		if ( empty( $post_id ) ) {
			global $post;
			$post_id = $post->ID;
		}
		$sidebar_id = Voce_Post_Meta_Widgets::WIDGET_ID_PREFIX . $field->get_input_id() . '_post_id_' . $post_id;
		$sidebars = get_option( Voce_Post_Meta_Widgets::SIDEBAR_OPTION_NAME );
		$sidebars = array_unique(array_merge( (array) $sidebars, array( $sidebar_id ) ));
		$sidebars = Voce_Post_Meta_Widgets::clean_sidebars( $sidebars );
		update_option( Voce_Post_Meta_Widgets::SIDEBAR_OPTION_NAME, $sidebars );
	}
}

Voce_Post_Meta_Widgets::initialize();