<?php
/**
 * Tube_VC_Twitch_Videos
 * 
 * Functions for searching Twitch API for videos
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.1.0
 */
    
 
class Tube_VC_Twitch_Videos {
  
  public static $instance;
  
  public static $twitch;
  
  public static function init() {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Twitch_Videos();
      return self::$instance;
  }
  
  
  // Constructor    
  function __construct() {
    
    // include the Twitch PHP Library
    require_once TUBE_VIDEO_CURATOR_DIR . '/lib/twitchphp/Twitch.php';
     
     // add embed filters to add custom twitch args
    add_filter('embed_handler_html', array( $this, 'custom_twitch_oembed_args' ) );
    add_filter('embed_oembed_html', array( $this, 'custom_twitch_oembed_args' ) ); 
 
    // add the custom oembd provider
    add_action ('init', array( $this, 'custom_twitch_oembed_handler') );
 
    // add the linkify filter to twitch content import
    add_action ('init', array( $this, 'add_linkify_filter') );

  }
  
  
  // attach the "linkify" function in the Linkify object to the 'tube_vc_filter_youtube_content_import' filter
  function add_linkify_filter() {   
    
    global $tube_video_curator;    
    
    add_filter('tube_vc_filter_twitch_content_import', array( $tube_video_curator::$tube_linkify, 'linkify'), 10, 1  );    
    
  }
  
  // get the Twitch client object
  function get_twitch_client() {      
  
    if ( self::$twitch ) :
      return self::$twitch;
    endif;

    // TODO: Try/catch here with error messaging
    self::$twitch = $this->set_twitch_client();
  
    if ( self::$twitch ) :
      return self::$twitch;
    endif;
    
  }



  // set the $twitch client object
  function set_twitch_client() {  
    //print_r($search_args);
    
    $twitch_api_key = $this -> get_twitch_api_key();
    
    // TODO: raise a proper error here
    if( ! is_array($twitch_api_key) ):
      return;
    endif;  
    
    //$lib = new \Twitch\Twitch( $twitch_api_key['client_id'], $twitch_api_key['client_secret'] );
    
    global $twitch_clientKey;
    $twitch_clientKey = $twitch_api_key['client_id'];

    global $twitch_clientSecret;
    $twitch_clientSecret = $twitch_api_key['client_secret'];

    $lib = new twitch( $twitch_clientKey, $twitch_clientSecret);
    
    return $lib;
    
  }
  
  
  
   // Get the Twitch API key if Twitch API settings are filled
  function get_twitch_api_key() {
    
    //return ;
    $client_id = get_option( 'tube_vc_twitch_client_id');
    
    $client_secret = get_option( 'tube_vc_twitch_client_secret');
    
    if ( ! $client_id || ! $client_secret ):
      
      return NULL;
      
    endif;
    
    $twitch_api_key = array(
      'client_id' => $client_id,
      'client_secret' => $client_secret,
    );
    
    return $twitch_api_key;    
   
  }
  
  
  
  
  
  // Get common Twitch search arguments from the querystring
  function get_source_search_args_from_querystring(  ){    
    
    $search_args['offset'] =  0;
    
    if ( array_key_exists('prev', $_GET) ):
      $search_args['offset'] = $_GET['prev'];
    elseif ( array_key_exists('next', $_GET) ):
      $search_args['offset'] = $_GET['next'];
    endif;
      
      
    $results_per_page = 10;
      
    if ( array_key_exists('results', $_GET) ):
    
      $results_per_page = $_GET['results'];
      
      if ( $results_per_page == 'all' ):
        $search_args['get_all'] = true;
        $results_per_page = 50;
      else:
        // get requested amount, can't be more than 50
        $results_per_page = min( array( intval($results_per_page), 50) ) ;
      endif;
            
    endif;    
       
    $search_args['limit'] = $results_per_page;
    
    return $search_args;
    
  }
  
  
 
 
  // Get videos for a Twitch Channel (i.e. Playlist)
  
