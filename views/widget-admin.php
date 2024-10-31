<?php
/**
 * Seentient Related Products widget admin template
 */

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');
?> 
<div id="<?php echo $this->get_field_id('fields'); ?>">
	<p><label for="<?php echo $this->get_field_id('title'); ?>">Title</label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['title'])); ?>" /></p>
</div>