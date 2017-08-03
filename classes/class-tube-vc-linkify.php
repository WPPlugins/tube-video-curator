<?php
/**
 * Tube_VC_Linkify
 * 
 * Parses content to add links to URLs
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
class Tube_VC_Linkify {
  
  public static $instance;
  
  public static function init() {
    
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Linkify();
      return self::$instance;
  }
  
  // Constructor    
  function __construct() {    
    

  } 
    

  function linkify( $content ){    
         
   // Parses content to add links to URLs
	 $content = str_replace("&amp;","&",$content);
	
    $content = preg_replace("/(https?:\/\/[\da-z\.-]+\.[a-z\.]{2,6})(\/?\??[\.\?\/a-zA-Z=0-9_\-\&\@\~\%\+]*)/i",'<a target="_blank" href="$1$2">$1$2</a>',$content);
    
    $content = preg_replace("/([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})/i",'<a target="_blank" href="mailto:$1@$2.$3">$1@$2.$3</a>',$content);
        
    return $content;
            
  }

    
}
