<?php 

if (!defined('ABSPATH')) { exit; }
/**
 * 
 * Responsible for rendering Custom data on Cart & Checkout<br/>
 * If either fields cloning is enabled or Editable option is enabled (or both)
 * 
 * @author Saravana Kumar K
 * @copyright Sarkware Pvt Ltd
 *
 */
class Wcff_CartEditor {
    
	/* Holds the supplied html of the callback (may have Title or Quantity) */
    private $html;
    /* Holds the Cart Item Object */
    private $cart_item = null;
    /* Holds the Cart Item Key */
    private $cart_item_key = null;
    /* Flaq that tells whether we are in Cart or Checkout */
    private $is_review_table;
    
    /* Holds the generated html of custom field */
    private $meta_html;
    /*Cloning flag */
    private $fields_cloning;
    /* Visibility flaq */
    private $show_custom_data;
    /* Fields group title (on cart & check out) */
    private $fields_group_title;
    /* Multilingual flag */
    private $multilingual;
    
    /* Datepicker flaq - to include date picker releated scripts */
    private $is_datepicker_there = false;
    /* Colorpicker flaq - to include Spectrum related scripts */
    private $is_colorpicker_there = false;
    
    /* Holds the all product fields list (Across the Product Fields Post) */
    private $product_fields = null;
    /* Holds the all admin fields list (Across the Admin Fields Post) */
    private $admin_fields = null;
    
    public function __construct() {}
    
    /**
     * 
     * Generate custom fields list with value (Label => User Value) and append with<br/>
     * Product Title (if it is Cart) or Quantity (If it is Checkout)<br/>
     * It also enqueue necessary JS script for Fields Editior (to update fields value on Cart).
     * 
     * @param string $_html
     * @param object $_cart_item
     * @param string $_cart_item_key
     * @param boolean $_is_review_table
     * @return string|unknown
     * 
     */
    public function render_fields_data($_html, $_cart_item, $_cart_item_key, $_is_review_table) {
        $this->html = $_html;
        $this->cart_item = $_cart_item;
        $this->cart_item_key = $_cart_item_key;
        $this->is_review_table = $_is_review_table;
       
        $this->meta_html = "";
        $wccpf_options = wcff()->option->get_options();
        $this->fields_cloning= isset($wccpf_options["fields_cloning"]) ? $wccpf_options["fields_cloning"] : "yes";
        $this->multilingual = isset($wccpf_options["enable_multilingual"]) ? $wccpf_options["enable_multilingual"] : "no";
       
        /* Enqueue flag */
        $should_enqueue = false;
        
        if (isset($wccpf_options["fields_group_title"]) && $wccpf_options["fields_group_title"] != "") {
            $this->fields_group_title = $wccpf_options["fields_group_title"];
        } else {
            $this->fields_group_title = "Additional Options ";
        }
        
        if (isset($this->cart_item['product_id'])) {
            $this->product_fields = wcff()->dao->load_fields_for_product($this->cart_item['product_id'], 'wccpf');
            $this->admin_fields = wcff()->dao->load_fields_for_product($this->cart_item['product_id'], 'wccaf', 'any');
            
            if (isset($this->cart_item["quantity"])) {
            	/* Before start to render, make sure there are fields to render */
            	if ($this->determine_fields_there_to_render()) {
            		/* Set enqueue flag */
            		$should_enqueue = true;
            		/* Fields wrapper start */
            		$this->meta_html .= '<div class="wccpf-cart-data-editor">';
            		/**/
            		$this->render_product_fields_data();
            		/* Fields wrapper end */
            		$this->meta_html .= '</div>';
            	}                
            	/* Before start to render, make sure there are pricing rules to render */
            	if ($this->determine_pricing_there_to_render()) {
            		/* Pricing wrapper start */
            		$this->meta_html .= '<div class="wccpf-pricing-group-on-cart">';
            		/**/
            		$this->render_pricing_rules_data();
            		/* Pricing wrapper end */
            		$this->meta_html .= '</div>';
            	}                
            }
        }
        
        if (!$this->is_review_table) {
            $this->html = $this->html . $this->meta_html;
        } else {
       		$this->html = $this->meta_html . $this->html;
        }
        
        /* Enqueue client side asset */
        if ($should_enqueue) {
        	$this->enqueue_client_side_assets();
        }                	
        /* Return the generated html */
        return $this->html;
    }    
    
