<?php
require_once 'class.Simplesignup.php';

class JJ_Simplesignup_Widget extends WP_Widget {
			
	function JJ_Simplesignup_Widget( ) {
		global $wpdb;
		$this->WP_Widget( 'jj_simplesignup', 'Simple Signup', array( 'description' => __('This is my widget description', 'jj-simplesignup') ), $control_options );
		
		$this->db = $wpdb;
		$this->data = $this->db->get_results('SELECT * FROM ' . $this->db->prefix . 'jj_simplesignup');
	}  // JJ Simple Signup Widget
	
	public function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'id' => '' ) );
		
		$event = $this->db->get_row('SELECT * FROM ' . $this->db->prefix . 'jj_simplesignup WHERE id = ' . $instance['id'] . ' LIMIT 1');

		$myEvent = new Simplesignup($event->simplesignup_id, $event->simplesignup_token);
		$myEvent = $myEvent->getData();

		echo $before_widget;
		echo $before_title . $instance['title'] . $after_title; ?>
		<?php if( $myEvent->event ): ?>
		<div id="jj_simplesignup_widget">
			<dl>
				<dt><?php _e('Host', 'jj-simplesignup'); ?></dt>
				<dd><?php echo $myEvent->event->name; ?> <?php echo $myEvent->event->organizer; ?></dd>
				<dt><?php _e('Address', 'jj-simplesignup'); ?></dt>
				<dd><?php echo $myEvent->event->location; ?></dd>
				<dt><?php _e('Date and time', 'jj-simplesignup'); ?></dt>
				<dd><?php echo date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($myEvent->event->start_time)); ?></dd>
				<dt>&nbsp;</dt>
				<dd><a class="jj_simplesignup_widget_button" href="http://simplesignup.se/event/<?php echo $instance['id']; ?>"><?php _e('Buy ticket', 'jj-simplesignup'); ?></a></dd>
			</dl>
		</div>
		<?php else: ?>
		<div id="jj_simplesignup_widget">
			<dl>
				<dt><?php _e('Error', 'jj-simplesignup'); ?></dt>
				<dd><?php _e('Error loading event data', 'jj-simplesignup'); ?></dd>
			</dl>
		</div>
		<?php endif;
		echo $after_widget;
	} // widget
	
	public function form($instance) { 
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'id' => '' ) );
		$title = strip_tags($instance['title']);
		
		$myEvents = $this->db->get_results('SELECT * FROM ' . $this->db->prefix . 'jj_simplesignup ORDER BY id DESC LIMIT 15');
		?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'jj-simplesignup')?>
				<input type="text" class="regular-text" value="<?php echo $instance['title'] ?>" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>"></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('id'); ?>"><?php _e('Display event', 'jj-simplesignup')?>
					<select id="<?php echo $this->get_field_id('id'); ?>" name="<?php echo $this->get_field_name('id'); ?>">
					<?php foreach($myEvents as $e ): ?>
						<?php 
						$event = new Simplesignup($e->simplesignup_id, $e->simplesignup_token);
						$event = $event->getData();
						?>
						<option value="<?php echo $e->id; ?>" <?php echo $instance['id'] == $e->id ? 'selected' : ''; ?>>(<?php echo $e->simplesignup_id; ?>) <?php echo $event->event->name; ?></option>
					<?php endforeach; ?>
					</select>
				</label>
			</p>

	<?php } // form
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['id'] = strip_tags($new_instance['id']);

		return $instance;
	} // update
		
}