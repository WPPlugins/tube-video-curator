!function ($) {        
  
  $(function(){         // document ready
          
    // on editor for TUBE Source post type, disbable the title tag editor
    if($('#post_type').val() === 'tube_source'){
      $('#title').attr('disabled','disabled');
    }
    
  });  
}(window.jQuery);
      
      
      
jQuery(document).ready(function() {

    Tube_VC_Ajax_Videos.init(Tube_VC_Ajax_Videos.query);
    
    jQuery('#updateCatOnChange').on('change', function() {
        Tube_VC_Ajax_Videos.updateCategories('#updateCatOnChange');
    });
    
});


var Tube_VC_Ajax_Videos = {
    query: '',
    lastID: '',
    nbPage: 0,
    trans: {},
    queryFormChanged: false,
    init: function(tag) {
        Tube_VC_Ajax_Videos.insertPost();
        Tube_VC_Ajax_Videos.rejectPost();
        Tube_VC_Ajax_Videos.updateCategories('#updateCatOnChange');
        Tube_VC_Ajax_Videos.publishAll();
        Tube_VC_Ajax_Videos.skipAll();
        Tube_VC_Ajax_Videos.restore();
    },
    showAlert: function(message) {
        var msg;
        jQuery('#tube-video-curator-notice').removeClass('notice-info notice-error updated is-dismissible');
        
        if (message === "needUpdate") {
            msg = Tube_VC_Ajax_Videos.trans.needUpdate;
        }
        else if (message === "mediaFailed") {
            jQuery('#tube-video-curator-notice').addClass('updated-error is-dismissible');
            msg = Tube_VC_Ajax_Videos.trans.mediaFailed;
        }
        else if (message === "mediaAdded") {
            jQuery('#tube-video-curator-notice').addClass('updated is-dismissible');
            msg = Tube_VC_Ajax_Videos.trans.mediaAdded;
        }
        else if (message === "mediaRejected") {
            jQuery('#tube-video-curator-notice').addClass('notice-error is-dismissible');
            msg = Tube_VC_Ajax_Videos.trans.mediaRejected;
        }
        else if (message === "sourceAdded") {
            jQuery('#tube-video-curator-notice').addClass('updated is-dismissible');
            msg = Tube_VC_Ajax_Videos.trans.sourceAdded;
        }
        else if (message === "restoreFailed") {
            jQuery('#tube-video-curator-notice').addClass('notice-error is-dismissible');
            msg = Tube_VC_Ajax_Videos.trans.restoreFailed;
        }
        else if (message === "mediaRestored") {
            jQuery('#tube-video-curator-notice').addClass('updated is-dismissible');
            msg = Tube_VC_Ajax_Videos.trans.mediaRestored;
        }
        jQuery('#tube-video-curator-notice p').html(msg).fadeIn(function() {
            jQuery('#tube-video-curator-notice').fadeIn();   
        }).after('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');

    },
    updateCategories: function(el) {
        var type = jQuery(el).val();
        var data = {
            'action': 'get_all_terms_for_tube_video_post_type_via_ajax',
            'post_type': type
        };

        jQuery.post(ajaxurl, data, function(response) {

            if (response === "empty") {
                jQuery('#catsSelect').addClass('hidden');
            } else {
                jQuery('#catsSelect').removeClass('hidden');
                jQuery('#catsSelect select').html(function() {
                    var html = "";
                    response = JSON.parse(response);
                    for (var i = 0; i < response.length; i++) {
                        if (Tube_VC_Ajax_Videos.currentCat == response[i].id) {
                           html += '<option selected value="' + response[i].id + '" >' + response[i].name + '</option>';
                        } else {
                          html += '<option value="' + response[i].id + '" >' + response[i].name + '</option>';
                        }



                    }
                    return html;
                });
            }




        });
    },
    insertPost: function() {
        jQuery(document).on('click', '.btn-import', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $that = jQuery(this);

            $result = $that.closest('.result');

            $result.addClass('loading');
            
            var data = {
                'action': 'create_tube_video_post_via_ajax',
                
                'id': $that.attr('data-id'),
                'title': $that.attr('data-title'),
                'channel_id': $that.attr('data-channel-id'),
                'content': $that.attr('data-content'),
                'embed': $that.attr('data-embed-url'),
                'image_url': $that.attr('data-image-url'),
                'media_url': $that.attr('data-media-url'),
                'author':  $that.attr('data-author'),
                'date': $that.attr('data-date'),
                'creator_name': $that.attr('data-creator-name'),
                'tags': $that.attr('data-tags'),
                'site': $that.attr('data-site'),
                'status': $that.attr('data-status'),  
                'post_type': Tube_VC_Ajax_Videos.post_type,
                'query': Tube_VC_Ajax_Videos.query,
                'queryType': Tube_VC_Ajax_Videos.queryType
            };
            
            jQuery('#tube-video-curator-notice').removeClass('notice-info notice-error updated is-dismissible').addClass('notice-info');
            jQuery('#tube-video-curator-notice p').html(Tube_VC_Ajax_Videos.trans.loading).parent().fadeIn();
            jQuery.post(ajaxurl, data, function(response) {
              
              
              
                if (response === 'error') {
                  
                    $result.removeClass('loading');
                    Tube_VC_Ajax_Sources.showAlert('mediaFailed');
                    
                } else {
                  
                    response = response.replace(/&amp;/g, '&');
                    response = JSON.parse(response);
                   //console.log(response.edit);
                   //console.log(response.view);
                     
                    
                    
                    var publish_flag = '<strong class="wp-ui-text-notification">['+Tube_VC_Ajax_Videos.trans.videoPublishedFlag+']</strong> ';
                    
                    var pending_flag = '<strong class="wp-ui-text-highlight">['+Tube_VC_Ajax_Videos.trans.videoPendingFlag+']</strong> ';
                    
                    var draft_flag = '<strong class="wp-ui-text-highlight">['+Tube_VC_Ajax_Videos.trans.videoDraftFlag+']</strong> ';
                    
                    
                    if ( data.status == 'publish' ){
                      
                      
                      $result.toggleClass('video-status-new video-status-publish')
                        .find('.video-title a')
                        .before(publish_flag);
                      
                      $result.find('tube-video-new-buttons').hide();
                      
                      $published_buttons = $result.find('.tube-video-published-buttons');
                                            
                      $published_buttons.children('.btn-view').attr('href',response.view);
                      $published_buttons.children('.btn-edit').attr('href',response.edit);
          
                      Tube_VC_Ajax_Videos.showAlert('mediaAdded');
                      
                    }
                    else  if ( data.status == 'draft' ){
                      
                      
                      $result.toggleClass('video-status-new video-status-draft')
                        .find('.video-title a')
                        .before(draft_flag);
                      
                      $result.find('tube-video-new-buttons').hide();
                      
                      $draft_buttons = $result.find('.tube-video-published-buttons');
                                            
                      $draft_buttons.children('.btn-view').attr('href',response.view);
                      $draft_buttons.children('.btn-edit').attr('href',response.edit);
          
                      Tube_VC_Ajax_Videos.showAlert('mediaAdded');
                      
                    }else{
                      
                      // this is the case for video set to Pending Review
                      
                      $result.toggleClass('video-status-new video-status-pending')
                        .find('.video-title a')
                        .before(pending_flag);
                      
                      $result.find('tube-video-new-buttons').hide();
                      
                      $pending_buttons = $result.find('.tube-video-pending-buttons');
                      
                      $pending_buttons.children('.btn-view') .attr('href',response.edit);
                      $pending_buttons.children('.btn-edit') .attr('href',response.view);
                      
                      Tube_VC_Ajax_Videos.showAlert('mediaAdded');
                      
                      
                    }
                      
                   
                    $result.removeClass('loading');
                    
                    checkNextResult( $result );
                    
                    return;
                    
                    
        				}
        				
            });
        });
    },
    rejectPost: function() {
        jQuery(document).on('click', '.btn-skip', function(e) {
            e.preventDefault();
            var $that = jQuery(this);

            $result = $that.closest('.result');

            $result.addClass('loading');
                    
            var data = {
                'action': 'create_skipped_tube_video_post_via_ajax',
                'id': $that.attr('data-id'),
                'title': $that.attr('data-title'),
                'channel_id': $that.attr('data-channel-id'),
                'content': $that.attr('data-content'),
                'embed': $that.attr('data-embed-url'),
                'image_url': $that.attr('data-image-url'),
                'media_url': $that.attr('data-media-url'),
                'author':  $that.attr('data-author'),
                'date': $that.attr('data-date'),
                'creator_name': $that.attr('data-creator-name'),
                'site': $that.attr('data-site')
            };
            
            jQuery('#tube-video-curator-notice p').html(Tube_VC_Ajax_Videos.trans.loading).parent().fadeIn();
            
            jQuery.post(ajaxurl, data, function(response) {
              
                if (response === 'error') {
                  
                    $result.removeClass('loading');
                    Tube_VC_Ajax_Sources.showAlert('mediaFailed');
                    
                } else {
                  
                    response = JSON.parse(response);

                    var skipped_flag = '<strong class="wp-ui-text-highlight">['+Tube_VC_Ajax_Videos.trans.videoSkippedFlag+']</strong> ';
                    
                    
                    $result.toggleClass('video-status-new video-status-skipped')
                        .find('.video-title a')
                        .before(skipped_flag);
                    
                    $result.find('.btn-restore').attr('data-postid',response.id);
                    
                    Tube_VC_Ajax_Videos.showAlert('mediaRejected');
                                        
                    
                    $result.removeClass('loading');
                    
                    checkNextResult( $result );
                    
                    return;
                    
                    //$that.attr('disabled','disabled');
                    //$that.siblings('a').attr('href','');
                }
                
            }); // wp_insert_post();
        });
    },
    restore: function() {
        jQuery(document).on('click', '.btn-restore', function(e) {
            e.preventDefault();
            var $that = jQuery(this);            

            $result = $that.closest('.result');

            $result.addClass('loading');
            
            var data = {
                'action': 'restore_skipped_tube_video_post_via_ajax',
                'postid': $that.attr('data-postid'),
            };
            
            jQuery('#tube-video-curator-notice p').html(Tube_VC_Ajax_Videos.trans.loading).parent().fadeIn();
            
            jQuery.post(ajaxurl, data, function(response) {
              
                
                if (response === 'error') {

                    $result.removeClass('loading');
            
                    Tube_VC_Ajax_Sources.showAlert('restoreFailed');
                } else {
                  
                    
                    $result.toggleClass('video-status-new video-status-skipped');
                    
                    $result.find('.wp-ui-text-highlight').remove();
                    
                    //$result.find('.tube-video-import-buttons').hide();
                    
                    Tube_VC_Ajax_Videos.showAlert('mediaRestored');
                    

                    $result.removeClass('loading');
                    //$that.attr('disabled','disabled');
                    //$that.siblings('a').attr('href','');
                }
            }); // wp_insert_post();
        });
    },
    publishAll: function() {
      
        var publishAllButton = jQuery('#publish-all');
        publishAllButton.on('click', function(e) { 
          e.preventDefault();
          jQuery(".tube-video-quicklinks").slideUp();
          doImportAll('.btn-publish');
        });
    },
    skipAll: function() {
      
        var skipAllButton = jQuery('#skip-all');
        skipAllButton.on('click', function(e) { 
          e.preventDefault();
          jQuery(".tube-video-quicklinks").slideUp();
          doImportAll('.btn-skip');
        });
        
    }
    
    
};


!function ($) { 
  
  window.checkNextResult = function( $result ){        
    

      // see if we need to click the next one
      var do_next = $result.attr('data-donext');
        
      if ( do_next === undefined ) return;
      
      $next_result = $result.next('.video-status-new');
      
      if( $next_result.length == 0 ){
        
        alert('All videos have been processed.');
        return;
        
      }
      
      // see if we need to click the next one
      var button_class_to_click = $result.attr('data-donext-btn');
      
      $next_button = $next_result.find(button_class_to_click);
      
      
      if( $next_button.length == 0 ){
        
        return;
        
      }
      
      // click the button for the next result
      $next_button.click();
      
  };
  
  window.doImportAll = function(btn_class){        
      
      var container = jQuery('.tube-admin-list-videos .result.video-status-new');  
      
      container.addClass('loading').attr('data-donext', 'true').attr('data-donext-btn', btn_class);
      
      container.first().find(btn_class).click();
      
  };
  
}(window.jQuery);