    /**
     * 
     * Mine the Cart Item object for Product Fields, and construct the Html out of it to render on Cart & Checkout
     * 
     */
    private function render_product_fields_data() {		
		$field_editors = "";
		foreach ( $this->product_fields as $fields ) {
			if (is_array ( $fields ) && count ( $fields )) {
				foreach ( $fields as $field ) {
					$field ["visibility"] = isset ( $field ["visibility"] ) ? $field ["visibility"] : "yes";
					if ($field ["visibility"] == "yes" && isset ( $this->cart_item ['wccpf_' . $field ["name"]] )) {
						$field_editors .= $this->render_data ( $field, $this->cart_item ['wccpf_' . $field ["name"]] );
					}
				}
			}
		}
		/* Render admin field's data */
		$field_editors .= $this->render_admin_fields_data ();
		
		
		if ($field_editors != "") {
			/* Field's group wrapper start */
			$this->meta_html .= '<fieldset>';
			/* Sandwich the editors within wrapper */
			$this->meta_html .= $field_editors;
			/* Field's group wrapper end */
			$this->meta_html .= '</fieldset>';
		}	
		
		if ($this->fields_cloning == "yes") {
			$pcount = intval ( $this->cart_item ["quantity"] );
			for($i = 1; $i <= $pcount; $i ++) {
				/* Reset the editor holder */
				$field_editors = "";
				/* Cloning fields group title flag */
				$title_displayed = false;				
				foreach ( $this->product_fields as $fields ) {
					if (is_array ( $fields ) && count ( $fields )) {
						$meta_there = false;
						/* Make sure cart_item does contains some custom meta to display */
						foreach ( $fields as $field ) {
							$field ["cloneable"] = isset ( $field ["cloneable"] ) ? $field ["cloneable"] : "yes";
							$field ["visibility"] = isset ( $field ["visibility"] ) ? $field ["visibility"] : "yes";
							if ($field ["cloneable"] == "yes" && $field ["visibility"] == "yes" && isset ( $this->cart_item ['wccpf_' . $field ["name"] . "_" . $i] )) {
								$meta_there = true;
								break;
							}
						}
						if ($meta_there) {
							$title_displayed = true;
							/* If the line item quantity is 1 then no need to append the numeric suffix */
							$field_editors .= '<h5>' . esc_html ( $this->fields_group_title ) . (($pcount == 1) ? "" : (" : " . $i)) . '</h5>';
						}
						foreach ( $fields as $field ) {
							$field ["cloneable"] = isset ( $field ["cloneable"] ) ? $field ["cloneable"] : "yes";
							$field ["visibility"] = isset ( $field ["visibility"] ) ? $field ["visibility"] : "yes";
							if ($field ["cloneable"] == "yes" && $field ["visibility"] == "yes" && isset ( $this->cart_item ['wccpf_' . $field ["name"] . "_" . $i] )) {
								$field_editors .= $this->render_data ( $field, $this->cart_item ['wccpf_' . $field ["name"] . "_" . $i], ("_" . $i) );
							}
						}
					}
				}
				/* Render admin field's data */
				$field_editors .= $this->render_admin_fields_data ( $i, $title_displayed );
				
				if ($field_editors != "") {
					/* Field's group wrapper start */
					$this->meta_html .= '<fieldset>';
					/* Sandwich the editors within wrapper */
					$this->meta_html .= $field_editors;
					/* Field's group wrapper end */
					$this->meta_html .= '</fieldset>';
				}	
			}
		}
	}
    
