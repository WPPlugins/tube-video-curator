<?php
/**
 * Tube_VC_YouTube_Videos
 * 
 * Functions for searching YouTube API for videos
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
class Tube_VC_YouTube_Videos {
  
  public static $instance;
  public static $youtube;
  
  public static function init()
  {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_YouTube_Videos();
      return self::$instance;
  }
  
  // Constructor  
  
  function __construct() {
            
    // Include the Google API client
    require_once TUBE_VIDEO_CURATOR_DIR . '/lib/youtube-api/src/Google/Client.php';
    
    // Include the YouTube service API
    require_once TUBE_VIDEO_CURATOR_DIR . '/lib/youtube-api/src/Google/Service/YouTube.php';
    
    // add oembed filters for YouTube embeds 
    add_filter('embed_handler_html', array( $this, 'custom_youtube_oembed_args' ) );    
    add_filter('embed_oembed_html', array( $this, 'custom_youtube_oembed_args' ) );
 
    // add the linkify filter to youtube content import
    add_action ('init', array( $this, 'add_linkify_filter') );    

  }
  
  
  // attach the "linkify" function in the Linkify object to the 'tube_vc_filter_youtube_content_import' filter
  function add_linkify_filter() {   
    
    global $tube_video_curator;    
    
    add_filter('tube_vc_filter_youtube_content_import', array( $tube_video_curator::$tube_linkify, 'linkify'), 10, 1  );    
    
  }
  
  
  // get the YouTube client object
  function get_youtube_client() {      
  
    if ( self::$youtube ) :
      return self::$youtube;
    endif;

    self::$youtube = $this->set_youtube_client();

    return self::$youtube;
      
  }

  // set the YouTube client object
  function set_youtube_client() {  
    
    //print_r($search_args);
    
    $DEVELOPER_KEY = $this -> get_youtube_api_key();
  
    if( ! $DEVELOPER_KEY ):
      return;
    endif;    
    
    $client = new Google_Client();
    $client->setDeveloperKey($DEVELOPER_KEY);
  
    return new Google_Service_YouTube($client);
    
  }
  
  
   // Get the Vimeo API key if Vimeo API settings are filled
  function get_youtube_api_key() { // Check if Youtube API settings are filled
    
    return get_option( 'tube_vc_youtube_api_key');
   
  }  



  // Get common YouTube search arguments from the querystring
  function get_source_search_args_from_querystring(  ){    
    
    if ( array_key_exists('prev', $_GET) ):
      $search_args['pageToken'] = $_GET['prev'];
    elseif ( array_key_exists('next', $_GET) ):
      $search_args['pageToken'] = $_GET['next'];
    else:
      $search_args['pageToken'] = '';
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
       
    $search_args['maxResults'] = $results_per_page;
    
    return $search_args;
    
  }
  
  
  
  // Function to search for videos by a querystring
  function query_youtube_videos_via_api( $query, $args = NULL, $orig_videos = NULL ) {
 
    /*
    if ( ! $this -> get_youtube_api_key() ):
      ?>
      <p>
        <?php _e('YouTube API key not found.', 'tube-video-curator'); ?>
      </p>
      <?php
      return;
    endif;
    */
    
    //echo date(DATE_ATOM,  mktime(23,59,59));
    
    $defaults =   array(
        'q' => $query,
        'maxResults' => get_option('posts_per_page'),
        'order' => 'date',
        'type' => 'video',
        'videoEmbeddable' => 'true',
        'publishedBefore' => date(DATE_ATOM,  mktime(23,59,59)), // just before midnight current day
        'pageToken' => '',
        'get_all' => false
    );
    
    $search_args = wp_parse_args( $args, $defaults );
    
    $get_all = $search_args['get_all'];
    
    unset($search_args['get_all']);
    
    
    $search_parts = 'id,snippet';
    
    $transient_key = 'tube-yt-srch-qry-' . md5($search_parts . serialize($search_args) );
        
    $search_videos = get_transient( $transient_key );  
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
    
    // if no results from tranient, or flushing tranient        
    if( empty( $search_videos ) || $flush_transient ):  
    
      try {
        
        $youtube = $this -> get_youtube_client();        
       
        if ( ! $youtube ):
          
          _e( 'Error retrieving YouTube API client.', 'tube-video-curator' );
          return;
          
        endif;    
      
        $search_response = $youtube->search->listSearch( $search_parts, $search_args );
  
          //$youtube->videos->listVideos()
      
        } catch (Google_ServiceException $e) {
          echo 'Google_ServiceException ' . esc_html( $e->getMessage() );
          return;
        } catch (Google_Exception $e) {
          //var_dump($e->getMessage());
          echo 'Google_Exception ' . esc_html( $e->getMessage() );
          return;
        }
      
      $search_videos = $this -> format_youtube_channel_response_data($search_response);
            
      // Save the API response so we don't have to call again for half an hour
      set_transient( $transient_key, $search_videos, HOUR_IN_SECONDS / 2 );
    
    endif;

    return $search_videos;

  }




  function get_youtube_channel_videos_via_api_next_page( $youtube_channel_id, $args = NULL, $orig_videos ) {
 
    $args['get_all'] = true;
    
    //print_r($orig_videos);
    
    $next_page_token = $orig_videos['next_page_token'];
        
    if ( $next_page_token != '' ): //    CAUQAA
      
      $args['pageToken'] = $next_page_token;
      
      $channel_videos = $this->get_youtube_channel_videos_via_api( $youtube_channel_id, $args, $orig_videos['items'] );   
      
      return $channel_videos;
      
    endif;
    
    return $orig_videos;
      
  }

 
  function get_youtube_channel_videos_via_api( $youtube_channel_id, $args = NULL, $orig_videos = NULL ) {
 
 
    /*
    if ( ! $this -> get_youtube_api_key() ):
            ?>
      <p>
        <?php _e('YouTube API key not found.', 'tube-video-curator'); ?>
      </p>
      <?php
      return;
    endif;
    */
    
    //echo date(DATE_ATOM,  mktime(23,59,59));
    
    $defaults =   array(
        'channelId' => $youtube_channel_id,
        'maxResults' => get_option('posts_per_page'),
        'order' => 'date',
        'type' => 'video',
        'videoEmbeddable' => 'true',
        'publishedBefore' => date(DATE_ATOM,  mktime(23,59,59)), // just before midnight current day
        'pageToken' => '',
        'get_all' => false
    );
    
    $search_args = wp_parse_args( $args, $defaults );
  
    //print_r($search_args);
    
    //unset($search_args['publishedBefore']);
    
    $get_all = $search_args['get_all'];
    
    unset($search_args['get_all']);
    
    
    
    $search_parts = 'id';
    
    $transient_key = 'tube-yt-srch-lst-' . md5($search_parts . serialize($search_args) );
        
    $channel_videos = get_transient( $transient_key );  
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
    
    // if no results from tranient, or flushing tranient        
    if( empty( $channel_videos ) || $flush_transient ):  
        
      //echo 'CALLING YOUTUBE <br />';
      
     try {
       
        $youtube = $this -> get_youtube_client();    
     
        if ( ! $youtube ):
          
          _e( 'Error retrieving YouTube API client.', 'tube-video-curator' );
          return;
          
        endif;    
        
        $youtube_response = $youtube->search->listSearch($search_parts, $search_args);
        
       
  //print_r($search_args); 
  
      } catch (Google_Service_Exception $e) {
        echo '<p>Google_Service_Exception ' . esc_html( $e->getMessage() ) . '</p>';
        return;
      } catch (Google_Exception $e) {
        echo '<p>Google_Exception ' . esc_html( $e->getMessage() ) . '</p>';
        return;
      }
  
      $channel_videos = $this -> format_youtube_channel_response_data($youtube_response);
  
      //print_r($channel_videos);
      
      // Save the API response so we don't have to call again for an hour
      set_transient( $transient_key, $channel_videos, HOUR_IN_SECONDS );
      
    endif;
    
      
    if ($orig_videos):
     $channel_videos['items'] = array_merge (  $orig_videos, $channel_videos['items'] );
    endif;
    
     // print_r($channel_videos);
    
     if ( $get_all ):
       
       $channel_videos = $this -> get_youtube_channel_videos_via_api_next_page($youtube_channel_id, $search_args,$channel_videos);
       
     endif;
       
     return $channel_videos;
       
      
    }







  



  function get_youtube_playlist_videos_via_api_next_page( $youtube_playlist_id, $args = NULL, $orig_videos ) {
 
    $args['get_all'] = true;
    
    //print_r($orig_videos);
    
    $next_page_token = $orig_videos['next_page_token'];
        
    if ( $next_page_token != '' ): //    CAUQAA
      
      $args['pageToken'] = $next_page_token;
      
      $playlist_videos = $this->get_youtube_playlist_videos_via_api( $youtube_playlist_id, $args, $orig_videos['items'] );   
      
      return $playlist_videos;
      
    endif;
    
    return $orig_videos;
      
  }

 


    
    function format_youtube_channel_response_data( $youtube_response ){
      
      
        if ( $youtube_response['pageInfo']['totalResults'] == 0 ):
          return NULL;
        endif;
        
      
        if ( count( $youtube_response['items'] )== 0 ):
          return NULL;
        endif;
        
        $results['total_results'] = $youtube_response['pageInfo']['totalResults'];
        $results['results_per_page'] = $youtube_response['pageInfo']['resultsPerPage'];
        $results['next_page_token'] = $youtube_response['nextPageToken'];
        $results['prev_page_token'] = $youtube_response['prevPageToken'];
        
        //print_r($youtube_response);
        
        foreach ($youtube_response['items'] as $video_id_data):
              
              $video_ids[] = $video_id_data['id']['videoId'];
        
        endforeach;
        
        
        $video_ids = implode(',', $video_ids);
        
        $results['items'] = $this->get_structured_youtube_video_details($video_ids); 
        
        return $results;
    
  }


  function get_youtube_playlist_videos_via_api( $youtube_playlist_id, $args = NULL, $orig_videos = NULL ) {
 
    /*
    if ( ! $this -> get_youtube_api_key() ):
            ?>
      <p>
        <?php _e('YouTube API key not found.', 'tube-video-curator'); ?>
      </p>
      <?php
      return;
    endif;
    */
    //echo date(DATE_ATOM,  mktime(23,59,59));
    
    $defaults =   array(
        'playlistId' => $youtube_playlist_id,
        'maxResults' => get_option('posts_per_page'),
        'pageToken' => '',
        'get_all' => false
    );
    $search_args = wp_parse_args( $args, $defaults );
  
    //print_r($search_args);
    
    //unset($search_args['publishedBefore']);
    
    $get_all = $search_args['get_all'];
    
    unset($search_args['get_all']);
    
    
    
    $search_parts = 'id,snippet,contentDetails';
    
    $transient_key = 'tube-yt-srch-pllst-' . md5($search_parts . serialize($search_args) );
        
    $playlist_videos = get_transient( $transient_key );  
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
    
    // if no results from tranient, or flushing tranient        
    if( empty( $playlist_videos ) || $flush_transient ):    
      //echo 'CALLING YOUTUBE <br />';
     try {
       
        $youtube = $this -> get_youtube_client();  
     
        if ( ! $youtube ):
          
          _e( 'Error retrieving YouTube API client.', 'tube-video-curator' );
          return;
          
        endif;    
        
        $youtube_response = $youtube->playlistItems->listPlaylistItems($search_parts, $search_args);
        
       
  
      } catch (Google_Service_Exception $e) {
        echo '<p>Google_Service_Exception ' . esc_html( $e->getMessage() ) . '</p>';
        return;
      } catch (Google_Exception $e) {
        echo '<p>Google_Exception ' . esc_html ( $e->getMessage() ) . '</p>';
        return;
      }
  
        
      $playlist_videos = $this -> format_youtube_playlist_response_data($youtube_response);
  
      //print_r($playlist_videos);
      
      // Save the API response so we don't have to call again for an hour
      set_transient( $transient_key, $playlist_videos, HOUR_IN_SECONDS );
      
    endif;
    
      
    if ($orig_videos):
     $playlist_videos['items'] = array_merge (  $orig_videos, $playlist_videos['items'] );
    endif;
    
     // print_r($playlist_videos);
    
     if ( $get_all ):
       
       $playlist_videos = $this -> get_youtube_playlist_videos_via_api_next_page($youtube_playlist_id, $search_args,$playlist_videos);
       
     endif;
       
     return $playlist_videos;
       
      
    }




    
    function format_youtube_playlist_response_data( $youtube_response ){
      
      
        if ( $youtube_response['pageInfo']['totalResults'] == 0 ):
          return NULL;
        endif;
        
      
        if ( count( $youtube_response['items'] )== 0 ):
          return NULL;
        endif;
        
        $results['total_results'] = $youtube_response['pageInfo']['totalResults'];
        $results['results_per_page'] = $youtube_response['pageInfo']['resultsPerPage'];
        $results['next_page_token'] = $youtube_response['nextPageToken'];
        $results['prev_page_token'] = $youtube_response['prevPageToken'];
        
        //print_r($youtube_response);
        
        foreach ($youtube_response['items'] as $video_id_data):
        
              $video_ids[] = $video_id_data['snippet']['resourceId']['videoId'];
        
        endforeach;
        
        if ( ! isset($video_ids) ):
          return NULL;
        endif;
          
            
        $video_ids = implode(',', $video_ids);
        
        $results['items'] = $this->get_structured_youtube_video_details($video_ids); 
        
        return $results;
    
  }







  // Get stuctured video data from YouTube Results
  // NOTE:  Unlike Vimeo, we only have IDs here so need to do a second loop through
  //         to get additional data not provided via API search mechanisms
  
  function get_structured_youtube_video_details( $video_ids ){  
    
    $transient_key = 'tube-yt-vid-dtl-' . md5( $video_ids );
        
  
    $videos = get_transient( $transient_key );  
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
        
    // if no results from tranient, or flushing tranient        
    if( empty( $videos ) || $flush_transient ):  
    
  
      try {       
          
        // this sets up the client inside of this class
        $youtube = $this -> get_youtube_client();  
     
        if ( ! $youtube ):
          
          _e( 'Error retrieving YouTube API client.', 'tube-video-curator' );
          return;
          
        endif;    
      
        // nested query gets the full description
        $videos_results = $youtube->videos->listVideos('snippet, status', array(
          'id' => $video_ids,
        ));  
       
      } catch (Google_Service_Exception $e) {
        echo 'Google_Service_Exception ' . esc_html( $e->getMessage() );
        return;
      } catch (Google_Exception $e) {
        echo 'Google_Exception ' . esc_html( $e->getMessage() );
        return;
      }
      
      $videos = NULL;
      
      foreach ( $videos_results as $video_data_raw):
            
         if ( ! $video_data_raw['status']['embeddable'] ):
        
        
          add_action( 'admin_notices', array( $this, 'video_not_embeddable') );     
          return NULL;
         
         endif;
         
        $video_data['id'] = $video_data_raw['id'];
        
        $video_data['title'] = $video_data_raw['snippet']['title'];
        
        $video_content = $video_data_raw['snippet']['description'];
                
        $video_data['content_original'] = $video_content;
            
        $video_content = apply_filters( 'tube_vc_filter_content_import', $video_content );
            
        $video_content = apply_filters( 'tube_vc_filter_youtube_content_import', $video_content );
        
        $video_data['content'] = $video_content;
        
        $video_data['thumb_image_url'] = $video_data_raw['snippet']['thumbnails']['medium']['url'];
        
        // desired resolutions for featured image                        
        $resolutions = array('maxres', 'standard', 'high');   
        
        $image_url = NULL;
        
        // loop through resolutions
        foreach ( $resolutions as $resolution ):
          
          // check if resolution is there
          if ( $video_data_raw['snippet']['thumbnails'][$resolution]  ):
            
            // set the image and break out of loop
            $image_url = $video_data_raw['snippet']['thumbnails'][$resolution]['url'];
            break;
            
          endif;
            
        endforeach;

        // add the image to video data
        $video_data['image_url'] = $image_url;
                      
        // if no max-res, use high res thumbnail
        if ( ! $video_data['image_url'] ):
          $video_data['image_url'] = $video_data_raw['snippet']['thumbnails']['high']['url'];
        endif;
        
        //print_r( $video_data_raw['snippet']['publishedAt'] );
        
        $video_data['date'] = $video_data_raw['snippet']['publishedAt'];
        $video_data['tags'] = $video_data_raw['snippet']['tags'];
        $video_data['channel_id'] = $video_data_raw['snippet']['channelId'];              
        $video_data['creator_name'] = $video_data_raw['snippet']['channelTitle'];
        $video_data['media_url'] = 'https://www.youtube.com/watch?v='.$video_data['id'];
        //$video_data['embed_url'] = '//www.youtube.com/embed/'.$video_data['id'];
        $video_data['embed_url'] = NULL;
        $video_data['site'] = 'youtube';


        $videos[] = $video_data;    
  
  
      endforeach;
    
      // Save the API response so we don't have to call again for an hour
        set_transient( $transient_key, $videos, HOUR_IN_SECONDS );
        

    endif;

    
    
    if ( ! $videos ):
      
      add_action( 'admin_notices', array( $this, 'no_video_details_found') );     
      return NULL;
      
    endif;
    
    return $videos;

  }

    
    
    




  function custom_youtube_oembed_args($embed_code){    
    
    // determine if it's youtube
    if(strpos($embed_code, 'youtu.be') === false && strpos($embed_code, 'youtube.com') === false):
      
      // not YouTube, do nothing
      return $embed_code;
    
    endif;  
 
 
    // filter the arguments to allow changes / additions
    $youtube_args = array(
      'autoplay' => intval( get_option( 'tube_vc_player_autoplay') ),
      'showinfo' => intval( get_option( 'tube_vc_player_showinfo') ),
      'rel' => intval( get_option( 'tube_vc_player_related') ),
      'autohide' => intval( get_option( 'tube_vc_player_autohide') ),
      'controls' =>  intval( get_option( 'tube_vc_player_controls') ),
      'fs' => intval( get_option( 'tube_vc_player_fullscreen') ),
      'theme' => esc_attr( get_option( 'tube_vc_player_theme') ),
      'hl' => esc_attr( strtolower( get_bloginfo( 'language' ) ) )
    );
    
    // filter the arguments to allow changes / additions
    $youtube_args = apply_filters( 'tube_vc_filter_oembed_args', $youtube_args );
    $youtube_args = apply_filters( 'tube_vc_filter_youtube_oembed_args', $youtube_args );
        
    // convert the arguments to a string
    $youtube_args_query_str = http_build_query($youtube_args);
    
    // append the arguments to the URL
    $embed_code = preg_replace("@src=(['\"])?([^'\">\s]*)@", "src=$1$2&" . $youtube_args_query_str, $embed_code);
    
    return $embed_code;
    
  }  


  
  // Admin message for no domain in Add Via URL url
  function no_youtube_domain(  ){
    
    $class = 'notice notice-error';
    
    $message = __( 'Please use a URL from www.youtube.com', 'tube-video-curator' );
  
    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
  
  }
  
  // Admin message for no querystring in Add Via URL url
  function no_youtube_querystring(  ){
    
    $class = 'notice notice-error';
    
    $message = __( 'Please use a URL like this https://www.youtube.com/watch?v=JhvrQeY3doI', 'tube-video-curator' );
  
    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
  
  }
  
  // Admin message for no video ID in Add Via URL url
  function no_youtube_video_id(  ){
    
    $class = 'notice notice-error';
    
    $message = __( 'No video ID found. Please use a URL like this https://www.youtube.com/watch?v=JhvrQeY3doI', 'tube-video-curator' );
  
    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
  
  }
  
  
  // Admin message for no video details found for Add Via URL url
  function no_video_details_found(  ){
    
    $class = 'notice notice-error';
    
    $message = __( 'Sorry, this video could not be found. Please try another.', 'tube-video-curator' );
  
    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
  
  }
  
  // Admin message for no video not embeddable
  function video_not_embeddable(  ){
    
    $class = 'notice notice-error';
    
    $message = __( 'Sorry, this video is not embeddable. Please try another.', 'tube-video-curator' );
  
    printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
  
  }
  
  // Grab the data for a video using a single URL
  function get_youtube_video_data_via_url( $url ){
       
    $url_parts = parse_url( $url );    
    
    
    if (     
      ! array_key_exists( 'host', $url_parts )
      || 
      ( 
        ( $url_parts['host'] != 'youtube.com' ) && ( $url_parts['host'] != 'www.youtube.com' ) && ( $url_parts['host'] != 'm.youtube.com' ) 
      ) 
        ):
      
      add_action( 'admin_notices', array( $this, 'no_youtube_domain') );     
      return NULL;
      
    endif;
    
    
    if ( ! array_key_exists('query' , $url_parts) ):
      
      add_action( 'admin_notices', array( $this, 'no_youtube_querystring') );     
      return NULL;
      
    endif;
   
    parse_str( $url_parts['query'], $url_params);
   
    
    if ( ! array_key_exists('v' , $url_params) ):
      
      add_action( 'admin_notices', array( $this, 'no_youtube_video_id') );     
      return NULL;
      
    endif;
    
    $video_id = $url_params['v'];
    
    // this sets up the client inside of this class
    $youtube = $this -> get_youtube_client();
        
    $video_details = $this -> get_structured_youtube_video_details( $video_id );
    
    return $video_details;
    
  }
    
  
    
}