  function get_twitch_channel_videos_via_api( $twitch_channel_guid, $args = NULL, $orig_videos = NULL ) {
    
    $defaults =   array(
      'limit' => get_option('posts_per_page'),
      'offset' => 0
    );
        
    $search_args = wp_parse_args( $args, $defaults );
    
    //$get_all =  $search_args['get_all'];
    
    //unset($search_args['get_all']);    
    
    $transient_key = 'tube-vim-srch-chnl-' . md5($twitch_channel_guid . serialize($search_args) );
        
    $channel_videos = get_transient( $transient_key ); 
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
    
    // if no results from tranient, or flushing tranient        
    if( empty( $channel_videos ) || $flush_transient ):    
      
      //echo 'CALLING VIMEO<br />';
      
      $twitch = $this -> get_twitch_client();
    
      if ( ! $twitch ):
        _e( 'Error retrieving Twitch API client.', 'tube-video-curator' );
        return;
      endif;
        
      // TODO: Add a try/catch block here like the YouTube version
       $twitch_response =  $twitch -> get_channel_videos($twitch_channel_guid, $search_args, 'GET');
  
      
      //return;
      if (  array_key_exists('error', $twitch_response) ):
        
        echo esc_html( $twitch_response['message'] );
        
        return;
        
      endif;
      
      if ( ! array_key_exists('videos', $twitch_response) ):
        
        _e('Error retrieving Twitch results.', 'tube-video-curator');
        
        return;
        
      endif;
      
        
      $channel_videos = $this -> format_twitch_response_data($twitch_response);
      
  
      //print_r($channel_videos);
      
      // Save the API response so we don't have to call again until tomorrow.
      set_transient( $transient_key, $channel_videos, HOUR_IN_SECONDS );
      
    endif;
       
    if ($orig_videos):
      $channel_videos['items'] = array_merge (  $orig_videos, $channel_videos['items'] );
    endif;
    
    /*
     if ( $get_all ):
       
       $channel_videos = $this -> get_twitch_channel_videos_via_api_next_page( $twitch_channel_guid, $search_args, $channel_videos);
       
     endif;
     */
     
     return $channel_videos;       
      
  }


 
  // Format basic Twitch response data
  function format_twitch_response_data( $twitch_response ){
    
    //print_r($twitch_response);
    
      if ( !$twitch_response ):
        return NULL;
      endif;
    
      if ( $twitch_response['_total'] == 0 ):
        return NULL;
      endif;
      
      $results['total_results'] = $twitch_response['_total'];
      $results['results_per_page'] = $twitch_response['limit'];
   
      
      $prev_next = $this -> get_twitch_prev_next_vals( $twitch_response );
               
      $results['prev_page_token'] = $prev_next[0];
      
      $results['next_page_token'] = $prev_next[1];
      
      $results['items'] = $this->get_structured_twitch_video_details( $twitch_response['videos']); 
      
      return $results;
    
  }
  
  
  // Function to extract previous and next values from a Twitch reponse
  function get_twitch_prev_next_vals( $twitch_response ){
       
       
      if ( ! array_key_exists( '_links', $twitch_response ) ):
        return array( NULL, NULL );
      endif;
      
     
     $previous_page = NULL;
       
     if ( array_key_exists( 'prev', $twitch_response['_links'] ) ):
       
       $previous = parse_url( $twitch_response['_links']['prev'] );
  
       if ( $previous && array_key_exists( 'query', $previous ) ):
         
         // parse the $previous['query'] into an array called $previous_query_args 
         parse_str($previous['query'], $previous_query_args);     
         
         // set the $previous_page to the 'page' within the query args
         $previous_page = $previous_query_args['offset'];
         
       endif;
     
     endif;
     
     
     $next_page = NULL;
       
     if ( array_key_exists( 'next', $twitch_response['_links'] ) ):
       
       // parse out next page argument, if any
       $next = parse_url( $twitch_response['_links']['next'] );
  
       if ( $next && array_key_exists( 'query', $next ) ):
         
         // parse the $next['query'] into an array called $next_query_args 
         parse_str($next['query'], $next_query_args);     
         
         // set the next_page to the 'page' within the query args
         $next_page = $next_query_args['offset'];
         
       endif;
     
     endif;
      
    return array( $previous_page, $next_page );
       
    
  }
    
  
  
