<?php
/**
 * 
 * @author 	Joakim Jarsäter
 * @todo 	Cache RSS Reader
 */
class adminSimplesignup {
		
	protected $tablename = 'jj_simplesignup';
	
	protected $message;
	
	protected $feed;
	
	function __construct() {
		$this->adminSimplesignup();	
	}
	
	
	
	function adminSimplesignup() {
		
		$feed = new SimplePie();
		$feed->set_feed_url("http://blog.simplesignup.se/feed");
		$feed->set_cache_location( WP_PLUGIN_DIR . '/jj-simple-signup/tmp/');
		$feed->set_cache_duration(7200);
		$feed->init();
		$feed->handle_content_type();
		$numOfEntries = 4;
		for($i = 0; $i < $numOfEntries; $i++)
		{
			$this->feed[] = $feed->get_item($i);
		}
		
		if(isset($_GET['delete']))
			$this->adminDelete($_GET['simplesignup-id']);
	} // adminSimplesignup
	
	function adminSettings() {
		global $wpdb;
		if(isset($_POST['jj_simplesignup_new_submit'])) {
			$data = array( $_POST['jj_simplesignup_new_id'],	$_POST['jj_simplesignup_new_token']	);
			if(!empty($data[0]) || !empty($data[1])) {
				$sql = $wpdb->prepare('INSERT INTO ' . $wpdb->prefix . $this->tablename . '(simplesignup_id, simplesignup_token) VALUES (%d, %s)', $data);
				$wpdb->query( $sql );
				$this->message = __('New event added successfully!', 'jj-simplesignup');
			} else {
				$this->message = __('Please enter values for ID and Token', 'jj-simplesignup');
			}
		} // New event
		if(isset($_POST['jj_simplesignup_print_guestlist_default_submit'])) {
			$this->message = __('Default value changed', 'jj-simplesignup');
			update_option('jj_simplesignup_print_guestlist', $_POST['jj_simplesignup_print_guestlist_default_val']);
		} // Print guestlist as default

		$myEvents = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . $this->tablename);
		?>
		<?php if($this->message): ?>
			<div class="updated fade" id="message"><p><strong><?php echo $this->message; ?></strong></p></div>
		<?php endif; ?>
			<h3><?php _e('Add'); ?></h3>
			<form name="jj_simplesignup_new_form" method="post" action="">
				<input type="hidden" name="action" id="action" value="jj_simplesignup_new_form" />
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="jj_simplesignup_new_id"><?php _e('Simple Signup ID', 'jj-simplesignup')?></label></th>
							<td><input type="text" class="regular-text" value="" id="jj_simplesignup_new_id" name="jj_simplesignup_new_id"></td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="jj_simplesignup_new_token"><?php _e('Simple Signup Token', 'jj-simplesignup')?></label></th>
							<td><input type="text" class="regular-text" value="" id="jj_simplesignup_new_token" name="jj_simplesignup_new_token"></td>
						</tr>
						<tr valign="top">
							<th scope="row">&nbsp;</th>
							<td><input type="submit" class="button-primary" value="<?php _e('Add'); ?>" id="jj_simplesignup_new_submit" name="jj_simplesignup_new_submit"></td>
						</tr>
					</tbody>
				</table>
			</form>
			<hr />
			<h3><?php _e('Other settings', 'jj-simplesignup'); ?></h3>
			<form name="jj_simplesignup_print_guestlist_default" method="post" action="">
				<input type="hidden" name="action" id="action" value="jj_simplesignup_print_guestlist_default" />
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="jj_simplesignup_print_guestlist_default_val"><?php _e('Print guestlist as default', 'jj-simplesignup')?></label></th>
							<td><input type="checkbox" class="" value="1" <?php echo get_option('jj_simplesignup_print_guestlist') ? 'checked="checked"' : ''; ?>" id="jj_simplesignup_print_guestlist_default_val" name="jj_simplesignup_print_guestlist_default_val"></td>
						</tr>
						<tr valign="top">
							<th scope="row">&nbsp;</th>
							<td><input type="submit" class="button-primary" value="<?php _e('Save'); ?>" id="jj_simplesignup_print_guestlist_default_submit" name="jj_simplesignup_print_guestlist_default_submit"></td>
						</tr>
					</tbody>
				</table>
			</form>
	<?php } // adminSimplesignupSettings
	
	function adminListEvents() {
		global $wpdb;
		$myEvents = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . $this->tablename);
		?>
		<?php if($this->message): ?>
			<div class="updated fade" id="message"><p><strong><?php echo $this->message; ?></strong></p></div>
		<?php endif; ?>
		<div class="tablenav">
			<div class="tablenav-pages">
				<span class="displaying-num">Visar 1&ndash;2 av 17</span><span class="page-numbers current">1</span>
				<a href="#" class="page-numbers">2</a>
				<a href="#" class="page-numbers">3</a>
				<span class="page-numbers dots">...</span>
				<a href="#" class="page-numbers">9</a>
				<a href="#" class="next page-numbers">»</a>
			</div>
			<div class="clear"></div>
		</div>
		<table class="widefat fixed" cellspacing="0">
			<thead>
				<tr>
					<th id="cb" class="column-cb check-column"><input type="checkbox"></th>
					<th><?php _e('Name', 'jj-simplesignup'); ?></th>
					<th><?php _e('ID', 'jj-simplesignup'); ?></th>
					<th><?php _e('Token', 'jj-simplesignup'); ?></th>
					<th><?php _e('Sold tickets', 'jj-simplesignup'); ?></th>
					<th><?php _e('Date', 'jj-simplesignup'); ?></th>
					<th><?php _e('Shortcode', 'jj-simplesignup'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th id="cb" class="column-cb check-column"><input type="checkbox"></th>
					<th><?php _e('Name', 'jj-simplesignup'); ?></th>
					<th><?php _e('ID', 'jj-simplesignup'); ?></th>
					<th><?php _e('Token', 'jj-simplesignup'); ?></th>
					<th><?php _e('Sold tickets', 'jj-simplesignup'); ?></th>
					<th><?php _e('Date', 'jj-simplesignup'); ?></th>
					<th><?php _e('Shortcode', 'jj-simplesignup'); ?></th>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach( $myEvents as $event ): ?>
				<?php $thisEvent = new Simplesignup($event->simplesignup_id, $event->simplesignup_token);?>
				<?php $thisEvent = $thisEvent->getData(); ?>
				<tr>
					<th class="check-column"><input type="checkbox" value="<?php echo $event->id; ?>" name="post[]"></th>
					<td>
						<?php echo $thisEvent->event->name; ?> / <?php echo $thisEvent->event->organizer; ?>
						<div class="row-actions">
							<span class="edit"><a href="#"><?php _e('Edit'); ?></a> &Iota;</span>
							<span class="trash"><a href="admin.php?page=jj-simplesignup-menu-listevents&delete=true&simplesignup-id=<?php echo $event->id?>"><?php _e('Remove'); ?></a> &Iota;</span>
							<span class="view"><a href="#"><?php _e('View'); ?></a></span>
						</div>
					</td>
					<td><?php echo $event->simplesignup_id; ?></td>
					<td><?php echo $event->simplesignup_token; ?></td>
					<td><?php echo count($thisEvent->event->tickets); ?>
					<td><?php echo date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($thisEvent->event->start_time)); ?></td>
					<td>[jj-simplesignup-event id="<?php echo $event->id; ?>"]</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
		<?php
	} // adminListEvents
	
	public function adminInformation() { 
	global $wpdb;
	$e = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'jj_simplesignup WHERE 1 ORDER BY id DESC');
	$myEvent = new Simplesignup($e->simplesignup_id, $e->simplesignup_token);
	?>
		<div class="jj-simplesignup-overview" id="dashboard-widgets-wrap">
			<div class="ngg-overview" id="dashboard-widgets-wrap">
				<div class="metabox-holder" id="dashboard-widgets">
					<div id="post-body">
						<div id="dashboard-widgets-main-content">
							<div style="width: 75%;" class="postbox-container">
								<div class="meta-box-sortables ui-sortable" id="left-sortables">
									<div id="dashboard_right_now" class="postbox ">
										<h3 class="hndle"><span><?php _e('My next event'); ?></span></h3>
										<div class="inside">
											<div class="table table_content">
												<p class="sub"><?php _e('Event', 'jj-simplesignup'); ?></p>
												<table>
													<tbody>
														<tr class="first">
															<td class="first b b-posts"><a href="#"><?php _e('Date and time', 'jj-simplesignup'); ?></a></td>
															<td class="t posts"><a href="#"></a><?php echo $myEvent->eventTime(); ?></td>
														</tr>
														<tr>
															<td class="first b b_pages"><a href="#"><?php _e('Address', 'jj-simplesignup'); ?></a></td>
															<td class="t pages"><a href="#"></a><?php echo $myEvent->eventAddress(); ?></td>
														</tr>
														<tr>
															<td class="first b b-cats"><a href="#"><?php _e('Host', 'jj-simplesignup');?></a></td>
															<td class="t cats"><a href="#"></a><?php echo $myEvent->eventHost(); ?></td>
														</tr>
													</tbody>
												</table>
												<?php echo $myEvent->eventDescription(); ?>
											</div>
											<div class="table table_discussion">
												<p class="sub"><?php _e('Guestlist', 'jj-simplesignup'); ?></p>
												<?php echo $myEvent->eventAttendees(); ?>
											</div>
											<br class="clear">
										</div>
									</div>
								</div>	
							</div>
							
							<div style="width: 24%;" class="postbox-container">
								<div class="meta-box-sortables ui-sortable" id="left-sortables">
									<div id="dashboard_right_simplesignup_rss" class="postbox ">
										<h3 class="hndle"><span><?php _e('Simple Signup RSS'); ?></span></h3>
										<div class="inside">
											<ul>
												<?php foreach( $this->feed as $entry ): ?>
												<li>
													<h4><a title="<?php echo strip_tags($entry->get_content()); ?>" class="rsswidget" href="<?php echo $entry->get_link(); ?>" target="_blank"><?php echo $entry->get_title();?></a></h4>
													<div class="rssSummary">
														<?php echo strip_tags(substr($entry->get_content(), 0, 150)); ?>[...]
													</div>
													<span><?php echo date(get_option('date_format') . ', ' . get_option('time_format'), strtotime($entry->get_date())); ?></span>
												</li>
												<?php endforeach; ?>
											</ul>
										</div>								
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php }
	
	public function adminDelete($id) {
		global $wpdb;
		$sql = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . $this->tablename . ' WHERE id = %d', array($id));
		$wpdb->query($sql);
		$this->message = __('Event has been removed', 'jj-simplesignup');
	} // adminDelete
		
}