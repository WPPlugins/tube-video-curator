<?php
/**
 * Tube_VC_Source_Manager
 * 
 * ADD STUFF HERE
 * 
 * @package Tube_Video_Curator
 * @subpackage Classes
 * @author  .TUBE gTLD <https://www.get.tube>
 * @copyright Copyright 2017, TUBE®
 * @link https://www.get.tube/wordpress
 * @since 1.0.0
 */
 
 

class Tube_VC_Source_Manager {
  
  public static $instance;
  
  //public static $default_source_site;
  
  //public static $default_feed_type;
  
  //public static $default_autosync_option;
  
  public static $current_source;    
  
  public static function init() {
      if ( is_null( self::$instance ) )
          self::$instance = new Tube_VC_Source_Manager();
      return self::$instance;
  }
  
  
  // Constructor  
  
  function __construct() {
    
      
      require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-source.php'; 
      require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-source-youtube-channel.php'; 
      require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-source-youtube-playlist.php'; 
      require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-source-vimeo-user.php'; 
      require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-source-vimeo-channel.php';
      require_once TUBE_VIDEO_CURATOR_DIR . '/classes/class-tube-vc-source-twitch-channel.php';
      
      
      // create the custom post type for Tube Sources (Channels & Playlists)
      add_action( 'init', array( $this, 'register_sources_post_type') );
      
      // Don't use the Yoast metabox on the Source post type
      add_action( 'add_meta_boxes', array( $this, 'remove_yoast_metabox_source'), 11 );      
       
       
      add_filter('manage_tube_source_posts_columns' , array( $this, 'add_source_admin_columns') );
      
      add_action('manage_tube_source_posts_custom_column' , array( $this, 'manage_source_admin_columns'), 10, 2 );
      
      add_action( 'admin_enqueue_scripts', array( $this, 'load_find_videos_scripts_for_source'), 10, 1 );
      
      add_action( 'admin_enqueue_scripts',  array( $this, 'prevent_source_autosave'), 10, 1 );
      
    
      // customize post updated messages for Source 
      add_filter( 'post_updated_messages', array( $this, 'source_post_updated_messages' ) );
 
      // set the current source early so it can be used
      add_action( 'init', array( $this, 'set_current_source') );
    
      // add a function to save the source meta data
      add_action('save_post_tube_source', array( $this, 'save_source_meta' ), 99, 2 ); // save the custom fields 
      
      // hijack Add New Source clicks and go to "Add New Sources" page
      add_action('load-post-new.php', array( $this, 'redirect_add_new_source_link') );


   // endif;
    
    //self::$default_source_site = 'youtube';
    
    //self::$default_feed_type = 'youtube-username';
    
    //self::$default_autosync_option = 0;

    
  }


 
 // when clicking "Add New Source" this will redirect to the new Find Sources page
 function redirect_add_new_source_link() {
   
    if( array_key_exists("post_type", $_GET) && $_GET["post_type"] == "tube_source") :
      
        wp_redirect( TUBE_VIDEO_CURATOR_FIND_SOURCES_URL );
      
    endif;
  
  }
      
  
  // Adds the find videos JS on the tube_source post type    
  function load_find_videos_scripts_for_source($hook) {
    global $post;
    global $tube_video_curator;
  
    if ($hook == 'post.php' && $post->post_type == 'tube_source'):
      $tube_video_curator -> enqueue_find_videos_scripts();
    endif;
    
  }

  
  // Don't do autosave on the .TUBE Source post type
    //http://wordpress.stackexchange.com/questions/5584/possible-to-turn-off-autosave-for-single-custom-post-type
    function prevent_source_autosave() {
        if ( 'tube_source' == get_post_type() )
            wp_dequeue_script( 'autosave' );
    }


    // Attempt to set the current source
    function set_current_source() {
       
      if ( is_array($_POST) && array_key_exists('post_ID', $_POST) ):
        $post_id = $_POST['post_ID'];
      elseif ( is_array($_GET) && array_key_exists('post', $_GET) ):
        $post_id = $_GET['post'];
      else:
        return;
      endif;
      
      $this -> set_current_source_by_post_id( $post_id );

    }
      

