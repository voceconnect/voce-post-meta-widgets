=== Voce Meta Post Widget Area ===
Contributors: matstars, voceplatforms
Donate link: 
Tags: 
Requires at least: 3.5.0
Tested up to: 3.6
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extend Voce Post Meta with widget fields

== Description ==

Add widget area into meta elements

== Installation ==


1. Upload `voce-post-widget-area` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create post meta fields like this
```
$post_types = array( "page" );
add_action('init', function() use ( $post_types ) {
	add_metadata_group( 'demo_meta', 'Page Options', array(
		'capability' => 'edit_posts'
	));
	add_metadata_field( 'demo_meta', 'demo_widget_area', 'Demo widget Area', 'widget_area' );
	foreach( $post_types as $post_type) {
		add_post_type_support( $post_type, 'demo_meta' );
	}
});
add_filter('voce_post_meta_widget_area_post_types', function( $current_post_types ) use ( $post_types ) {
	if ( is_array( $post_types ) ) $post_types = array_merge( $post_types, $current_post_types );
	return $post_types;
});
```

== Changelog ==

= 1.0 =
* Initial release