	/**
	 * 
	 * Mine the Cart Item object for Admin Fields (which might be choosed to display as product field on front end),<br/>
	 * and construct the Html out of it to render on Cart & Checkout.
	 * 
	 * @param number $_index
	 * @param string $_title_displayed
	 * 
	 */
    private function render_admin_fields_data($_index = 0, $_title_displayed = true) {
    	$field_editors = "";
        if ($_index == 0) {
            foreach ($this->admin_fields as $afields) {
                if (is_array($afields) && count($afields)) {
                    foreach ($afields as $key => $afield) {
                        $afield["visibility"] = isset($afield["visibility"]) ? $afield["visibility"] : "yes";
                        if ($afield["visibility"] == "yes" && isset($this->cart_item['wccpf_'. $afield["name"]])) {
                        	$field_editors .= $this->render_data($afield, $this->cart_item['wccpf_'. $afield["name"]]);
                        }
                    }
                }
            }
        } else {
            if ($this->fields_cloning == "yes") {
                foreach ($this->admin_fields as $afields) {
                    if (is_array($afields) && count($afields)) {
                        /* If there is no Product fields but only admin fields that is being displayed on product page
                         * In that instace we need */
                        if (!$_title_displayed) {
                            $meta_there = false;
                            /* Make sure cart_item does contains some custom meta to display */
                            foreach ($afields as $key => $afield) {
                            	$afield["visibility"] = isset($afield["visibility"]) ? $afield["visibility"] : "yes";
                            	$afield["cloneable"] = isset($afield["cloneable"]) ? $afield["cloneable"] : "yes";
                                if ($afield["cloneable"] == "yes" && $afield["visibility"] == "yes" && isset($this->cart_item['wccpf_'. $afield["name"] . "_" . $_index])) {
                                    $meta_there = true;
                                }
                            }
                            if ($meta_there) {
                                /* If the line item quantity is 1 then no need to append the numeric suffix */
                            	$field_editors .= '<h5>'. esc_html($this->fields_group_title) . ((intval($this->cart_item["quantity"]) == 1) ? "" : (" : ".$_index)) .'</h5>';
                            }
                        }                        
                        foreach ($afields as $key => $afield) {                           
                            $afield["visibility"] = isset($afield["visibility"]) ? $afield["visibility"] : "yes";
                            $afield["cloneable"] = isset($afield["cloneable"]) ? $afield["cloneable"] : "yes";
                            if ($afield["cloneable"] == "yes" && $afield["visibility"] == "yes" && isset($this->cart_item['wccpf_'. $afield["name"] . "_" . $_index])) {
                            	$field_editors .= $this->render_data($afield, $this->cart_item['wccpf_'. $afield["name"] . "_" . $_index], ("_" . $_index));
                            }
                        }
                    }
                }
            }
        }   
        return $field_editors;
    }
    
    /**
     * 
     * Mine the Car Item object for any applied pricing rules<br/>
     * If found any then extract it and generate a html to render it on Cart & Checkout
     * 
     */
    private function render_pricing_rules_data() {
    	    $is_there = false;
    	    foreach ($this->cart_item as $ckey => $cval) {
    		    if (strpos($ckey, "wccpf_pricing_applied_") !== false) {
    			    $is_there = true;
    			    break;
    		    }
    	    }
        	if ($is_there) {        		
        		foreach ($this->cart_item as $ckey => $cval) {
        			if (strpos($ckey, "wccpf_pricing_applied_") !== false) {
        				$prules = $this->cart_item[$ckey];
        				if (isset($prules["title"]) && isset($prules["amount"])) {
        				    $this->meta_html .= '<ul class="wccpf-pricing-rule-ul">';
        					$this->meta_html .= '<li>'. $prules["title"].' : </li>';
        					$this->meta_html .= '<li> '. $prules["amount"].'</li>';
        					$this->meta_html .= '</ul>';        					
        				}
        			}
        		}        		
        	}    	
    }
    
