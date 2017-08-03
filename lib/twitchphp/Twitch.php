<?php
/**
 * Twitch
 * 
 * Simple wrapper for Twitch.tv API functions
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.5
 */


class Twitch {
  
  public static $client_id;
  
  public static $client_secret;
    
  // Constructor    
  function __construct( $client_id, $client_secret ) {    
    
    self::$client_id = $client_id;
    
    self::$client_secret = $client_secret;

    return;
    
  }
  

  public function get_api_url( ) {
    
    $api_base = "https://api.twitch.tv/kraken";
    
    return $api_base;
    
  }
  
  
  public function get_video( $video_id ) {   
      
    $url = sprintf(
      $this->get_api_url().'/videos/%1$s',
      urlencode($video_id)
    );
    
    $video = $this -> simple_curl($url);
    
    $video = json_decode( $video, true );
    
    return $video;
    
  }
  
  public function search_channels( $args ) {
    
    $query = $args['query'];
    
    $limit = $args['limit'];
    
    $offset = $args['offset'];    
      
    $url = sprintf(
      $this->get_api_url().'/search/channels?q=%1$s&limit=%2$d&offset=%3$d&client_id=%4$s',
      urlencode($query),
      intval($limit),
      intval($offset),
      urlencode(self::$client_id)
    );
        
    $channels = $this -> simple_curl($url);
    
    $channels = json_decode( $channels, true );
    
    return $channels;
    
  }
  
  
  public function get_channel_videos( $channel, $args) {
    
    $limit = $args['limit'];
    
    $offset = $args['offset'];  
      
    $url = sprintf(
      $this->get_api_url().'/channels/%1$s/videos?broadcasts=true&limit=%2$d&offset=%3$d&client_id=%4$s',
      urlencode($channel),
      intval($limit),
      intval($offset),
      urlencode(self::$client_id)
    );
    
    $videos = $this -> simple_curl($url);
    
    $videos = json_decode( $videos, true );  
      
    $videos['limit'] = $limit;  
    
    return $videos;
    
  }
  
  function simple_curl( $url ){
    
    $ch = curl_init( $url );
   
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        
    $result = curl_exec($ch);
 
    curl_close($ch);
    
    return $result;
    
  }

}
?>