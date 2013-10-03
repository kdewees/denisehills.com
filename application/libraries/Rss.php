<?php
class Rss {
    
    private $_url;
    private $_feed_urls = array();
    private $_cache_life = 600; // 10 minutes
    private $_cache_path = '';
    private $_cache_prefix = 'rss_';
    private $_use_cache = true;
    private $_debug = false;
    private $_items_limit = 5;
    private $_raw_data;
    private $CI;
    
    public function __construct(){
        $this->CI =& get_instance();
        $this->_cache_path = $this->CI->config->item('cache_path') == '' ? BASEPATH . 'cache/' : $this->CI->config->item('cache_path');
        if(!is_dir($this->_cache_path)){
            $this->_use_cache = false;
        }
    }
    
    public function set_url($url){
        if(is_array($url)){
            $this->_feed_urls = $url;
        } else {
            $this->_feed_urls[] = $url;
        }
        
        if($this->_debug === true) echo 'Feed url - ' . implode(', ', $this->_feed_urls) . '<br/>';
        
        return $this;
    }
    
    public function set_cache_life($time = 0){
        $this->_cache_life = (int)$time * 60;
        if($this->_debug === true) echo 'Cache life time - ' . $this->_cache_life . 's<br/>';
        
        return $this;
    }
    
    public function set_cache_path($path){
        $this->_cache_path = $path;
        if($this->_debug === true) echo 'Cache path - ' . $this->_cache_path . '</br>';
        if(!is_dir($this->_cache_path)){
            $this->_use_cache = false;
            if($this->_debug === true) echo 'WARNING! Cache path is not a directory';   
        }
        
        return $this;
    }
    
    public function set_debug($debug = true){
        $this->_debug = $debug;
        if($this->_debug === true) echo '<em>Debug mode is ON</em><br/>';
        
        return $this;
    }
    
    public function set_items_limit($limit){
        $limit = (int)$limit;
        if($limit > 0) $this->_items_limit = $limit;
        
        return $this;
    }
    
    public function get_raw_data(){
        return $this->_raw_data;
    }
    
    private function _cache_expired(){
        $filename = $this->_cache_path . $this->_cache_prefix . md5($this->_url) . '.cache';
        
        return !(file_exists($filename) AND filemtime($filename) > time() - $this->_cache_life);
    }
    
    private function _cache_read(){
        $filename = $this->_cache_path . $this->_cache_prefix . md5($this->_url) . '.cache';
        $fh = fopen($filename, 'r');
        flock($fh, LOCK_SH);
        $cache = fread($fh, filesize($filename));
        flock($fh, LOCK_UN);
        fclose($fh);
        
        return unserialize(base64_decode($cache));
    }
    
    private function _cache_write($data){
        if($this->_use_cache){
            $filename = $this->_cache_path . $this->_cache_prefix . md5($this->_url) . '.cache';
            // check if cache not expired
            if($this->_cache_expired()){
                $data = serialize($data);
                $fh = fopen($filename, 'w+');
                flock($fh, LOCK_EX);
                ftruncate($fh, 0);
                $bytes = fwrite($fh, base64_encode($data));
                flock($fh, LOCK_UN);
                fclose($fh);
                if($this->_debug === true){
                    if($bytes === false)
                        echo 'WARNING! Cannot frite to the cache!<br/>';
                    else
                        echo 'Cache file ' . $filename . ' was written successfully. File size: ' . $bytes . ' bytes<br/>';
                }
            } else {
                echo 'SUCCESS! Cache hit with file: ' . $filename . '</br>';
            }
        }
    }
    
    public function parse(){
        $return = array();
        foreach($this->_feed_urls as $url){
            $this->_url = $url;
            if($this->_cache_expired()){
                if($this->_debug) echo 'Not in cache, try to retrieve from ' . $this->_url . '</br>';
                $xml = file_get_contents($this->_url);
                $this->_cache_write($xml);
            } else {
                if($this->_debug) echo 'This feed already cached<br/>';
                $xml = $this->_cache_read();
            }
                $xmldoc = new SimpleXMLElement($xml, LIBXML_NOCDATA);
                $this->_raw_data = $xmldoc;
                if($this->_debug) echo 'Raw RSS data: <pre>' . print_r($this->_raw_data, true) . '</pre><br/>';
                
                $items = $xmldoc->channel->item;
            
            $c = 0;
            foreach($items as $item){
                $return[] = $item;
                $c++;
                if($c == $this->_items_limit) break;
            }
        }
        
        return $return;       
    }
}