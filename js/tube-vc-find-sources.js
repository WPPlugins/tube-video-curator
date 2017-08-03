
      
!function ($) {       
  

        function tube_vc_set_find_sources_query_hint( $query_option ){

            $hint = $('#source-query-hint');

            switch( $query_option.val() ) {
              case 'youtube-channel':
                  $hint.html( Tube_VC_Ajax_Sources.trans.hintYouTubeChannel );        
                  break;
              case 'youtube-playlist':
                  $hint.html( Tube_VC_Ajax_Sources.trans.hintYouTubePlaylist );        
                  break;
              case 'vimeo-username':
                  $hint.html( Tube_VC_Ajax_Sources.trans.hintVimeoUsername );        
                  break;
              case 'vimeo-channel':
                  $hint.html( Tube_VC_Ajax_Sources.trans.hintVimeoChannel );        
                  break;
              default:
                  break;
          }
              
        }
        
        
  $(function(){         // document ready   
    
    // Adds custom hints to the query box in the "Add New Channels & Playlists / Find Sources" page
    
       $('select[name="tube_vc_find_sources_feed_type"]').on('change', function(event) {                       
         
         tube_vc_set_find_sources_query_hint( $(this) );  
          
      });
      
      // check the query typ4e on load to populate the help text
      tube_vc_set_find_sources_query_hint( $('select[name="tube_vc_find_sources_feed_type"]') );     
                   
    
  });
}(window.jQuery);
        
        
        
jQuery(document).ready(function() {

    Tube_VC_Ajax_Sources.init();      
    
});

var Tube_VC_Ajax_Sources = {
    query: '',
    trans: {},
    init: function(tag) {
        Tube_VC_Ajax_Sources.saveSource();
    },  
    
    showAlert: function(message) {
        var msg;
        jQuery('#tube-video-curator-notice').removeClass('notice-info notice-error updated is-dismissible');
        
        if (message === "sourceAdded") {
            jQuery('#tube-video-curator-notice').addClass('updated is-dismissible');
            msg = Tube_VC_Ajax_Sources.trans.sourceAdded;
        }
        else if (message === "sourceAddError") {
            jQuery('#tube-video-curator-notice').addClass('notice-error is-dismissible');
            msg = Tube_VC_Ajax_Sources.trans.sourceAddError;
        }
        jQuery('#tube-video-curator-notice p').html(msg).fadeIn(function() {
            jQuery('#tube-video-curator-notice').fadeIn();   
        }).after('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>');

    },  
    saveSource: function() {
        jQuery(document).on('click', '.btn-save-source', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $that = jQuery(this);

            $result = $that.closest('.result');

            $result.addClass('loading');
            
            var $autosync_label = $that.next('label');
            
            var autosync = $autosync_label.children('input.autosync-toggle').prop('checked');
            
            autosync = autosync ? 1 : 0;
            
            //console.log(autosync);
            
            //return;
           
            var data = {
                'action': 'tube_vc_save_source_via_ajax',
                'title': $that.attr('data-title'),
                'external_guid': $that.attr('data-external-guid'),
                'content': $that.attr('data-content'),
                'image_url': $that.attr('data-image-url'),
                'external_url': $that.attr('data-external-url'),
                'site': $that.attr('data-site'),
                'feed_type': $that.attr('data-feed-type'),
                'autosync': autosync
            };
            
            //console.log(data);
            //return;
            
            jQuery('#tube-video-curator-notice').removeClass('notice-info notice-error updated is-dismissible').addClass('notice-info');
            jQuery('#tube-video-curator-notice p').html(Tube_VC_Ajax_Sources.trans.loading).parent().fadeIn();
            jQuery.post(ajaxurl, data, function(response) {
              
                if (response === 'error') {

                    $result.removeClass('loading');
                    Tube_VC_Ajax_Sources.showAlert('sourceAddError');
                    
                } else {
                    
                    response = response.replace(/&amp;/g, '&');
                    // alert(response);
                                         
                    var publish_flag = '<strong class="wp-ui-text-notification">['+Tube_VC_Ajax_Sources.trans.sourcePublishedFlag+']</strong> ';
                    
                    $that
                      .attr('href',response)
                      .removeClass('btn-save-source button-primary')
                      .addClass('btn-edit-source')
                      .text('Find Videos')
                      .blur();
                    
                    $autosync_label.hide();
                    
                    $result.toggleClass('source-status-publish')
                        .find('.video-title a')
                        .before(publish_flag);
                        
                    $result.removeClass('loading');
                    
                    Tube_VC_Ajax_Sources.showAlert('sourceAdded');
                    
                    return;
                }
                
            });
        });
    }
    
    
};

