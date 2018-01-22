(function($){

	 
       //almost done modal dialog here 
       $( "#esig-cf7-almost-done" ).dialog({
			  dialogClass: 'esig-dialog',
			  height:350,
			  width:350,
			  modal: true,
			});
            
      // do later button click 
       $( "#esig-cf7-setting-later" ).click(function() {
          $( '#esig-cf7-almost-done' ).dialog( "close" );
        });
      
     
		
})(jQuery);