  // Get stuctured video data from Twitch Results

  function get_structured_twitch_video_details( $videos_results ){              
       
      // Check for errors
      
      foreach ( $videos_results as $video_data_raw):
    
      //print_tl($video_data_raw);
      //return;
     
            //print_r($video_data_raw);
            
            //$video_uri = $video_data_raw['uri'];
            
            //$video_uri_parts = explode('/', $video_uri);
            
            $video_data['id'] = $video_data_raw['_id'];
            
            $video_data['title'] = $video_data_raw['title'];              
            
            
            $video_content = $video_data_raw['description'];
            
            $video_content = apply_filters( 'tube_vc_filter_content_import', $video_content );
            
            $video_content = apply_filters( 'tube_vc_filter_twitch_content_import', $video_content );
            
            $video_data['content'] = $video_content;
        
        
            $medium_image_url = $video_data_raw['thumbnails'][0]['url'];
            
            $video_data['thumb_image_url'] = $video_data_raw['preview'];
            
            
            $max_thumb_index = count( $video_data_raw['thumbnails'] ) - 1 ;            
            
            $max_image_url = $video_data_raw['thumbnails'][$max_thumb_index]['url'];
            
            // try for maxres thumbnail
            $video_data['image_url'] = $max_image_url;          
            
            
            $video_data['date'] = $video_data_raw['created_at'];
            
            // TODO :: add support for Twitch recorded_at
            
            /*
            if ( array_key_exists('tags', $video_data_raw) && ( count( $video_data_raw['tags'] ) != 0 ) ):
              
              $video_data['tags'] = array();
              
              foreach ( $video_data_raw['tags'] as $tag ) :
                $video_data['tags'][] = trim( $tag['tag'] );
              endforeach;
                
            endif;                         
            */
            
            $video_data['channel_id'] = $video_data_raw['channel']['name'];
                         
            $video_data['creator_name'] = $video_data_raw['channel']['display_name'];
            
            
            $video_data['media_url'] = $video_data_raw['url'];
            
            $video_data['embed_url'] = NULL;
    
            $video_data['site'] = 'twitch';
              
            $videos[] = $video_data;  
  
  
      endforeach;
                   
      if ( ! isset($videos) ):
        return NULL;
      endif;
      
      return $videos;

  }



  // Register a custom oembed provider for twitch
  function custom_twitch_oembed_handler(){    

    //wp_oembed_add_provider( '#http://(secure|www\.)?twitch\.tv/v.*#i', NULL, true );
    
    wp_embed_register_handler( 'twitch_embed_url', '#https://(secure|www).twitch.tv/([a-zA-Z0-9_-]+)/v/([a-zA-Z0-9_-]+)$#i', array( $this, 'twitch_embed_handler' ) );
    
    
  }
  
  function twitch_embed_handler( $matches, $attr, $url, $rawattr ) {
  
    
    $video_id = $matches[3];
    
    $height = $attr['height'];
    
    $width = $attr['width'];
    
    $embed = sprintf(
        '<div class="embed-wrap embed-twitch embed-responsive" style="padding-bottom:%1$s%%"><iframe src="https://player.twitch.tv/?video=v%2$s" frameborder="0" scrolling="no" height="%3$spx" width="%4$spx"></iframe></div>',
        esc_attr('56.239316239316'), //esc_attr(($height / $width) * 100),
        esc_attr($video_id),
        esc_attr($height),
        esc_attr($width)
        );
    
    return $embed;
    
    
  }
    