    /**
     * 
     * Helper method which actualy generate the HTML for custom data.
     * 
     * @param object $_field
     * @param string|number|array $_val
     * @param string $_index
     * 
     */
	private function render_data($_field, $_val, $_index = "") {
		if ($this->multilingual == "yes") {
			/* Localize field */
			$_field = wcff ()->locale->localize_field ( $_field );
		}
		$_val = (($_val && isset ( $_val ["user_val"] )) ? $_val ["user_val"] : $_val);
		$is_editable = isset ( $_field ["cart_editable"] ) ? $_field ["cart_editable"] : "no";
		$editable_class = ($is_editable == "yes") ? "wcff_cart_editor_field" : "";
		$tooltip = ($is_editable == "yes") ? 'title="Double click to edit"' : '';
		
		$is_editable = (is_checkout()) ? "no" : $is_editable;
		
		$meta_html = '<ul class="wccpf-cart-editor-ul wccpf-is-editable-'. $is_editable .'">';
		$meta_html .= '<li>' . $_field ["label"] . ' : </li>';
		
		if ($_field ["type"] != "file" && $_field ["type"] != "checkbox" && $_field ["type"] != "colorpicker") {
			$meta_html .= '<li class="' . $editable_class . '" ' . $tooltip . ' data-field="' . $_field ["name"] . '" data-field_name="' . ($_field ["name"] . $_index) . '" data-product_id="' . esc_attr ( $this->cart_item ["product_id"] ) . '" data-item_key="' . esc_attr ( $this->cart_item_key ) . '">' . wp_kses_post ( wpautop ( stripslashes ( $_val ) ) ) . '</li>';
		} else if ($_field ["type"] == "checkbox") {
			$meta_html .= '<li class="' . $editable_class . '" ' . $tooltip . ' data-field="' . $_field ["name"] . '" data-field_name="' . ($_field ["name"] . $_index) . '" data-product_id="' . esc_attr ( $this->cart_item ["product_id"] ) . '" data-item_key="' . esc_attr ( $this->cart_item_key ) . '">' . wp_kses_post ( wpautop ( (is_array ( $_val ) ? implode ( ",", $_val ) : stripslashes ( $_val )) ) ) . '</li>';
		} else if ($_field ["type"] == "colorpicker") {
			$color_val = "";
			$show_as_color = 'data-color-box="no"';
			if (isset ( $_field ["hex_color_show_in"] ) && $_field ["hex_color_show_in"] == "yes") {
				if (strpos ( $_val, "wcff-color-picker-color-show" ) == false) {
					$color_val = '<span class="wcff-color-picker-color-show" code="' . $_val . '" style="background-color: ' . $_val . '"></span>';
				} else {
					$color_val = $_val;
				}
				$show_as_color = 'data-color-box="yes"';
			} else {
				$color_val = wp_kses_post ( wpautop ( $_val ) );
			}
			$meta_html .= '<li class="' . $editable_class . '" ' . $tooltip . ' data-field="' . $_field ["name"] . '" data-field_name="' . ($_field ["name"] . $_index) . '" data-product_id="' . esc_attr ( $this->cart_item ["product_id"] ) . '" data-item_key="' . esc_attr ( $this->cart_item_key ) .'" '. $show_as_color .'>' . $color_val . '</li>';
		} else {
			$is_multi_file = isset ( $_field ["multi_file"] ) ? $_field ["multi_file"] : "no";
			if ($is_multi_file == "yes") {
				$fnames = array ();
				$farray = json_decode ( $_val, true );
				foreach ( $farray as $fobj ) {
					$path_parts = pathinfo ( $fobj ['file'] );
					$fnames [] = $path_parts ["basename"];
				}
				$meta_html .= '<li class="wcff_field_cart_updater_clone" data-field="' . $_field ["name"] . '" data-field_name="' . ($_field ["name"] . $_index) . '" data-product_id="' . esc_attr ( $this->cart_item ["product_id"] ) . '" data-item_key="' . esc_attr ( $this->cart_item_key ) . '">' . wp_kses_post ( implode ( ", ", $fnames ) ) . '</li>';
			} else {
				$fobj = json_decode ( $_val, true );
				$path_parts = pathinfo ( $fobj ['file'] );
				if ($_field ["img_is_prev"] == "yes" && @getimagesize ( $fobj ["url"] )) {
					$meta_html .= '<li data-field="' . $_field ["name"] . '" data-field_name="' . ($_field ["name"] . $_index) . '" data-product_id="' . esc_attr ( $this->cart_item ["product_id"] ) . '" data-item_key="' . esc_attr ( $this->cart_item_key ) . '"><img src="' . $fobj ["url"] . '" style="width:' . $_field ["img_is_prev_width"] . 'px;"></li>';
				} else {
					$meta_html .= '<li class="wcff_field_cart_updater_clone" data-field="' . $_field ["name"] . '" data-field_name="' . ($_field ["name"] . $_index) . '" data-product_id="' . esc_attr ( $this->cart_item ["product_id"] ) . '" data-item_key="' . esc_attr ( $this->cart_item_key ) . '">' . wp_kses_post ( stripslashes ( $path_parts ["basename"] ) ) . '</li>';
				}
			}
		}
		$meta_html .= '</ul>';
		
		/* Let other plugins override this value - if they wanted */		
		if (has_filter("wcff_before_rendering_cart_editor")) {
			$meta_html = apply_filters("wcff_before_rendering_cart_editor", $_field, $meta_html);
		}  
		
		if ($_field ["type"] == "datepicker") {
			$this->is_datepicker_there = true;
		}
		if ($_field ["type"] == "colorpicker") {
			$this->is_colorpicker_there = true;
		}
		
		return $meta_html;
	}
	
