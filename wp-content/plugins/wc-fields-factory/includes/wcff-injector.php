<?php 

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Wcff_FieldsInjector {
	
	private $group_index = 1;
	private $is_datepicker_there = false;
	private $is_colorpicker_there = false;
	
	private $fields_cloning = "no";
	private $multilingual = false;
	
	private $product_fields = null;
	private $admin_fields = null;
	
	public function __construct() {}
	
	public function inject() {
		Global $product;
		$fields_group_title = "";
		$wccpf_options = wcff()->option->get_options();
		$this->fields_cloning = isset( $wccpf_options["fields_cloning"] ) ? $wccpf_options["fields_cloning"] : "no";
		$this->multilingual = isset($wccpf_options["enable_multilingual"]) ? $wccpf_options["enable_multilingual"] : "no";
		
		if (isset( $wccpf_options["fields_group_title"]) && $wccpf_options["fields_group_title"] != "") {
			$fields_group_title = $wccpf_options["fields_group_title"];
		} else {
			$fields_group_title = "Additional Options : ";
		}
		
		/* Translate cloning title - if multilingual option enabled */
		if ($this->multilingual == "yes") {
			$current_locale = wcff()->locale->detrmine_current_locale();
			if ($current_locale != "en" && isset($wccpf_options["fields_group_title_". $current_locale]) && ! empty($wccpf_options["fields_group_title_". $current_locale])) {
				$fields_group_title = $wccpf_options["fields_group_title_". $current_locale];
			}
		}
		
		/* Let other plugins change the Cloning Title */
		if (has_filter('wccpf_cloning_fields_group_title')) {
			$fields_group_title = apply_filters('wccpf_cloning_fields_group_title', $fields_group_title);
		}
		
		$this->product_fields = wcff()->dao->load_fields_for_product($this->get_product_id($product), 'wccpf');
		$this->admin_fields = wcff()->dao->load_fields_for_product($this->get_product_id($product), 'wccaf', 'any');
		
		do_action('wccpf_before_fields_start');
		
		/* Inject label field - whichever comes at top */
		$this->handle_label_field("beginning");
		
		/* Fields wrapper container starts here ( only for cloning option, otherwise no wrapper added ) */
		if ($this->fields_cloning == "yes") {
			if (count($this->product_fields) > 0 || count($this->admin_fields) > 0) {
				echo '<div id="wccpf-fields-container">';
				echo '<input type="hidden" id="wccpf_fields_clone_count" value="1" />';
				echo '<div class="wccpf-fields-group">';
				echo '<h4>'. $fields_group_title .' <span class="wccpf-fields-group-title-index">1</span></h4>';
			}
		}
		
		/* Inject product fields */
		$this->render_product_fields();
		/* Reset group index */
		$this->group_index = 1;
		/* Inject admin fields */
		$this->render_admin_fields();
		
		/**/
		WC()->session->__unset("wcff_validation_failed");
		
		if ($this->fields_cloning == "yes") {
			if (count($this->product_fields) > 0 || count($this->admin_fields) > 0) {
				echo '</div>';
				echo '</div>';
			}
		}
		/* Cloning wrapper ends */
		
		/* Inject label field - whichever comes at bottom */
		$this->handle_label_field("end");
		
		do_action( 'wccpf_after_fields_end' );
		
		/**/
		$this->enqueue_client_side_assets();
		
		?>
	    
	    <script type="text/javascript">
	    var wccpf_opt = {
	        cloning : "<?php echo isset( $wccpf_options["fields_cloning"] ) ? $wccpf_options["fields_cloning"] : "no"; ?>",
	        location : "<?php echo isset( $wccpf_options["field_location"] ) ? $wccpf_options["field_location"] : "woocommerce_before_add_to_cart_button"; ?>",
	        validation : "<?php echo isset( $wccpf_options["client_side_validation"] ) ? $wccpf_options["client_side_validation"] : "no"; ?>",
	        validation_type : "<?php echo isset( $wccpf_options["client_side_validation_type"] ) ? $wccpf_options["client_side_validation_type"] : "submit"; ?>"
	    };
	    </script>
	    
	    <?php	   	    
	}
	
	private function render_product_fields() {
		if (count($this->product_fields) > 0) {
			foreach ($this->product_fields as $fields) {
				if (is_array($fields) && count($fields) > 0) {
					echo '<div class="wccpf-fields-group-'. $this->group_index++ .'">';
					
					/* Trigger 'wccpf_before_fields_group_start' action, can be used to display title for this group */
					do_action('wccpf_before_fields_group_start', $this->group_index);
					
					foreach ($fields as $key => $field) {
						if ($field["type"] == "label" && $field["position"] != "normal") {
							continue;
						}	
						if ($this->multilingual == "yes") {
							/* Localize field */
							$field = wcff()->locale->localize_field($field);
						}  	
						/*
						 * This is not necessary here, but variation fields have some issues, so we have to do this in all places
						 * Since CSS class name connot contains special characters especially [ ] */
						if ($field["type"] == "datepicker" || $field["type"] == "colorpicker") {
							$field["admin_class"] = $field["name"];
						}
						
						if (WC()->session->__isset("wcff_validation_failed")) {
							/* Last add to cart operation failed
							 * Try to restore the fields old value */
							$index = "";
							if ($this->fields_cloning == "yes") {
								$index= "_1";
							}
							if (isset($_REQUEST[$field["name"] . $index])) {
								$field["default_value"] = $_REQUEST[$field["name"] . $index];
							}
						}
						
						/* generate html for wccpf fields */
						$html = wcff()->builder->build_user_field($field, "wccpf");
						/* Allow third party apps logic to render wccpf fields with their own wish */
						if (has_filter('wccpf_before_fields_rendering')) {
							$html = apply_filters('wccpf_before_fields_rendering', $field, $html);
						}
						
						do_action('wccpf_before_field_start', $field);
						
						echo $html;
						
						do_action('wccpf_after_field_end', $field);
						
						if ($field["type"] == "datepicker") {
							$this->is_datepicker_there = true;
						}						
						if ($field["type"] == "colorpicker") {
							$this->is_colorpicker_there = true;
						}
					}
					echo '</div>';
				}
			}
		}
	}
	
	private function render_admin_fields() {
	    Global $product;
	    if (count($this->admin_fields) > 0) {
			foreach ($this->admin_fields as $afields) {
			    if (count($afields) > 0) {
					echo '<div class="wccpf-admin-fields-group-'. $this->group_index++ .'">';
					foreach ($afields as $key => $afield) { 
						$afield["show_on_product_page"] = isset($afield["show_on_product_page"]) ? $afield["show_on_product_page"] : "no";
						/* Url field is special case here
						 * As the reason for the field itself to display some reference link on front end product page */
						if ($afield["show_on_product_page"] == "yes" || $afield["type"] == "url") {							
							if ($this->multilingual == "yes") {
								/* Localize field */								
								$afield = wcff()->locale->localize_field($afield);
							}  							
						    /* Determine the fields value */
							$afield["default_value"] = $this->determine_field_value($afield, $this->get_product_id($product));
							/* Set this property for helping builder
							 * Admin's select, check & radio fields value attribute is difference from the Product field */
							$afield["is_admin_field"] = true;
							
							if (WC()->session->__isset("wcff_validation_failed")) {
								/* Last add to cart operation failed
								 * Try to restore the fields old value */
								$index = "";
								if ($this->fields_cloning == "yes") {
									$index= "_1";
								}
								if (isset($_REQUEST[$afield["name"] . $index])) {
									$afield["default_value"] = $_REQUEST[$afield["name"] . $index];
								}
							}
							
							/*
							 * This is not necessary here, but variation fields have some issues, so we have to do this in all places
							 * Since CSS class name connot contains special characters especially [ ] */
							if ($afield["type"] == "datepicker" || $afield["type"] == "colorpicker") {
								$afield["admin_class"] = $afield["name"];
							}
							
							/* generate html for wccpf fields
							 * Eventhough it is an admin field we are rendering this as a product field ( hense 'wccpf' ) */
							$html = wcff()->builder->build_user_field($afield, "wccpf");
							/* Allow third party apps logic to render wccpf fields with their own wish */
							if (has_filter('wccpf_before_fields_rendering')) {
								$html = apply_filters('wccpf_before_fields_rendering', $afield, $html);
							}
							
							do_action('wccpf_before_field_start', $afield);
							
							echo $html;
							
							do_action('wccpf_after_field_end', $afield);
							
							if ($afield["type"] == "datepicker") {
								$this->is_datepicker_there = true;
							}							
							if ($afield["type"] == "colorpicker") {
								$this->is_colorpicker_there = true;
							}					
						}
					}
					echo '</div>';
			    }
			}
		}
	}
	
	/**
	 * 
	 * Helper method for retrieving Admin Field's value
	 * If value no there then default value will be returned
	 * Except check box other fields value will be returned as it is,
	 * but for checkbox the value will be converted as Array and then returned
	 * 
	 * @param object $_meta
	 * @param number $_id
	 * @param string $_ptype
	 * @return boolean|array|unknown|mixed|string
	 * 
	 */
	private function determine_field_value($_meta, $_id = 0) {
	    $mval = false;
	    /**
	     * We are assuming that here the user will use whatever the Admin Fields that is placed for the product page
	     * not on the Product Taxonomies page or Admin Fields for variable sections. because it doesn't make any sense.
	     * and if they do then we have a problem here
	     */
        if (metadata_exists("post", $_id, "wccaf_". $_meta["name"])) {
            $mval = get_post_meta($_id, "wccaf_". $_meta["name"], true);
            /* Incase of checkbox - the values has to be deserialzed as Array */
            if ($_meta["type"] == "checkbox") {
                $mval = explode(',', $mval);
            }
        } else {
            /* This will make sure the following section fill with default value instead */
            $mval = false;
        }	    
	    /* We can trust this since we never use boolean value for any meta
	     * instead we use 'yes' or 'no' values */
	    if (!$mval) {
	        /* Value is not there - probably this field is not yet saved */
	        if ($_meta["type"] == "checkbox") {
	            $d_choices = array();
	            if ($_meta["default_value"] != "") {
	                $choices = explode(";", $_meta["default_value"]);
	                if (is_array($choices)) {
	                    foreach ($choices as $choice) {
	                    	$d_value = explode("|", $choice);
	                    	$d_choices[] = $d_value[0];
	                    }
	                }
	            }
	            $mval = $d_choices;
	        } else if ($_meta["type"] == "radio" || $_meta["type"] == "select") {
	            $mval = "";
	            if ($_meta["default_value"] != "") {
	            	$d_value = explode("|", $_meta["default_value"]);
	            	$mval = $d_value[0];
	            }
	        } else {
	            /* For rest of the fields - no problem */
	        	$mval = isset($_meta["default_value"]) ? $_meta["default_value"] : "";
	        }
	    }
	    return $mval;
	}
	
	private function handle_label_field($position = "beginning") {
		foreach ($this->product_fields as $fields) {
			if (is_array($fields) && count($fields) > 0) {
				foreach ($fields as $field) {
					if ($field["type"] == "label" && $field["position"] == $position) {
						/* generate html for wccpf fields */
						$html = wcff()->builder->build_user_field($field, "wccpf");
						/* Allow third party apps logic to render wccpf fields with their own wish */
						if (has_filter('wccpf_before_fields_rendering')) {
							$html = apply_filters('wccpf_before_fields_rendering', $field, $html);
						}
						
						do_action('wccpf_before_field_start', $field);
						
						echo $html;
						
						do_action('wccpf_after_field_end', $field);
					}
				}
			}
		}		
	}
	
	/**
	 *
	 * @param WC_Product $_product
	 * @return integer
	 *
	 * Wrapper method for getting Wc Product object's ID attribute
	 *
	 */
	private function get_product_id($_product){
	    return method_exists($_product, 'get_id') ? $_product->get_id() : $_product->id;
	}
	
	/**
	 * 
	 * Enqueue assets for Front end Product Page
	 * 
	 * @param boolean $isdate_css
	 * 
	 */
	private function enqueue_client_side_assets($isdate_css = false) { ?>
		
		<?php if (is_product() || is_cart() || is_checkout()) : ?>
		
			<?php if($this->is_datepicker_there) : ?>
			
				<link rel="stylesheet" type="text/css" href="<?php echo wcff()->info['dir']; ?>assets/css/jquery-ui.css">
				<link rel="stylesheet" type="text/css" href="<?php echo wcff()->info['dir']; ?>assets/css/jquery-ui-timepicker-addon.css">
					
				<?php if(!wp_script_is("jquery-ui-core")) : ?>
					<?php 
						$jquery_ui_core = includes_url() . "/js/jquery/ui/core.min.js";									
						if(!file_exists(ABSPATH ."wp-includes/js/jquery/ui/core.min.js")) {
							$jquery_ui_core = includes_url() . "/js/jquery/ui/jquery.ui.core.min.js";											
						}
					?>
					<script type="text/javascript" src="<?php echo $jquery_ui_core; ?>"></script>
				<?php endif; ?>
			
				<?php if(!wp_script_is("jquery-ui-datepicker")) : ?>
					<?php 
						$jquery_ui_datepicker = includes_url() . "/js/jquery/ui/datepicker.min.js";	
						if(!file_exists(ABSPATH ."wp-includes/js/jquery/ui/core.min.js")) {
							$jquery_ui_datepicker = includes_url() . "/js/jquery/ui/jquery.ui.datepicker.min.js";	
						}
					?>
					<script type="text/javascript" src="<?php echo $jquery_ui_datepicker; ?>"></script>					
				<?php endif; ?>		
			
				<script type="text/javascript" src="<?php echo wcff()->info['dir']; ?>assets/js/jquery-ui-i18n.min.js"></script>
				<script type="text/javascript" src="<?php echo wcff()->info['dir']; ?>assets/js/jquery-ui-timepicker-addon.min.js"></script>
			
			<?php endif; ?>
			
			<?php if($this->is_colorpicker_there) : ?>			
				<link rel="stylesheet" type="text/css" href="<?php echo wcff()->info['dir']; ?>assets/css/spectrum.css">
				<script type="text/javascript" src="<?php echo wcff()->info['dir']; ?>assets/js/spectrum.js"></script>	
						
			<?php 
			
				$this->enqueue_color_picker_script();
				endif; ?>
		
		<link rel="stylesheet" type="text/css" href="<?php echo wcff()->info['dir']; ?>assets/css/wcff-client.css">
		<script type="text/javascript" src="<?php echo wcff()->info['dir']; ?>assets/js/wcff-client.js"></script>
		
		<?php endif; ?>
		
		<?php 
		
	}
	
	private function enqueue_color_picker_script() {
		Global $product;
		
		echo '<script type="text/javascript">
				var $ = jQuery;
				function wccpf_init_color_pickers() {';
		
		foreach ( $this->product_fields as $title => $fields ) {
			foreach ( $fields as $key => $field ) {
				if( $field["type"] == "colorpicker" ) {
					$palettes = null;
					$colorformat = isset( $field["color_format"] ) ? $field["color_format"] : "hex";
					$defaultcolor = isset( $field["default_value"] ) ? $field["default_value"] : "#000";
					
					if( isset( $field["palettes"] ) && $field["palettes"] != "" ) {
						$palettes = explode( ";", $field["palettes"] );
					} ?>					
											
					$( ".wccpf-color-<?php echo esc_attr( $field["name"] ); ?>").spectrum({
						 color: "<?php echo $defaultcolor; ?>", 
						 preferredFormat: "<?php echo $colorformat; ?>",					
						<?php 
						$comma = "";
						$indexX = 0;
						$indexY = 0;
						if( is_array( $palettes ) && count( $palettes ) > 0 ) {
							if( $field["show_palette_only"] == "yes" ) {
								echo "showPaletteOnly: true,";
							}
							echo "showPalette: true,";
							echo "palette : [";						
							foreach ( $palettes as $palette ) {		
								$indexX = 0;								
								$comma = ( $indexY == 0 ) ? "" : ",";
								echo $comma."[";
								$colors = explode( ",", $palette );
							 	foreach ( $colors as $color ) {							 		
							 		$comma = ( $indexX == 0 ) ? "" : ","; 
							 		echo $comma ."'". $color ."'";	
							 		$indexX++;
								}
								echo "]";
								$indexY++;
							} 
							echo "]";						
						}
						?>
					});		
				<?php 
				}
			}
		}		
			
		if (count($this->admin_fields) > 0) {
			foreach ($this->admin_fields as $title => $afields) {
				if (count($afields) > 0) {
					foreach ($afields as $key => $afield) {
						if($afield["type"] == "colorpicker") {
							$palettes = null;
							$colorformat = isset($afield["color_format"]) ? $afield["color_format"] : "hex";
							$defaultcolor = isset($afield["default_value"]) ? $afield["default_value"] : "#000";
							
							$mval = get_post_meta($this->get_product_id($product), "wccaf_". $afield["name"], true);
							if (!$mval || $mval == "") {								
								$mval = $defaultcolor;								
							}
								
							if (isset( $afield["palettes"]) && $afield["palettes"] != "") {
								$palettes = explode( ";", $afield["palettes"] );
							} ?>
							
							$(".wccpf-color-<?php echo esc_attr($afield["name"]); ?>").spectrum({
								 color: "<?php echo $mval; ?>", 
								 preferredFormat: "<?php echo $colorformat; ?>",					
								<?php 
								$comma = "";
								$indexX = 0;
								$indexY = 0;
								if (is_array($palettes) && count($palettes) > 0) {
									if ($afield["show_palette_only"] == "yes") {
										echo "showPaletteOnly: true,";
									}
									echo "showPalette: true,";
									echo "palette : [";						
									foreach ($palettes as $palette) {		
										$indexX = 0;								
										$comma = ($indexY == 0) ? "" : ",";
										echo $comma."[";
										$colors = explode(",", $palette);
									 	foreach ($colors as $color) {							 		
									 		$comma = ($indexX == 0) ? "" : ","; 
									 		echo $comma ."'". $color ."'";	
									 		$indexX++;
										}
										echo "]";
										$indexY++;
									} 
									echo "]";						
								}
								?>
							});	
							
							<?php 
						}
					}	
				}				
			}
		}
			
		echo '}				
			$(document).ready(function() {			
				wccpf_init_color_pickers();
			});
		</script>';
	}
	
}



?>