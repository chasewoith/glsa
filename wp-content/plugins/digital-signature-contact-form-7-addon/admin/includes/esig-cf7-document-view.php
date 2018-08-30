<?php
/**
 *
 * @package ESIG_NINJAYFORM_DOCUMENT_VIEW
 * @author  Arafat Rahman <arafatrahmank@gmail.com>
 */



if (! class_exists('esig-cf7-document-view')) :
class esig_cf7_document_view {
    
    
            /**
        	 * Initialize the plugin by loading admin scripts & styles and adding a
        	 * settings page and menu.
        	 * @since     0.1
        	 */
        	final function __construct() {
                        
        	}
        	
        	/**
        	 *  This is add document view which is used to load content in 
        	 *  esig view document page
        	 *  @since 1.1.0
        	 */
        	
        	final function esig_cf7_document_view()
        	{
        	    
        	   
        	    $assets_dir = ESIGN_ASSETS_DIR_URI;
        	    
                    
        	   $more_option_page = ''; 
        	   
        	    
        	    $more_option_page .= '<div id="esig-contact-option" class="esign-form-panel" style="display:none;">
        	        
        	        
                	               <div align="center"><img src="' . $assets_dir .'/images/logo.png" width="200px" height="45px" alt="Sign Documents using WP E-Signature" width="100%" style="text-align:center;"></div>
                    			
                                    
                    				<div id="esig-cf7-form-first-step">
                        				
                                        	<div align="center" class="esig-popup-header esign-form-header">'.__('What Are You Trying To Do?', 'esig').'</div>
                                            	
                        				<p id="create_cf7" align="center">';
                                	    
                                	    $more_option_page .=	'
                        			
                        				<p id="select-cf7-form-list" align="center">
                                	    
                        		        <select data-placeholder="Choose a Option..." class="chosen-select" tabindex="2" id="esig-cf7-form-id" name="esig-cf7-form-id">
                        			     <option value="sddelect">'.__('Select a Contact Form 7', 'esig').'</option>';
                                          
                                          $contact_form = get_posts(array('post_type'=>'wpcf7_contact_form','posts_per_page'   => -1)); 
                                            
                                         
                                            if(!empty($contact_form)){
                                            
                                	    foreach($contact_form as $form)
                                	    {
                                           
                                	        $more_option_page .=	'<option value="'. $form->ID . '">'.$form->post_title.'</option>';
                                	    }
                                            }
                                           
                                	    $more_option_page .='</select>
                                	    
                        				</p>
                         	  
                                	    </p>
                                            
                                           
                         	  
                                	    </p>
                                	    
                                        <p id="upload_cf7_button" align="center">
                                           <a href="#" id="esig-cf7-create" class="button-primary esig-button-large">'.__('Next Step', 'esig').'</a>
                                         </p>
                                     
                                    </div>  <!-- Frist step end here  --> ';
                            
                                    
                 $more_option_page .='<!-- Cf7 form second step start here -->
                                            <div id="esig-cf7-second-step" style="display:none;">
                                            
                                        	<div align="center" class="esig-popup-header esign-form-header">'.__('What contact form 7 field data would you like to insert?', 'esig').'</div>
                                            
                                            <p id="esig-cf7-field-option" align="center">
                               



                                             </p>
                                            
                                               
                                            
                                             <p id="upload_cf7_button" align="center">
                                           <a href="#" id="esig-cf7-insert" class="button-primary esig-button-large" >'.__('Add to Document', 'esig').'</a>
                                         </p>
                                            
                                            </div>
                                    <!-- cf7 form second step end here -->';           
                                    
                                    
        	    
        	    $more_option_page .= '</div><!--- cf7 option end here -->' ;
        	    
        	    
        	    return $more_option_page ; 
        	}
       
    }
endif ; 

