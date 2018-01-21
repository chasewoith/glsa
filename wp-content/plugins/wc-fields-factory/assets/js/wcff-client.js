(function($) {	
	
	var wcff_cart_handler = function() {
		/* to prevetn Ajax conflict. */
		this.ajaxFlaQ = true;
		
		this.initialize = function() {
			this.registerEvent();
		};
		
		this.registerEvent = function() {
			/* Double clikc handler for vcart field - which will show the editor window for that field */
			$(document).on("dblclick", "li.wcff_cart_editor_field", this, function(e) {	
				if ($("div.wccpf-cart-edit-wrapper").length > 0) {
					/* Do nothing since already one field is in edit mode */
					return;
				}
				var target = $(this);	
				target.closest("ul.wccpf-is-editable-yes").removeClass("wccpf-is-editable-yes");
				if(  !target.find( "input, select, textarea, label" ).length != 0 && target.is( ".wcff_cart_editor_field" ) ){					
					e.data.getFieldForEdit(target);
				}				
			});					
			/* Click event hanlder cart field Update button */
			$(document).on( "click", ".wccpf-update-cart-field-btn", this, function(e) {
				e.data.updateField( $( this ) );
				e.preventDefault();
			});	
			/* Click event hanlder for Cart Editor close button */
			$(document).on("click", "#wccpf-cart-editor-close-btn", function(e) {
				var editor = $(this).parent();
				editor.closest("ul.wccpf-cart-editor-ul").addClass("wccpf-is-editable-yes");
				editor.prev().show();
				editor.remove();
				e.preventDefault();
			});
			/* Key down event handler - for ESC key 
			 * If pressed the editor window will be closed */
			$(window).on("keydown", function(e) {
				var keyCode = (event.keyCode ? event.keyCode : event.which);   
				var editor = $("div.wccpf-cart-edit-wrapper");
				if (keyCode === 27 && editor.length > 0) {
					editor.closest("ul.wccpf-cart-editor-ul").addClass("wccpf-is-editable-yes");
					editor.prev().show();
					editor.remove();
				}
			});
		};
		
		this.getFieldForEdit = function(_target) {						
			/* Retrieve the value (for color picker it is different, if store admin chosen to display as color itself) */
			var fieldValue = (_target.find(".wcff-color-picker-color-show").length != 0) ? _target.find(".wcff-color-picker-color-show").css("background-color") : $.trim(_target.find("p").text());			
			var payload = { 
				product_id: _target.attr("data-product_id"), 
				product_cart_id: _target.attr("data-item_key"), 
				data: { 
					value: fieldValue,
					field: _target.attr("data-field"),
					name: _target.attr("data-field_name")					 
				} 
			};
			this.prepareRequest("wcff_render_field_on_cart_edit", "GET", payload);
			this.dock("inflate_field_for_edit", _target);
		};
		
		this.updateField = function(_btn) {
			var payload,
			fvalue = null,
			validator = new wcffValidator(),
			field_key = _btn.closest( "div.wccpf-cart-edit-wrapper" ).attr( "data-field" ),
			field_name = _btn.closest( "div.wccpf-cart-edit-wrapper" ).attr( "data-field_name" ),
			field_type = _btn.closest( "div.wccpf-cart-edit-wrapper" ).attr( "data-field_type" ),
			productId = _btn.closest( "div.wccpf-cart-edit-wrapper" ).attr( "data-product_id" ),
			cartItemKey = _btn.closest( "div.wccpf-cart-edit-wrapper" ).attr( "data-item_key" );		
			
			if (field_type === "radio") {
				validator.doValidate( _btn.closest( "div.wccpf-cart-edit-wrapper" ).find( "input" ) );				
				fvalue = _btn.closest( "div.wccpf-cart-edit-wrapper" ).find( "input:checked" ).val();								
			} else if (field_type === "checkbox") {
				validator.doValidate( _btn.closest( "div.wccpf-cart-edit-wrapper" ).find( "input" ) );
				fvalue = _btn.closest( "div.wccpf-cart-edit-wrapper" ).find("input:checked").map(function() {
				    return this.value;
				}).get();
			} else {				
				validator.doValidate( _btn.closest( "div.wccpf-cart-edit-wrapper" ).find( ".wccpf-field" ) );
				fvalue = _btn.closest( "div.wccpf-cart-edit-wrapper" ).find( ".wccpf-field" ).val();
			}			
			
			if (validator.isValid) {
				/* Initiate the ajax Request */
				payload = { 
					product_id : productId, 
					cart_item_key : cartItemKey,
					data : { 
						field: field_key, 
						name: field_name, 
						value: fvalue, 
						field_type : field_type
					}					
				}
				this.prepareRequest( "wcff_update_cart_field_data", "PUT", payload );
				this.dock( "update_cart_field_data", _btn );
			}		
		};
		
		/* Request object for all the wcff cart related Ajax operation */
		this.prepareRequest = function(_request, _method, _data) {
			this.request = {
				request 	: _method,
				context 	: _request,
				post 		: "",
				post_type 	: "wccpf",
				payload 	: _data,
			};
		};
		
		/* Ajax response wrapper object */
		this.prepareResponse = function(_status, _msg, _data) {
			this.response = {
				status : _status,
				message : _msg,
				payload : _data
			};
		};
		
		this.dock = function(_action, _target, is_file) {		
			var me = this;
			/* see the ajax handler is free */
			if( !this.ajaxFlaQ ) {
				return;
			}		
			
			$.ajax({  
				type       : "POST",  
				data       : { action : "wcff_ajax", wcff_param : JSON.stringify( this.request ) },  
				dataType   : "json",  
				url        : woocommerce_params.ajax_url,  
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
					
				}   
			});		
		};
		
		this.responseHandler = function(_action, _target) {
			
			if (!this.response.status) {
				/* Something went wrong - Do nothing */
				return;
			}			
			
			if (_action === "inflate_field_for_edit" && this.response.payload) {
				var wrapper = '';
				/* Get the reference of head tag, we might need to inject some script tag there
				 * incase if the data being edited is either datepicker or color picker */
				var dHeader = $("head");
				/* Find the last td of the field wrapper to add update button */
				var editFieldHtml = $(this.response.payload.html).find("td:last");
				/* Construct update button */
				var updateBtn = '<button data-color_show="'+ this.response.payload.color_showin +'" class="button wccpf-update-cart-field-btn">Update</button>';
				
				if (this.response.payload.field_type !== "file") {		
					wrapper = '<div class="wccpf-cart-edit-wrapper wccpf-cart-edit-'+ this.response.payload.field_type +'-wrapper" data-field_type="'+ this.response.payload.field_type +'" data-field="'+ _target.attr("data-field") +'" data-field_name="'+ _target.attr("data-field_name") +'" data-product_id="'+ _target.attr("data-product_id") +'" data-item_key="'+ _target.attr("data-item_key") +'">';
					wrapper += '<a href="#" id="wccpf-cart-editor-close-btn" title="Close Editor"></a>';
					wrapper += (editFieldHtml.html() + updateBtn);
					wrapper += '<div>';
					wrapper = $(wrapper);
					_target.hide();
					_target.parent().append(wrapper);
				}				
				if( this.response.payload.field_type == "email" || this.response.payload.field_type == "text" || this.response.payload.field_type == "number" || this.response.payload.field_type == "textarea" ){
					//_target.parent().find( ".wccpf-field" ).val( this.request.payload.data.value );
					wrapper.find("input").trigger( "focus" );
				} else if( this.response.payload.field_type == "colorpicker" ){
					dHeader.append( this.response.payload.script );
				} else if( this.response.payload.field_type == "datepicker" ){
					_target.parent().find( ".wccpf-field" ).val( this.request.payload.data.value );
					if( dHeader.find( "script[data-type=wpff-datepicker-script]" ).length == 0 ){
						dHeader.append( this.response.payload.script );
					}
					dHeader.append( $( this.response.payload.html )[2] );
				}
			} else if( _action == "update_cart_field_data" ){
				if( this.response.payload.status ) {
					if (this.response.payload.field_type !== "colorpicker") {							
						_target.closest( "div.wccpf-cart-edit-wrapper" ).parent().find("li.wcff_cart_editor_field").show().html( '<p>'+ decodeURI( this.response.payload.value ) +'</p>' );
					} else {
						if (_target.closest( "div.wccpf-cart-edit-wrapper" ).parent().find("li.wcff_cart_editor_field").attr("data-color-box") === "yes") {
							_target.closest( "div.wccpf-cart-edit-wrapper" ).parent().find("li.wcff_cart_editor_field").show().html( '<p><span class="wcff-color-picker-color-show" style="background: '+ decodeURI( this.response.payload.value ) + ';"></span></p>' );
						} else {
							_target.closest( "div.wccpf-cart-edit-wrapper" ).parent().find("li.wcff_cart_editor_field").show().html( '<p>'+ decodeURI( this.response.payload.value ) +'</p>' );
						}
					}					
					_target.closest( "ul.wccpf-cart-editor-ul" ).addClass("wccpf-is-editable-yes");
					_target.closest( "div.wccpf-cart-edit-wrapper" ).remove();
				} else {
					_target.prev().html( this.response.payload.message ).show();
				}
			}
		};
		
	};
	
	$(document).on( "submit", "form.cart", function() {				
		if( typeof( wccpf_opt.location ) !== "undefined" && 
				wccpf_opt.location !== "woocommerce_before_add_to_cart_button" && 
				wccpf_opt.location !== "woocommerce_after_add_to_cart_button" ) {			
			var me = $(this);			
			me.find(".wccpf_fields_table").each(function(){
				$(this).remove();	
			});		
			
			$(".wccpf_fields_table").each(function(){
				var cloned = $(this).clone( true );
				cloned.css("display", "none");
				
				/* Since selected flaq doesn't carry over by Clone method, we have to do it manually */
				if ($(this).find( ".wccpf-field " ).attr("wccpf-type") === "select") {
					cloned.find( "select.wccpf-field " ).val( $(this).find( "select.wccpf-field " ).val() );
				}
				
				me.append( cloned );	
			});
			
		}
	});
	
	var wcffCloner = function() {
		this.initialize = function() {
			$( document ).on( "change", "input[name=quantity]", function() {
				var product_count = $(this).val();
				var fields_count = parseInt( $("#wccpf_fields_clone_count").val() );
				$("#wccpf_fields_clone_count").val( product_count );
				
				if( fields_count < product_count ) {
					for( var i = fields_count + 1; i <= product_count; i++ ) {
						var cloned = $('.wccpf-fields-group:first').clone( true );
						cloned.find("script").remove();				
						cloned.find("div.sp-replacer").remove();
						cloned.find("span.wccpf-fields-group-title-index").html( i );
						cloned.find(".hasDatepicker").attr( "id", "" );
						cloned.find(".hasDatepicker").removeClass( "hasDatepicker" );						
						cloned.find(".wccpf-field").each(function(){
							var cloneable = $(this).attr('data-cloneable');
							if ($(this).attr( "wccpf-type" ) === "checkbox" || $(this).attr( "wccpf-type" ) === "radio") {
								cloneable = $(this).closest("ul").attr('data-cloneable');
							}
							/* Check if the field is allowed to clone */
							if (typeof cloneable !== typeof undefined && cloneable !== false) {
								var name_attr = $(this).attr("name");					
								if( name_attr.indexOf("[]") != -1 ) {
									var temp_name = name_attr.substring( 0, name_attr.lastIndexOf("_") );							
									name_attr = temp_name + "_" + i + "[]";						
								} else {
									name_attr = name_attr.slice( 0, -1 ) + i;
								}
								$(this).attr( "name", name_attr );
							} else {
								/* Otherwise remove from cloned */								
								$(this).closest("table.wccpf_fields_table").remove();																
							}										 				
						});
						/* Check for the label field - since label is using different class */
						cloned.find(".wcff-label").each(function() {
							var cloneable = $(this).attr('data-cloneable');							
							if (typeof cloneable === typeof undefined || cloneable === false) {
								$(this).remove();
							}
						});
						/* Append the cloned fields to the DOM */
						$("#wccpf-fields-container").append( cloned );		
						/* Trigger the color picker init function */
						setTimeout( function(){ if( typeof( wccpf_init_color_pickers ) == 'function' ) { wccpf_init_color_pickers(); } }, 500 );
					}					
				} else {					
					$("div.wccpf-fields-group:eq("+ ( product_count - 1 ) +")").nextAll().remove();
				}
				
				if( $(this).val() == 1 ) {
		            $(".wccpf-fields-group-title-index").hide();
		        } else {
		            $(".wccpf-fields-group-title-index").show();
		        }
				
			});			
			/* Trigger to change event - fix for min product quantity */
			setTimeout( function(){ $( "input[name=quantity]" ).trigger("change"); }, 300 );
		};
	};
	
	var wcffValidator = function() {		
		this.isValid = true;		
		this.initialize = function(){						
			$( document ).on( "submit", "form.cart", this, function(e) {
				var me = e.data; 
				e.data.isValid = true;				
				$( ".wccpf-field" ).each(function() {
					if ($(this).attr("wccpf-mandatory") === "yes") {
						me.doValidate( $(this) );
					}					
				});					
				return e.data.isValid;
			});
			if( wccpf_opt.validation_type === "blur" ) {
				$( document ).on( "blur", ".wccpf-field", this, function(e) {	
					if ($(this).attr("wccpf-mandatory") === "yes") {
						e.data.doValidate( $(this) );
					}
				});
			}
		};
		
		this.doValidate = function( field ) {			
			if( field.attr("wccpf-type") !== "radio" && field.attr("wccpf-type") !== "checkbox" && field.attr("wccpf-type") !== "file" ) {
				if(field.attr("wccpf-type") !== "select") {
					if( this.doPatterns( field.attr("wccpf-pattern"), field.val() ) ) {						
						field.next().hide();
					} else {						
						this.isValid = false;
						field.next().show();
					}
				} else {
					if (field.val() !== "" && field.val() !== "wccpf_none") {
						field.next().hide();
					} else {
						this.isValid = false;
						field.next().show();
					}
				}							
			} else if( field.attr("wccpf-type") === "radio" ) {				
				if( field.closest("ul").find("input[type=radio]").is(':checked') ) {
					field.closest("ul").next().hide();
				} else {
					field.closest("ul").next().show();
					this.isValid = false;					
				}	 			
			} else if( field.attr("wccpf-type") === "checkbox" ) {			
				var values = field.closest("ul").find("input[type=checkbox]:checked").map(function() {
				    return this.value;
				}).get();
				if( values.length === 0 ) {
					field.closest("ul").next().show();
					this.isValid = false;
				} else {						
					field.closest("ul").next().hide();
				}			
			} else if( field.attr("wccpf-type") === "file" ) {				
				if( field.val() == "" ) {
					field.next().show();
					this.isValid = false;
				} else {
					field.next().hide();
				}									
			}
		}
		
		this.doPatterns = function( patt, val ){
			var pattern = {
				mandatory	: /\S/, 
				number		: /^-?\d+\.?\d*$/,
				email		: /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i,	      	
			};			    
		    return pattern[ patt ].test(val);	
		};
		
	};
	
	$(document).ready(function() {		

		$(document).on( "change", ".wccpf-field", function( e ) {
			var target = $( this ),
				prevExt = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];

			if(target.is( "input[type=file]" ) && target.attr("data-preview") === "yes") {
				if ( $.inArray( target.val().split('.').pop().toLowerCase(), prevExt ) !== -1 ) {
			        if( !target.next().is( ".wcff_image_prev_shop_continer" ) ) {
			        	   	target.after( '<div class="wcff_image_prev_shop_continer" style="width:'+ target.attr("data-preview-width") +'"></div>' );
			        }		          
		        	    var html = "";
		        	    for( var i = 0; i < target[0].files.length; i++ ) {
		        		   html += '<img class="wcff-prev-shop-image" src="'+ URL.createObjectURL( target[0].files[i] ) +'">';
		        		   target[0].files[i].name = target[0].files[i].name.replace(/'|$|,/g, '');
		        		   target[0].files[i].name = target[0].files[i].name.replace('$', '');
		        	    }
		        	    target.next( ".wcff_image_prev_shop_continer" ).html( html );			           
			    }
			}
		});		
		
		if( typeof wccpf_opt != "undefined" ){
			if (typeof(wccpf_opt.cloning) !== "undefined" && wccpf_opt.cloning === "yes") {
				var wcff_cloner_obj = new wcffCloner();
				wcff_cloner_obj.initialize();
			}
			if (typeof(wccpf_opt.validation) !== "undefined" && wccpf_opt.validation === "yes") {			
				var wcff_validator_obj = new wcffValidator();
				wcff_validator_obj.initialize();
			}
			if (typeof(wccpf_opt.editable) !== "undefined" && wccpf_opt.editable === "yes") {
				var editor_obj = new wcff_cart_handler();
				editor_obj.initialize();
			}
		}
		
	});
	
})(jQuery);