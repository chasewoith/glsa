<?php 

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * 
 * Cart Line Item price calculator.<br/>
 * Alter the existing line item price based on user values.<br/>
 * Also adds custom fee to the cart if configured so.
 * 
 * @author Saravana Kumar K
 * @copyright Sarkware Pvt Ltd
 *
 */
class Wcff_Negotiator {
	
    public function __construct() {}
    
    /**
     * 
     * Determine the line item price based on User submitted values ( while adding product to cart )<br/>
     * Loop through all the line item and calculate the product price based on Pricing Rules of each fields (if the criteria is matched) 
     * 
     * @param object $_cart
     * 
     */
    public function handle_custom_pricing($_cart = null) {
        if ($_cart) {
            foreach (WC()->cart->get_cart() as $key => $citem) {
                foreach ($citem as $ckey => $cval) {
                    if (strpos($ckey, "wccpf_") !== false && isset($citem[$ckey]["pricing_rules"]) && $citem[$ckey]["user_val"]) {
                        $fname = $citem[$ckey]["fname"];
                        $ftype = $citem[$ckey]["ftype"];
                        $dformat = $citem[$ckey]["format"];
                        $uvalue = $citem[$ckey]["user_val"];
                        $p_rules = $citem[$ckey]["pricing_rules"];
                        /* Iterate through the rules and update the price */
                        foreach ($p_rules as $prule) {
                            if ($this->check_rules($prule, $uvalue, $ftype, $dformat)) {
                                $orgPrice = 0;
                                /* Get the original price */
                                if (method_exists($citem["data"], "get_price")) {
                                    /* Woocommerce 3.0.6 + */
                                    $orgPrice = $citem['data']->get_price();
                                } else {
                                    /* Woocommerece before 3.0.6 */
                                    $orgPrice = floatval($citem['data']->price);
                                }
                                
                                /* Determine the price */
                                if ($prule["ptype"] == "add") {
                                    $orgPrice = $orgPrice + floatval($prule["amount"]);
                                } else {
                                    $orgPrice = floatval($prule["amount"]);
                                }
                                
                                /* Update the price */
                                if (method_exists($citem["data"], "set_price")) {
                                    /* Woocommerce 3.0.6 + */
                                    $citem["data"]->set_price($orgPrice);
                                } else {
                                    /* Woocommerece before 3.0.6 */
                                    $citem["data"]->price = $orgPrice;
                                }
                                
                                $now = new DateTime();
                                /* Add pricing rules label - for user notification */
                                WC()->cart->cart_contents[$key]["wccpf_pricing_applied_" . strtolower(str_replace(" ", "_", $prule["title"]))] = array(
                                    "title" => $prule["title"],
                                    "amount" => get_woocommerce_currency_symbol() . $prule["amount"]
                                );
                            }
                        }
                    }
                }
            }
            /* Update the cart object */
            WC()->cart->set_session();
        }
    }
    