    // Attempt to set the current source based on post ID
    function set_current_source_by_post_id( $post_id ) {
      
      // make sure post ID isn't an array
      if ( is_array($post_id) ):
        return;
      endif;
      
      // get the post type
      $post_type = get_post_type( $post_id );
      
      // make sure it's the right post type
      if ( $post_type != 'tube_source' ):
        return;
      endif;
      
      // get the feed type of the current source
      $feed_type = get_post_meta( $post_id, 'tube_feed_type', true );
      
      // get the site of the current source
      $source_site = get_post_meta( $post_id, 'tube_source_site', true );
      
      switch ($feed_type):

        case 'youtube-channel':
          
          self::$current_source = new Tube_VC_Source_YouTube_Channel( $post_id );
          break;
          
        case 'youtube-playlist':
          
          self::$current_source = new Tube_VC_Source_YouTube_Playlist( $post_id );
          break;
                             
        case 'vimeo-username':
          
          self::$current_source = new Tube_VC_Source_Vimeo_User( $post_id );
          break;
                             
        case 'vimeo-channel':
          
          self::$current_source = new Tube_VC_Source_Vimeo_Channel( $post_id );
          break;
                             
        case 'twitch-channel':
          
          self::$current_source = new Tube_VC_Source_Twitch_Channel( $post_id );
          break;
        
        default: 
          
          break;
          
      endswitch;
      
      // TODO :: FIGURE OUT WHY THIS GETS HERE W/ AN ERROR
      if ( ! self::$current_source ):
        return;
      endif;
     
     // Have the current source set its local properties     
     self::$current_source -> set_source_properties( $post_id );
      
    }



  // create the custom post type for Tube Sources (Channels & Playlists)
  function register_sources_post_type() {
    
    $labels = array(
      'name'               => _x( 'Channels &amp; Playlists', 'post type general name', 'tube-video-curator' ),
      'singular_name'      => _x( 'Channel / Playlist', 'post type singular name', 'tube-video-curator' ),
      'menu_name'          => _x( 'Channels &amp; Playlists', 'admin menu', 'tube-video-curator' ),
      'name_admin_bar'     => _x( 'Channel / Playlist', 'add new on admin bar', 'tube-video-curator' ),
      'add_new'            => _x( 'Add New', 'source', 'tube-video-curator' ),
      'add_new_item'       => __( 'Add New Channel / Playlist', 'tube-video-curator' ),
      'new_item'           => __( 'New Channel / Playlist', 'tube-video-curator' ),
      'edit_item'          => __( 'View Channel / Playlist', 'tube-video-curator' ),
      'view_item'          => __( 'View Channel / Playlist', 'tube-video-curator' ),
      'all_items'          => __( 'Your Channels &amp; Playlists', 'tube-video-curator' ),
      'search_items'       => __( 'Search Channels &amp; Playlists', 'tube-video-curator' ),
      'parent_item_colon'  => __( 'Parent Channels &amp; Playlists:', 'tube-video-curator' ),
      'not_found'          => __( 'No channels / playlists found.', 'tube-video-curator' ),
      'not_found_in_trash' => __( 'No channels / playlists found in Trash.', 'tube-video-curator' )
    );
  //
    $args = array(
      'labels'             => $labels,
      'description'        => __( 'Description.', 'tube-video-curator' ),
      'public'             => false,
      'publicly_queryable' => false,
      'show_ui'            => true,
      'show_in_nav_menus'  => false,
      'show_in_menu'       => TUBE_VIDEO_CURATOR_SLUG,
      'query_var'          => true,
      'rewrite'            => array( 'slug' => 'source' ),
      'capability_type'    => 'post',
      'has_archive'        => false,
      'hierarchical'       => false,
      'menu_position'      => null,
      'menu_icon'          => 'dashicons-playlist-video',
      //'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
      'supports'           => array( 'title', 'custom-fields' ),
      'register_meta_box_cb' => array($this,'add_source_metaboxes')     
    );
  
    register_post_type( 'tube_source', $args );
    
    //remove_post_type_support('post', 'title');
  }


