



        <?php if(!is_plugin_active('contact-form-7/wp-contact-form-7.php')) : ?>
        
        
        <div class="error"><span class="icon-esig-alert"></span><h4>Contact Forms 7 plugin is not installed. Please install Contact Forms 7 version 2.0 or greater - <a href="https://wordpress.org/plugins/contact-form-7/">Get it here now</a></h4></div>
        
        
        <?php endif ; 
        
        if(!function_exists("WP_E_Sig")):

        ?>
        
          <div class="error"> <h4>WP E-Signature is not installed. &nbsp; It is required to run the Contact Forms 7 Signature add-on. &nbsp;Get your business license now  - <a href="http://www.approveme.com/?utm_source=wprepo&utm_medium=link&utm_campaign=contactform7">http://aprv.me</a></h4></div>
        
        <?php 
        endif; 
        
        if(!class_exists('ESIG_SAD_Admin')) :
        
        ?>
        <div class="error"><span class="icon-esig-alert"></span><h4>WP E-Signature <a href="https://www.approveme.me/downloads/stand-alone-documents/?utm_source=wprepo&utm_medium=link&utm_campaign=contactform7" target="_blank">"Stand Alone Documents"</a> Add-on is not installed. Please install WP E-Signature Stand Alone Documents - version 1.2.5 or greater.  </h4></div>
        
        <?php endif; 
        
        if(!class_exists('ESIG_SIF_Admin')) :
        
        ?>
        
        <div class="error"><span class="icon-esig-alert"></span><h4>WP E-signature <a href="https://www.approveme.me/downloads/signer-input-fields/?utm_source=wprepo&utm_medium=link&utm_campaign=contactform7" target="_blank">"Custom Fields/Signer Input Fields"</a> is not installed. Please install WP E-Signature Custom Fields/Signer Input Fields Version 1.2.5 or greater.</h4></div>
         
        <?php endif ; ?>

