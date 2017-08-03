!function ($) {   
   
    
   function tube_update_import_term_dropdown( post_type ){
     
     
        var data = {
            'action': 'get_all_terms_for_tube_video_post_type_via_ajax',
            'post_type': post_type
        };

     
        jQuery.post(ajaxurl, data, function(response) {

              
              
            if (response === "empty") {
                $('#catsSelect').addClass('hidden');
            } else {
                $('#catsSelect').removeClass('hidden');
                $('#catsSelect').html(function() {
                  
                    var html = "";
                    
                   html += '<option value="" >None</option>';
                    
                    response = JSON.parse(response);
                    
                    var prev_taxonomy = '';
                    
                    for (var i = 0; i < response.length; i++) {
                      
                      taxonomy = response[i].taxonomy_slug;
                      
          
                      if ( prev_taxonomy != taxonomy ){
                      
                        if ( prev_taxonomy != '' ){
                          html += '</optgroup>';
                        }
                        
                        html += '<optgroup label="' + response[i].taxonomy_name + '">';
                        
                      
                      }
                       // console.log(response[i].id + '----');
                        
                        if (Tube_VC_Ajax_Videos.currentCat == response[i].id) {
                           html += '<option selected value="' + response[i].id + '" >' + response[i].name + '</option>';
                        } else {
                          html += '<option value="' + response[i].id + '" >' + response[i].name + '</option>';
                        }

                      prev_taxonomy = taxonomy;

                    }
                    
                    //html += '</optgroup>';
                    
                    return html;
                });
            }




        });
        
        
     
   } 
   
   
   
    
   function tube_update_tag_import_taxonomy_dropdown( post_type ){
     
     
        var data = {
            'action': 'get_all_taxonomies_for_tube_video_post_type_via_ajax',
            'post_type': post_type
        };

     
        jQuery.post(ajaxurl, data, function(response) {
        
        
              
            if (response === "empty") {
                $('#tube_vc_default_tag_import_taxonomy').addClass('hidden');
            } else {
                $('#tube_vc_default_tag_import_taxonomy').removeClass('hidden');
                $('#tube_vc_default_tag_import_taxonomy').html(function() {
                  
              
                    var html = "";
                    
                   html += '<option value="" >None</option>';
                    
                    response = JSON.parse(response);
                    
                    var prev_taxonomy = '';
                    
                    for (var i = 0; i < response.length; i++) {
                      
                      taxonomy = response[i].taxonomy_slug;
                        
                        if (Tube_VC_Ajax_Videos.currentTagImportTax == response[i].taxonomy_slug) {
                           html += '<option selected value="' + response[i].taxonomy_slug + '" >' + response[i].taxonomy_name + '</option>';
                        } else {
                          html += '<option value="' + response[i].taxonomy_slug + '" >' + response[i].taxonomy_name + '</option>';
                        }

                      prev_taxonomy = taxonomy;

                    }
                    
                    //html += '</optgroup>';
                    
                    return html;
                });
            }




        });
        
        
     
   } 

  $(function(){         // document ready
    
     $('.settings-help-toggle').on('click', function(event) {
       
        event.preventDefault();
        event.stopPropagation();
        
        $that = $(this);      
       
        $that.parents('p').next('.settings-help').slideToggle( function(){
          $that.blur().find('i').toggleClass('dashicons-dismiss dashicons-editor-help');
        });
        
    });
    
    
     $("#updateCatOnChange").on('change', function(event) {
       
        event.preventDefault();
        event.stopPropagation();
        
        var post_type = $(this).val();
        
        tube_update_import_term_dropdown( post_type );
        
        tube_update_tag_import_taxonomy_dropdown( post_type );
        
    });
    
    
    
    
    
    
    
    
  }) ; 
}(window.jQuery);