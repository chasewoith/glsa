/**
 * @author  	: Saravana Kumar K
 * @author url 	: http://iamsark.com
 * @url			: http://sarkware.com/
 * @copyrights	: SARKWARE
 * @purpose 	: wcff Controller Object.
 */
(function($) {	

	var mask = null;
	var wcff = function() {
		/* used to holds next request's data (most likely to be transported to server) */
		this.request = null;
		/* used to holds last operation's response from server */
		this.response = null;
		/* to prevetn Ajax conflict. */
		this.ajaxFlaQ = true;
		/* Holds currently selected fields */
		this.activeField = null;
		/**/
		this.pricingRules = [];
		/**/
		this.feeRules = [];
		
		this.initialize = function() {
			this.registerEvents();
		};
		
		/* Responsible for registering handlers for various DOM events */
		this.registerEvents = function() {			
			/* Click handler for Adding Condition */
			$(document).on( "click", "a.condition-add-rule", this, function(e) {
				e.data.addCondition( $(this) );
				e.preventDefault();
			});		
			/* Click handler for Removing Condition */
			$(document).on( "click", "a.condition-remove-rule", this, function(e) {
				e.data.removeRule( $(this) );
				e.preventDefault();
			});			
			/* Click handler for Adding Condition Group */
			$(document).on( "click", "a.condition-add-group", this, function(e) {
				e.data.addConditionGroup( $(this) );
				e.preventDefault();
			});			
			/* Click handler for Adding Location Rule */
			$(document).on( "click", "a.location-add-rule", this, function(e) {
				e.data.addLocation( $(this) );
				e.preventDefault();
			});		
			/* Click handler for Removing Location Rule */
			$(document).on( "click", "a.location-remove-rule", this, function(e) {
				e.data.removeRule( $(this) );
				e.preventDefault();
			});			
			/* Click handler for Adding Location Group Rule */
			$(document).on( "click", "a.location-add-group", this, function(e) {
				e.data.addLocationGroup( $(this) );
				e.preventDefault();
			});			
			/* Click handler for Removing Pricing Rule */
			$(document).on( "click", "a.pricing-remove-rule", function(e) {
				$(this).parent().parent().parent().parent().remove();
				e.preventDefault();
			});			
			/* Click handler for Removing Fee Rule */
			$(document).on( "click", "a.fee-remove-rule", function(e) {
				$(this).parent().parent().parent().parent().remove();
				e.preventDefault();
			});		
			/* Click handler for Sub Field Group Rule */
			$(document).on( "click", "a.fgroup-remove-rule", function(e) {
				$(this).parent().parent().parent().parent().remove();
				e.preventDefault();
			});			
			/* Click handler for Field Delete */
			$(document).on( "click", "a.wcff-field-delete", this, function(e) {
				uc = confirm("Are you sure, you want to delete this field.?");
				if (uc === true) {
					mask.doMask( $(this).parent().parent().parent().parent().parent() );
					e.data.prepareRequest( "DELETE", "wcff_fields", { field_key : $(this).attr("data-key") } );
					e.data.dock( "wcff_fields", $(this) );
				}				
				e.preventDefault();
			});			
			/* Click handler for Field Delete Button ( on the footer ) */
			$(document).on( "click", ".wcff-field-delete-btn", this, function(e) {
				mask.doMask( $(this) );
				e.data.prepareRequest( "DELETE", "wcff_fields", { field_key : $(this).attr("data-key") } );				
				e.data.dock( "wcff_fields", $(this) );
				e.preventDefault();
			});			
			/* Change handler for Field Type select field */
			$(document).on( "change", "#wcff-field-type-meta-type", this, function(e) {				
				e.data.prepareRequest( "GET", "wcff_meta_fields", { type : $(this).val() } );
				e.data.dock( "wcff_meta_fields", $(this) );
			});			
			/* Click handler for Field Label - whenever user click on it, will go to Field Edit mode */
			$(document).on( "click", ".wcff-field-label", this, function() {
				$(this).next().find("a.wcff-field-edit").trigger("click");
			});			
			/* Click handler for Field Edit */
			$(document).on( "click", "a.wcff-field-edit", this, function(e) {
				$(".wcff-meta-row").removeClass("active");
				$(this).parent().parent().parent().parent().parent().parent().addClass("active");
				mask.doMask( $(this).parent().parent().parent().parent().parent() );
				e.data.prepareRequest( "GET", "wcff_fields", { field_key : $(this).attr("data-key") } );
				e.data.dock( "wcff_fields", $(this) );				
				e.preventDefault();
			});			
			/* Keyup handler for Field Label field - as the user keep typing on it, 
			 * all the characters will be url sanitized and placed on the Name field */
			$(document).on( "keyup", "#wcff-field-type-meta-label", this, function(e) {
				$( "#wcff-field-type-meta-name" ).val( e.data.sanitizeStr( $(this).val() ) );	
				if( $(this).val() !== "" ) {
					$(this).removeClass("wcff-form-invalid");
				}
			});			
			/* Keyup handler for Repeater Field Label - as the user keep typing on it, 
			 * all the characters will be url sanitized and placed on the Repeater Name field */
			$(document).on( "keyup", "#wcff-repeater-meta-label", this, function(e) {
				$( "#wcff-repeater-meta-name" ).val( e.data.sanitizeStr( $(this).val() ) );
				if( $(this).val() !== "" ) {
					$(this).removeClass("wcff-form-invalid");
				}
			});		
			/* Change handler for Condition Param - it has to reload the target ( Product List, Cat List, Tag List ... ) */
			$(document).on( "change", ".wcff_condition_param", this, function(e) {
				e.data.prepareRequest( "GET", $(this).val(), "" );
				e.data.dock( $(this).val(), $(this) );
			});			
			/* Change handler for Location Param - it has to reload the target ( Tab List, Meta Box Context List ... ) */
			$(document).on( "change", ".wcff_location_param", this, function(e) {
				e.data.prepareRequest( "GET", $(this).val(), "" );
				e.data.dock( $(this).val(), $(this) );
			});	
			$(document).on( "click", "#wcff-factory-tab-header > a", function(e) {
				$("#wcff-factory-tab-container > div").fadeOut("fast");
				$("#wcff-factory-tab-header > a").removeClass();
				$(this).addClass("selected");			
				$($(this).attr("href")).fadeIn("fast");
				e.preventDefault();
			});
			$(document).on( "click", ".wcff-pricing-rule-toggle > a, .wcff-fee-rule-toggle > a", function(e) {
				$(this).parent().find("a").removeClass("selected");
				$(this).addClass("selected");
				e.preventDefault();
			});
			/* Click handler for Pricing rule add button */
			$(document).on( "click", "#wcff-add-price-rule-btn", this, function(e) {
				e.data.addPricingRule( $(this) );
			});
			/* Click handler for Fee rule add button */
			$(document).on( "click", "#wcff-add-fee-rule-btn", this, function(e) {
				e.data.addFeeRule( $(this) );
			});
			/* CLick handler for both cancel & update buttons */
			$(document).on( "click", ".wcff-cancel-update-field-btn", this, function(e) {				
				$("#wcff_fields_factory").attr( "action", "POST");
				$("#wcff-field-factory-footer").hide();
				
				$("#wcff-field-type-meta-label").val("");
				$("#wcff-field-type-meta-name").val("");			
				
				$("#wcff-factory-locale-label-dialog").hide();
				$("#wcff-factory-locale-label-dialog div.wcff-locale-block").each(function() {
					$(this).find("input").val("");
				});
				
				/* Clear the pricing rules section */
				$("#wcff-factory-pricing-rules-wrapper").children().not("#wcff-add-price-rule-btn").remove();
				/* Clear the fee rules section */
				$("#wcff-factory-fee-rules-wrapper").children().not("#wcff-add-fee-rule-btn").remove();
					
				/* Make sure the Fields Meta tab is active */
				$("#wcff-factory-tab-header > a:first-child").trigger("click");
				
				$(".wcff-add-new-field").html("+ Add Field");
				$("#wcff-field-type-meta-type").trigger("change");
				
				$(".wcff-meta-row").removeClass("active");
				/* Hide prcing rule tab */
				$("#wcff-factory-tab-header a:last-child").hide();
				/* Clear the Active Field property */
				this.activeField = null;
				/* Enable Field Type options */
				$('#wcff-field-type-meta-type option').prop('disabled', false);				
				e.preventDefault();
			});		
			/* Click handler for add new field button */
			$(document).on( "click", "a.wcff-add-new-field", this, function(e) {
				e.data.onFieldSubmit( $(this) );
				e.preventDefault();
			});
			/* Click hanlder tab headers */
			$(document).on( "click", "div.wcff-factory-tab-left-panel li", this, function(e) {					
				$(this).parent().parent().next().find(">div").hide()
				$(this).parent().find("> li").removeClass();
				$(this).addClass("selected");			
				$(this).parent().parent().next().find(">div:nth-child("+ ($(this).index() + 1) +")").show();
			});	
			/* Click hanlder for clearing Week ends and Week days radio buttons */
			$(document).on( "click", "a.wcff-date-disable-radio-clear", this, function(e) {	
				$(this).parent().prev().find( "input" ).prop( "checked", false );
				e.preventDefault();
			});
			/* Change event handler for File preview option radio button */
			$(document).on( "change", "input[name=wcff-field-type-meta-img_is_prev]", this, function(e) {
				if( $( this ).val() === "yes" ){
					$( "div[data-param=img_is_prev_width]" ).fadeIn();
				} else{
					$( "div[data-param=img_is_prev_width]" ).fadeOut();
				}
				e.preventDefault();
			});
			/* Keyup hanlder for Choices textarea - which is used to generate default options ( select, radio and check box ) */
			$(document).on( "keyup", "textarea.wcff-choices-textarea", this, function(e) {
				e.data.handleDefault($(this));
			});
			/* Change event handler for validtaing Choice's label and value text bix - Choice Widget */
			$(document).on( "change", "#wcff-option-value-text, #wcff-option-label-text", this, function(e) {
				if($(this).val() == "") {
					$(this).addClass("invalid");
				} else {
					$(this).removeClass("invalid");
				}
			});
			/* Click handler for add option button - Choice Widget */
			$(document).on( "click", "button.wcff-add-opt-btn", this, function(e) {				
				e.data.addOption($(this));
				e.preventDefault();
				e.stopPropagation();
			});
			/**/
			$(document).on( "change", "input[name=wcff-field-type-meta-show_on_product_page]", this, function (e) {
				var display = "table-row";
				if ($(this).val() === "no") {
					display = "none";
				}
				$("div.wcff-field-types-meta").each(function () {
					var flaq = false;
					if ($(this).attr("data-param") === "visibility" || 
						$(this).attr("data-param") === "order_meta" ||
						$(this).attr("data-param") === "login_user_field" ||
						$(this).attr("data-param") === "cart_editable" ||
						$(this).attr("data-param") === "cloneable" ||
						$(this).attr("data-param") === "show_as_read_only" ||
						$(this).attr("data-param") === "showin_value" ) {
						flaq = true;
					}					
					if (flaq) {
						$(this).closest("tr").css("display", display);
					}
				});
			});
			$(document).on( "change", "input[name=wcff-field-type-meta-login_user_field]", this, function (e) {				
				var display = ($(this).val() === "no") ? "none" : "table-row";				
				$("div[data-param=show_for_roles]").closest("tr").css("display", display);
			});
			$(document).on( "change", "input[name=wcff-field-type-meta-timepicker]", this, function (e) {
				var display = ($(this).val() === "no") ? "none" : "table-row";
				$("div[data-param=min_max_hours_minutes]").closest("tr").css("display", display);
			});
			/**/
			$(document).on( "click", "#wcff-factory-multilingual-label-btn, button.wcff-factory-multilingual-btn", function(e) {
				if ($(this).hasClass("wcff-factory-multilingual-btn")) {
					$(this).nextAll("div.wcff-locale-list-wrapper").first().toggle("normal");
				} else {
					$(this).next().toggle("normal");
				}				
				e.preventDefault();
			});
			/**/
			$(document).on( "change", "input.invalid", function() {
				$(this).removeClass("invalid");
			});
			/* Submit action handler for Wordpress Update button */
			$(document).on( "submit", "form#post", this, function(e) {			
				return e.data.onPostSubmit( $(this));
			});
		};
		
		this.addOption = function(_btn) {
			var	value = _btn.prevAll("input.wcff-option-value-text").first(),	
				label = _btn.prevAll("input.wcff-option-label-text").first();							
			if (value.val() == "") {
				value.addClass("invalid");
				value.focus();
			} else {
				value.removeClass("invalid");
			}
			if (label.val() == "") {
				label.addClass("invalid");
				label.focus();
			} else {
				label.removeClass("invalid");
			}
			if (value.val() != "" && label.val() != "") {
				var opt_holder = $("#" + _btn.attr("data-target"));
				/* Make sure the textarea has newline as last character
				 * As newline is used as delimitter */
				if(opt_holder.val() != "") {
					if(opt_holder.val().slice(-1) != "\n") {
						opt_holder.val( opt_holder.val() + "\n" );
					}
				}
				opt_holder.val( opt_holder.val() + ( value.val() +"|"+ label.val()) +"\n" );
				/* Clear the fields */
				value.val("");
				label.val("");
				/* Set the focus to value box
				 * So that user can start input next option */
				value.focus();
				/**/
				this.handleDefault($("#" + _btn.attr("data-target")));
			}
		};
		
		this.handleDefault = function(_option_field) {
			var html = '',
				keyval = [],
				is_valid = true,				
				default_val = null,	
				options = _option_field.val(),				
				dcontainer = $("#wcff-default-option-holder");
			
			var locale = _option_field.attr('data-locale');
			var ftype = document.getElementById('wcff-field-type-meta-type');
			ftype = ftype.options[ftype.selectedIndex].value;
			
			if (typeof locale !== typeof undefined && locale !== false) {
				dcontainer = $("#wcff-default-option-holder-"+locale);
			}
				
			/* Shave of any unwanted character at both ends, includig \n */
			options = options.trim();
			options = options.split("\n");
			/* Handle the default option */
			if (ftype === "checkbox") {
				default_val = dcontainer.find("input[type=checkbox]:checked").map(function() {
				    return this.value;
				}).get();	
				/* Reset it */
				dcontainer.html("");				
				html += '<ul>';
				for (var i = 0; i < options.length; i++) {
					keyval = options[i].split("|");
					if (keyval.length == 2 && keyval[0].trim() != "" && keyval[1].trim() != "") {
						if (default_val && default_val.indexOf(keyval[0]) > -1) {
							html += '<li><input type="checkbox" value="'+ keyval[0] +'" checked /> '+ keyval[1] +'</li>';
						} else {
							html += '<li><input type="checkbox" value="'+ keyval[0] +'" /> '+ keyval[1] +'</li>';
						}						
					}
				}				
				html += '</ul>';
				dcontainer.html(html);
			} else if(ftype === "radio") {
				default_val = dcontainer.find("input[type=radio]:checked" ).val();
				/* Reset it */
				dcontainer.html("");
				html += '<ul>';
				for (var i = 0; i < options.length; i++) {
					keyval = options[i].split("|");
					if (keyval.length == 2 && keyval[0].trim() != "" && keyval[1].trim() != "") {
						if (default_val && default_val === keyval[0]) {
							html += '<li><input type="radio" value="'+ keyval[0] +'" checked /> '+ keyval[1] +'</li>';
						} else {
							html += '<li><input type="radio" value="'+ keyval[0] +'" /> '+ keyval[1] +'</li>';
						}						
					}
				}				
				html += '</ul>';
				dcontainer.html(html);
			} else {
				/* This must be select box */
				default_val = dcontainer.find("select").val();
				/* Reset it */
				dcontainer.html("");
				html += '<select>';
				html += '<option value="">-- Choose the default Option --</option>';
				for (var i = 0; i < options.length; i++) {
					keyval = options[i].split("|");
					if (keyval.length == 2 && keyval[0].trim() != "" && keyval[1].trim() != "") {
						if (default_val && default_val === keyval[0]) {
							html += '<option value="'+ keyval[0] +'" selected >'+ keyval[1] +'</option>';
						} else {
							html += '<option value="'+ keyval[0] +'">'+ keyval[1] +'</option>';
						}						
					}
				}				
				html += '</select>';
				dcontainer.html(html);
			}	
		};
		
		this.addCondition = function( target ) {
			var ruleTr = $( '<tr></tr>' );			
			ruleTr.html( target.parent().parent().parent().find("tr").last().html() );				
			if( target.parent().parent().parent().children().length == 1 ) {
				ruleTr.find("td.remove").html( '<a href="#" class="condition-remove-rule wcff-button-remove"></a>' );
			}			
			target.parent().parent().parent().append( ruleTr );		
			ruleTr.find( "select.wcff_condition_param" ).trigger( "change" );
		};
		
		this.addLocation = function( target ) {
			var locationTr = $( '<tr></tr>' );
			locationTr.html( target.parent().parent().parent().find("tr").last().html() );
			if( target.parent().parent().parent().children().length === 1 ) {
				locationTr.find("td.remove").html( '<a href="#" class="location-remove-rule wcff-button-remove"></a>' );
			}	
			target.parent().parent().parent().append( locationTr );			
			locationTr.find( "select.wcff_location_param" ).trigger( "change" );
		};
		
		this.removeRule = function( target ) {		
			var parentTable = target.parent().parent().parent().parent(),
			rows = parentTable.find( 'tr' );		
			if( rows.size() === 1 ) {
				parentTable.parent().remove();
			} else {
				target.parent().parent().remove();
			}
		}; 
		
		this.addConditionGroup = function( target ) {
			var groupDiv = $( 'div.wcff_logic_group:first' ).clone( true );
			var rulestr = groupDiv.find("tr");			
			if( rulestr.size() > 1 ) {
				var firstTr = groupDiv.find("tr:first").clone( true );
				groupDiv.find("tbody").html("").append( firstTr );				
			}
			groupDiv.find("h4").html( "or" );
			target.prev().before( groupDiv );			
			groupDiv.find("td.remove").html( '<a href="#" class="condition-remove-rule wcff-button-remove"></a>' );
			groupDiv.find( "select.wcff_condition_param" ).trigger( "change" );
		};
		
		this.addLocationGroup = function( target ) {
			var groupDiv = $( 'div.wcff_location_logic_group:first' ).clone( true );
			var rulestr = groupDiv.find("tr");			
			if( rulestr.size() > 1 ) {
				var firstTr = groupDiv.find("tr:first").clone( true );
				groupDiv.find("tbody").html("").append( firstTr );				
			}
			groupDiv.find("h4").html( "or" );
			target.prev().before( groupDiv );			
			groupDiv.find("td.remove").html( '<a href="#" class="location-remove-rule wcff-button-remove"></a>' );
			groupDiv.find( "select.wcff_condition_param" ).trigger( "change" );
		};
		
		this.addPricingRule = function( target ) {
			var html = '';				
			if (this.activeField["type"] === "datepicker") {
				html = this.buildPricingWidgetDatePicker("pricing", null);
			} else if (this.activeField["type"] === "checkbox") {
				html = this.buildPricingWidgetMultiChoices("pricing", null);
			} else if (this.activeField["type"] === "radio" || this.activeField["type"] === "select") {
				html = this.buildPricingWidgetChoice("pricing", null);
			} else {
				html = this.buildPricingWidgetInput("pricing", null);
			}			
			target.before($(html));						
		};
		
		this.addFeeRule = function( target ) {
			var html = '';				
			if (this.activeField["type"] === "datepicker") {
				html = this.buildPricingWidgetDatePicker("fee", null);
			} else if (this.activeField["type"] === "checkbox") {
				html = this.buildPricingWidgetMultiChoices("fee", null);
			} else if (this.activeField["type"] === "radio" || this.activeField["type"] === "select") {
				html = this.buildPricingWidgetChoice("fee", null);
			} else {
				html = this.buildPricingWidgetInput("fee", null);
			}			
			target.before($(html));				
		};
		
		this.renderSingleView = function( _target ) {
			var i = 0,
				j = 0,
				me = this,
				html = '',
				keyval = [],
				options = [],
				fee_row = null,				
				pricing_row = null,
				default_val = null,
				temp_holder = null,
				dcontainer = $( "#wcff-default-option-holder" );
			/* Store meta key in to activeField */
			this.activeField["key"] = _target.attr( "data-key" );
						
			/* Scroll down to Field Factory Container */
			$('html,body').animate(
				{ scrollTop: $("#wcff_factory").offset().top - 50  },
		        'slow'
		    );
			
			$("table.wcff_price_rules_table").remove();
			$("table.wcff_fee_rules_table").remove();
			$("table.wcff_fields_group_rules_table").not(':first').remove();
			
			/* Clear the pricing rules section */
			$("#wcff-factory-pricing-rules-wrapper").children().not("#wcff-add-price-rule-btn").remove();
			/* Clear the fee rules section */
			$("#wcff-factory-fee-rules-wrapper").children().not("#wcff-add-fee-rule-btn").remove();
			
			$("table.wcff_fields_group_rules_table").find("input.wcff-sub-fields-group-rule-value").val("");
			$("table.wcff_fields_group_rules_table").find("select.wcff_fgroup_rules_operator select").val("equal");
			$("table.wcff_fields_group_rules_table").find("select.wcff-sub-fields-group").val("-1");				
		
			$("#wcff-factory-tab-header > a:first-child").trigger("click");				
			$("#wcff-field-type-meta-type").val( this.unEscapeQuote( this.activeField["type"] ) );	
			
			$("#wcff-field-type-meta-label").val( this.unEscapeQuote( this.activeField["label"] ) );
			$("#wcff-field-type-meta-name").val( this.unEscapeQuote( this.activeField["name"] ) );
			
			/* Locales for Label */
			if (me.activeField["locale"]) {
				for(var i = 0; i < wcff_var.locales.length; i++) {
					if($("#wcff-field-type-meta-label-" + wcff_var.locales[i]).length > 0) {
						if(this.activeField["locale"][wcff_var.locales[i]] && this.activeField["locale"][wcff_var.locales[i]]["label"]) {
							$("#wcff-field-type-meta-label-" + wcff_var.locales[i]).val(this.activeField["locale"][wcff_var.locales[i]]["label"]);
						}
					}
				}
			}
			/* Hide it, it may not necessory */
			$("#wcff-factory-locale-label-dialog").hide();
			
			/* If it is Datepicker then reset the Disable Date widget */
			if (this.activeField["type"] === "datepicker") {
				$("div.wcff-factory-tab-right-panel").find("div.wcff-field-types-meta").each(function() {
					if ($(this).attr("data-param") !== "") {
						var param = $(this).attr("data-param");
						var type = $(this).attr("data-type");
						if (type === "checkbox" || type === "radio") {
							$(this).find("input[type="+ type +"]").prop('checked', false);
						} else {							
							$(this).find(type).val("");							
						}
					}
				});
			}
			
			/* Set the appropriate params with values */
			$("#wcff-field-types-meta-body div.wcff-field-types-meta").each(function() {
				if ($(this).attr("data-param") === "choices" || $(this).attr("data-param") === "palettes") {					
					me.activeField[$(this).attr("data-param")] = me.activeField[$(this).attr("data-param")].replace( /;/g, "\n");
				}								
				if ($(this).attr("data-type") === "checkbox") {
					var choices = me.activeField[$(this).attr("data-param")];	
					if (choices) {
						for (i = 0; i < choices.length; i++) {					
							$("input[name=wcff-field-type-meta-"+ $(this).attr("data-param") +"][value="+ choices[i] +"]" ).prop('checked', true);	
						}
					}					
				} else if ($(this).attr("data-type") === "radio") {
					$("input[name=wcff-field-type-meta-"+ $(this).attr("data-param") +"][value="+ me.activeField[$(this).attr("data-param")] +"]").prop('checked', true);				
				} else {
					if ($(this).attr("data-type") !== "html") {
						$("#wcff-field-type-meta-"+$(this).attr("data-param")).val(me.unEscapeQuote(me.activeField[$(this).attr("data-param")]));
					}						
				}
				/* Load locale related fields */
				if (me.activeField["locale"]) {
					for (i = 0; i < wcff_var.locales.length; i++) {
						if ($("#wcff-field-type-meta-"+ $(this).attr("data-param") + "-" + wcff_var.locales[i]).length > 0) {
							if ($(this).attr("data-param") === "choices" && me.activeField["locale"][wcff_var.locales[i]] && me.activeField["locale"][wcff_var.locales[i]][$(this).attr("data-param")]) {
								me.activeField["locale"][wcff_var.locales[i]][$(this).attr("data-param")] = me.activeField["locale"][wcff_var.locales[i]][$(this).attr("data-param")].replace( /;/g, "\n");
							}
							if (me.activeField["locale"][wcff_var.locales[i]] && me.activeField["locale"][wcff_var.locales[i]][$(this).attr("data-param")]) {
								$("#wcff-field-type-meta-"+ $(this).attr("data-param") + "-" + wcff_var.locales[i]).val(me.activeField["locale"][wcff_var.locales[i]][$(this).attr("data-param")]);
							}						
						}					
					}
				}					
			});			
			
			dcontainer.html("");
			/* Render default section */
			/* Default section handling for Check Box */
			if (this.activeField["type"] === "checkbox") {
				if (this.activeField["choices"] != "") {
					/* Prepare default value property */
					default_val = [];
					/* CHeck for this property, until V1.4.0 check box for Admin Fields doesn't has this property */
					if (this.activeField["default_value"]) {
						temp_holder = this.activeField["default_value"];
						/* This is for backward compatibility - <= V 1.4.0 */
						if (Object.prototype.toString.call(temp_holder) !== '[object Array]') {
							/* Since we haven't replaced the default value - as we used before */
							temp_holder = temp_holder.split(";");
							for (i = 0; i < temp_holder.length; i++) {
								keyval = temp_holder[i].trim().split("|");
								if(keyval.length === 2) {
									default_val.push(keyval[0].trim());
								}							
							}
						} else {
							default_val = this.activeField["default_value"];
						}
					}					
					options = this.activeField["choices"].split("\n");
					html = '<ul>';
					for (i = 0; i < options.length; i++) {
						keyval = options[i].split("|");
						if (keyval.length === 2) {
							if (default_val.indexOf(keyval[0]) > -1) {
								html += '<li><input type="checkbox" value="'+ this.unEscapeQuote(keyval[0]) +'" checked /> '+ this.unEscapeQuote(keyval[1]) +'</li>';
							} else {
								html += '<li><input type="checkbox" value="'+ this.unEscapeQuote(keyval[0]) +'" /> '+ this.unEscapeQuote(keyval[1]) +'</li>';
							}							
						}						
					}
					html += '</ul>';
					dcontainer.html(html);
					/* Now inflate the default value for locale */	
					if (me.activeField["locale"]) {
						for(i = 0; i < wcff_var.locales.length; i++) {						
							if (this.activeField["locale"][wcff_var.locales[i]] && 
								this.activeField["locale"][wcff_var.locales[i]]["choices"] &&
								this.activeField["locale"][wcff_var.locales[i]]["choices"] != "") {							
								options = this.activeField["locale"][wcff_var.locales[i]]["choices"].split("\n");
								default_val = (this.activeField["locale"][wcff_var.locales[i]]["default_value"]) ? this.activeField["locale"][wcff_var.locales[i]]["default_value"] : "";
								
								html = '<ul>';
								for (j = 0; j < options.length; j++) {
									keyval = options[j].split("|");
									if (keyval.length === 2) {
										if (default_val.indexOf(keyval[0]) > -1) {
											html += '<li><input type="checkbox" value="'+ this.unEscapeQuote(keyval[0]) +'" checked /> '+ this.unEscapeQuote(keyval[1]) +'</li>';
										} else {
											html += '<li><input type="checkbox" value="'+ this.unEscapeQuote(keyval[0]) +'" /> '+ this.unEscapeQuote(keyval[1]) +'</li>';
										}	
									}
								}
								html += '</ul>';
								$("#wcff-default-option-holder-" + wcff_var.locales[i]).html(html);							
							}					
						}
					}
				}		
			}			
			/* Default section handling for Radio Button */
			if (this.activeField["type"] === "radio") {
				if (this.activeField["choices"] != "") {
					/* Prepare default value property */
					default_val = "";
					if (this.activeField["default_value"]) {
						if (this.activeField["default_value"].indexOf("|") != -1) {
							/* This is for backward compatibility - <= V 1.4.0 */
							keyval = this.activeField["default_value"].trim().split("|");
							if (keyval.length === 2) {
								default_val = keyval[0];
							}							
						} else {
							default_val = this.activeField["default_value"].trim();
						}
					}					
					options = this.activeField["choices"].split("\n");
					html = '<ul>';
					for (i = 0; i < options.length; i++) {
						keyval = options[i].split("|");
						if (keyval.length === 2) {
							if (default_val === keyval[0]) {
								html += '<li><input name="wcff-default-choice" type="radio" value="'+ this.unEscapeQuote(keyval[0]) +'" checked /> '+ this.unEscapeQuote(keyval[1]) +'</li>';
							} else {
								html += '<li><input name="wcff-default-choice" type="radio" value="'+ this.unEscapeQuote(keyval[0]) +'" /> '+ this.unEscapeQuote(keyval[1]) +'</li>';
							}							
						}						
					}
					html += '</ul>';
					dcontainer.html(html);					
					/* Now inflate the default value for locale */	
					if (me.activeField["locale"]) {
						for(i = 0; i < wcff_var.locales.length; i++) {						
							if (this.activeField["locale"][wcff_var.locales[i]] && 
								this.activeField["locale"][wcff_var.locales[i]]["choices"] &&
								this.activeField["locale"][wcff_var.locales[i]]["choices"] != "") {
								
								options = this.activeField["locale"][wcff_var.locales[i]]["choices"].split("\n");
								default_val = (this.activeField["locale"][wcff_var.locales[i]]["default_value"]) ? this.activeField["locale"][wcff_var.locales[i]]["default_value"] : "";
								
								html = '<ul>';
								for (j = 0; j < options.length; j++) {
									keyval = options[j].split("|");
									if (keyval.length === 2) {
										if (default_val === keyval[0]) {
											html += '<li><input name="wcff-default-choice-'+ wcff_var.locales[i] +'" type="radio" value="'+ this.unEscapeQuote(keyval[0]) +'" checked /> '+ this.unEscapeQuote(keyval[1]) +'</li>';
										} else {
											html += '<li><input name="wcff-default-choice-'+ wcff_var.locales[i] +'" type="radio" value="'+ this.unEscapeQuote(keyval[0]) +'" /> '+ this.unEscapeQuote(keyval[1]) +'</li>';
										}	
									}
								}
								html += '</ul>';
								$("#wcff-default-option-holder-" + wcff_var.locales[i]).html(html);							
							}					
						}
					}
				}
			}		
			/* Default section handling for Select */
			if (this.activeField["type"] === "select") {
				/* Prepare default value property */
				default_val = "";
				if (this.activeField["default_value"]) {
					if (this.activeField["default_value"].indexOf("|") != -1) {
						/* This is for backward compatibility - <= V 1.4.0 */
						keyval = this.activeField["default_value"].trim().split("|");
						if (keyval.length === 2) {
							default_val = keyval[0];
						}							
					} else {
						default_val = this.activeField["default_value"].trim();
					}
				}		
				options = this.activeField["choices"].split("\n");
				html = '<select>';
				html += '<option value="">-- Choose the default Option --</option>';
				for (i = 0; i < options.length; i++) {
					keyval = options[i].split("|");
					if (keyval.length === 2) {
						if (default_val === keyval[0]) {
							html += '<option value="'+ this.unEscapeQuote(keyval[0]) +'" selected>'+ this.unEscapeQuote(keyval[1]) +'</option>';
						} else {
							html += '<option value="'+ this.unEscapeQuote(keyval[0]) +'">'+ this.unEscapeQuote(keyval[1]) +'</option>';
						}					
					}						
				}
				html += '</select>';
				dcontainer.html(html);
				/* Now inflate the default value for locale */	
				if (me.activeField["locale"]) {
					for(i = 0; i < wcff_var.locales.length; i++) {						
						if (this.activeField["locale"][wcff_var.locales[i]] && 
							this.activeField["locale"][wcff_var.locales[i]]["choices"] &&
							this.activeField["locale"][wcff_var.locales[i]]["choices"] != "") {
							
							options = this.activeField["locale"][wcff_var.locales[i]]["choices"].split("\n");
							default_val = (this.activeField["locale"][wcff_var.locales[i]]["default_value"]) ? this.activeField["locale"][wcff_var.locales[i]]["default_value"] : "";
							
							html = '<select>';
							html += '<option value="">-- Choose the default Option --</option>';
							for (j = 0; j < options.length; j++) {
								keyval = options[j].split("|");
								if (keyval.length === 2) {
									if (default_val === keyval[0]) {
										html += '<option value="'+ this.unEscapeQuote(keyval[0]) +'" selected>'+ this.unEscapeQuote(keyval[1]) +'</option>';
									} else {
										html += '<option value="'+ this.unEscapeQuote(keyval[0]) +'">'+ this.unEscapeQuote(keyval[1]) +'</option>';
									}	
								}
							}
							html += '</select>';
							$("#wcff-default-option-holder-" + wcff_var.locales[i]).html(html);							
						}					
					}
				}
			}
			
			/* Show or hide Img width config row - for file field */
			if (this.activeField["type"] === "file") {
				var isPrev = $("input[name=wcff-field-type-meta-img_is_prev]:checked").val();
				if (isPrev && isPrev === "yes") {
					$("div[data-param=img_is_prev_width]").show();
				} else {
					$("div[data-param=img_is_prev_width]").hide();
				}
			}
			
			if (this.activeField["type"] === "datepicker") {
				var isTimePicker = $("input[name=wcff-field-type-meta-timepicker]:checked").val();				
				if (isTimePicker && isTimePicker === "yes") {
					$("div[data-param=min_max_hours_minutes]").closest("tr").css("display", "table-row");
				} else {
					$("div[data-param=min_max_hours_minutes]").closest("tr").css("display", "none");
				}
				/* Set the min max hours & minutes */
				if (this.activeField["min_max_hours_minutes"] && this.activeField["min_max_hours_minutes"] !== "") {
					var min_max = this.activeField["min_max_hours_minutes"].split("|");
					if (min_max instanceof Array) {
						if (min_max.length >= 1) {
							$("#wccpf-datepicker-min-max-hours").val(min_max[0])
						}
						if (min_max.length >= 2) {
							$("#wccpf-datepicker-min-max-minutes").val(min_max[1])
						}
					}										
				}
			}
			
			/* Show the roles selector config, if the field is private */
			var isPrivate = $("input[name=wcff-field-type-meta-login_user_field]:checked").val();
			if (isPrivate === "yes") {
				$("div[data-param=show_for_roles]").closest("tr").css("display", "table-row");
			} else {
				$("div[data-param=show_for_roles]").closest("tr").css("display", "none");
			}			
			
			/* Render Pricing & Fee rules */
			if (wcff_var.post_type === "wccpf") {			
				var pricing_rules = this.activeField["pricing_rules"];
				if (Object.prototype.toString.call(pricing_rules) === '[object Array]') {
				    for (i = 0; i < pricing_rules.length; i++) {	
				    	this.renderPricingRow("pricing", pricing_rules[i], $("#wcff-add-price-rule-btn"));		    		
				    }
				}			
				var fee_rules = this.activeField["fee_rules"];
				if( Object.prototype.toString.call( fee_rules ) === '[object Array]' ) {
				    for(i = 0; i < fee_rules.length; i++) {				    	
				    	this.renderPricingRow("fee", fee_rules[i], $("#wcff-add-fee-rule-btn"));
				    }				
				}				
			}
			
			/* Hides the unnecessory config rows - ( only for Admin Fields ) */
			if (wcff_var.post_type === "wccaf") {
				if (this.activeField["show_on_product_page"]) {
					var display = "table-row";
					if (this.activeField["show_on_product_page"] === "no") {
						display = "none";
					} 
					$("div.wcff-field-types-meta").each(function () {
						var flaq = false;
						if ($(this).attr("data-param") === "visibility" || 
							$(this).attr("data-param") === "order_meta" ||
							$(this).attr("data-param") === "login_user_field" ||
							$(this).attr("data-param") === "cart_editable" ||
							$(this).attr("data-param") === "cloneable" ||
							$(this).attr("data-param") === "show_as_read_only" ||
							$(this).attr("data-param") === "showin_value" ) {
							flaq = true;
						}					
						if (flaq) {
							$(this).closest("tr").css("display", display);
						}
					});
				} 
			}
			
			/* Set Fields Factory mode to PUT */		
			$(".wcff-add-new-field").html("Update Field");
			
			/* Show pricing tab */
			if (this.activeField["type"] !== "file" && this.activeField["type"] !== "email" && this.activeField["type"] !== "label" && this.activeField["type"] !== "hidden") {
				$("#wcff-factory-tab-header a:last-child").show();
			} else {
				/* Pricing rules not applicable for the following field type 
				 * 1. File
				 * 2. Email
				 * 3. Hidden
				 * 4. Label */
				$("#wcff-factory-tab-header a:last-child").hide();
			}	
			
			/* Make the field type select box disabled - to prevent the user upating the Field Type
			 * From V2.0.0, we have added Pricing Rules - which will conflict if user changes the Field Type once created */
			$('#wcff-field-type-meta-type option:not(:selected)').prop('disabled', true);
			
			/*Switch to Update mode */
			$("#wcff_fields_factory").attr("action", "PUT");
			$("#wcff-field-factory-footer").show();
			$("#wcff-field-factory-footer").find( "a.wcff-field-delete-btn" ).attr( "data-key", _target.attr( "data-key" ) );
		};
		
		this.onFieldSubmit = function( target ) {
			var i = 0,
			me = this,			
			payload = {},
			dcontainer = $("#wcff-default-option-holder");
			
			/**/
			this.pricingRules = [];
			/**/
			this.feeRules = [];
			
			/* Since jQuery some time unrliable (I guess) on retriving values from select box */
			var ftype = document.getElementById('wcff-field-type-meta-type');
			payload.type = ftype.options[ftype.selectedIndex].value;
			
			payload.label = me.escapeQuote( $("#wcff-field-type-meta-label").val() );
			payload.name = me.escapeQuote( $("#wcff-field-type-meta-name").val() );
			
			if( payload.label !== "" ) {
				/* Fetching regular config meta starts here */
				$("#wcff-field-types-meta-body div.wcff-field-types-meta").each(function() {				
					if( $(this).attr("data-type") === "checkbox" ) {			
						payload[ $(this).attr("data-param") ] = $(this).find("input[name=wcff-field-type-meta-"+ $(this).attr("data-param") +"]:checked").map(function() {
						    return me.escapeQuote(this.value);
						}).get();
					} else if( $(this).attr("data-type") === "radio" ) {
						payload[ $(this).attr("data-param") ] = me.escapeQuote( $("input[name=wcff-field-type-meta-"+ $(this).attr("data-param") +"]:checked" ).val() );			
					} else {
						if ($(this).attr("data-type") !== "html") {
							payload[ $(this).attr("data-param") ] = me.escapeQuote( $("#wcff-field-type-meta-"+ $(this).attr("data-param") ).val() );				
							if( $(this).attr("data-param") === "choices" || $(this).attr("data-param") === "palettes" ) {
								payload[ $(this).attr("data-param") ] = payload[ $(this).attr("data-param") ].replace( /\n/g, ";" );
							}
						}						
					}
				});		
				/* Fetching regular config meta ends here */
				
				/* If it is date picker then */
				if (payload.type === "datepicker") {
					var isTimePicker = $("input[name=wcff-field-type-meta-timepicker]:checked").val();
					var min_max_hours = "0:23";
					var min_max_minutes = "0:59";
					if ($("#wccpf-datepicker-min-max-hours").val() != "") {
						min_max_hours = $("#wccpf-datepicker-min-max-hours").val();
					}
					if ($("#wccpf-datepicker-min-max-minutes").val() != "") {
						min_max_minutes = $("#wccpf-datepicker-min-max-minutes").val();
					}
					payload[ "min_max_hours_minutes" ] = min_max_hours + "|" + min_max_minutes;
				}
				
				/* Fetching locale related config meta starts here */
				var resources = {};
				var properties = {};
				for(i = 0; i < wcff_var.locales.length; i++) {
					properties = {};
					$("div.wcff-locale-block").each(function() {						
						properties[$(this).attr("data-param")] = $("#wcff-field-type-meta-"+ $(this).attr("data-param") +"-"+ wcff_var.locales[i]).val();
						if ($(this).attr("data-param") === "choices") {
							properties[$(this).attr("data-param")] = properties[$(this).attr("data-param")].replace( /\n/g, ";" );
						}
					});
					resources[wcff_var.locales[i]] = properties;
				}			
				
				/* Fetching default values related config meta starts here */
				if (payload.type === "checkbox") {
					payload["default_value"] = dcontainer.find("input[type=checkbox]:checked").map(function() {
					    return me.escapeQuote(this.value);
					}).get();					
					/* Fetch default value for locale */				
					for(i = 0; i < wcff_var.locales.length; i++) {						
						resources[wcff_var.locales[i]]["default_value"] = $("#wcff-default-option-holder-" + wcff_var.locales[i]).find("input[type=checkbox]:checked").map(function() {
						    return me.escapeQuote(this.value);
						}).get();						
					}
				} 				
				if (payload.type === "radio") {
					payload["default_value"] = this.escapeQuote(dcontainer.find("input[type=radio]:checked").val());
					/* Fetch default value for locale */					
					for(i = 0; i < wcff_var.locales.length; i++) {						
						resources[wcff_var.locales[i]]["default_value"] = this.escapeQuote($("#wcff-default-option-holder-" + wcff_var.locales[i]).find("input[type=radio]:checked").val());						
					}
				}				
				if (payload.type === "select") {
					payload["default_value"] = this.escapeQuote(dcontainer.find("select").val());
					/* Fetch default value for locale */					
					for(i = 0; i < wcff_var.locales.length; i++) {						
						resources[wcff_var.locales[i]]["default_value"] = this.escapeQuote($("#wcff-default-option-holder-" + wcff_var.locales[i]).find("select").val());						
					}
				}
				/* Fetching default values related config meta ends here */
				
				/* Put the locale resource on payload object */
				payload["locale"] = resources;				
				
				if( $("#wcff_fields_factory").attr("action") === "POST" ) {
					payload["order"] = ($('.wcff-meta-row').length + 1);
				} else if( $("#wcff_fields_factory").attr("action") === "PUT" ) {
					payload["key"] = this.escapeQuote(this.activeField["key"]);
					payload["order"] = $('input[name='+ this.activeField["key"] +'_order]').val();
					
					/* Fetch the pricing and fee rules only on PUT mode */
					$("table.wcff-pricing-row").each(function() {												
						me.fetchPricingRules($(this), "pricing");							
					});					
					$("table.wcff-fee-row").each(function() {				
						me.fetchPricingRules($(this), "fee");				
					});
					
					if( this.pricingRules.length > 0 ) {
						payload["pricing_rules"] = this.pricingRules;
					}
					if( this.feeRules.length > 0 ) {
						payload["fee_rules"] = this.feeRules;
					}
				}				
				
				mask.doMask( target );
				
				/* Double make sure the Type property is there
				 * For some unknown reason it is keeping randomly disappearing */			
				if (!payload.type) {
					alert("Sorry, something went wrong, nothing to worry though, just reload the page and try again.!");
					return;
				}
				
				this.prepareRequest( $("#wcff_fields_factory").attr("action"), "wcff_fields", payload );
				this.dock( "wcff_fields", target );
			} else {
				$("#wcff-field-type-meta-label").addClass("wcff-form-invalid");
			}			
		};
		
		this.onPostSubmit = function( _target ) {		
			var location_rules_group = [], 
				condition_rules_group = [];			
			$(".wcff_logic_group").each(function() {
				var rules = [];
				$(this).find("table.wcff_rules_table tr").each(function() {
					rule = {};
					rule["context"] = $(this).find("select.wcff_condition_param").val();
					rule["logic"] = $(this).find("select.wcff_condition_operator").val();
					rule["endpoint"] = $(this).find("select.wcff_condition_value").val();
					rules.push( rule );
				});
				condition_rules_group.push( rules );
			});
			$(".wcff_location_logic_group").each(function() {
				var rules = [];
				$(this).find("table.wcff_location_rules_table tr").each(function() {
					rule = {};
					rule["context"] = $(this).find("select.wcff_location_param").val();
					rule["logic"] = $(this).find("select.wcff_location_operator").val();					
					if( $(this).find("select.wcff_location_param").val() !== "location_product_data" ) {
						rule["endpoint"] = { 
							"context" : $(".wcff_location_metabox_context_value").val(),
							"priority": $(".wcff_location_metabox_priorities_value").val()
						}
					} else {
						rule["endpoint"] = $(this).find("select.wcff_location_product_data_value").val();
					}					
					rules.push( rule );
				});				
				location_rules_group.push( rules );
			});			
			$("#wcff_condition_rules").val( JSON.stringify( condition_rules_group ) );
			if( location_rules_group.length > 0 ) {
				$("#wcff_location_rules").val( JSON.stringify( location_rules_group ) );
			}			
			return true;
		};	
		
		this.fetchPricingRules = function(_current, _type) {
			var rule = {},
				me = this,
				dtype = "",
				pvalue = "",
				logic = "",
				amount = 0,
				ftype = $("#wcff-field-type-meta-type").val();
			
			rule["expected_value"] = {};
			rule["amount"] = _current.find("input.wcff-"+ _type +"-rules-amount").val();
			rule["ptype"] = _current.find("div.wcff-"+ _type +"-rule-toggle > a.selected").data("ptype");
			
			if (_type === "fee") {
				rule["title"] = this.escapeQuote(_current.find("input.wcff-fee-rules-title").val());	
				if (rule["title"] === "" || !rule["title"]) {
					return;
				}
			} else {
				rule["title"] = this.escapeQuote(_current.find("input.wcff-pricing-rules-title").val());	
				if (rule["title"] === "" || !rule["title"]) {
					return;
				}
			}	
			
			if (ftype === "datepicker") {				
				dtype = _current.find("ul.wcff-"+ _type +"-date-type-header > li.selected").attr("data-dtype");
				rule["expected_value"]["dtype"] = dtype;
				rule["expected_value"]["value"] = null; 			
				if (dtype === "days") {					
					rule["expected_value"]["value"] = _current.find("input[type=checkbox]:checked").map(function() {
					    return this.value;
					}).get();
				} else if (dtype === "specific-dates") {
					rule["expected_value"]["value"] = _current.find("textarea.wcff-field-type-meta-specific_dates").val();
				} else if (dtype === "weekends-weekdays") {
					rule["expected_value"]["value"] = _current.find("input[name=wcff-field-type-meta-weekend_weekdays_"+ _current.index() +"]:checked").val();
				} else {
					rule["expected_value"]["value"] = _current.find("textarea.wcff-field-type-meta-specific_date_each_months").val();
				}
				
				if (rule["expected_value"]["value"] !== null && rule["amount"] !== "") {
					if (_type === "pricing") {
						this.pricingRules.push(rule);					
					} else {
						this.feeRules.push(rule);
					}					
				}						
			} else if(ftype === "select" || ftype === "radio") {
				pvalue = _current.find("select.wcff-"+ _type +"-choice-expected-value").val();
				logic = _current.find("select.wcff-"+ _type +"-choice-condition-value").val();
				
				if( pvalue !== "" && logic !== "" && rule["amount"] !== "" ) {
					rule["expected_value"] = pvalue;
					rule["logic"] = logic;					
					if (_type === "pricing") {
						this.pricingRules.push(rule);					
					} else {
						this.feeRules.push(rule);
					}
				}	
			} else if(ftype === "checkbox") {
				pvalue = [];
				pvalue = _current.find("input[type=checkbox]:checked").map(function() {
				    return this.value;
				}).get();
				logic = _current.find("select.wcff-"+ _type +"-multi-choice-condition-value").val();
				
				if( pvalue.length > 0 && logic !== "" && rule["amount"] !== "" ) {
					rule["expected_value"] = pvalue;
					rule["logic"] = logic;
					if (_type === "pricing") {
						this.pricingRules.push(rule);					
					} else {
						this.feeRules.push(rule);
					}
				}	
			} else {
				pvalue = _current.find("input.wcff-"+ _type +"-input-expected-value").val();
				logic = _current.find("select.wcff-"+ _type +"-input-condition-value").val();
				
				if( pvalue !== "" && logic !== "" && rule["amount"] !== "" ) {
					rule["expected_value"] = pvalue;
					rule["logic"] = logic;
					if (_type === "pricing") {
						this.pricingRules.push(rule);					
					} else {
						this.feeRules.push(rule);
					}
				}	
			}		
		};
		
		this.renderPricingRow = function(_type, _obj, _aBtn) {
			var widget = "";
			if (this.activeField["type"] === "text" || this.activeField["type"] === "number" || 
	    		this.activeField["type"] === "colorpicker" || this.activeField["type"] === "textarea") {
					widget = $(this.buildPricingWidgetInput(_type));
					widget.find("select.wcff-"+ _type +"-input-condition-value").val(_obj.logic);
					widget.find("input.wcff-"+ _type +"-input-expected-value").val(this.unEscapeQuote(_obj.expected_value));				    		
		    	} else if (this.activeField["type"] === "select" || this.activeField["type"] === "radio") {
		    		widget = $(this.buildPricingWidgetChoice(_type));
		    		widget.find("select.wcff-"+ _type +"-choice-condition-value").val(_obj.logic);
		    		widget.find("select.wcff-"+ _type +"-choice-expected-value").val(_obj.expected_value);		
		    	} else if (this.activeField["type"] === "checkbox") {			    		
		    		widget = $(this.buildPricingWidgetMultiChoices(_type));
		    		widget.find("select.wcff-"+ _type +"-multi-choice-condition-value").val(_obj.logic);
		    		if (_obj.expected_value) {
		    			for (var j = 0; j < _obj.expected_value.length; j++) {				    		
			    			widget.find("input[type=checkbox][value="+ _obj.expected_value[j] +"]").prop('checked', true);
			    		}
		    		}		    		
		    	} else {
		    		/* This must be date picker */				    		
		    		widget = $(this.buildPricingWidgetDatePicker(_type));
		    		widget.find("ul.wcff-"+ _type +"-date-type-header li").removeClass("selected");
		    		var pos = widget.find("ul.wcff-"+ _type +"-date-type-header li[data-dtype="+ _obj.expected_value.dtype +"]").addClass("selected").index();
		    		widget.find("div.wcff-factory-tab-right-panel > div").hide();
		    		widget.find("div.wcff-factory-tab-right-panel > div:nth-child("+ (pos + 1) +")").show();
		    		if (_obj.expected_value.dtype === "days" && _obj.expected_value && _obj.expected_value.value) {
		    			for (var k = 0; k < _obj.expected_value.value.length; k++) {
		    				widget.find("input[type=checkbox][value="+ _obj.expected_value.value[k] +"]").prop('checked', true);	
		    			}
		    		} else if (_obj.expected_value.dtype === "specific-dates") {
		    			widget.find("textarea.wcff-field-type-meta-specific_dates").val(_obj.expected_value.value);
		    		} else if (_obj.expected_value.dtype === "weekends-weekdays") {
		    			widget.find("input[type=radio][value="+ _obj.expected_value.value +"]").prop('checked', true);	
		    		} else {
		    			widget.find("textarea.wcff-field-type-meta-specific_date_each_months").val(_obj.expected_value.value);
		    		}
		    	}
			
			if(_type === "pricing") {
				widget.find("input.wcff-pricing-rules-title").val(this.unEscapeQuote(_obj.title));
			} else if (_type === "fee") {
				widget.find("input.wcff-fee-rules-title").val(this.unEscapeQuote(_obj.title));
			}
			widget.find("input.wcff-"+ _type +"-rules-amount").val(_obj.amount);
			widget.find("div.wcff-"+ _type +"-rule-toggle > a").removeClass("selected");
			widget.find("div.wcff-"+ _type +"-rule-toggle > a[data-ptype=" + _obj.ptype +"]").addClass("selected");
    			_aBtn.before(widget);	
		};
		
		this.buildPricingWidgetInput = function(_type, _val) {
			var html = '<table class="wcff-'+ _type +'-row">';			
			html += '<tr>';
			html += '<td class="wcff-'+ _type +'-left-td">';
			
			html += '<table class="wcff-'+ _type +'-table wcff-'+ _type +'-input-table">';
			html += '<tr>';
			html += '<td class="wcff-'+ _type +'-label-td">';
			if (this.activeField["type"] === "number") {
				html += '<label>If user entered number =></label>';
			} else if (this.activeField["type"] === "colorpicker") {
				html += '<label>If user\'s chosen color =></label>';
			} else {
				html += '<label>If user entered text =></label>';
			}			
			html += '</td>';
			
			/* Condition field section */
			html += '<td class="wcff-'+ _type +'-condition-td">';
			html += '<select class="wcff-'+ _type +'-input-condition-value">';
			if (this.activeField["type"] === "number") {
				html += '<option value="equal">is equal to</option>';
				html += '<option value="not-equal">is not equal to</option>';
				html += '<option value="less-than">less than</option>';
				html += '<option value="less-than-equal">less than or equal to</option>';
				html += '<option value="greater-than">greater than</option>';
				html += '<option value="greater-than-equal">greater than or equal to</option>';
			} else {
				html += '<option value="equal">is equal to</option>';
				html += '<option value="not-equal">is not equal to</option>';
			}			
			html += '</select>';
			html += '</td>';
			
			/* Expected value field section */
			html += '<td class="wcff-'+ _type +'-value-td">';
			if (this.activeField["type"] !== "colorpicker") {
				html += '<input type="text" class="wcff-'+ _type +'-input-expected-value" value="" placeholder="Expected Value.?" />';
			} else {
				html += '<input type="text" class="wcff-'+ _type +'-input-expected-value" value="" placeholder="Expected Color.? (Use comma if more then one color value)" />';
			}			
			html += '</td>';
			html += '</tr>';
			html += '</table>';
			
			/* Bottom table which holds Amount field and Change Mode widget */
			html += this.buildAmountWidget(_type, "input");
			
			html += '</td>';
			/* Pricing rule remove button section starts here */
			html += '<td class="wcff-'+ _type +'-right-td wcff-rule-table-td-remove">';
			html += '<a href="#" class="'+ _type +'-remove-rule wcff-button-remove"></a>';
			html += '</td>';
			/* Pricing rule remove button section ends here */
			html += '</tr>';			
			html += '</table>';
			
			return html;
		};
		
		this.buildPricingWidgetChoice = function(_type, _val) {
			var html = '<table class="wcff-'+ _type +'-row">';			
			html += '<tr>';
			/* Pricing rules section starts here */
			html += '<td class="wcff-'+ _type +'-left-td">';
			
			html += '<table class="wcff-'+ _type +'-table wcff-'+ _type +'-choice-table">';
			html += '<tr>';
			html += '<td class="wcff-'+ _type +'-label-td">';
			html += '<label>If user chosen option =></label>';
			html += '</td>';
			
			/* Condition field section */
			html += '<td class="wcff-'+ _type +'-condition-td">';
			html += '<select class="wcff-'+ _type +'-choice-condition-value">';			
			var isNumber = this.isNumberChoices(this.activeField["choices"]);			
			if (isNumber) {
				html += '<option value="equal">is equal to</option>';
				html += '<option value="not-equal">is not equal to</option>';
				html += '<option value="less-than">less than</option>';
				html += '<option value="less-than-equal">less than or equal to</option>';
				html += '<option value="greater-than">greater than</option>';
				html += '<option value="greater-than-equal">greater than or equal to</option>';
			} else {
				html += '<option value="equal">is equal to</option>';
				html += '<option value="not-equal">is not equal to</option>';
			}			
			html += '</select>';
			html += '</td>';
			
			/* Expected value field section */
			html += '<td class="wcff-'+ _type +'-value-td">';
			html += '<select class="wcff-'+ _type +'-choice-expected-value">';
			var opt = [];
			var choices = this.activeField["choices"].trim().split("\n");
			if (choices) {
				for (var i = 0; i < choices.length; i++) {
					opt = choices[i].split("|");
					html += '<option value="'+ opt[0] +'">'+ opt[1] +'</option>';
				}
			}			
			html += '</select>';
			html += '</td>';
			html += '</tr>';
			html += '</table>';
			
			/* Bottom table which holds Amount field and Change Mode widget */
			html += this.buildAmountWidget(_type, "choice");
			
			html += '</td>';
			/* Pricing rules section ends here */
			
			/* Pricing rule remove button section starts here */
			html += '<td class="wcff-'+ _type +'-right-td wcff-rule-table-td-remove">';
			html += '<a href="#" class="'+ _type +'-remove-rule wcff-button-remove"></a>';
			html += '</td>';
			/* Pricing rule remove button section end here */
			html += '</tr>';			
			html += '</table>';
			
			return html;
		};
		
		this.buildPricingWidgetMultiChoices = function(_type, _val) {
			var html = '<table class="wcff-'+ _type +'-row">';			
			html += '<tr>';
			/* Pricing rules section starts here */
			html += '<td class="wcff-'+ _type +'-left-td">';
			
			html += '<table class="wcff-'+ _type +'-table wcff-'+ _type +'-multi-choice-table">';
			html += '<tr>';
			html += '<td class="wcff-'+ _type +'-label-td">';
			html += '<label>If user chosen option =></label>';
			html += '</td>';
			
			/* Condition field section */
			html += '<td class="wcff-'+ _type +'-condition-td">';
			html += '<select class="wcff-'+ _type +'-multi-choice-condition-value">';		
			html += '<option value="is-only">is only these</option>';
			html += '<option value="is-also">is also these</option>';
			html += '<option value="any-one-of">any of these</option>';	
			html += '</select>';
			html += '</td>';
			
			/* Expected value field section */
			html += '<td class="wcff-'+ _type +'-value-td">';
			html += '<ul class="wcff-'+ _type +'-multi-choices-ul">';
			var opt = [];
			var choices = this.activeField["choices"].trim().split("\n");
			if (choices) {
				for (var i = 0; i < choices.length; i++) {
					opt = choices[i].split("|");
					html += '<li><label><input type="checkbox" name="wcff-'+ _type +'-multi-choice-expected-value" value="'+ opt[0] +'" /> '+ opt[1] +'</label></li>';
				}
			}			
			html += '</ul>';
			html += '</td>';
			html += '</tr>';
			html += '</table>';
			
			/* Bottom table which holds Amount field and Change Mode widget */
			html += this.buildAmountWidget(_type, "multi-choice");
			
			html += '</td>';
			/* Pricing rules section ends here */
			
			/* Pricing rule remove button section starts here */
			html += '<td class="wcff-'+ _type +'-right-td wcff-rule-table-td-remove">';
			html += '<a href="#" class="'+ _type +'-remove-rule wcff-button-remove"></a>';
			html += '</td>';
			/* Pricing rule remove button section end here */
			html += '</tr>';			
			html += '</table>';
			
			return html;
		};
		
		this.buildPricingWidgetDatePicker = function(_type, _val) {
			var target = null;
			if (_type == "pricing") {
				target = $("#wcff-add-price-rule-btn");
			} else {
				target = $("#wcff-add-fee-rule-btn");
			}		
			var count = target.prevAll("table.wcff-"+ _type +"-row").length;
			var html = '<table class="wcff-'+ _type +'-row">';			
			html += '<tr>';
			/* Pricing rules section starts here */
			html += '<td class="wcff-'+ _type +'-left-td">';
			
			html += '<table class="wcff-'+ _type +'-table wcff-'+ _type +'-date-table">';
			html += '<tr>';
			html += '<td class="wcff-'+ _type +'-label-td">';
			html += '<label>If user picked date =></label>';
			html += '</td>';			
			html += '<td class="wcff-'+ _type +'-date-config-td">';
			
			html += '<div class="wcff-factory-tab-container">';
			html += '<div class="wcff-factory-tab-left-panel">';
			html += '<ul class="wcff-'+ _type +'-date-type-header">';
			html += '<li class="selected" data-dtype="days">Days</li>';
			html += '<li data-dtype="specific-dates">Specific Dates</li>';
			html += '<li data-dtype="weekends-weekdays">Weekends Or Weekdays</li>';
			html += '<li data-dtype="specific-dates-each-month">Specific Dates Each Months</li>';
			html += '</ul>';
			html += '</div>';
			html += '<div class="wcff-factory-tab-right-panel">';
			html += '<div class="wcff-factory-tab-content" style="display: block;">';
			html += '<div class="wcff-field-types-meta">';
			html += '<ul class="wcff-field-layout-horizontal">';
			html += '<li><label><input type="checkbox" name="wcff-field-type-meta-'+ _type +'-disable_days[]" value="sunday"> Sunday</label></li>';
			html += '<li><label><input type="checkbox" name="wcff-field-type-meta-'+ _type +'-disable_days[]" value="monday"> Monday</label></li>';
			html += '<li><label><input type="checkbox" name="wcff-field-type-meta-'+ _type +'-disable_days[]" value="tuesday"> Tuesday</label></li>';
			html += '<li><label><input type="checkbox" name="wcff-field-type-meta-'+ _type +'-disable_days[]" value="wednesday"> Wednesday</label></li>';
			html += '<li><label><input type="checkbox" name="wcff-field-type-meta-'+ _type +'-disable_days[]" value="thursday"> Thursday</label></li>';
			html += '<li><label><input type="checkbox" name="wcff-field-type-meta-'+ _type +'-disable_days[]" value="friday"> Friday</label></li>';
			html += '<li><label><input type="checkbox" name="wcff-field-type-meta-'+ _type +'-disable_days[]" value="saturday"> Saturday</label></li>';
			html += '</ul>';
			html += '</div>';
			html += '</div>';
			html += '<div class="wcff-factory-tab-content" style="display: none;">';
			html += '<div class="wcff-field-types-meta">';
			html += '<textarea class="wcff-field-type-meta-specific_dates" placeholder="Format: MM-DD-YYYY Example: 1-22-2017,10-7-2017" rows="2"></textarea>';
			html += '</div>';
			html += '</div>';
			html += '<div class="wcff-factory-tab-content" style="display: none;">';
			html += '<div class="wcff-field-types-meta">';
			html += '<ul class="wcff-field-layout-horizontal">';
			html += '<li><label><input type="radio" name="wcff-field-type-meta-weekend_weekdays_'+ count +'" value="weekends"> Week Ends</label></li>';
			html += '<li><label><input type="radio" name="wcff-field-type-meta-weekend_weekdays_'+ count +'" value="weekdays"> Week Days</label></li>';
			html += '</ul>';
			html += '</div>';
			html += '<div class="wcff-field-types-meta" data-type="html"><a href="#" class="wcff-date-disable-radio-clear button">Clear</a></div>';
			html += '</div>';
			html += '<div class="wcff-factory-tab-content" style="display: none;">';
			html += '<div class="wcff-field-types-meta">';
			html += '<textarea class="wcff-field-type-meta-specific_date_each_months" placeholder="Example: 5,10,12" rows="2"></textarea>';
			html += '</div>';
			html += '</div>';
			html += '</div>';
			html += '</div>';
			
			html += '</td>';			
			html += '</tr>';
			html += '</table>';		
			
			/* Bottom table which holds Amount field and Change Mode widget */
			html += this.buildAmountWidget(_type, "date");
			
			html += '</td>';
			/* Pricing rules section ends here */
			
			/* Pricing rule remove button section starts here */
			html += '<td class="wcff-'+ _type +'-right-td wcff-rule-table-td-remove">';
			html += '<a href="#" class="'+ _type +'-remove-rule wcff-button-remove"></a>';
			html += '</td>';
			/* Pricing rule remove button section end here */
			html += '</tr>';			
			html += '</table>';
			
			return html;
		};
		
		this.buildAmountWidget = function(_type, _ftype) {
			var html = '';
			if (_type === "pricing") {
				html += '<table class="wcff-pricing-table wcff-pricing-amount-table '+ _ftype +'">';
				html += '<tr>';
				html += '<td class="wcff-pricing-label-td">';
				html += '<label>Then change the price to =></label>';
				html += '</td>';
				html += '<td class="wcff-pricing-title-td">';
				html += '<input type="text" class="wcff-pricing-rules-title" value="" placeholder="Pricing Title" />';
				html += '</td>';
				html += '<td class="wcff-pricing-amount-td">';
				html += '<input type="number" class="wcff-pricing-rules-amount" value="" placeholder="Amount" />';
				html += '</td>';
				html += '<td class="wcff-pricing-mode-td">';
				html += '<div class="wcff-pricing-rule-toggle">';
				html += '<a href="#" data-ptype="add" title="add this amount with product original price" class="price-rule-add selected">Add</a>';
				html += '<a href="#" data-ptype="change" title="replace the original product price with this amount" class="price-rule-change">Change</a>';
				html += '</div>';
				html += '</td>';
				html += '</tr>';			
				html += '</table>';
			} else {
				html += '<table class="wcff-fee-table wcff-fee-table wcff-fee-amount-table '+ _ftype +'">';
				html += '<tr>';
				html += '<td class="wcff-fee-label-td">';
				html += '<label>Then add this Fee =></label>';
				html += '</td>';
				html += '<td class="wcff-fee-title-td">';
				html += '<input type="text" class="wcff-fee-rules-title" value="" placeholder="Fee Title" />';
				html += '</td>';
				html += '<td class="wcff-fee-amount-td">';
				html += '<input type="number" class="wcff-fee-rules-amount" value="" placeholder="Fee Amount" />';
				html += '</td>';
				html += '<td class="wcff-fee-mode-td">';
				html += '<div class="wcff-fee-rule-toggle">';
				html += '<a href="#" data-ptype="all" title="Add this fee for all quantity" class="fee-rule-all selected">All</a>';
				html += '<a href="#" data-ptype="quantity" title="Add this fee per quantity" class="fee-rule-quantity">Per Quantity</a>';
				html += '</div>';
				html += '</td>';
				html += '</tr>';			
				html += '</table>';
			}
			return html;
		};
		
		this.buildFeeWidgetInput = function(_val) {
			var html = '<table class="wcff-pricing-row">';			
			html += '<tr>';
			html += '<td class="wcff-pricing-left-td">';
			
			html += '</td>';
			/* Pricing rules section ends here */
			
			/* Pricing rule remove button section starts here */
			html += '<td class="wcff-pricing-right-td wcff-rule-table-td-remove">';
			html += '<a href="#" class="pricing-remove-rule wcff-button-remove"></a>';
			html += '</td>';
			/* Pricing rule remove button section end here */
			html += '</tr>';			
			html += '</table>';
			
			return html;
		};
		
		this.isNumberChoices = function(_options) {
			var opt = [];
			var flaq = false;
			var choices = _options.split("\n");
			if (choices) {
				flaq = true;
				for (var i = 0; i < choices.length; i++) {
					if (isNaN(choices[i].split("|")[0])) {
						flaq = false;
						break;
					}
				}
			}			
			return flaq;
		};
				
		this.reloadHtml = function( _where ) {
			_where.html( this.response.payload );
		};
		
		/* convert string to url slug */
		this.sanitizeStr = function( str ) {
			if( str ) {
				return str.toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'_');
			}
			return str;
		};	 
		
		this.escapeQuote = function( str ) {	
			if( str ) {
				str = str.replace( /'/g, '&#39;' );
				str = str.replace( /"/g, '&#34;' );
			}			
			return str;
		};
		
		this.unEscapeQuote = function( str ) {
			if( str ) {
				str = str.replace( /&#39;/g, "'" );
				str = str.replace( /&#34;/g, '"' );
			}
			return str;
		};
		
		/**
		 * Converts a string to its html characters completely.
		 *
		 * @param {String} str String with unescaped HTML characters
		 **/
		this.encode = function(str) {
			var buf = [];			
			for (var i=str.length-1;i>=0;i--) {
				buf.unshift(['&#', str[i].charCodeAt(), ';'].join(''));
			}			
			return buf.join('');
		},
		/**
		 * Converts an html characterSet into its original character.
		 *
		 * @param {String} str htmlSet entities
		 **/
		this.decode = function(str) {
			return str.replace(/&#(\d+);/g, function(match, dec) {
				return String.fromCharCode(dec);
			});
		}
		
		/**/
		this.prepareFactoryMeta = function() {
			var ftype = $("#wcff-field-type-meta-type").val();
			/* Product field related house keeping */
			if (wcff_var.post_type === "wccpf") {				
				if (ftype === "file") {
					$("div[data-param=img_is_prev_width]").hide();
				}
				if ($("#wcff-factory-multilingual-label-btn").length > 0) {
					if (ftype === "hidden" || ftype === "label") {
						$("#wcff-factory-multilingual-label-btn").hide();
					} else {
						$("#wcff-factory-multilingual-label-btn").show();
					}
				}				
			}
			/* Admin field related house keeping */
			if (wcff_var.post_type === "wccaf") {
				$("div.wcff-field-types-meta").each(function () {					
					if ($(this).attr("data-param") === "visibility" || 
						$(this).attr("data-param") === "order_meta" ||
						$(this).attr("data-param") === "login_user_field" ||
						$(this).attr("data-param") === "cart_editable" ||
						$(this).attr("data-param") === "cloneable" ||
						$(this).attr("data-param") === "show_as_read_only" ||
						$(this).attr("data-param") === "showin_value" ) {
						$(this).closest("tr").hide();
					}				
				});
				/* For url field we need to show the cloneable */
				if (ftype === "url") {
					$("div.wcff-field-types-meta").each(function () {	
						if ($(this).attr("data-param") === "login_user_field" || $(this).attr("data-param") === "cloneable") {
							$(this).closest("tr").show();
						}
					});
				}
			}			
		};
		
		this.prepareRequest = function( _request, _context, _payload ) {
			this.request = {
				request 	: _request,
				context 	: _context,
				post 		: wcff_var.post_id,
				post_type 	: wcff_var.post_type,
				payload 	: _payload
			};
		};
		
		this.prepareResponse = function( _status, _msg, _data ) {
			this.response = {
				status : _status,
				message : _msg,
				payload : _data
			};
		};
		
		this.dock = function( _action, _target ) {		
			var me = this;
			/* see the ajax handler is free */
			if( !this.ajaxFlaQ ) {
				return;
			}		
			
			$.ajax({  
				type       : "POST",  
				data       : { action : "wcff_ajax", wcff_param : JSON.stringify(this.request)},  
				dataType   : "json",  
				url        : wcff_var.ajaxurl,  
				beforeSend : function(){  				
					/* enable the ajax lock - actually it disable the dock */
					me.ajaxFlaQ = false;				
				},  
				success    : function(data) {				
					/* disable the ajax lock */
					me.ajaxFlaQ = true;				
					me.prepareResponse( data.status, data.message, data.data );		               
	
					/* handle the response and route to appropriate target */
					if( me.response.status ) {
						me.responseHandler( _action, _target );
					} else {
						/* alert the user that some thing went wrong */
						//me.responseHandler( _action, _target );
					}				
				},  
				error      : function(jqXHR, textStatus, errorThrown) {                    
					/* disable the ajax lock */
					me.ajaxFlaQ = true;
				},
				complete   : function() {
					mask.doUnMask();
				} 
			});		
		};
		
		this.responseHandler = function( _action, _target ){		
			if( _action === "product" ||
				_action === "product_cat" ||
				_action === "product_tag" ||
				_action === "product_type" ) {
				this.reloadHtml( _target.parent().parent().find("td.condition_value_td") );
			} else if(  _action === "location_product_data" ||
						_action === "location_product" ||
						_action === "location_product_cat" ) {
				this.reloadHtml( _target.parent().parent().find("td.location_value_td") );
			} else if( _action === "wcff_meta_fields" ) {
				this.reloadHtml( $("#wcff-field-types-meta-body") );
				/* Does some house keeping work for each fields type */
				this.prepareFactoryMeta();
			} else if( _action === "wcff_fields" ) {			
				if( this.request.request === "GET" ) {	
					this.activeField = JSON.parse( this.response.payload );				
					if( this.activeField["type"] === $("#wcff-field-type-meta-type").val() ) {
						this.renderSingleView( _target );
					} else {
						if (this.activeField["type"]) {
							this.prepareRequest( "GET", "wcff_meta_fields", { type : this.activeField["type"] } );
							this.dock( "single", _target );
						} else {
							alert("Looks like this field's meta is corrupted, please remove it and re create it.!");
						}						
					}				
				} else {
					if(this.response.status ) {
						/* Set Fields Factory to POST mode, on successfull completeion of any operation */
						$("#wcff-empty-field-set").hide();
						$("#wcff-field-factory-footer").hide();
						$(".wcff-add-new-field").html("+ Add Field");
						$("#wcff_fields_factory").attr("action","POST");	
			
						/* Hide prcing rule tab */
						$("#wcff-factory-tab-header a:last-child").hide();
						/* Clear the Active Field property */
						this.activeField = null;
						/* Enable Field Type options */
						$('#wcff-field-type-meta-type option').prop('disabled', false);					
					}				
					if( this.request.request === "DELETE" ) {						
						if( $(".wcff-meta-row").length <= 1 ) {										
							$("#wcff-empty-field-set").show();
						} else {
							$("#wcff-empty-field-set").hide();
						}						
					}					
					this.reloadHtml( $("#wcff-fields-set") );				
					$("#wcff-field-type-meta-label").val("");
					$("#wcff-field-type-meta-name").val("");	
					
					/* Clear the locale section for Label */
					$("#wcff-factory-locale-label-dialog div.wcff-locale-block").each(function() {
						$(this).find("input").val("");
					});
					$("#wcff-factory-locale-label-dialog").hide();
					
					/* Clear the pricing rules section */
					$("#wcff-factory-pricing-rules-wrapper").children().not("#wcff-add-price-rule-btn").remove();
					/* Clear the fee rules section */
					$("#wcff-factory-fee-rules-wrapper").children().not("#wcff-add-fee-rule-btn").remove();
					/* This will reset the factory area */
					$("#wcff-field-type-meta-type").trigger("change");
					/* Make sure the Fields Meta tab is active */
					$("#wcff-factory-tab-header > a:first-child").trigger("click");
				}
			} else if( _action === "single" ) {
				this.reloadHtml( $("#wcff-field-types-meta-body") );
				this.renderSingleView( _target );
			} 	
		};
	};	
	
	/* Masking object ( used to mask any container whichever being refreshed ) */
	var wcffMask = function() {
		this.top = 0;
		this.left = 0;
		this.bottom = 0;
		this.right = 0;
		
		this.target = null;
		this.mask = null;
		
		this.getPosition = function( target ) {
			this.target = target;		
			
			var position = this.target.position();
			var offset = this.target.offset();
		
			this.top = offset.top;
			this.left = offset.left;
			this.bottom = $( window ).width() - position.left - this.target.width();
			this.right = $( window ).height() - position.right - this.target.height();
		};

		this.doMask = function( target ) {
			this.target = target;
			this.mask = $('<div class="wcff-dock-loader"></div>');						
			this.target.append( this.mask );

			this.mask.css("left", "0px");
			this.mask.css("top", "0px");
			this.mask.css("right", this.target.innerWidth()+"px");
			this.mask.css("bottom", this.target.innerHeight()+"px");
			this.mask.css("width", this.target.innerWidth()+"px");
			this.mask.css("height", this.target.innerHeight()+"px");
		};

		this.doUnMask = function() {
			if( this.mask ) {
				this.mask.remove();
			}			
		};
	};
		
	$(document).ready( function() {
		$('#wcff-fields-set').sortable({
			update : function(){
				var order = 0;
				$('.wcff-meta-row').each(function(){
					$(this).find("input.wcff-field-order-index").val(order);
					order++;
				});
			}
		});		
	});
	
	mask = new wcffMask();
	
	var wcffObj = new wcff();
	wcffObj.initialize();
	
})(jQuery);