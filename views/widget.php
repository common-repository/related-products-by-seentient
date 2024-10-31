<?php
/**
 * Show widget
 * See the readme.txt file for more info.
 */

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

echo $before_widget;

$seent_show_sidebar_widget = get_option('seentient_show_sidebar_widget');	

if (
$seent_show_sidebar_widget == 'yes' and
(
	(is_single()) or
	(is_page()) or
	0
)
) { 

	if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }

	echo $html;

}
echo $after_widget;
?>