  // Create a custom "Submit" metabox for the Tube Source post type
  function add_source_metaboxes() {
    
    // remove the dfeault Submit metabox
    remove_meta_box( 'submitdiv', 'tube_source', 'side' );
    
    /*
    // delete metabox order for current user so we can sculpt it
    global $wpdb;
    $sql = "SELECT * 
    FROM  `$wpdb->usermeta` 
    WHERE  `user_id` = " . get_current_user_id() . "
    AND  `meta_key` =  'meta-box-order_tube_source'";

    $delete = $wpdb->get_results($sql, OBJECT );
    
    global $post;
    
    //print_r($post);
    
    if ( empty( $post -> post_title ) ):
      $this -> add_source_settings_metabox( 'normal' );
    else:
      $this -> add_source_settings_metabox( 'side' );
      $this -> add_source_videos_metabox();
    endif;    
    */
    
    $this -> add_source_settings_metabox( 'side' );
    
    $this -> add_source_videos_metabox();
    
    $this -> check_import_request();      
      
    
  }
  
  
  
  // Add the metabox for the Source settings
  function add_source_settings_metabox( $settings_placement ) {
         
    add_meta_box(
      'tube_source_meta', // slug
      __('Source Settings', 'tube-video-curator'), // title
      array( $this, 'show_source_settings_metabox' ), // display callback
      'tube_source', // post type
      $settings_placement, // placement
      'high' // priority
    );
    
  }


  // Display the metabox for the source settings
  
