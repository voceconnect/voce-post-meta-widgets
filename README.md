Voce Post Meta Widgets
==================

Contributors: matstars, voceplatforms
Tags: post, meta, widgets, widget area  
Requires at least: 3.3
Tested up to: 3.6  
Stable tag: 0.1.0  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html


## Description
Extend Voce Post Meta with widget fields

## Installation

### As standard plugin:
> See [Installing Plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

### As theme or plugin dependency:
> After dropping the plugin into the containing theme or plugin, add the following:
```php
if( ! class_exists( 'Voce_Post_Meta_Widgets' ) ) {
	require_once( $path_to_voce_post_meta_widgets . '/voce-post-meta-widgets.php' );
}
```

## Usage

#### Example

```php
<?php
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

?>
```

**1.0**  
*Initial version.*