<?php
/**
 * @author		Joakim Jarsäter
 * @date		2010-10-21
 */

class Simplesignup {
	
	/**
	 * Secret token for specific
	 * Simple Signup event
	 * 
	 * @var string $token
	 */
	protected $token;
	
	/**
	 * Public ID of the event
	 * 
	 * @var int $id
	 */
	protected $id;
	
	/**
	 * Array of data
	 * 
	 * @var array $data
	 */
	protected $data;
	
	/**
	 * Default format of date and time
	 * strings, ex: 25 October 2010 14:00
	 * 
	 * @var string DATETIME_FORMAT
	 */
	protected $datetime_format = 'j F, Y H:i';
	
	/**
	 *  
	 * @param int $id
	 * @param string $token
	 */
	function __construct($id, $token) {
		
		$this->id = $id;
		$this->token = $token;
		
		$frontEndOptions =  array(
			'lifetime' => 7200, //Cache lifetime = 2 hours
			'automatic_serialization' => true
		);
		
		$backEndOptions = array('cache_dir' => realpath(dirname(__FILE__)) . '/tmp');
		
		$Cache = Zend_Cache::factory('Core', 'File', $frontEndOptions, $backEndOptions);
		
		$this->data = $Cache->load('jj_simplesignup_event_' . $this->id);
		if(!$this->data) {
			if($this->_iscurlinstalled()) { // if cURL is installed
				$ch = curl_init();  
		     	curl_setopt ($ch, CURLOPT_URL, 'http://simplesignup.se/events/' . $this->id . '/feed.json?token=' . $this->token);  
		   		curl_setopt ($ch, CURLOPT_HEADER, 0);  
		   		ob_start();  
		  	 	curl_exec ($ch);  
		   		curl_close ($ch);  
		   		$data = ob_get_contents();  
		   		ob_end_clean();  
				$this->data = json_decode($data);
			} else { // If cURL is not installet, use file_get_contets()
				$data = file_get_contents('http://simplesignup.se/events/' . $this->id . '/feed.json?token=' . $this->token);
				$this->data = json_decode($data);
			}
			$Cache->save($this->data, 'jj_simplesignup_event_' . $this->id);
		}

	}	
	
	/**
	 * @return stdClass $data
	 */
	public function getData() {		
		return $this->data;
	}
	
	/**
	 * Get information about the
	 * event in a nice looking DL
	 * 
	 * @return string $data
	 */
	public function eventInformation() {
		$data = '<dl id="information_' . strtolower($this->data->event->name) . '_' . $this->id . '" class="simplesignup_event_information">';
		$data .= '<dt>' . __('Host', 'jj-simplesignup') . '</dt>';
		$data .= '<dd>' . $this->data->event->organizer . '</dd>';
		$data .= '<dt>' . __('Address', 'jj-simplesignup') . '</dt>';
		$data .= '<dd>' . $this->data->event->location . '</dd>';
		$data .= '<dt>' . __('Date and time', 'jj-simplesignup') . '</dt>';
		$data .= '<dd>' . $this->eventTime(). '</dd>';
		$data .= '</dl>';
		
		return $data;
	}
	
	/**
	 * Get information about the
	 * event in a nice looking DL
	 * 
	 * @return string $data
	 */
	public function eventAttendees() {
		if( count($this->data->event->tickets) < 1 )
			return '<em>' . __('No sold tickets') . '</em>';
			
		$data = '<ol id="attendees_' . strtolower($this->data->event->name) . '_' . $this->id . '" class="simplesignup_event_attendees">';
		foreach( $this->data->event->tickets as $ticket ) {
			$data .= '<li>' . $ticket->attendee->first_name . ' ' . $ticket->attendee->last_name . '</li>';
		}
		$data .= '</ol>';
		
		return $data;
	}
	
	/**
	 * Get description about the
	 * 
	 * @return string $data
	 */
	public function eventDescription() {
		if($this->data->event->description)
			return '<p>' . $this->data->event->description . '</p>';
	}	
	
	/**
	 * Get the time of the event. If there is no
	 * end time, then only start time is return.
	 * 
	 * @return string $start_time || $start_time - $end_time
	 */
	public function eventTime() {
		$start_time = date($this->datetime_format, strtotime($this->data->event->start_time));

		if(!empty($this->data->event->end_time)) {
			$end_time = date($this->datetime_format, strtotime($this->data->event->end_time));
			
			return $start_time . ' - ' . $end_time;
		}
		
		return $start_time;
	}
	
	/**
	 * Set the date time output format
	 */
	public function setDateTimeFormat($format) {
		$this->datetime_format = $format;
	}
	
	/**
	 * Fetch location
	 */
	public function eventAddress() {
		return $this->data->event->location;
	}
	
	/**
	 * Fetch host
	 */
	public function eventHost() {
		return $this->data->event->organizer;
	}
	
	/**
	 * Check if cURL is insalled
	 */
	private function _iscurlinstalled() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return true;
		}
		else{
			return false;
		}
	}
}