  function show_source_settings_metabox() {
     
    global $post;
    global $tube_video_curator;
    
    if ( empty( $post -> post_title ) ):
      $button_text = __('Save Source', 'tube-video-curator');
      $show_trash_link = false;
    else:
      $button_text = __('Save Settings', 'tube-video-curator');
      $show_trash_link = true;
    endif;
    
    ?>
    
    <div class="form-table source-settings-metabox">     
      
          <?php
          
          $source_image = self :: $current_source -> image;
          if ( $source_image ):
            ?>
            <div id="postimagediv">
              <div class="inside" style="margin:0; padding:0;">
                
                <p>
                  <?php echo $source_image; ?>
                  
                  
                </p>
              </div>
            </div>
            
            <?php
          endif;
          ?>
       
          <?php
          
          // Get the tube_source_site if its already been entered
          if ( self :: $current_source ):
            
            $source_site = self :: $current_source -> site;
          
            $source_site_label = $tube_video_curator -> get_external_site_label( $source_site );
            
            $source_title = self :: $current_source -> title;
            
            $source_feed_type = self :: $current_source -> feed_type;
          
            $source_feed_type_label = $tube_video_curator -> get_feed_type_label( $source_feed_type );
            
            $source_external_url = self :: $current_source -> external_url;
            
            ?>
              <h4>
               <?php echo esc_html( $source_title ); ?>
              </h4>
              <p>
                <strong><?php _e( 'Type:', 'tube-video-curator' ); ?></strong> <?php echo esc_html( $source_feed_type_label ); ?>
              </p>
              <p>
                <strong><?php _e( 'Original:', 'tube-video-curator' ); ?></strong> <a href="<?php echo esc_url($source_external_url); ?>" target="_blank">View on <?php echo esc_html($source_site_label); ?></a>
              </p>
          
            
             <?php 
              if ( $source_feed_type == 'youtube-channel' ):
                $this -> show_source_autosync_setting();
              endif;
              
            endif; 
            
            ?>
        

        <div id="major-publishing-actions" >
          
          <input name="post_status" id="post_status" value="publish" type="hidden" />

          <?php if ( $show_trash_link ) : ?>
            <div id="delete-action">
              <a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>">
                <?php _e( 'Move to Trash', 'tube-video-curator' ); ?>
              </a>
            </div>
          <?php endif; ?>
          
          <div class="clear"></div>
          
        </div>
        
        
    </div> <!-- .source-settings-metabox -->
    <?php     
    
        
  }

    
  // Display the Auto-sync setting for the source
  function show_source_autosync_setting() {
          
      // Noncename needed to verify where the data originated
      echo '<input type="hidden" name="sourcemeta_noncename" id="sourcemeta_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
       
      // get the current source's autosync setting
      $autosync_setting = self :: $current_source -> autosync;
    
      // see if the autosync setting is set
      if ( ! isset($autosync_setting) || ! $autosync_setting ) :
        
        // autosync setting is false
        $autosync_setting = 0;
        
      endif;
            
      // Not really used any more
      //$autosync_options = $this -> get_autosync_options();
    
      // get the post type object 
      $post_type = $this->get_autosync_post_type(  );
        
      // make sure the post type exists
      if ( ! $post_type ):
         
        // Boo, post type doesn't really exist
         echo '<p>' . sprintf( __( 'Boo, post type &#8220;%1$s&#8221; doesn&rsquo;t really exist', 'tube-video-curator' ), esc_html($post_type_slug) ) . '</p>';
         
        return NULL;
         
      endif;
          
      $post_type_slug = $post_type->name;
        
      $post_type_name = strtolower ($post_type->labels->singular_name);
              
      $importable_post_types = Tube_Video_Curator::$tube_settings -> get_importable_post_types();
      
      ?>
        
        <div class="autosync-settings-wrap">
       
        
        <?php
        
          switch ($autosync_setting):
            
            case 1: // autosync is on
              ?>
              <h4>
                <?php _e( 'Auto-sync is ON', 'tube-video-curator' ); ?>                
              </h4>
              <p>
                <?php 
                echo sprintf( 
                  __( 'A new <a href="%1$s">%2$s</a> will automatically be created when this channel adds a new video.', 'tube-video-curator' ), 
                  esc_url('edit.php?post_type=' . $post_type_slug), 
                  esc_html($post_type_name) 
                ); 
                ?>
              </p>
              <div id="publishing-action clearfix">
                <button type="submit" name="publish" id="publish" class="button button-primary button-large">
                  <?php esc_attr_e( 'Turn OFF Auto-sync', 'tube-video-curator' ); ?>
                </button>
              </div>
              <input type="hidden" name="tube_source_autosync" value="0" />
              <?php
              break;
            
            default: // autosync is off
              ?>
              <h4>
                <?php _e( 'Auto-sync is OFF', 'tube-video-curator' ); ?>                
              </h4>
              <p>
                <?php 
                echo sprintf( 
                  __( 'If you turn on auto-sync, a new <a href="%1$s">%2$s</a> will automatically be created when this channel adds a new video.', 'tube-video-curator' ), 
                  esc_url( 'edit.php?post_type=' . $post_type_slug), 
                  esc_html($post_type_name) 
                ); 
                ?>
              </p>
              <div id="publishing-action clearfix">
                <button type="submit" name="publish" id="publish" class="button button-primary button-large">
                  <?php esc_attr_e( 'Turn ON Auto-sync', 'tube-video-curator' ); ?>
                </button>
              </div>
              <input type="hidden" name="tube_source_autosync" value="1" />
              <?php
              break;
              
          endswitch;
          
          
        
        if ( count($importable_post_types) > 1 ):
          ?>
          <p class="description">
             <a href="<?php echo esc_url(TUBE_VIDEO_CURATOR_IMPORT_SETTINGS_URL); ?>"><?php _e( 'Change post type for new imports', 'tube-video-curator' ); ?></a>.
          </p>
          <?php
        endif;
        
    
          switch ($autosync_setting):
            
            case 1: // autosync is on
              ?>
              <?php
              break;
            
            default: // autosync is off
              ?>
              <?php
              break;
              
          endswitch;
          
        ?>
        <!--
        <fieldset>
        <?php 
        
        $last = count($autosync_options);
        $count = 0;
        
        if ( $autosync_options ):
          foreach ( $autosync_options as $autosync_option => $autosync_option_name ): 
            
            $count++;
            ?>
            
            <label for="tube_source_autosync<?php echo $autosync_option; ?>">
              <input type="radio" id="tube_source_autosync<?php echo $autosync_option; ?>" name="tube_source_autosync" value="<?php echo $autosync_option; ?>" <?php checked( $autosync_setting, $autosync_option ); ?> /> <?php echo $autosync_option_name; ?>
            </label>
            
            <?php       
            if ( $count != $last ) echo '<br />';
            
          endforeach; 
        endif;
        ?>
        </fieldset> 
        -->
        
        <?php 
        ?>
        
        
          
          
        </div>
          
        <?php
  }
    
    
  // Get the default value for the source site
  // NOT USED - REMOVE WHEN TESTED
  /*    
  function get_default_source_site() {
    
    $default_source_site = self::$default_source_site;
    
    $default_source_site = apply_filters( 'tube_vc_filter_default_source_site', $default_source_site );
    
    return $default_source_site;
    
  }
  */  
    
