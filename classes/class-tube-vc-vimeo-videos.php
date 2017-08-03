<?php
/**
 * Tube_VC_Vimeo_Videos
 * 
 * Functions for searching Vimeo API for videos
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
    
 
class Tube_VC_Vimeo_Videos {
  
  public static $instance;
  
  public static $vimeo;
  
  public static function init() {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Vimeo_Videos();
      return self::$instance;
  }
  
  
  // Constructor    
  function __construct() {
            
    // include the Vimeo PHP Library
    require_once TUBE_VIDEO_CURATOR_DIR . '/lib/vimeo.php-1.2.3/autoload.php';
     
     // add embed filters to add custom vimeo args
    add_filter('embed_handler_html', array( $this, 'custom_vimeo_oembed_args' ) );
    add_filter('embed_oembed_html', array( $this, 'custom_vimeo_oembed_args' ) );
 
    // add the linkify filter to vimeo content import
    add_action ('init', array( $this, 'add_linkify_filter') );

  }
  
  
  // attach the "linkify" function in the Linkify object to the 'tube_vc_filter_youtube_content_import' filter
  function add_linkify_filter() {   
    
    global $tube_video_curator;    
    
    add_filter('tube_vc_filter_vimeo_content_import', array( $tube_video_curator::$tube_linkify, 'linkify'), 10, 1  );    
    
  }
  
  // get the Vimeo client object
  function get_vimeo_client() {      
  
    if ( self::$vimeo ) :
      return self::$vimeo;
    endif;

    // TODO: Try/catch here with error messaging
    self::$vimeo = $this->set_vimeo_client();
  
    if ( self::$vimeo ) :
      return self::$vimeo;
    endif;
    
  }



  // set the $vimeo client object
  function set_vimeo_client() {  
    //print_r($search_args);
    
    $vimeo_api_key = $this -> get_vimeo_api_key();
    
    // TODO: raise a proper error here
    if( ! is_array($vimeo_api_key) ):
      return;
    endif;  
    
    $lib = new \Vimeo\Vimeo( $vimeo_api_key['client_id'], $vimeo_api_key['client_secret'] );
    
        
    // use the token
    $lib->setToken( $vimeo_api_key['access_token'] );
    
    return $lib;
    
  }
  
  
  
   // Get the Vimeo API key if Vimeo API settings are filled
  function get_vimeo_api_key() {
    
    //return ;
    $client_id = get_option( 'tube_vc_vimeo_client_id');
    
    $client_secret = get_option( 'tube_vc_vimeo_client_secret');
    
    if ( ! $client_id || ! $client_secret ):
      
      // wipe the access token if there isn't an ID and secret
      delete_option( 'tube_vc_vimeo_access_token' );
      
      return NULL;
      
    endif;
      
    $access_token = $this -> get_vimeo_access_token( $client_id, $client_secret );
    
    $vimeo_api_key = array(
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'access_token' => $access_token,
    );
    
    return $vimeo_api_key;    
   
  }
  
  

  // Get the Vimeo access token
  function get_vimeo_access_token( $client_id, $client_secret ){
    
    // get the Access Token Option
    $access_token = get_option( 'tube_vc_vimeo_access_token' );
    
    // See if the access token is set
    if ( $access_token ) :
      
      // Return the access token      
      return $access_token;
      
    endif;
    
    // Need to create an access token
    $lib = new \Vimeo\Vimeo($client_id, $client_secret);
    
    // scope is an array of permissions your token needs to access. You can read more at https://developer.vimeo.com/api/authentication#scopes
    $token = $lib->clientCredentials( array( 'public' ) );
    
    // usable access token
    $access_token = $token['body']['access_token'];
    
    // Don't need to autoload the access token
    $autoload = false; 
    
    // Update the Access Token option
    update_option( 'tube_vc_vimeo_access_token', $access_token, $autoload ); 
    
    // Return the access token
    return $access_token;
    
  }
  
  
  
  // Get common Vimeo search arguments from the querystring
  function get_source_search_args_from_querystring(  ){    
    
    if ( array_key_exists('prev', $_GET) ):
      $search_args['page'] = $_GET['prev'];
    elseif ( array_key_exists('next', $_GET) ):
      $search_args['page'] = $_GET['next'];
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
       
    $search_args['per_page'] = $results_per_page;
    
    return $search_args;
    
  }
  
  
  // Function to search for videos by a querystring
  function query_vimeo_videos_via_api( $query, $args = NULL, $orig_videos = NULL ) {
    
    /*
    if ( ! $this -> get_vimeo_api_key() ):
            ?>
      <p>
        <?php _e('Vimeo API key not found.', 'tube-video-curator'); ?>
      </p>
      <?php
      return;
    endif;
    */
    
    
    $defaults =   array(
        'query' => $query,
        'per_page' => get_option('posts_per_page'),
        'sort' => 'date',
        'direction' => 'desc',
        'page' => NULL,
        'filter' => NULL,
    );
    
    $search_args = wp_parse_args( $args, $defaults );    
  
    $transient_key = 'tube-vim-srch-qry-' . md5( serialize($search_args) );        
  
    $search_videos = get_transient( $transient_key );  
    
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
        
    // if no results from tranient, or flushing tranient        
    if( empty( $search_videos ) || $flush_transient ):  
        
    
      $vimeo = $this -> get_vimeo_client();
      
      if ( ! $vimeo ):
        _e( 'Error retrieving Vimeo API client.', 'tube-video-curator' );
        return;
      endif;
        
      // TODO: Add a try/catch block here like the YouTube version
       $search_response =  $vimeo->request('/videos', $search_args, 'GET');
         
  
      // Check for errors
      
      if ( ! array_key_exists('body', $search_response) ):
        _e('Error retrieving Vimeo results.', 'tube-video-curator');
        return;
      endif;
      
      if (  array_key_exists('error', $search_response['body']) ):
        echo esc_html($search_response['body']['error']);
        return;
      endif;
          
      
      $search_videos = $this -> format_vimeo_response_data($search_response);
   
      // Save the API response so we don't have to call again for half an hour
      set_transient( $transient_key, $search_videos, HOUR_IN_SECONDS / 2 );
      
   endif;
      
   return $search_videos;

  }
  
  
  

  
  // TODO :: Get Vimeo Videos "all" working
  function get_vimeo_user_videos_via_api_next_page( $vimeo_user_guid, $args = NULL, $orig_videos ) {
    
    // grab the next page token
    $next_page_token = $orig_videos['next_page_token'];
   
    // see if there is a next page     
    if ( $next_page_token == '' ): //    CAUQAA
    
      // there is no next page, so return the original set
      return $orig_videos;
      
    endif;
      
    // set the next page token arg
    $args['pageToken'] = $next_page_token;
    
    // force get_all argument to true
    $args['get_all'] = true;
  
    // get the next page worth of videos
    $user_videos = $this->get_vimeo_user_videos_via_api( $vimeo_user_guid, $args, $orig_videos['items'] );   
    
    // return the videos results
    return $user_videos;      
      
  }
  
 
  // Get videos for a Vimeo user
  function get_vimeo_user_videos_via_api( $vimeo_user_guid, $args = NULL, $orig_videos = NULL ) {
 
    /*
    if ( ! $this -> get_vimeo_api_key() ):
      
            ?>
      <p>
        <?php _e('Vimeo API key not found.', 'tube-video-curator'); ?>
      </p>
      <?php
      return;
    endif;
    */
    
    $defaults =   array(
        'per_page' => get_option('posts_per_page'),
        'page' => NULL,
        'get_all' => false
    );
        
    $search_args = wp_parse_args( $args, $defaults );
    
    $get_all =  $search_args['get_all'];
    
    unset($search_args['get_all']);
    
    
    
    $transient_key = 'tube-vim-srch-usr-' . md5($vimeo_user_guid . serialize($search_args) );
    
    
    $user_videos = get_transient( $transient_key ); 
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
    
    // if no results from tranient, or flushing tranient        
    if( empty( $user_videos ) || $flush_transient ):  
        
      //echo 'CALLING VIMEO<br />';
      $vimeo = $this -> get_vimeo_client();
            
      if ( ! $vimeo ):
        _e( 'Error retrieving Vimeo API client.', 'tube-video-curator' );
        return;
      endif;
    
      // TODO: Add a try/catch block here like the YouTube version
       $vimeo_response =  $vimeo->request('/users/' . $vimeo_user_guid . '/videos', $search_args, 'GET');
  
      // Check for errors
      
      if ( ! array_key_exists('body', $vimeo_response) ):
        _e('Error retrieving Vimeo results.', 'tube-video-curator');
        return;
      endif;
      
      if (  array_key_exists('error', $vimeo_response['body']) ):
        echo esc_html($vimeo_response['body']['error']);
        return;
      endif;
        
      $user_videos = $this -> format_vimeo_response_data($vimeo_response);
      
  
      //print_r($user_videos);
      
      // Save the API response so we don't have to call again for an hour
      set_transient( $transient_key, $user_videos, HOUR_IN_SECONDS );
      
    endif;
       
    if ($orig_videos):
      $user_videos['items'] = array_merge (  $orig_videos, $user_videos['items'] );
    endif;
    
    
    // TODO: Get this working
     if ( $get_all ):
       
       $user_videos = $this -> get_vimeo_user_videos_via_api_next_page( $vimeo_user_guid, $search_args, $user_videos);
       
     endif;
     
     
       
     return $user_videos;
       
      
  }



    
  // TODO :: Get Vimeo Videos "all" working

  function get_vimeo_channel_videos_via_api_next_page( $vimeo_channel_guid, $args = NULL, $orig_videos ) {
    
    // grab the next page token
    $next_page_token = $orig_videos['next_page_token'];
   
    // see if there is a next page     
    if ( $next_page_token == '' ): //    CAUQAA
    
      // there is no next page, so return the original set
      return $orig_videos;
      
    endif;
      
    // set the next page token arg
    $args['pageToken'] = $next_page_token;
    
    // force get_all argument to true
    $args['get_all'] = true;
  
    // get the next page worth of videos
    $channel_videos = $this->get_vimeo_channel_videos_via_api( $vimeo_channel_guid, $args, $orig_videos['items'] );   
    
    // return the videos results
    return $channel_videos;      
      
  }

 
 
  // Get videos for a Vimeo Channel (i.e. Playlist)
  
  function get_vimeo_channel_videos_via_api( $vimeo_channel_guid, $args = NULL, $orig_videos = NULL ) {
 

  /*
    if ( ! $this -> get_vimeo_api_key() ):
      
            ?>
      <p>
        <?php _e('Vimeo API key not found.', 'tube-video-curator'); ?>
      </p>
      <?php
      
      return;
      
    endif;
    */
    
    $defaults =   array(
        'per_page' => get_option('posts_per_page'),
        //'filter' => 'embeddable',
        //'filter_embeddable' => 'true', // Creates unpredictable pagination and result counts
        'sort' => 'date',
        'direction' => 'desc',
        'page' => NULL,
        'get_all' => false
    );
        
    $search_args = wp_parse_args( $args, $defaults );
    
    $get_all =  $search_args['get_all'];
    
    unset($search_args['get_all']);    
    
    $transient_key = 'tube-vim-srch-chnl-' . md5($vimeo_channel_guid . serialize($search_args) );
        
    $channel_videos = get_transient( $transient_key ); 
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
    
    // if no results from tranient, or flushing tranient        
    if( empty( $channel_videos ) || $flush_transient ):    
      
      //echo 'CALLING VIMEO<br />';
      
       
      $vimeo = $this -> get_vimeo_client();
    
      if ( ! $vimeo ):
        _e( 'Error retrieving Vimeo API client.', 'tube-video-curator' );
        return;
      endif;
      
        
      // TODO: Add a try/catch block here like the YouTube version
       $vimeo_response =  $vimeo->request('/channels/' . $vimeo_channel_guid . '/videos', $search_args, 'GET');
  
      // Check for errors
      
      if ( ! array_key_exists('body', $vimeo_response) ):
        
        _e('Error retrieving Vimeo results.', 'tube-video-curator');
        
        return;
        
      endif;
      
      if (  array_key_exists('error', $vimeo_response['body']) ):
        
        echo esc_html($vimeo_response['body']['error']);
        
        return;
        
      endif;
        
      $channel_videos = $this -> format_vimeo_response_data($vimeo_response);
      
  
      //print_r($channel_videos);
      
      // Save the API response so we don't have to call again until tomorrow.
      set_transient( $transient_key, $channel_videos, HOUR_IN_SECONDS );
      
    endif;
       
    if ($orig_videos):
      $channel_videos['items'] = array_merge (  $orig_videos, $channel_videos['items'] );
    endif;
    
    
     if ( $get_all ):
       
       $channel_videos = $this -> get_vimeo_channel_videos_via_api_next_page( $vimeo_channel_guid, $search_args, $channel_videos);
       
     endif;
     
     return $channel_videos;       
      
  }


 
  // Format basic Vimeo response data
  function format_vimeo_response_data( $vimeo_response ){
    
    //print_r($vimeo_response);
    
    
      if ( !$vimeo_response ):
        return NULL;
      endif;
    
      if ( $vimeo_response['body']['total'] == 0 ):
        return NULL;
      endif;
      
      $results['total_results'] = $vimeo_response['body']['total'];
      $results['results_per_page'] = $vimeo_response['body']['per_page'];
   
      $prev_next = $this -> get_vimeo_prev_next_vals( $vimeo_response );
               
      $results['prev_page_token'] = $prev_next[0];
      
      $results['next_page_token'] = $prev_next[1];
      
      $results['items'] = $this->get_structured_vimeo_video_details($vimeo_response['body']['data']); 
      
      return $results;
    
  }
  
  
  // Function to extract previous and next values from a Vimeo reponse
  function get_vimeo_prev_next_vals( $vimeo_response ){
       
    if ( ! array_key_exists( 'paging', $vimeo_response['body'] ) ):
      return array( NULL, NULL );
    endif;
    

       
     // parse out prev page argument, if any
     $previous = parse_url( $vimeo_response['body']['paging']['previous'] );

     if ( $previous && array_key_exists( 'query', $previous ) ):
       
       // parse the $previous['query'] into an array called $previous_query_args 
       parse_str($previous['query'], $previous_query_args);     
       
       // set the $previous_page to the 'page' within the query args
       $previous_page = $previous_query_args['page'];
       
     else:
       
       // no previous page
       $previous_page = NULL;
       
     endif;
     
     
     // parse out next page argument, if any
     $next = parse_url( $vimeo_response['body']['paging']['next'] );

     if ( $next && array_key_exists( 'query', $next ) ):
       
       // parse the $next['query'] into an array called $next_query_args 
       parse_str($next['query'], $next_query_args);     
       
       // set the next_page to the 'page' within the query args
       $next_page = $next_query_args['page'];
       
     else:
       
       // no next page
       $next_page = NULL;
       
     endif;
      
    return array( $previous_page, $next_page );
       
    
  }
    
  
  
  // Get stuctured video data from Vimeo Results

  function get_structured_vimeo_video_details( $videos_results ){              
       
      foreach ( $videos_results as $video_data_raw):
     
            //print_r($video_data_raw);
            
            $video_uri = $video_data_raw['uri'];
            
            $video_uri_parts = explode('/', $video_uri);
            
            $video_data['id'] = $video_uri_parts[2];
            
            $video_data['title'] = $video_data_raw['name'];              
            
            
            $video_content = $video_data_raw['description'];
            
            $video_content = apply_filters( 'tube_vc_filter_content_import', $video_content );
            
            $video_content = apply_filters( 'tube_vc_filter_vimeo_content_import', $video_content );
            
            $video_data['content'] = $video_content;
        
        
            $medium_image_url = $video_data_raw['pictures']['sizes'][2]['link'];
            
            // remove querystring that vimeo passes along
            $medium_image_url = str_replace('?r=pad', '', $medium_image_url);
            
            $video_data['thumb_image_url'] = $medium_image_url;
            
            $max_thumb_index = count( $video_data_raw['pictures']['sizes'] ) - 1 ;
            
            
            $max_image_url = $video_data_raw['pictures']['sizes'][$max_thumb_index]['link'];
            
            // remove querystring that vimeo passes along
            $max_image_url = str_replace('?r=pad', '', $max_image_url);
            
            // try for maxres thumbnail
            $video_data['image_url'] = $max_image_url;          
            
            
            $video_data['date'] = $video_data_raw['created_time'];
            
            // TODO :: add support for Vimeo modified_time
            
            if ( array_key_exists('tags', $video_data_raw) && ( count( $video_data_raw['tags'] ) != 0 ) ):
              
              $video_data['tags'] = array();
              
              foreach ( $video_data_raw['tags'] as $tag ) :
                $video_data['tags'][] = trim( $tag['tag'] );
              endforeach;
                
            endif;                         
            
            $channel_uri = $video_data_raw['user']['uri'];     
             
            $channel_uri_parts = explode('/', $channel_uri);
            
            $video_data['channel_id'] = $channel_uri_parts[2]; 
                         
            $video_data['creator_name'] = $video_data_raw['user']['name'];
            
            
            $video_data['media_url'] = $video_data_raw['link'];
            
            $video_data['embed_url'] = NULL;
    
            $video_data['site'] = 'vimeo';
              
            $videos[] = $video_data;  
  
  
      endforeach;
                   
      if ( ! isset($videos) ):
        return NULL;
      endif;
      
      return $videos;

  }



  // Customize the omebed for the Vimeo player
  function custom_vimeo_oembed_args($embed_code){    
    
    // determine if it's vimeo
    if( strpos($embed_code, 'vimeo.com') === false):
      
      // not vimeo, do nothing
      return $embed_code;
    
    endif;  
    
    // filter the arguments to allow changes / additions
    $vimeo_args = array(
      'autoplay' => intval( get_option( 'tube_vc_player_autoplay') ),      
      'badge' => intval( get_option( 'tube_vc_player_showinfo') ),
      'byline' => intval( get_option( 'tube_vc_player_showinfo') ),
      'portrait' => intval( get_option( 'tube_vc_player_showinfo') ),
      'title' => intval( get_option( 'tube_vc_player_showinfo') )
      
    );
    
    // filter the arguments to allow changes / additions
    $vimeo_args = apply_filters( 'tube_vc_filter_oembed_args', $vimeo_args );
    $vimeo_args = apply_filters( 'tube_vc_filter_vimeo_oembed_args', $vimeo_args );
        
    // convert the arguments to a string
    $vimeo_args_query_str = http_build_query($vimeo_args);
    
    // append the arguments to the URL
    $embed_code = preg_replace("@src=(['\"])?([^'\">\s]*)@", "src=$1$2?" . $vimeo_args_query_str, $embed_code);
    
    return $embed_code;
    
  }
   


  
  // Admin message for no domain in Add Via URL url
  function no_vimeo_domain(  ){
    
    $class = 'notice notice-error';
    
    $message = __( 'Please use a URL from vimeo.com', 'tube-video-curator' );
  
    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
  
  }
  
  
  
  // Admin message for no video ID in Add Via URL url
  function no_vimeo_video_id(  ){
    
    $class = 'notice notice-error';
    
    $message = __( 'No video ID found. Please use a URL like this https://vimeo.com/26783966', 'tube-video-curator' );
  
    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
  
  }
  
  
  
  // Admin message for no video details found for Add Via URL url
  function no_video_details_found(  ){
    
    $class = 'notice notice-error';
    
    $message = __( 'Sorry, this video could not be found. Please try another.', 'tube-video-curator' );
  
    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
  
  }
  
  
    
  // Get Data for a single video ID from the API
  function get_vimeo_video_data_via_api( $video_id ) {
 /*
    if ( ! $this -> get_vimeo_api_key() ):
        
      ?>
      <p>
        <?php _e('Vimeo API key not found.', 'tube-video-curator'); ?>
      </p>
      <?php
      
      return;
      
    endif;
    */
    
    $vimeo = $this -> get_vimeo_client();
    
    if ( ! $vimeo ):
      _e( 'Error retrieving Vimeo API client.', 'tube-video-curator' );
      return;
    endif;
      
    // TODO: Add a try/catch block here like the YouTube version
     $api_response =  $vimeo->request('/videos/' . $video_id, NULL, 'GET');

    // Check for errors
    
    if ( ! array_key_exists('body', $api_response) ):
      
      _e('Error retrieving Vimeo results.', 'tube-video-curator');
      
      return;
      
    endif;
    
    if (  array_key_exists('error', $api_response['body']) ):
      
      echo esc_html( $api_response['body']['error'] );
      
      return;
      
    endif;

    $video_data = $this -> get_structured_vimeo_video_details( array( $api_response['body'] ) );
      
    return $video_data;

  }
  
  
  // Grab the data for a video using a single URL
  function get_vimeo_video_data_via_url( $url ){
       
    $url_parts = parse_url( $url );    
    
    
    if (     
      ! array_key_exists( 'host', $url_parts )
      || 
      ( 
        ( $url_parts['host'] != 'vimeo.com' ) && ( $url_parts['host'] != 'www.vimeo.com' ) && ( $url_parts['host'] != 'm.vimeo.com' ) 
      ) 
        ):
      
      add_action( 'admin_notices', array( $this, 'no_vimeo_domain') );     
      return NULL;
      
    endif;
    
    
    if ( ! array_key_exists('path' , $url_parts) ):
      
      add_action( 'admin_notices', array( $this, 'no_vimeo_video_id') );     
      return NULL;
      
    endif;
   
    
    $url_path = explode('/', $url_parts['path'] );
    
    
    $video_id = $url_path[1]; 
    
    // this sets up the client inside of this class
    $vimeo = $this -> get_vimeo_client();
        
    if ( ! $vimeo ):
      _e( 'Error retrieving Vimeo API client.', 'tube-video-curator' );
      return;
    endif;
          
    $video_details = $this -> get_vimeo_video_data_via_api( $video_id );
    
    
    if ( ! $video_details ):
      
      add_action( 'admin_notices', array( $this, 'no_video_details_found') );     
      return NULL;
      
    endif;
    
    return $video_details;
    
  }
    
}
