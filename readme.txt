=== Voce Meta Post Meta Widgets ===  
Contributors: matstars, markparolisi, banderon, voceplatforms  
Tags: post, meta, widgets, widget area  
Requires at least: 3.5  
Tested up to: 4.0  
Stable tag: 1.0.0  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Extend Voce Post Meta to add widget areas into posts

== Installation ==

1. Upload `voce-post-meta-widgets` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Example ==  
```
add_action('init', function() {
	if ( class_exists('Voce_Meta_API') ) {
		add_metadata_group( 'demo_meta', 'Page Options', array(
			'capability' => 'edit_posts'
		));
		add_metadata_field( 'demo_meta', 'demo_widgets', 'Demo Widget Area', 'widgets' );

		add_post_type_support( 'post', 'demo_meta' );
	}
});
```

== Display the sidebar on a page ==  
```
if( class_exists( 'Voce_Post_Meta_Widgets' ) && !dynamic_sidebar( Voce_Post_Meta_Widgets::WIDGET_ID_PREFIX . $group_name . '_' . $field_name . '_post_id_' . get_the_ID() )): endif;
```

== Changelog ==  

= 1.0.0 =  
* Initial release