  // Get the default value for the feed type
  // NOT USED - REMOVE WHEN TESTED
  /*    
  function get_default_feed_type() {
    
    $default_feed_type = self::$default_feed_type;
    
    $default_feed_type = apply_filters( 'tube_vc_filter_default_feed_type', $default_feed_type );
    
    return $default_feed_type;
    
  }
  */  
    
        
  // Get the default value for autosync
  // NOT USED - REMOVE WHEN TESTED
  /*    
  function get_default_autosync_option() {
    
    $default_autosync_option = self::$default_autosync_option;
    
    $default_autosync_option = apply_filters( 'tube_vc_filter_default_autosync_option', $default_autosync_option );
    
    return $default_autosync_option;
    
  }
  */  
  
  
  /*
  function get_autosync_options() {    

    // get the post type object 
    $post_type = $this->get_autosync_post_type(  );
    
    // make sure the post type exists
    if ( ! $post_type ):
     
      // Boo, post type doesn't really exist
      echo 'Boo, post type &#8220;' . $post_type_slug . '&#8221; doesn&rsquo;t really exist';
      return NULL;
     
    endif;
      
      
    $post_type_slug = $post_type->name;
    
    $post_type_name = strtolower ($post_type->labels->name);
    
    $autosync_options = array(
      '0' => '<strong>No:</strong> I&rsquo;ll add videos when I feel like it',
      '1' => '<strong>Yes:</strong> Create new <a href="edit.php?post_type=' . $post_type_slug . '">' . $post_type_name . '</a> when this channel adds new videos'
    );      
      
    // return the options
    return $autosync_options;
    
  }
  */
  
  // get the post type object for the autosync post type
  function get_autosync_post_type() {
    
    // get the import post type slug
    $post_type_slug = get_option( 'tube_vc_default_import_post_type' );

    // get the post type object 
    $post_type = get_post_type_object( $post_type_slug );
    
    // make sure the post type exists
    if ( ! $post_type ):
      
      return NULL;
     
    endif;
    
    return $post_type;
    
  }
    
    
  
 
 
 

 
  // function allows to force import of new videos view 'importnew' query string argument
  
  function check_import_request(){
    
    if ( array_key_exists('importnew', $_GET) ):
      
      self :: $current_source -> get_new_videos_for_import();
            
    endif;
    
  } 
  
  
  
  
 
 
 
  // Adds a metabox to show the available videos from the current source
    
  function add_source_videos_metabox() {
    
    add_meta_box(
      'tube_source_videos', // slug
      'Available Videos', // title
      array( $this, 'show_source_videos_metabox' ), // display callback
      'tube_source', // post type
      'normal', // placement
      'high' // priority
    );
    
    
  }
 