	private function determine_fields_there_to_render() {
		foreach ( $this->product_fields as $fields ) {
			if (is_array ( $fields ) && count ( $fields )) {
				foreach ( $fields as $field ) {
					$field ["visibility"] = isset ( $field ["visibility"] ) ? $field ["visibility"] : "yes";
					if ($field ["visibility"] == "yes" && isset ( $this->cart_item ['wccpf_' . $field ["name"]] )) {
						return true;
					}
				}				
			}
		}		
		foreach ($this->admin_fields as $afields) {
			if (is_array($afields) && count($afields)) {
				foreach ($afields as $key => $afield) {
					$afield["visibility"] = isset($afield["visibility"]) ? $afield["visibility"] : "yes";
					if ($afield["visibility"] == "yes" && isset($this->cart_item['wccpf_'. $afield["name"]])) {
						return true;
					}
				}		
			}
		}	 
		if ($this->fields_cloning == "yes") {
			$pcount = intval ( $this->cart_item ["quantity"] );
			for($i = 1; $i <= $pcount; $i ++) {
				foreach ( $this->product_fields as $fields ) {
					if (is_array ( $fields ) && count ( $fields )) {						
						foreach ( $fields as $field ) {
							$field ["cloneable"] = isset ( $field ["cloneable"] ) ? $field ["cloneable"] : "yes";
							$field ["visibility"] = isset ( $field ["visibility"] ) ? $field ["visibility"] : "yes";
							if ($field ["cloneable"] == "yes" && $field ["visibility"] == "yes" && isset ( $this->cart_item ['wccpf_' . $field ["name"] . "_" . $i] )) {
								return true;
							}
						}						
					}
				}
				foreach ($this->admin_fields as $afields) {
					if (is_array($afields) && count($afields)) {
						foreach ($afields as $key => $afield) {
							$afield["visibility"] = isset($afield["visibility"]) ? $afield["visibility"] : "yes";
							$afield["cloneable"] = isset($afield["cloneable"]) ? $afield["cloneable"] : "yes";
							if ($afield["cloneable"] == "yes" && $afield["visibility"] == "yes" && isset($this->cart_item['wccpf_'. $afield["name"] . "_" . $i])) {
								return true;
							}
						}
					}
				}
			}
		}
		return false;
	}
	
	private function determine_pricing_there_to_render() {
		foreach ($this->cart_item as $ckey => $cval) {
			if (strpos($ckey, "wccpf_pricing_applied_") !== false) {
				return true;
			}
		}
	}
    
    /**
     *
     * Used to render actual field with data on a Cart Item (In Cart Page itself)
     * Used for editing custom fields value in Cart page
     *
     * When user double click on any values on the cart line item, Fields Factory's client module will trigger an Ajax request with the following details
     * {
     *      product_id: "",
     *      product_cart_id: "",
     *      check_edit: "",
     *      data: {
     *          name: "fields_name",
     *          value: "fields_current_value"
     *      }
     * }
     *
     * @param object $_ci_fdata
     * @return string
     *
     */
    public function render_field_with_data($_payload) {
        $res = null;
        /* Holds the target field's config meta */
        $field = null;
        
        $this->product_fields = wcff()->dao->load_fields_for_product($_payload['product_id'], 'wccpf');
        $this->admin_fields = wcff()->dao->load_fields_for_product($_payload['product_id'], 'wccaf', 'any');
        
        foreach ($this->product_fields as $fields) {
            if (isset($fields["wccpf_".$_payload["data"]["field"]])) {
                $field = $fields["wccpf_".$_payload["data"]["field"]];
            }
        }
        foreach ($this->admin_fields as $fields) {
            if (isset($fields["wccaf_".$_payload["data"]["field"]])) {
                $field = $fields["wccaf_".$_payload["data"]["field"]];
                
                
            }
        }
        if ($this->multilingual == "yes") {
            /* Localize field */
            $field = wcff()->locale->localize_field($field);
        }
        
        /* Continue only when we have the valid field's config meta */
        if ($field != null) {
            $editable = isset($field["cart_editable"]) ? $field["cart_editable"] : "no";
            if ($editable == "yes") {
                /* Set the "default_value" with user entered value */
            	$field["default_value"] = isset($_payload['data'][ 'value' ]) ? (($field["type"] == "checkbox") ? explode(",", $_payload['data'][ 'value' ]) : $_payload['data'][ 'value' ]) : $field["default_value"];
                $res= $this->render_field($field, $_payload['product_id']);
            }
        }
        
        return $res;
    }
    
    private function render_field($_field, $_product_id, $_cvalue="") {
        $script = "";
        $is_this_colorpicker = false;
        if ($_field["type"] == "colorpicker") {
            $is_this_colorpicker = true;
            $_field["admin_class"] = $_field["name"];
            $script .= $this->initialize_color_picker_field($_field, $_product_id, $_field["default_value"]);
        }
        if ($_field["type"] == "datepicker") {
        	$_field["admin_class"] = $_field["name"];
            $script .= $this->initialize_datepicker_field($_field, "wccpf");
        }
        $html = wcff()->builder->build_user_field($_field, "wccpf");
        return array("status" => true, "field_type" => $_field["type"], "html" => $html, "script" => $script, "color_showin" => $is_this_colorpicker);
    }
    
