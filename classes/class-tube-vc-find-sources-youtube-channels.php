<?php
/**
 * Tube_VC_Find_Sources_YouTube_Channels
 * 
 * Used by Tube_VC_Find_Sources to search for YouTube Channels
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
  
  
class Tube_VC_Find_Sources_YouTube_Channels {
  
  function __construct() {
    
  }
    
  
  function find_sources( $query ){   
    
    // get the normalized search arguments for YouTube
    $args = Tube_Video_Curator::$tube_youtube_videos -> get_source_search_args_from_querystring();
    
    // add the query to the arguments    
    $args['q'] = $query;    
    
    // create an array to store data about sources (i.e. YouTube Channels)
    $sources_data = array();
    
    // assume there's no username match
    $username_channel_id = NULL;
    
    // If this is the first page, try a username search    
    if ( !array_key_exists( 'pageToken', $args ) || ! $args['pageToken'] ):      
    
      // strip spaces from the query
      $query_as_username = str_replace(' ', '', $query);
      
      // attempt to get chanel from userid
      $username_channel = $this -> get_youtube_channel_from_userid( $query_as_username );
      
      // see if we got one back
      if ( $username_channel ):
        
        // save the channel ID to a local variable (so we can ignore it later)
        $username_channel_id = $username_channel['id'];
        
        // add the channel to the sources array
        $sources_data[] = $username_channel;
        
      endif;
     
    endif;
     
    
    // Now, try to get any channels that match the query
    $channel_response = $this -> get_youtube_channels_from_query( $args, $username_channel_id );    
    
    // see if channel search returned any results
    if ( $channel_response && is_array($channel_response['items']) ):
      
      $sources_data = array_merge($sources_data, $channel_response['items']);
      
    endif;    
    
    
    // doh, no sources found
    if ( count($sources_data) == 0  ):
      //echo 'Sorry, no channels matching that search could not be found.';
      return NULL;
      
    endif;    
    
    
    // normalize the source data
     foreach ($sources_data as $source):

       $source_data['title']= $source['snippet']['title'];
       
       $source_data['external_guid']= $source['id'];
       
       $source_data['external_url']= 'https://www.youtube.com/channel/' . $source['id'];
       
       $source_data['description']= $source['snippet']['description'];
       
       $source_data['image_url']= $source['snippet']['thumbnails']['high']['url'];
       
       $source_data['thumbnail_image_url']= $source['snippet']['thumbnails']['medium']['url'];
       
       $source_data['subscribers']= $source['statistics']['subscriberCount'];
       
       $source_data['videos']= $source['statistics']['videoCount'];
       
       $sources['sources'][] = $source_data;
       
     endforeach;

     $sources['total_results'] = $channel_response['pageInfo']['totalResults'];
     
     $sources['feed_type'] = 'youtube-channel';
     
     $sources['per_page'] = $args['maxResults'];
     
     $sources['site'] = 'youtube';
     
     $sources['next_page_token'] = $channel_response['nextPageToken'];
     
     $sources['prev_page_token'] = $channel_response['prevPageToken'];
        
    // return the sources
    return $sources;
    
  }


  function get_youtube_channel_from_userid( $username ){
            
    // get the youtube API client
    $youtube = Tube_Video_Curator::$tube_youtube_videos -> get_youtube_client();    
     
    if ( ! $youtube ):
      
      _e( 'Error retrieving YouTube API client.', 'tube-video-curator' );
      return;
      
    endif;    
    
    $username_search_parts = 'snippet,statistics';
    
    $username_search_args = array( 'forUsername' => $username );
    
    
    $transient_key = 'tube-yt-chnl-frm-usr-' . md5($username_search_parts . serialize($username_search_args) );
        
    $list_channels_response = get_transient( $transient_key );  
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
    
    // if no results from tranient, or flushing tranient        
    if( empty( $list_channels_response ) || $flush_transient ):  
      
            // attempt to get details for the username
      try {
        
         $list_channels_response = $youtube->channels->listChannels( $username_search_parts, $username_search_args);
      
      
      } catch (Google_Service_Exception $e) {
        echo 'Google_Service_Exception ' . $e->getMessage();;
        return;
      } catch (Google_Exception $e) {
        echo 'Google_Exception ' . $e->getMessage();;
        return;
      }
            
      if ( count($list_channels_response["items"]) == 0 ):
        return NULL;
      endif;
      
      // Save the API response so we don't have to call again for half an hour
      set_transient( $transient_key, $list_channels_response, HOUR_IN_SECONDS / 2 );
      
    endif;
      
    return $list_channels_response["items"][0];
      
  }
  
  
  
  function get_youtube_channels_from_query( $args, $ignore_channel_id = NULL  ){  
  
    $args['type'] = 'channel';
    
    
    // get the youtube API client
    $youtube = Tube_Video_Curator::$tube_youtube_videos -> get_youtube_client();  
      
     
    if ( !$youtube ):
      
      _e( 'Error retrieving YouTube API client.', 'tube-video-curator' );
      return;
      
    endif;    
    
    
    
    $transient_key = 'tube-yt-chnl-frm-qry' . md5( serialize($args) );
        
    $channel_response = get_transient( $transient_key );  
    
    // see if requested to flush / drop transient
    $flush_transient = array_key_exists('flush', $_GET);
    
    // if no results from tranient, or flushing tranient        
    if( empty( $channel_response ) || $flush_transient ):  
    
      
      // attempt to get channels for the query
      try {
        
         $channel_response = $youtube->search->listSearch( 'id', $args );
        
       
      } catch (Google_Service_Exception $e) {
        echo 'Google_Service_Exception ' . $e->getMessage();;
        return;
      } catch (Google_Exception $e) {
        echo 'Google_Exception ' . $e->getMessage();;
        return;
      }
  
  
      // create an array to store any resultant channel IDs    
      $channel_ids = array();
      
      // check if there are channel search results
      if ( isset($channel_response) && count($channel_response["items"]) != 0 ):
  
          // loop through resulting channels
          foreach ($channel_response['items'] as $channel_data):
            
             // see if this channel matches the ignored channel (if any)
             if ( $channel_data['id']['channelId'] == $ignore_channel_id):
               continue;
             endif;
      
             // add this channel's ID to the list of channel ids
             $channel_ids[] = $channel_data['id']['channelId'];
          
          endforeach;
          
      endif;      
      
      ////print_r($channel_ids);
      
      // see if there are channel IDs
      if ( count($channel_ids) != 0 ):
  
        // implode channel IDs into a string
        $channel_ids = implode(',', $channel_ids);
      
        $list_channels_search_parts = 'snippet,statistics';
        
         // get channel details for each of the channel IDs
        try {
          
           $list_channels_response = $youtube->channels->listChannels( $list_channels_search_parts, array(
            'id' => $channel_ids
          ));
        
        
        } catch (Google_Service_Exception $e) {
          echo 'Google_Service_Exception ' . $e->getMessage();;
          return;
        } catch (Google_Exception $e) {
          echo 'Google_Exception ' . $e->getMessage();;
          return;
        }
          
      endif;
      
      // check if there are channel search results
      if ( ! isset($list_channels_response) || count($list_channels_response["items"]) == 0 ):
        
          return;
      
      endif;
      
      // replace the original data with the more detailed channel data
      $channel_response['items'] = $list_channels_response["items"];
            
      // Save the API response so we don't have to call again for half an hour
      set_transient( $transient_key, $channel_response, HOUR_IN_SECONDS / 2 );
      
    endif;    
    
    // return the query response
    return $channel_response;
    
    
  }


}