  // display the metabox with videos for the current source
  function show_source_videos_metabox() {
     
    global $post;
    
    // Get the source site
    $source_site = self :: $current_source -> site;    
    
    //$search_args = self :: $current_source -> get_source_search_args_from_querystring();
    
    switch ($source_site):
      
      case 'youtube':
        
        $search_args = Tube_Video_Curator::$tube_youtube_videos -> get_source_search_args_from_querystring();
        $max_results = $search_args['maxResults'];
        break;
        
      case 'vimeo':
        
        $search_args = Tube_Video_Curator::$tube_vimeo_videos -> get_source_search_args_from_querystring();
        $max_results = $search_args['per_page'];  
        break;
        
      case 'twitch':
        
        $search_args = Tube_Video_Curator::$tube_twitch_videos -> get_source_search_args_from_querystring();
        $max_results = $search_args['limit'];  
        break;
        
    endswitch;
    
    // call the source-specific function to get videos
    $source_videos = self :: $current_source -> get_source_videos( $search_args );   
    
    // See if any videos were found
    if ( ! $source_videos || ! is_array($source_videos) || ! is_array($source_videos['items']) ):
      
       echo '<p>' . __( 'Sorry, no results found.', 'tube-video-curator' ) . '</p>';
       return;
        
    endif;
     
    // display the videos list
    $this -> show_source_videos_list( $source_videos, $source_site, $max_results);
      
        
  }


 


  // Save the Source Metabox Data  
  function save_source_meta($post_id, $post) {
    
           
    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    
    if ( ! array_key_exists('sourcemeta_noncename', $_POST) ):
       return $post_id;
    endif;

    
    if ( !wp_verify_nonce( $_POST['sourcemeta_noncename'], plugin_basename(__FILE__) )) {
      return $post_id;
    }
        
    // Is the user allowed to edit the post or page?
    if ( !current_user_can( 'edit_post', $post_id ))
      return $post_id;  
    
    // OK, we're authenticated: we need to find and save the data
    // We'll put it into an array to make it easier to loop though.
   
    
    //$source_meta['tube_feed_type'] = $_POST['tube_feed_type'];
    
    // extract the site from the feed type (via "dash" delimiter)
    //$tube_feed_type_parts = explode('-', $source_meta['tube_feed_type']) ;
    
    //$source_meta['tube_source_site'] = $tube_feed_type_parts[0];
    
    $source_meta['tube_source_autosync'] = $_POST['tube_source_autosync'];   
    
    
    // Add values of $source_meta as custom fields

    foreach ($source_meta as $key => $value) : // Cycle through the $source_meta array!
      if( $post->post_type == 'revision' ) return; // Don't store custom data twice
      $value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
      if(get_post_meta($post_id, $key, FALSE)) : // If the custom field already has a value
        update_post_meta($post_id, $key, $value);
      else: // If the custom field doesn't have a value
        add_post_meta($post_id, $key, $value);
      endif;
      if(!$value) delete_post_meta($post_id, $key); // Delete if blank
    endforeach;
           
    // setup the source data following updated meta
    $this -> set_current_source();
        
    return;
    
  
  }





  // Display a list of videos from the source
  