    /**
     * 
     * Add custom fee to Cart, based on user submitted values (while adding product to cart).
     * Loop through all the line item and add the custom fee, based on Fee Rules of each fields (if the criteria is matched) 
     * 
     * @param object $_cart
     * 
     */
    public function handle_custom_fee($_cart = null) {
        if ($_cart) {
            foreach (WC()->cart->get_cart() as $key => $citem) {
                foreach ($citem as $ckey => $cval) {
                    if (strpos($ckey, "wccpf_") !== false && isset($citem[$ckey]["fee_rules"]) && $citem[$ckey]["user_val"]) {
                        $ftype = $citem[$ckey]["ftype"];
                        $dformat = $citem[$ckey]["format"];
                        $uvalue = $citem[$ckey]["user_val"];
                        $f_rules = $citem[$ckey]["fee_rules"];
                        /* Iterate through the rules and update the price */
                        foreach ($f_rules as $frule) {
                            if ($this->check_rules($frule, $uvalue, $ftype, $dformat)) {
                                WC()->cart->add_fee($frule["title"], $frule["amount"], true, "");
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * 
     * Evoluate the rules (Pricing or Fee) of the given field against the submitted user value
     * 
     * @param array $_rules
     * @param mixed $_value
     * @return boolean
     * 
     */
    private function check_rules($_rule, $_value, $_ftype, $_dformat) {
        if (($_rule && isset($_rule["expected_value"]) && isset($_rule["logic"]) && ! empty($_value)) || $_ftype == "datepicker") {
            if ($_ftype != "checkbox" && $_ftype != "datepicker") {
                if ($_rule["logic"] == "equal") {
                    return ($_rule["expected_value"] == $_value);
                } else if ($_rule["logic"] == "not-equal") {
                    return ($_rule["expected_value"] != $_value);
                } else if ($_rule["logic"] == "greater-than" && is_numeric($_rule["expected_value"]) && is_numeric($_value)) {
                    return ($_value > $_rule["expected_value"]);
                } else if ($_rule["logic"] == "less-than" && is_numeric($_rule["expected_value"]) && is_numeric($_value)) {
                    return ($_value < $_rule["expected_value"]);
                } else if ($_rule["logic"] == "greater-than-equal" && is_numeric($_rule["expected_value"]) && is_numeric($_value)) {
                    return ($_value >= $_rule["expected_value"]);
                } else if ($_rule["logic"] == "less-than-equal" && is_numeric($_rule["expected_value"]) && is_numeric($_value)) {
                    return ($_value <= $_rule["expected_value"]);
                }
            } else if ($_ftype == "checkbox") {
                /* This must be a check box field */
                if (is_array($_rule["expected_value"]) && is_array($_value)) {
                    if ($_rule["logic"] == "is-only") { 
                        /* User chosen option (or options) has to be exact match */
                        /* In that case both end has to be same quantity */
                        if (count($_rule["expected_value"]) == count($_value)) {
                            /* Now check for the individual options are equals */
                            foreach ($_rule["expected_value"] as $e_val) {
                                if (! in_array($e_val, $_value)) {
                                    /* Well has exact quantity on both side but one or more different values */
                                    return false;
                                }
                            }
                            /* Has equal options, and all are matching with expected values */
                            return true;
                        }
                    } else if ($_rule["logic"] == "is-also") {
                        /* User chosen option should contains expected option
                         * There can be other options also chosen (but expected option has to be one of them) */
                        if (count($_value) >= count($_rule["expected_value"])) {
                            foreach ($_rule["expected_value"] as $e_val) {
                                if (! in_array($e_val, $_value)) {
                                    return false;
                                }
                            }
                            /* Well expected option(s) is chosen by the User */
                            return true;
                        }
                    } else if ($_rule["logic"] == "any-one-of") {
                        /* Well there can be more then one expected options, but any one of them are present 
                         * with the user submitted options then rules are met */
                        $res = false;
                        foreach ($_rule["expected_value"] as $e_val) {
                            if (in_array($e_val, $_value)) {
                                $res = true;
                            }
                        }
                        return $res;
                    }
                }
            } else if ($_ftype == "datepicker") {
                $user_date = DateTime::createFromFormat($_dformat, $_value);                
                if ($user_date && isset($_rule["expected_value"]["dtype"]) && isset($_rule["expected_value"]["value"])) { 
                    if ($_rule["expected_value"]["dtype"] == "days") {
                        /* If user chosed any specific day like "sunday", "mondaay" ... */
                    	$day = $user_date->format('l');                  	
                        if (is_array($_rule["expected_value"]["value"]) && in_array(strtolower($day), $_rule["expected_value"]["value"])) {
                            return true;
                        }
                    } 
                    if ($_rule["expected_value"]["dtype"] == "specific-dates") {             
                        /* Logic for any specific date matches ( Exact date ) */
                        $sdates = explode(",", (($_rule["expected_value"]["value"]) ? $_rule["expected_value"]["value"] : ""));
                    	if (is_array($sdates)) {
                    		foreach ($sdates as $sdate) {
                    			$sdate = DateTime::createFromFormat("m-d-Y", trim($sdate)); 
                    			if ($user_date->format("Y-m-d") == $sdate->format("Y-d-m")) {
                    				return true;
                    			}
                    		}
                    	}                        
                    } 
                    if ($_rule["expected_value"]["dtype"] == "weekends-weekdays") {
                        /* Logic for the weekends */
                    	if ($_rule["expected_value"]["value"] == "weekends") {
                    		if (strtolower($user_date->format('l')) == "saturday" || strtolower($user_date->format('l')) == "sunday") {
                    			return true;
                    		}
                    	} else {
                    		if (strtolower($user_date->format('l')) != "saturday" && strtolower($user_date->format('l')) != "sunday") {
                    			return true;
                    		}
                    	}
                        
                    } 
                    if ($_rule["expected_value"]["dtype"] == "specific-dates-each-month") {
                        /* Logic for the exact date of each month */
                        $sdates = explode(",", (($_rule["expected_value"]["value"]) ? $_rule["expected_value"]["value"] : ""));
                        foreach ($sdates as $sdate) {
                            if (trim($sdate) == $user_date->format("j")) {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
    
}

?>