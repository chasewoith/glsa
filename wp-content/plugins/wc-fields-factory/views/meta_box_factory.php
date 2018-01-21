<?php 

if (!defined('ABSPATH')) { exit; }

global $post;

$fields = array();

/* $ftypes = wcff()->builder->get_fields_meta();

foreach ($ftypes as $id => $fmeta) {
	if(in_array($post->post_type, $fmeta["support"])) {
		$fields[] = array("id" => $id, "title" => $fmeta["title"]);
	}
} */

if( $post->post_type != "wccaf" ) {
    $fields = apply_filters( "wccpf/fields/factory/supported/fields", array(
        array( "id" => "text", "title" => __( 'Text', 'wc-fields-factory' ) ),
        array( "id" => "number", "title" => __( 'Number', 'wc-fields-factory' ) ),
        array( "id" => "email", "title" => __( 'Email', 'wc-fields-factory' ) ),
        array( "id" => "hidden", "title" => __( 'Hidden', 'wc-fields-factory' ) ),
        array( "id" => "label", "title" => __( 'Label', 'wc-fields-factory' ) ),
        array( "id" => "textarea", "title" => __( 'Text Area', 'wc-fields-factory' ) ),
        array( "id" => "checkbox", "title" => __( 'Check Box', 'wc-fields-factory' ) ),
        array( "id" => "radio", "title" => __( 'Radio Button', 'wc-fields-factory' ) ),
        array( "id" => "select", "title" => __( 'Select', 'wc-fields-factory' ) ),
        array( "id" => "datepicker", "title" => __( 'Date Picker', 'wc-fields-factory' ) ),
        array( "id" => "colorpicker", "title" => __( 'Color Picker', 'wc-fields-factory' ) ),
        array( "id" => "file", "title" => __( 'File', 'wc-fields-factory' ) )
    ));
} else {
    $fields = apply_filters( "wccaf/fields/factory/supported/fields", array(
        array( "id" => "text", "title" => __( 'Text', 'wc-fields-factory' ) ),
        array( "id" => "number", "title" => __( 'Number', 'wc-fields-factory' ) ),
        array( "id" => "email", "title" => __( 'Email', 'wc-fields-factory' ) ),
        array( "id" => "textarea", "title" => __( 'Text Area', 'wc-fields-factory' ) ),
        array( "id" => "checkbox", "title" => __( 'Check Box', 'wc-fields-factory' ) ),
        array( "id" => "radio", "title" => __( 'Radio Button', 'wc-fields-factory' ) ),
        array( "id" => "select", "title" => __( 'Select', 'wc-fields-factory' ) ),
        array( "id" => "datepicker", "title" => __( 'Date Picker', 'wc-fields-factory' ) ),
        array( "id" => "colorpicker", "title" => __( 'Color Picker', 'wc-fields-factory' ) ),
        array( "id" => "image", "title" => __( 'Image', 'wc-fields-factory' ) ),
        array( "id" => "url", "title" => __( 'Url', 'wc-fields-factory' ) )
    ));
}

//$fields = apply_filters($post->post_type ."/fields/factory/supported/fields", $fields);

$logics = apply_filters( "wcff/pricing/and/sub/fields/logic", array(
	array( "id" => "equal", "title" => __( "is equal to", 'wc-fields-factory' ) ),
	array( "id" => "not-equal", "title" => __( "is not equal to", 'wc-fields-factory' ) ),
	array( "id" => "less-than", "title" => __( "less than", 'wc-fields-factory' ) ),
	array( "id" => "less-than-equal", "title" => __( "less than or equal to", 'wc-fields-factory' ) ),
	array( "id" => "greater-than", "title" => __( "greater than", 'wc-fields-factory' ) ),
	array( "id" => "greater-than-equal", "title" => __( "greater than or equal to", 'wc-fields-factory' ) )
));

$wccpf_options = wcff()->option->get_options();
$is_multilingual = isset($wccpf_options["enable_multilingual"]) ? $wccpf_options["enable_multilingual"] : "no";
$supported_locale = isset($wccpf_options["supported_lang"]) ? $wccpf_options["supported_lang"] : array();
	
?>