  function show_source_videos_list($response, $site, $results_per_page){
    
    $results_per_page_links = array(
      10 => __('10', 'tube-video-curator' ),
      20 => __('20', 'tube-video-curator' ),
      30 => __('30', 'tube-video-curator' ),
      40 => __('40', 'tube-video-curator' ),
      50 => __('50', 'tube-video-curator' ),
    );
    
    // add link to view all if fewer than 150 total results
    if ( $response['total_results'] < 150 ):
      $results_per_page_links['all'] = 'All';
    endif;
    
    // get the total number of available results
    $available_results = count( $response['items'] );
      
  //print_r($response);
    
  ?>
  
  <div class="tube-admin-list-videos-heading">
    
      <p style="float:left;">
        <?php 
        $total_results = $response['total_results'];
        
        $total_results_label = _n( 'video', 'videos', $total_results, 'tube-video-curator' );
        
        echo number_format ( $total_results ) . ' ' . esc_html( $total_results_label );?>
      </p> 
      <ul class="max-results-links" style="float:right;">
        <?php      
        foreach ( $results_per_page_links as $results_per_page_id => $results_per_page_label ):          
          
          if ( $results_per_page_id != $results_per_page ):
            
            $link_url = add_query_arg( 'results', $results_per_page_id ); 
            
            $link_url = remove_query_arg( array('next','prev'), $link_url);
            
            $link_url = esc_url( $link_url );
            
            ?><li><a href="<?php echo $link_url; ?>"><?php echo $results_per_page_label; ?></a></li><?php
            
          else:
            
            ?><li><strong><?php echo $results_per_page_label; ?></strong></li><?php
            
          endif;
        endforeach;
        ?>
        </ul>
    </div>
    
    
    <div class="notice notice-info" id="tube-video-curator-notice" style="display:none;">
      <p><?php _e('Media added !', 'tube-video-curator'); ?></p>
    </div>
    
      
      <div class="tube-video-quicklinks">
        <p>          
        <?php _e('Quick-links for new videos on this page: ', 'tube-video-curator' ); ?>
          <a href="#" id="publish-all" >
           <?php _e('Publish All', 'tube-video-curator' ); ?>
         </a> &bull; 
         <a href="#" id="skip-all">
           <?php _e('Skip All', 'tube-video-curator' ); ?>
         </a>
         </p>
      </div>
    
      <?php Tube_Video_Curator::$tube_videos_list -> show_videos_list( $response['items'], $site );  ?>
        
    
  <?php
    
    //print_r(get_current_screen());
    
    // trick to get base relative URL
    $base_url = remove_query_arg( array('prev','next') );
    
    if ( $response['prev_page_token'] != '' ):
      $prev_uri = add_query_arg( 'prev', $response['prev_page_token'], $base_url );
      ?>
      <a href="<?php echo $prev_uri; ?>" class="button button-primary">
        <?php _e( 'Prev', 'tube-video-curator' ); ?>
      </a>
      <?php
    endif;
    
    

    if ( $response['next_page_token'] != '' ):
      $next_uri = add_query_arg( 'next', $response['next_page_token'], $base_url);
      ?>
      <a href="<?php echo $next_uri ?>" class="button button-primary">
        <?php _e( 'Next', 'tube-video-curator' ); ?>
      </a>
      <?php
    endif;
  }


 
  // Don't use the Yoast metabox on the Source post type
  function remove_yoast_metabox_source(){
      remove_meta_box('wpseo_meta', 'tube_source', 'normal');
  }
  
  
  
  // Add a thumnail to the Sources List Grid in admin
  function add_source_admin_columns($columns) {
    
    // remove some of the common columns
    unset($columns['date']);
    unset($columns['title']);
    
    // remove the oEmbed column
    unset($columns['tube_video_oembed_url']);
    
    // Add in the desired columns in desired order
    return array_merge($columns,
        array(
          'tube_source_thumb' => __('Image'),
          'title' => __('Title'),
          'tube_source_site' => __('Site'),
          'tube_feed_type' => __('Type'),
          'tube_source_autosync' => __('Auto-sync'),
        )
     );
  }
   

