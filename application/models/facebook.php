<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Facebook extends CI_Model {

	var $rss_url = 'http://www.facebook.com/feeds/page.php?id=144639668921129&format=rss20';

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    } //end function
    
    function get_feed()
    {
    	$this->load->library('Rss');
    	$this->rss->set_debug();
    	
    	$this->rss->set_items_limit(5); // how many items to retreive from feed
		$this->rss->set_cache_life(10); // cache life in minutes
		
		// parameter can be array or string
		$this->rss->set_url($this->rss_url);
		// return array of objects containing rss data from all feeds
		$feed = $this->rss->parse();
		
		print_r($feed);
		
		/*
		$feed_output = '';
		if (isset($feed) && count($feed) > 0)
		{
			foreach ($feed as $feed_item)
			{
				$feed_output .= anchor($feed_item->link, $feed_item->title);
				$feed_output .= $feed_item->description;
			} //end foreach
		} //end if
		
		return $feed_output;
		*/
    } //end function
} //end class