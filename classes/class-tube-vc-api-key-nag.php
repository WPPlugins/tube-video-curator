<?php
/**
 * Tube_VC_Api_Key_Nag
 * 
 * Show an admin notice if the user hasn't set up any ALPI keys
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
class Tube_VC_Api_Key_Nag {
  
  public static $instance;
  
  public static function init() {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Api_Key_Nag();
      return self::$instance;
  }
    
  
  // Constructor    
  function __construct() {    
    
    // check if request to ignore the nag  
    add_action('admin_init', array( $this, 'ignore_api_key_nag' ) );
    
    // try to display the nag
    add_action('admin_notices', array( $this, 'display_api_key_nag' ) );

  }
  
 
  // Display a notice that can be dismissed
  function display_api_key_nag() {
    
    // if any key is set, do nothing
    if ( Tube_Video_Curator::has_api_keys() ):
      return;     
    endif;
     
    // Check that the user hasn't already clicked to ignore the message
    
    // get current User ID
    $user_id = get_current_user_id();
    
    // check if we should ignore the API Key nag
    if ( get_user_meta($user_id, 'ignore_api_key_nag') ) :
      return;
    endif;
    
    
    // make sure we're not on a page to input the settings
    global $pagenow;
    
    if ( $pagenow == 'admin.php' ):
      
      if ( ( array_key_exists('page', $_GET) ) && ($_GET['page'] == 'tube-video-curator-api-keys' ||  $_GET['page'] == TUBE_VIDEO_CURATOR_SLUG ) ):
        return;
      endif;
        
    endif;
    
    // Output the nag  
    ?>
    <div class="update-nag api-key-nag clearfix" style="display:block; margin-top:35px; position:relative;">
        <a href="<?php echo esc_url( add_query_arg('ignore_api_key_nag', '1') ); ?>" class="notice-dismiss"><?php _e('Dismiss', TUBE_VIDEO_CURATOR_SLUG); ?></a>
      <p>
        <strong><?php echo __('You&rsquo;re almost ready to curate videos&hellip;', 'tube-video-curator'); ?></strong>
      </p>
        <a href="<?php echo esc_url(TUBE_VIDEO_CURATOR_DASH_URL); ?>" class="button button-primary button-tube"><?php _e('Set up the .TUBE Video Curator', 'tube-video-curator'); ?></a>
    </div>
    <?php

  }
  
  
 
  // Check if there's a request to ignore the API Key nag  
  function ignore_api_key_nag() {
    
    // check for the ignore querystring
    if ( isset($_GET['ignore_api_key_nag']) && '1' == $_GET['ignore_api_key_nag'] ):
      
      // get current User ID
      $user_id = get_current_user_id();
      
      // add some user meta to ignore the API GET nag
      add_user_meta($user_id, 'ignore_api_key_nag', 'true', true);
      
    endif;
    
  }
    
  
  
    
      
  
    
}