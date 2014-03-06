=== Voce Meta Post Meta Widgets ===
Contributors: matstars, voceplatforms
Donate link: 
Tags: 
Requires at least: 3.3
Tested up to: 3.8.1
Stable tag: 0.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extend Voce Post Meta with widget fields

== Description ==

Add widget area into meta elements

== Installation ==


1. Upload `voce-post-meta-widgets` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create post meta fields like this
```
$post_types = array( "page" );
add_action('init', function() use ( $post_types ) {
	add_metadata_group( 'demo_meta', 'Page Options', array(
		'capability' => 'edit_posts'
	));
	add_metadata_field( 'demo_meta', 'demo_widgets', 'Demo widget Area', 'widgets' );
	foreach( $post_types as $post_type) {
		add_post_type_support( $post_type, 'demo_meta' );
	}
});
add_filter('voce_post_meta_widgets_post_types', function( $current_post_types ) use ( $post_types ) {
	if ( is_array( $post_types ) ) $post_types = array_merge( $post_types, $current_post_types );
	return $post_types;
});
```

== Changelog ==

= 0.1.1 =
* Update to check for Voce_Meta_API

= 0.1.0 =
* Initial release