    /**
     *
     * Used to update the value of the custom fields
     * Used for editing custom fields value in Cart page
     *
     */
    public function update_field_value($_payload) {
        $message = "";
        $return_value = "";
        $saveval = $_payload["data"]["value"];
        $validate = $this->validate_wccpf($_payload["product_id"], $_payload["data"]["field"], $_payload["data"]["value"]);
        
        if (isset($_payload["data"]["color_showin"])) {
            if ($_payload["data"]["color_showin"]) {
                $saveval = urldecode($_payload["data"]["value"]);
            }
        }
        if (!$validate["status"]){
            return array("status" => false, "message" => $validate["msg"]);
        } else {              
            if ($_payload["data"]["field_type"] != "file"){
            	WC()->cart->cart_contents[$_payload['cart_item_key']]["wccpf_".$_payload["data"]["name"]] = $saveval;
            	$return_value = WC()->cart->cart_contents[$_payload['cart_item_key']]["wccpf_".$_payload["data"]["name"]];
            }
            WC()->cart->set_session();
            return array("status" => true, "value" => $return_value, "field_type" => $_payload["data"]["field_type"]);
        }
    }
    
    function validate_wccpf($_prod_id, $_name, $_value) {
        $is_passed = true;
        $is_admin  = false;
        $wccpf_options = wcff()->option->get_options ();
        
        $this->product_fields = wcff()->dao->load_fields_for_product($_prod_id, 'wccpf');
        $this->admin_fields = wcff()->dao->load_fields_for_product($_prod_id, 'wccaf', 'woocommerce_product_options_general_product_data');
        
        $a_field 		= null;
        $fieldc  		= null;
        $fieldac		= null;
        $msg			= "";
        foreach ($this->product_fields as $val) {
            if(isset($val["wccpf_".$_name ])){
                $fieldc = $val["wccpf_".$_name];
            }
        }
        foreach ($this->admin_fields as $avalue) {
            if(isset($avalue["wccaf_".$_name])){
                $fieldac = $avalue["wccaf_".$_name];
            }
        }
        if ($fieldc != null) {
            $field = $fieldc;
            $res = true;
            $res_size_val = true;
            $field["required"] = isset ($field ["required"]) ? $field ["required"] : "no";
            if ($field ["required"] == "yes" || $field ["type"] == "file") {
                if ($field ["type"] != "file") {
                    $res = wcff()->validator->validate($_prod_id, $field, $_name, $_value);
                } else {
                    
                }
            }
            if (!$res || ! $res_size_val) {
                $is_passed = false;
                $msg = ! $res ? $field ["message"] : "Upload size limit exceed, Allow size is " . $field ["max_file_size"] . "kb.!";
            }
        }
        if ($fieldac != null) {
            $is_admin = true;
            $afield = $fieldac;
            $res = true;
            $afield ["show_on_product_page"] = isset ($afield ["show_on_product_page"]) ? $afield ["show_on_product_page"] : "no";
            if ($afield ["show_on_product_page"] == "yes" && $afield ["required"] == "yes") {
                $res = wcff()->validator->validate($_prod_id, $field, $_name, $_value);
            }
            if (!$res) {
                $is_passed = false;
                $msg = $afield ["message"];
            }
        }
        return array("status" => $is_passed, "is_admin" => $is_admin, "msg" => $msg);
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
     * Enqueue assets for Front end Cart Page
     *
     * @param boolean $isdate_css
     *
     */
    private function enqueue_client_side_assets() {
        $wccpf_options = wcff()->option->get_options();
        $cart_editable = isset($wccpf_options["edit_field_value_cart_page"]) ? $wccpf_options["edit_field_value_cart_page"] : "no"; ?>
        <script type="text/javascript">
        	var wccpf_opt = {
        		editable : "<?php echo isset( $wccpf_options["edit_field_value_cart_page"] ) ? $wccpf_options["edit_field_value_cart_page"] : "no" ?>",
        		cloning : "<?php echo isset( $wccpf_options["fields_cloning"] ) ? $wccpf_options["fields_cloning"] : "no"; ?>",
        		location : "<?php echo isset( $wccpf_options["field_location"] ) ? $wccpf_options["field_location"] : "woocommerce_before_add_to_cart_button"; ?>",
        		validation : "<?php echo isset( $wccpf_options["client_side_validation"] ) ? $wccpf_options["client_side_validation"] : "no"; ?>",
        		validation_type : "<?php echo isset( $wccpf_options["client_side_validation_type"] ) ? $wccpf_options["client_side_validation_type"] : "submit"; ?>"
        	};
        </script>
        <?php        
            
        if (is_cart() || is_checkout()) { ?>           
            <link rel="stylesheet" type="text/css" href="<?php echo wcff()->info['dir']; ?>assets/css/wcff-client.css">         
        <?php 
        }      
        
        if (is_cart() && $cart_editable == "yes") { ?>
        
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
			<?php endif; ?>
        
        	<script type="text/javascript" src="<?php echo wcff()->info['dir']; ?>assets/js/wcff-client.js"></script>
        
        <?php 
        }
    }
    
    private function initialize_datepicker_field($_field, $_post_type) {
        	$localize = "none";
        	$year_range = "-10:+10";        	
        	if ( isset( $_field["language"] ) && !empty( $_field["language"] ) && $_field["language"] != "default") {
        		$localize = esc_attr($_field["language"]);
        	}
        	if (isset($_field["dropdown_year_range"]) && !empty($_field["dropdown_year_range"])) {
        		$year_range = esc_attr($_field["dropdown_year_range"]);
        	}
        ob_start(); ?>
    	
		<script type="text/javascript">		
		(function($) {
			$(document).ready(function() {
			<?php			
			if ($localize != "none") { ?>
				/* Datepicker User configured localization */					
				var options = $.extend({}, $.datepicker.regional["<?php echo $localize; ?>"]);
				$.datepicker.setDefaults(options);
			<?php 
			} else { ?>
				/* Datepicker default configuration */										
				var options = $.extend({}, $.datepicker.regional["en-GB"]);
					$.datepicker.setDefaults(options);
			<?php 
			}				
			?>
			
				$("body").on("focus", ".<?php echo $_post_type; ?>-datepicker-<?php echo esc_attr($_field["name"]); ?>", function(){
					
				<?php if (isset($_field["timepicker"]) && $_field["timepicker"] == "yes") : ?>
					$(this).datetimepicker({
				<?php else : ?>
					$(this).datepicker({
				<?php endif; ?>											
				<?php			
					if (isset($_field["date_format"]) && $_field["date_format"] != "") {
						echo "dateFormat:'".esc_attr( $_field["date_format"] )."'";
					} else {
						echo "dateFormat:'dd-mm-yy'";
					}	
						
					if (isset($_field["display_in_dropdown"]) && !empty($_field["display_in_dropdown"])) {
						if ($_field["display_in_dropdown"] == "yes") {
							echo ",changeMonth: true";
							echo ",changeYear: true";
							echo ",yearRange:'". $year_range ."'";
						}
					}
					if (isset($_field["disable_date"]) && !empty($_field["disable_date"])) {
						if ("future" == $_field["disable_date"]) {
							echo ",maxDate: 0";
						}
						if ("past" == $_field["disable_date"]) {
							echo ",minDate: new Date()";
						}											
					}
					if (isset($_field["allow_next_x_years"]) && !empty($_field["allow_next_x_years"]) ||
						isset($_field["allow_next_x_months"]) && !empty($_field["allow_next_x_months"]) ||
						isset($_field["allow_next_x_weeks"]) && !empty($_field["allow_next_x_weeks"]) ||
						isset($_field["allow_next_x_days"]) && !empty($_field["allow_next_x_days"]) ) {
						$allowed_dates = "";
						if (isset($_field["allow_next_x_years"]) && !empty($_field["allow_next_x_years"]) && is_numeric($_field["allow_next_x_years"])) {
							$allowed_dates .= "+". trim($_field["allow_next_x_years"]) ."y ";
						}
						if (isset($_field["allow_next_x_months"]) && !empty($_field["allow_next_x_months"]) && is_numeric($_field["allow_next_x_months"])) {
							$allowed_dates .= "+". trim($_field["allow_next_x_months"]) ."m ";
						}
						if (isset($_field["allow_next_x_weeks"]) && !empty($_field["allow_next_x_weeks"]) && is_numeric($_field["allow_next_x_weeks"])) {
							$allowed_dates .= "+". trim($_field["allow_next_x_weeks"]) ."w ";
						}
						if (isset($_field["allow_next_x_days"]) && !empty($_field["allow_next_x_days"]) && is_numeric($_field["allow_next_x_days"])) {
							$allowed_dates .= "+". trim($_field["allow_next_x_days"]) ."d";
						}
						echo ",minDate: 0";
						echo ",maxDate: \"". trim($allowed_dates) ."\"";
					}
					/* Hooks up a call back for 'beforeShowDay' */
					echo ",beforeShowDay: disableDates";					
				?>					
						,onSelect: function( dateText ) {							
						    $( this ).next().hide();
						}								 
					});
				});		
				
				function disableDates( date ) {	
					<?php if (is_array($_field["disable_days"]) && count($_field["disable_days"]) > 0) { ?>
							 var disableDays = <?php echo json_encode($_field["disable_days"]); ?>;
							 var day 	= date.getDay();
							 for (var i = 0; i < disableDays.length; i++) {
									 var test = disableDays[i]
								 		 test = test == "sunday" ? 0 : test == "monday" ? 1 : test == "tuesday" ? 2 : test == "wednesday" ? 3 : test == "thursday" ? 4 : test == "friday" ? 5 : test == "saturday" ? 6 : "";
							        if ( day == test ) {									        
							            return [false];
							        }
							 }						
					<?php } ?>	
					<?php if (isset($_field["specific_date_all_months"]) && !empty($_field["specific_date_all_months"])){ ?>
					 		var disableDateAll = <?php echo '"'.$_field["specific_date_all_months"].'"'; ?>;
					 			disableDateAll = disableDateAll.split(",");
					 		for (var i = 0; i < disableDateAll.length; i++) {
								if (parseInt(disableDateAll[i].trim()) == date.getDate()){
									return [false];
								}					
					 		}
					<?php } ?>						
					<?php if (isset($_field["specific_dates"]) && !empty($_field["specific_dates"])) { ?>
								var disableDates = <?php echo "'".$_field["specific_dates"]."'"; ?>;
									disableDates = disableDates.split(",");
									/* Sanitize the dates */
									for (var i = 0; i < disableDates.length; i++) {	
										disableDates[i] = disableDates[i].trim();
									}		
									/* Form the date string to compare */							
								var m = date.getMonth(),
									d = date.getDate(),
									y = date.getFullYear(),
									currentdate = ( m + 1 ) + '-' + d + '-' + y ;
								/* Make dicision */								
								if ( $.inArray( currentdate, disableDates ) != -1 ) {
									return [false];
								}
								
					<?php } ?>					
					<?php if (isset($_field["weekend_weekdays"]) && !empty($_field["display_in_dropdown"])) { ?>
							<?php if ($_field["weekend_weekdays"] == "weekdays"){ ?>
								//weekdays disable callback
								var weekenddate = $.datepicker.noWeekends(date);
								var disableweek = [!weekenddate[0]]; 
								return disableweek;
							<?php } else if ($_field["weekend_weekdays"] == "weekends") { ?>
								//weekend disable callback
								var weekenddate = $.datepicker.noWeekends(date);
								return weekenddate; 
							<?php } ?>							
					<?php }  ?>						
					return [true];
				}
							
			});
		})(jQuery);
		</script>
		
		<?php
		return ob_get_clean();
	}
    
	private function initialize_color_picker_field($_field, $product_id, $color_code) {
		Global $product;
		$productid = null;
		if ($product_id == null) {
			$productid = $this->get_product_id ( $product );
		} else {
			$productid = $product_id;
		}
		
		$palettes = null;
		$palette_attr = "";
		$colorformat = isset ( $_field ["color_format"] ) ? $_field ["color_format"] : "hex";
		$defaultcolor = isset ( $_field ["default_value"] ) ? $_field ["default_value"] : "#000";
		$defaultcolor = $color_code != null ? $color_code : $defaultcolor;
		if (isset ( $_field ["palettes"] ) && $_field ["palettes"] != "") {
			$palettes = explode ( ";", $_field ["palettes"] );
			$palette_attr = ",palette : [";
			foreach ( $palettes as $palette ) {
				$indexX = 0;
				$comma = ($indexY == 0) ? "" : ",";
				$palette_attr .= $comma . "[";
				$colors = explode ( ",", $palette );
				foreach ( $colors as $color ) {
					$comma = ($indexX == 0) ? "" : ",";
					$palette_attr .= $comma . "'" . $color . "'";
					$indexX ++;
				}
				$palette_attr .= "]";
				$indexY ++;
			}
			$palette_attr .= "]";
		}
		ob_start(); ?>

		<script type="text/javascript">

        	(function($) {
        		$(document).ready(function() {
	        		$(".wccpf-color-<?php echo esc_attr( $_field["name"] ); ?>").spectrum({
	        			color: "<?php echo $defaultcolor; ?>", 
					preferredFormat: "<?php echo $colorformat; ?>"
					<?php if( is_array( $palettes ) && count( $palettes ) > 0 ) : ?>
					
					<?php if( $_field["show_palette_only"] == "yes" ) : ?>
					,showPaletteOnly: true
					<?php endif; ?>
	
					,showPalette: true
					<?php $palette_attr; ?>
					
					<?php endif; ?>					
	        		});	
        		});
           	})(jQuery);
        	
        </script>
        
		<?php
		return ob_get_clean();
	}
}

?>