  // Customize the data to show in the new admin columns 
  function manage_source_admin_columns( $column, $post_id ) {
  
    global $tube_video_curator;
    
    switch ( $column ) {
   
      case 'tube_source_thumb' :
          
          $edit_link = get_edit_post_link($post_id);
          
          $thumbnail = get_the_post_thumbnail($post_id,  array(125,80))  ;
          
          echo '<a href="' . esc_url($edit_link) . '">' . $thumbnail . '</a>';
          break;
   
      case 'tube_source_site' :
        
          $tube_source_site = get_post_meta($post_id, 'tube_source_site', true);
          
          $tube_source_site_label = $tube_video_curator->get_external_site_label($tube_source_site);
          
          echo esc_html( $tube_source_site_label );
          
          break;
   
      case 'tube_feed_type' :
                  
          // Get the feed type setting for this source
          $tube_feed_type = get_post_meta($post_id, 'tube_feed_type', true);
          
          // get a label for the setting
          $tube_feed_type_label = $tube_video_curator->get_feed_type_label($tube_feed_type);
          
          // display the label
          echo esc_html( $tube_feed_type_label );
          
          break;
   
      case 'tube_source_autosync' :        
        
          // get the type
          $tube_source_type =  get_post_meta($post_id, 'tube_feed_type', true) ;
          
          // check to see if NOT YouTube and if not, echo a dash (-)
          if ( $tube_source_type != 'youtube-channel' ):
            echo '&mdash;';
            return;
          endif;
          
          // Get the auto sync setting for this source
          $tube_source_autosync = intval( get_post_meta($post_id, 'tube_source_autosync', true) );
          
          // get a label for the setting
          $tube_source_autosync_label = $this->get_source_autosync_label($tube_source_autosync);
          
          // display the label
          echo esc_html( $tube_source_autosync_label );
          
          break;
    }
      
  }
  
  
  // Get available autosync options
  function get_source_autosync_options(){
        
     $options = array();

     $options[0] = __( 'No', 'tube-video-curator' );
     
     $options[1] = __( 'Yes', 'tube-video-curator' );
     
     return $options;
     
  }
  
  
  
  // Get the label for a given autosync option
  function get_source_autosync_label( $key ){
        
     $options = $this->get_source_autosync_options();
     
     if ( ! array_key_exists($key, $options) ):
       return NULL;
     endif;
     
     return $options[$key];
     
  }
  
  
   
  // customize post updated messages for Source 
  function source_post_updated_messages( $messages ) {
    
    global $post, $post_ID, $tube_video_curator;
           
    $post_type = get_post_type_object( $post->post_type );
    
    if ( $post->post_type != 'tube_source' ) :
      return $messages;
    endif;
    
    $post_type_name = $post_type->labels->singular_name;
    $post_type_name_lower = strtolower( $post_type_name );    
    
    
    $source_site_id = get_post_meta($post->ID, 'tube_source_site', true );
    
    $source_site = $tube_video_curator->get_external_site_label( $source_site_id );
          
          
    // Get the tube_source_autosync if its already been entered
    $autosync_setting = get_post_meta($post_ID, 'tube_source_autosync', true);
      
    $saved_addon = ( $autosync_setting ) ? __('Autosync is on.') : __('Autosync is off.');
       
        
        
    $messages[$post->post_type] = array(
      0 => '', // Unused. Messages start at index 1.
      1 => __($source_site . ' ' . $post_type_name_lower . ' updated.'),
      2 => __('Custom field updated.'),
      3 => __('Custom field deleted.'),
      4 => __($source_site . ' ' . $post_type_name_lower . ' updated.' . $saved_addon),
      /* translators: %s: date and time of the revision */
      5 => isset($_GET['revision']) ? sprintf( __($post_type_name . ' restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
      6 => __($source_site . ' ' . $post_type_name_lower . ' saved. ' . $saved_addon),
      7 => __($source_site . ' ' . $post_type_name_lower . ' saved. ' . $saved_addon),
      8 =>  __($source_site . ' ' . $post_type_name_lower . ' submitted.'),
      9 => sprintf( __($source_site . ' ' . $post_type_name . ' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview '.$post_type_name_lower.'</a>'),
        // translators: Publish box date format, see http://php.net/date
        date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
      10 => sprintf( __($post_type_name . ' draft updated. <a target="_blank" href="%s">Preview '.$post_type_name_lower.'</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    );
    return $messages;
    
  }


  // get "Source" posts
  function get_sources( $args = NULL ){
       
    $defaults = array(
      'posts_per_page' => -1,
    );
    $args = wp_parse_args( $args, $defaults );
    
    $args['post_type'] = 'tube_source';
    
   // See if the user has any sources
   $sources = get_posts( $args );     
   
   return $sources;
       
  }
  
}