<div id="wcff_fields_factory" action="POST">

	<table class="wcff_table wcff_fields_factory_header">
		<tr>
			<td>
				<select class="select" id="wcff-field-type-meta-type">
					<?php foreach ( $fields as $field ) : ?>
					<option value="<?php echo $field["id"]; ?>"><?php echo $field["title"]; ?></option>
					<?php endforeach;?>								
				</select>
			</td>
			<td style="<?php echo ($is_multilingual == "yes" && count($supported_locale) > 0) ? "padding-right: 25px;" : ""; ?>">
				<input type="text" id="wcff-field-type-meta-label" value="" placeholder="Label"/>
				<?php 				    
					if($is_multilingual == "yes" && count($supported_locale) > 0) {
				        echo '<button id="wcff-factory-multilingual-label-btn" title="Open Multilingual Panel"><img src="'. (esc_url(wcff()->info["dir"] ."assets/img/translate.png")) .'"/></button>';
				        echo '<div id="wcff-factory-locale-label-dialog">';
				        $locales = wcff()->locale->get_locales();
				        foreach ($supported_locale as $code) {				        	
				            echo '<div class="wcff-locale-block" data-param="label">';
				            echo '<label>Label for '. $locales[$code] .'</label>';
				            echo '<input type="text" id="wcff-field-type-meta-label-'. $code .'" value="" />';
				            echo '</div>';
				        }
				        echo '</div>';
				    }
				?>
			</td>
			<td><input type="text" id="wcff-field-type-meta-name" value="" placeholder="Name" readonly/></td>
			<td><a href="#" class="wcff-add-new-field button button-primary">+ <?php _e( 'Add Field', 'wc-fields-factory' ); ?></a></td>
		</tr>
	</table>
	
	<?php if(  $post->post_type == "wccpf" ) : ?>
	<div id="wcff-factory-tab-header">
		<a href="#wcff-factory-tab-fields-meta" class="selected">Fields Meta</a>		
		<a href="#wcff-factory-tab-pricing-rules" style="display: none;">Pricing Rules</a>	
	</div>
	<?php endif; ?>

	<div id="wcff-factory-tab-container">
		<div class="wcff-field-types-meta-container wcff-factory-tab-child" id="wcff-factory-tab-fields-meta" style="display:block;">
			<table class="wcff_table">
				<tbody id="wcff-field-types-meta-body">				
					<?php echo wcff()->builder->build_factory_fields("text", $post->post_type); ?>				
				</tbody>
			</table>
		</div>
		<?php if(  $post->post_type == "wccpf" ) : ?>
		<div class="wcff-factory-tab-child" id="wcff-factory-tab-pricing-rules">			
			<table class="wcff_table">
				<tbody id="wcff-field-types-meta-body">
					<tr>
						<td class="summary">
							<label for="post_type"><a href="https://sarkware.com/pricing-fee-rules-wc-fields-factory/" target="_blank" title="Documentation">Click here for Documentation</a></label>
							<br/>
							<label for="post_type"><?php _e( 'Pricing Rules', 'wc-fields-factory' ); ?></label>
							<p class="description"><?php _e( 'Change the product price whenever user submit the product along with this field', 'wc-fields-factory' ); ?></p>
							<br/>
							<label for="post_type"><?php _e( 'How it works', 'wc-fields-factory' ); ?></label>
							<p class="description"><?php _e( 'Use "Add Pricing Rule" button to add add a rule, specify the field value and the corresponding price, when the user submit the field with the given value while adding to cart, then the given price will be applied to the submitted product', 'wc-fields-factory' ); ?></p>
							<br/>
							<label for="post_type"><?php _e( 'Pricing Type', 'wc-fields-factory' ); ?></label>
							<p class="description"><?php _e( '<strong>Add :</strong> this option will add the given price with the product amount<br/><strong>Change :</strong> this option will replace the product original price with the given one', 'wc-fields-factory' ); ?></p>							
						</td>
						<td style="vertical-align: top;">
							<div class="wcff-tab-rules-wrapper price" id="wcff-factory-pricing-rules-wrapper">																
								<input type="button" id="wcff-add-price-rule-btn" class="wcff-add-price-rule-btn button" value="Add Pricing Rule">
							</div>
							<div class="wcff-tab-rules-wrapper fee" id="wcff-factory-fee-rules-wrapper">															
								<input type="button" id="wcff-add-fee-rule-btn" class="button" value="Add Fee Rule">
							</div>
							<input type="hidden" name="wcff_pricing_rules" id="wcff_pricing_rules" value="" />
							<input type="hidden" name="wcff_fee_rules" id="wcff_fee_rules" value="" />
						</td>
					</tr>					
				</tbody>
			</table>		
		</div>
		<?php endif; ?>
	</div>
	
	<div id="wcff-field-factory-footer" style="display:none">
		<a href="#" class="wcff-cancel-update-field-btn button"><?php _e( 'Cancel', 'wc-fields-factory' ); ?></a>
		<a href="#" data-key="" class="button wcff-field-delete-btn"><?php _e( 'Delete', 'wc-fields-factory' ); ?></a>								
	</div>
		
</div>