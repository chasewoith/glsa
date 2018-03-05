(function($){
        

        // next step click from sif pop
        $( "#esig-cf7-create" ).click(function() {
        
 
                   var form_id= $('select[name="esig-cf7-form-id"]').val();
                   
                 
                   $("#esig-cf7-form-first-step").hide();
                   
                   // jquery ajax to get form field . 
                   jQuery.post(esigAjax.ajaxurl,{ action:"esig_cf7_form_fields",form_id:form_id},function( data ){ 
                                $("#esig-cf7-field-option").html(data);
				},"html");
                   
                   $("#esig-cf7-second-step").show();                        
  
        });
 
        // contact for 7 add to document button clicked 
        $( "#esig-cf7-insert" ).click(function() {
         
 
                 var formid= $('select[name="esig-cf7-form-id"]').val();
                   
                 var field_id =$('select[name="esig_cf7_field_id"]').val();
                 
                 
                  var return_text = '[esigcf7 formid="'+ formid +'" field_id="'+ field_id +'" ]';
		  esig_sif_admin_controls.insertContent(return_text);
            
                 tb_remove();
                     
                   
        });
        
        
        //if overflow
        $('#select-cf7-form-list').click(function(){
            
            
          
            $(".chosen-drop").show(0, function () { 
				$(this).parents("div").css("overflow", "visible");
				});
            
            
            
        });
	
})(jQuery);



