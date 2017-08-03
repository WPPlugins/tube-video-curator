<?php
/**
 * Tube_VC_Settings_Api_Keys
 * 
 * Supporting for managing 3rd party API keys
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
class Tube_VC_Settings_Api_Keys {
  
  public static $instance;
  
  public static function init() {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Settings_Api_Keys();
      return self::$instance;
  }
    
  
  // Constructor    
  function __construct() {    
     
     // Register YouTube API Key Settings
    add_action('admin_init', array( $this, 'register_youtube_api_key_settings') );       
     
     // Register Vimeo API Key Settings
    add_action('admin_init', array( $this, 'register_vimeo_api_key_settings') );           
     
     // Register Twitch API Key Settings
    add_action('admin_init', array( $this, 'register_twitch_api_key_settings') );   
  }
  
  
  // Show the API Key Settings page
  function render_options_page_api_settings(){
   
    // get active tab, or default to youtube-api    
    $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'youtube-api';
    
    $url = remove_query_arg('settings-updated');
    
    $tabs = array(
      'youtube-api' => array(
        'name' => __('YouTube', 'tube-video-curator'), 
        'url' => add_query_arg('tab', 'youtube-api', $url)
      ),
      'vimeo-api' => array(
        'name' => __('Vimeo', 'tube-video-curator'), 
        'url' => add_query_arg('tab', 'vimeo-api', $url)
      ),
      'twitch-api' => array(
        'name' => __('Twitch', 'tube-video-curator'), 
        'url' => add_query_arg('tab', 'twitch-api', $url)
      ),
    );    
      
    ?>
    <div class="wrap">
      
      <h1>Your API Keys</h1>
      <?php settings_errors(); ?>
      <h2 class="nav-tab-wrapper">
        <?php foreach ( $tabs as $tab_slug => $tab ): ?>
            <a href="<?php echo $tab['url']; ?>" class="nav-tab <?php if ( $active_tab == $tab_slug ) echo 'nav-tab-active'; ?>"><?php echo $tab['name']; ?></a>
        <?php endforeach; ?>
      </h2>
      <form action='options.php' method='post' class="section-divider">
        
        <?php  
        switch ($active_tab) {
          
          case 'vimeo-api': // VIMEO
            settings_fields( 'tube_vc_vimeo_api_settings' );
            do_settings_sections( 'tube_vc_vimeo_api_settings' );
            submit_button(__('Save ID &amp; Secret', 'tube-video-curator'));
            $this -> show_vimeo_api_help();      
            break;
          
          case 'twitch-api': // TWITCH
            settings_fields( 'tube_vc_twitch_api_settings' );
            do_settings_sections( 'tube_vc_twitch_api_settings' );
            submit_button(__('Save ID &amp; Secret', 'tube-video-curator'));
            $this -> show_twitch_api_help();      
            break;
          
          default: // YOUTUBE         
            
            settings_fields( 'tube_vc_youtube_api_settings' );
            do_settings_sections( 'tube_vc_youtube_api_settings' );
            submit_button(__('Save API Key', 'tube-video-curator'));
            $this -> show_youtube_api_help();      
            break;
        }    
  
        ?>        
      </form>
      
    </div>
    <?php
  
  }
   
   
   
  
  // Register YouTube API Key Settings        
  function register_youtube_api_key_settings() {
    
    register_setting( 
      'tube_vc_youtube_api_settings', 
      'tube_vc_youtube_api_key'
    );
    
    

     add_settings_section(
      'tube_vc_youtube_api_settings_section', 
      'Enter your YouTube API key below.', // blank title 
      array( $this, 'show_youtube_api_section'), 
      'tube_vc_youtube_api_settings'
    );
    
  
    add_settings_field( 
      'tube_vc_youtube_api_key', 
      __( 'API Key', 'tube-video-curator' ), 
      array( $this, 'render_youtube_api_key_setting'), 
      'tube_vc_youtube_api_settings', 
      'tube_vc_youtube_api_settings_section' 
    );
    
      
  
  }

   
   

  
  // Show the heading for the YouTube API section (Not used)
  function show_youtube_api_section() {
  }

  
  // Display the YouTube API Help
  function show_youtube_api_help() {
    ?>
    <!--
    <hr />
    <p>
      <button class="settings-help-toggle api-help-toggle"><i class="dashicons dashicons-editor-help"></i> <?php _e('How to get an API Key', 'tube-video-curator') ?></button> 
    </p>
    -->

    <div class="card settings-help hidden333" style="margin-top:0; padding:1em;">
        
        <h1 style="margin-top:0; padding-top:0;">
          <?php _e('Help &amp; FAQs'); ?>
        </h1>
        
        <hr />
        
        <h2>
          <?php _e('What&rsquo;s a YouTube API Key?'); ?>
        </h2>        
        <p>
          <?php _e('A YouTube API Key is a unique code or &#8220;key&#8221; that you get from Google. This is required to get data directly from YouTube, so you quickly search for channels &amp; playlists, and curate YouTube content for your site.'); ?>
        </p>
    
    
        <h2>
           <?php _e('How to get your YouTube API Key?'); ?>
        </h2>
        <!-- https://developers.google.com/youtube/v3/getting-started -->
        <?php 
          $this -> show_youtube_api_howto();      
        ?>
      
    </div>    
    <?php
  }


  
  // Display the How To content for the YouTube API   
  function show_youtube_api_howto() {
    ?>
    <ul class="api-help-counter clearfix">
          <li>
            
            <div class="help-step">
              
            <p style=" font-size:1.125em;">              
              <strong><?php _e('Get a Google Account', 'tube-video-curator'); ?></strong><br />
               <?php // _e('You&rsquo;ll need a Google Account to request a YouTube API key.', 'tube-video-curator'); ?>
               
               
            </p>

            <ul style="list-style: disc; padding-left:1.75em;">
              <li>
                <?php _e('Already have a Google Account? Go on to step 2.', 'tube-video-curator'); ?>
              </li>
              <li>
                  <?php _e('Don&rsquo;t have a Google Account?', 'tube-video-curator'); ?>
                  <a target="_blank" href="https://www.google.com/accounts/NewAccount"><?php _e('Create a new Google account', 'tube-video-curator'); ?></a>.
              </li>
            </ul>

            </div>
          </li>
          
          <li>
            <div class="help-step">
              <p style=" font-size:1.125em;">
                <strong><?php _e('Create a Project in the Google Developers Console', 'tube-video-curator'); ?></strong><br />
                <?php // _e('Next you&rsquo;ll need to create a &#8220;project&#8221; in the Google Developers Console. Here&rsquo;s how&hellip;', 'tube-video-curator') ?>
              </p>
              <ul style="list-style: disc; padding-left:1.75em;">
                
                <li>
                   <a target="_blank" href="https://console.developers.google.com/projectselector/apis/api/youtube/overview"><?php _e('Click here to create your project', 'tube-video-curator') ?></a>.                   
                   
                </li>
                <li>
                  <?php _e('You&rsquo;ll need to log in to your Google account if you&rsquo;re not already logged in.', 'tube-video-curator'); ?>
                </li>
                <li>
                  <?php _e('Give your project a name, and optionally a custom ID.', 'tube-video-curator'); ?>
                </li>
                <li>
                  <?php _e('Choose your email opt-in settings, then review the terms.', 'tube-video-curator'); ?>
                </li>
                <li>
                  <?php _e('Click the &#8220;Create&#8221; button.', 'tube-video-curator'); ?>
                </li>
              </ul>
            </div>
            

          </li>
          
          <li>
            <div class="help-step">
              <p style=" font-size:1.125em;">
                <strong><?php _e('Enable the YouTube API for Your Project', 'tube-video-curator'); ?></strong><br />
                <?php //_e('Now, you are ready to &#8220;enable&#8221; the YouTube API&hellip; for your project', 'tube-video-curator') ?>
              </p>
              
              <ul style="list-style: disc; padding-left:1.75em;">
                
                <li>
                   <?php _e('First, click the &#8220;Enable&#8221; button.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('Next, click the &#8220;Go to Credentials&#8221; button.', 'tube-video-curator') ?>
                </li>
              </ul>
            
            </div>
          </li>
          
          
          <li>
            <div class="help-step">
              <p style=" font-size:1.125em;">
                <strong><?php _e('Add Credentials / Get Your API Key', 'tube-video-curator'); ?></strong><br />
                <?php // _e('Almost there! Here&rsquo;s how to add credentials to your project:', 'tube-video-curator') ?>
              </p>
              
              <ul style="list-style: disc; padding-left:1.75em;">
                
                <li>
                   <?php _e('First, make sure &#8220;YouTube Data API v3&#8221; is selected in the &#8220;Which API are you using?&#8221; dropdown.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('Next, choose &#8220;Web Server&#8221; in the &#8220;Where will you be calling the API from?&#8221; dropdown.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('Now, choose &#8220;Public data&#8221; for the &#8220;What data will you be accessing?&#8221; question.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('Click the &#8220;What credentials do I need&#8221; button.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('Optionally, give your API key a name and add any specific IP addresses to allow.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('Finally, click the &#8220;Create API key&#8221; button.', 'tube-video-curator') ?>
                </li>
                
            
            </div>
          </li>
          
          
          
          
          <li>
            <div class="help-step">
              <p style=" font-size:1.125em;">
                <strong><?php _e('Save Your Key Here', 'tube-video-curator'); ?></strong><br />
                <?php // _e('Almost there! Here&rsquo;s how to add credentials to your project:', 'tube-video-curator') ?>
              </p>
              
              <ul style="list-style: disc; padding-left:1.75em;">
                
                <li>
                   <?php _e('Copy your new API Key from Google.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('Come back to this page and paste the API key into the form field here.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('Finally, click &#8220;Save API Key&#8221; button.', 'tube-video-curator') ?>
                </li>
                
              </ul>
            
            </div>
          </li>
          
        </ol>
    <?php
  }

  
  // Show theh YouTube API Key Setting
  function render_youtube_api_key_setting(  ) { 
  
    $youtube_api_key = get_option( 'tube_vc_youtube_api_key' );
    ?>
    <input type="text" name="tube_vc_youtube_api_key" value="<?php echo esc_attr($youtube_api_key); ?>" class="regular-text ltr">
    <?php
  
  }
  
  
  
  
          
  // Register the Vimeo API Key Settings
  function register_vimeo_api_key_settings() { //register our settings
    
    
    register_setting( 
      'tube_vc_vimeo_api_settings', 
      'tube_vc_vimeo_client_id'
    );
    
    register_setting( 
      'tube_vc_vimeo_api_settings', 
      'tube_vc_vimeo_client_secret'
    );
    
    register_setting( 
      'tube_vc_vimeo_api_settings', 
      'tube_vc_vimeo_access_token'
    );
    

    
  
     add_settings_section(
      'tube_vc_vimeo_api_settings_section', 
      'Enter your Vimeo Client ID and Client Secret below.', 
      array( $this, 'show_vimeo_api_section'), 
      'tube_vc_vimeo_api_settings'
    );
    
    
    add_settings_field( 
      'tube_vc_vimeo_client_id', 
      __( 'Client ID', 'tube-video-curator' ), 
      array( $this, 'render_vimeo_client_id_setting'), 
      'tube_vc_vimeo_api_settings', 
      'tube_vc_vimeo_api_settings_section' 
    );
  
    add_settings_field( 
      'tube_vc_vimeo_client_secret', 
      __( 'Client Secret', 'tube-video-curator' ), 
      array( $this, 'render_vimeo_client_secret_setting'), 
      'tube_vc_vimeo_api_settings', 
      'tube_vc_vimeo_api_settings_section' 
    );
  
  
    $vimeo_access_token = get_option( 'tube_vc_vimeo_access_token' );
    
    
    if ( $vimeo_access_token ):
      add_settings_field( 
        'tube_vc_vimeo_access_token', 
        __( 'Access Token', 'tube-video-curator' ), 
        array( $this, 'render_vimeo_access_token_setting'), 
        'tube_vc_vimeo_api_settings', 
        'tube_vc_vimeo_api_settings_section' 
      );
    endif;
      
  
  }

  // Show the heading for the Vimeo API section (Not used)
  function show_vimeo_api_section() { 
  }
  
  
  // Show the Vimeo Client ID Setting
  function render_vimeo_client_id_setting(  ) { 
  
    $vimeo_client_id = get_option( 'tube_vc_vimeo_client_id' );
    
    ?>
    <input type="text" name="tube_vc_vimeo_client_id" value="<?php echo $vimeo_client_id; ?>" class="regular-text ltr">
    <?php
  
  }
  

  // Show the Vimeo Client Secret Setting
  function render_vimeo_client_secret_setting(  ) { 
  
    $vimeo_client_secret = get_option( 'tube_vc_vimeo_client_secret' );
    
    ?>
    <input type="text" name="tube_vc_vimeo_client_secret" value="<?php echo $vimeo_client_secret; ?>" class="regular-text ltr">   
    <?php
  
  }
  
  // Show the Vimeo Access Token Setting
  function render_vimeo_access_token_setting(  ) {
     
    $vimeo_access_token = get_option( 'tube_vc_vimeo_access_token' );
    ?>
      <input type="text" name="tube_vc_vimeo_access_token" value="<?php echo $vimeo_access_token; ?>" class="regular-text ltr" disabled="disabled">
      <p class="description">Your access token is set automatically.</p>
    <?php
  }
  
  
  // Display the Vimeo API Help
  function show_vimeo_api_help() {
    ?>
    <!--
    <hr />
    <p>
      <button class="settings-help-toggle api-help-toggle">
        <i class="dashicons dashicons-editor-help"></i> <?php _e('How to get an API Key', 'tube-video-curator') ?>
      </button> 
    </p>
    -->

    <div class="card settings-help hidden333" style="margin-top:0; padding:1em;">
        
        <h1 style="margin-top:0; padding-top:0;">
          <?php _e('Help &amp; FAQs'); ?>
        </h1>
        
        <hr />
        
        <h2>
          <?php _e('What&rsquo;s a Vimeo Client ID and Secret?'); ?>
        </h2>
        
        <p>
          <?php _e('The Client ID and Secret are a set of unique codes or &#8220;keys&#8221; that you get from Vimeo. These are required to get data directly from Vimeo, so you quickly search for users &amp; channels, and curate Vimeo content for your site.'); ?>
        </p>
    
    
      <h2>
          <?php _e('How to get your Vimeo Client ID and Secret?'); ?>        
      </h2>
      <!-- https://developers.google.com/youtube/v3/getting-started -->
      
        <?php 
          $this -> show_vimeo_api_howto();      
        ?>
      
    </div>    
    <?php
  }
      
  // Display the How To content for the Vimeo API   
  function show_vimeo_api_howto() {
  ?>
  
    <ul class="api-help-counter clearfix">
      <li>
            
        <div class="help-step">
              
          <p style=" font-size:1.125em;">              
            <strong><?php _e('Get a Vimeo Account', 'tube-video-curator'); ?></strong>
          </p>

          <ul style="list-style: disc; padding-left:1.75em;">
            <li>
              <?php _e('Already have a Vimeo Account? Go on to step 2.', 'tube-video-curator'); ?>
            </li>
            <li>
                  <?php _e('Don&rsquo;t have a Vimeo Account?', 'tube-video-curator'); ?>
                  <a href="https://vimeo.com/join" target="_blank"><?php _e('Create a new Vimeo account', 'tube-video-curator'); ?></a>.
            </li>
          </ul>

        </div>
      </li>
          
      <li>
        <div class="help-step">
          <p style=" font-size:1.125em;">
            <strong><?php _e('Create an Application in the Vimeo Developers Area', 'tube-video-curator'); ?></strong>
          </p>
          <ul style="list-style: disc; padding-left:1.75em;">
            
            <li>
               <a href="https://developer.vimeo.com/apps/new" target="_blank"><?php _e('Click here to create your application', 'tube-video-curator') ?></a>.
            </li>
            <li>
              <?php _e('You&rsquo;ll need to log in to your Vimeo account if you&rsquo;re not already logged in.', 'tube-video-curator'); ?>
            </li>
            <li>
              <?php _e('Give your application a name and description, enter your website URL, and optionally a logo. You do NOT need to enter an App Callback URL.', 'tube-video-curator'); ?>
            </li>
            <li>
              <?php _e('Review the terms.', 'tube-video-curator'); ?>
            </li>
            <li>
              <?php _e('Click the &#8220;Create app&#8221; button.', 'tube-video-curator'); ?>
            </li>
          </ul>
        </div>
        

      </li>
          
          
          <li>
            <div class="help-step">
              <p style=" font-size:1.125em;">
                <strong><?php _e('Get Your API Key', 'tube-video-curator'); ?></strong>
              </p>
              
              <ul style="list-style: disc; padding-left:1.75em;">
                
                <li>
                   <?php _e('From the Details page for your App, click the &#8220;Authentication&#8221; tab.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('There you will see your Client Identifier and Client Secret.', 'tube-video-curator') ?>
                </li>
                
            
            </div>
          </li>
          
          
          
          
          <li>
            <div class="help-step">
              <p style=" font-size:1.125em;">
                <strong><?php _e('Save Your Keys Here', 'tube-video-curator'); ?></strong>
              </p>
              
              <ul style="list-style: disc; padding-left:1.75em;">
                
                <li>
                   <?php _e('Copy your new Client Identifier and Client Secret from Vimeo.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('Come back to this page and paste the values into the corresponding form fields here.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('Finally, click the &#8220;Save ID &amp; Secret&#8221; button', 'tube-video-curator') ?>
                </li>
                
              </ul>
            
            </div>
          </li>
          
    </ol>
    <?php
  }







          
  // Register the Twitch API Key Settings
  function register_twitch_api_key_settings() { //register our settings
    
    
    register_setting( 
      'tube_vc_twitch_api_settings', 
      'tube_vc_twitch_client_id'
    );
    
    register_setting( 
      'tube_vc_twitch_api_settings', 
      'tube_vc_twitch_client_secret'
    );

    
  
     add_settings_section(
      'tube_vc_twitch_api_settings_section', 
      'Enter your Twitch Client ID and Client Secret below.', 
      array( $this, 'show_twitch_api_section'), 
      'tube_vc_twitch_api_settings'
    );
    
    
    add_settings_field( 
      'tube_vc_twitch_client_id', 
      __( 'Client ID', 'tube-video-curator' ), 
      array( $this, 'render_twitch_client_id_setting'), 
      'tube_vc_twitch_api_settings', 
      'tube_vc_twitch_api_settings_section' 
    );
  
    add_settings_field( 
      'tube_vc_twitch_client_secret', 
      __( 'Client Secret', 'tube-video-curator' ), 
      array( $this, 'render_twitch_client_secret_setting'), 
      'tube_vc_twitch_api_settings', 
      'tube_vc_twitch_api_settings_section' 
    );
  
      
  
  }

  // Show the heading for the Twitch API section (Not used)
  function show_twitch_api_section() { 
  }
  
  
  // Show the Twitch Client ID Setting
  function render_twitch_client_id_setting(  ) { 
  
    $twitch_client_id = get_option( 'tube_vc_twitch_client_id' );
    
    ?>
    <input type="text" name="tube_vc_twitch_client_id" value="<?php echo $twitch_client_id; ?>" class="regular-text ltr">
    <?php
  
  }
  

  // Show the Twitch Client Secret Setting
  function render_twitch_client_secret_setting(  ) { 
  
    $twitch_client_secret = get_option( 'tube_vc_twitch_client_secret' );
    
    ?>
    <input type="text" name="tube_vc_twitch_client_secret" value="<?php echo $twitch_client_secret; ?>" class="regular-text ltr">   
    <?php
  
  }
  
  // Display the Twitch API Help
  function show_twitch_api_help() {
    ?>
    <!--
    <hr />
    <p>
      <button class="settings-help-toggle api-help-toggle">
        <i class="dashicons dashicons-editor-help"></i> <?php _e('How to get an API Key', 'tube-video-curator') ?>
      </button> 
    </p>
    -->

    <div class="card settings-help hidden333" style="margin-top:0; padding:1em;">
        
        <h1 style="margin-top:0; padding-top:0;">
          <?php _e('Help &amp; FAQs'); ?>
        </h1>
        
        <hr />
        
        <h2>
          <?php _e('What&rsquo;s a Twitch Client ID and Secret?'); ?>
        </h2>
        
        <p>
          <?php _e('The Client ID and Secret are a set of unique codes or &#8220;keys&#8221; that you get from Twitch. These are required to get data directly from Twitch, so you quickly search for channels &amp; videos, and curate Twitch content for your site.'); ?> <a target="_blank" href="http://dev.twitch.tv/"><?php _e('Learn more at the Twitch Developers site', 'tube-video-curator'); ?></a>.
        </p>
    
    
      <h2>
          <?php _e('How to get a Twitch Client ID and Secret?'); ?>        
      </h2>
      
        <?php 
          $this -> show_twitch_api_howto();      
        ?>
      
    </div>    
    <?php
  }
      
  // Display the How To content for the Twitch API   
  function show_twitch_api_howto() {
  ?>
    <ul class="api-help-counter clearfix">
      <li>
            
        <div class="help-step">
              
              
            <p style=" font-size:1.125em;">              
              <strong><?php _e('Get a Twitch Account', 'tube-video-curator'); ?></strong><br />
               
               
            </p>

            <ul style="list-style: disc; padding-left:1.75em;">
              <li>
                <?php _e('Already have a Twitch Account? Go on to step 2.', 'tube-video-curator'); ?>
              </li>
              <li>
                  <?php _e('Don&rsquo;t have a Twitch Account?', 'tube-video-curator'); ?>
                  <a target="_blank" href="https://www.twitch.tv/signup"><?php _e('Create a new Twitch account', 'tube-video-curator'); ?></a>.
              </li>
              <li>
                  <?php _e('Be sure to verify your email address.', 'tube-video-curator'); ?>
              </li>
            </ul>

        </div>
      </li>
          
          
      <li>
        
        <div class="help-step">
          <p style=" font-size:1.125em;">
            <strong><?php _e('Create an Application in Your Twitch Account', 'tube-video-curator'); ?></strong>
          </p>
          <ul style="list-style: disc; padding-left:1.75em;">
            
            <li>
               <a href="https://www.twitch.tv/kraken/oauth2/clients/new" target="_blank"><?php _e('Click here to create your application', 'tube-video-curator') ?></a>.
            </li>
            <li>
              <?php _e('You&rsquo;ll need to log in to your Twitch account if you&rsquo;re not already logged in.', 'tube-video-curator'); ?>
            </li>
            <li>
              <?php _e('Give your application a name, enter your homepage URL for the redirect URI, and choose Website Integration for the category.', 'tube-video-curator'); ?>
            </li>
            <li>
              <?php _e('Review the terms.', 'tube-video-curator'); ?>
            </li>
            <li>
              <?php _e('Click the &#8220;Register&#8221; button.', 'tube-video-curator'); ?>
            </li>
          </ul>
        </div>        

      </li>
          
          
          <li>
            <div class="help-step">
              <p style=" font-size:1.125em;">
                <strong><?php _e('Get Your API Key', 'tube-video-curator'); ?></strong>
              </p>
              
              <ul style="list-style: disc; padding-left:1.75em;">
                
                <li>
                   <?php _e('On the next page you will see your Client ID and Client Secret.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('You can also find them later in your Twitch account settings, under the &#8220;Connections&#8221; tab.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('If you do not see a Client Secret, click the &#8220;New Secret&#8221; button.', 'tube-video-curator') ?>
                </li>
                
            
            </div>
          </li>
          
          
          
          
          <li>
            <div class="help-step">
              <p style=" font-size:1.125em;">
                <strong><?php _e('Save Your Keys Here', 'tube-video-curator'); ?></strong>
              </p>
              
              <ul style="list-style: disc; padding-left:1.75em;">
                
                <li>
                   <?php _e('Copy your new Client ID and Client Secret from Twitch.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('Come back to this page and paste the values into the corresponding form fields here.', 'tube-video-curator') ?>
                </li>
                
                <li>
                   <?php _e('Finally, click the &#8220;Save ID &amp; Secret&#8221; button', 'tube-video-curator') ?>
                </li>
                
              </ul>
            
            </div>
          </li>
          
    </ol>
    <?php
  }
    
}