<?php
/**
 * Tube_VC_Theme_Filters
 * 
 * Custom filters to work with the .TUBE Theme
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
class Tube_VC_Theme_Filters {
  
  public static $instance;
  
  public static function init() {
    
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Theme_Filters();
      return self::$instance;
  }
  
  // Constructor    
  function __construct() {
            
    // special filter that works with the .TUBE theme
    add_filter( 'tube_filter_post_meta_author_displayname', array( $this, 'replace_author_displayname_with_creator_name' ) );
    
    // special filter that works with the .TUBE theme
    add_filter( 'tube_filter_post_meta_author_url', array( $this, 'suppress_author_url' ) );
    
  } 
     
  
  // Show the video creator name instead of the author name
  function replace_author_displayname_with_creator_name( $author_displayname ){     

    global $post;
    
    $creator_name = get_post_meta( $post -> ID, 'tube_video_creator_name', true );
    
    if ( $creator_name ):
      
      return $creator_name;
      
    else:
      
      return $author_displayname;
      
    endif;
      
  }     
    
   
  // Prevent the author name from being linked
  function suppress_author_url( $author_url ){     

    global $post;
    
    // see if there's a creatorname for the post
    $creator_name = get_post_meta( $post -> ID, 'tube_video_creator_name', true );
    
    // if there is a creatorname, suppress the author URL
    if ( $creator_name ):
      
      return NULL;
      
    else:
      
      return $author_url;
      
    endif;
      
  }     
    
    


    
}