  // Customize the omebed for the Twitch player
  function custom_twitch_oembed_args($embed_code){    
    
    //var_dump($embed_code);
    //exit;
    
    // determine if it's twitch
    if( strpos($embed_code, 'twitch.tv') === false):
      
      // not twitch, do nothing
      return $embed_code;
    
    endif;  
    
    $autoplay = ( intval( get_option( 'tube_vc_player_autoplay') ) ) ? 'true' : 'false';
    
    // filter the arguments to allow changes / additions
    $twitch_args = array(
      'autoplay' => $autoplay, 
    );
    
    // filter the arguments to allow changes / additions
    $twitch_args = apply_filters( 'tube_vc_filter_oembed_args', $twitch_args );
    $twitch_args = apply_filters( 'tube_vc_filter_twitch_oembed_args', $twitch_args );
        
    // convert the arguments to a string
    $twitch_args_query_str = http_build_query($twitch_args);
    
    // append the arguments to the URL
    $embed_code = preg_replace("@src=(['\"])?([^'\">\s]*)@", "src=$1$2&" . $twitch_args_query_str, $embed_code);
    
    return $embed_code;
    
  }
   


  
  // Admin message for no domain in Add Via URL url
  function no_twitch_domain(  ){
    
    $class = 'notice notice-error';
    
    $message = __( 'Please use a URL from twitch.tv', 'tube-video-curator' );
  
    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
  
  }
  
  
  
  // Admin message for no video ID in Add Via URL url
  function no_twitch_video_id(  ){
    
    $class = 'notice notice-error';
    
    $message = __( 'No video ID found. Please use a URL like this https://www.twitch.tv/bungie/v/79073494', 'tube-video-curator' );
  
    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
  
  }
  
  
  
  // Admin message for no video details found for Add Via URL url
  function no_video_details_found(  ){
    
    $class = 'notice notice-error';
    
    $message = __( 'Sorry, this video could not be found. Please try another.', 'tube-video-curator' );
  
    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
  
  }
  
  
    
  // Get Data for a single video ID from the API
  function get_twitch_video_data_via_api( $video_id ) {
    
    $twitch = $this -> get_twitch_client();
    
    if ( ! $twitch ):
      _e( 'Error retrieving Twitch API client.', 'tube-video-curator' );
      return;
    endif;
      
    // TODO: Add a try/catch block here like the YouTube version
    $api_response =  $twitch -> get_video( $video_id );
    
    // Check for errors
    
    if (  array_key_exists('error', $api_response) ):
      
      //echo esc_html( $api_response['message'] );
      
      return;
      
    endif;

    $video_data = $this -> get_structured_twitch_video_details( array($api_response) );
      
    return $video_data;

  }
  
  
  // Grab the data for a video using a single URL
  function get_twitch_video_data_via_url( $url ){
       
    $url_parts = parse_url( $url );    
    
    
    if (     
      ! array_key_exists( 'host', $url_parts )
      || 
      ( 
        ( $url_parts['host'] != 'twitch.tv' ) && ( $url_parts['host'] != 'www.twitch.tv' ) && ( $url_parts['host'] != 'm.twitch.tv' ) 
      ) 
        ):
      
      add_action( 'admin_notices', array( $this, 'no_twitch_domain') );     
      return NULL;
      
    endif;
    
    
    if ( ! array_key_exists('path' , $url_parts) ):
      
      add_action( 'admin_notices', array( $this, 'no_twitch_video_id') );     
      return NULL;
      
    endif;
   
    
    $url_path = explode('/', $url_parts['path'] );
    
    
    $video_id = $url_path[2] . $url_path[3]; 
    
    // this sets up the client inside of this class
    $twitch = $this -> get_twitch_client();
        
    if ( ! $twitch ):
      _e( 'Error retrieving Twitch API client.', 'tube-video-curator' );
      return;
    endif;
          
    $video_details = $this -> get_twitch_video_data_via_api( $video_id );    
    
    if ( ! $video_details ):
      
      add_action( 'admin_notices', array( $this, 'no_video_details_found') );     
      return NULL;
      
    endif;
    
    return $video_details;
    
  }
    
}
