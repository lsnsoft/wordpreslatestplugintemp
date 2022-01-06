;(function ( $, window, undefined ) {

	var init_target_rule_select2  = function( selector ) {

		$(selector).select2({

			placeholder: "Search pages / post / archives",

			ajax: {
			    url: ajaxurl,
			    dataType: 'json',
			    method: 'post',
			    delay: 250,
			    data: function (params) {
			      	return {
			        	q: params.term, // search term
				        page: params.page,
						action: 'bsf_get_posts_by_query',
						nonce: Targetrule.security,

			    	};
				},
				processResults: function (data) {
					// console.log(data);
					// console.log("inside");
		            // parse the results into the format expected by Select2.
		            // since we are using custom formatting functions we do not need to
		            // alter the remote JSON data

		            return {
		                results: data
		            };
		        },
			    cache: true
			},
			minimumInputLength: 2,
		});
	};

	var update_target_rule_input = function(wrapper) {
		var rule_input 		= wrapper.find('.bsf-target_rule-input');
		var old_value = rule_input.val();
		var new_value = [];

		wrapper.find('.bsf-target-rule-condition').each(function(i) {

			var $this 			= $(this);
			var temp_obj 		= {};
			var rule_condition 	= $this.find('select.target_rule-condition');
			var specific_page 	= $this.find('select.target_rule-specific-page');

			var rule_condition_val 	= rule_condition.val();
			var specific_page_val 	= specific_page.val();

			if ( '' != rule_condition_val ) {

				temp_obj = {
					type 	: rule_condition_val,
					specific: specific_page_val
				}

				new_value.push( temp_obj );
			};
		})


		var rules_string = JSON.stringify( new_value );
		rule_input.val( rules_string );
	};

	var update_close_button = function(wrapper) {

		type 		= wrapper.closest('.bsf-target-rule-wrapper').attr('data-type');
		rules 		= wrapper.find('.bsf-target-rule-condition');
		show_close	= false;

		if ( 'display' == type ) {
			if ( rules.length > 1 ) {
				show_close = true;
			}
		}else{
			show_close = true;
		}

		rules.each(function() {
			if ( show_close ) {
				jQuery(this).find('.target_rule-condition-delete').removeClass('bsf-hidden');
			}else{
				jQuery(this).find('.target_rule-condition-delete').addClass('bsf-hidden');
			}
		});
	};

	var update_exclusion_button = function( force_show, force_hide ) {
		var display_on = $('.bsf-target-rule-display-on-wrap');
		var exclude_on = $('.bsf-target-rule-exclude-on-wrap');

		var exclude_field_wrap = exclude_on.closest('tr');
		var add_exclude_block  = display_on.find('.target_rule-add-exclusion-rule');
		var exclude_conditions = exclude_on.find('.bsf-target-rule-condition');

		if ( true == force_hide ) {
			exclude_field_wrap.addClass( 'bsf-hidden' );
			add_exclude_block.removeClass( 'bsf-hidden' );
		}else if( true == force_show ){
			exclude_field_wrap.removeClass( 'bsf-hidden' );
			add_exclude_block.addClass( 'bsf-hidden' );
		}else{

			if ( 1 == exclude_conditions.length && '' == $(exclude_conditions[0]).find('select.target_rule-condition').val() ) {
				exclude_field_wrap.addClass( 'bsf-hidden' );
				add_exclude_block.removeClass( 'bsf-hidden' );
			}else{
				exclude_field_wrap.removeClass( 'bsf-hidden' );
				add_exclude_block.addClass( 'bsf-hidden' );
			}
		}

	};

	$(document).ready(function($) {

		jQuery( '.bsf-target-rule-condition' ).each( function() {
			var $this 			= $( this ),
				condition 		= $this.find('select.target_rule-condition'),
				condition_val 	= condition.val(),
				specific_page 	= $this.next( '.target_rule-specific-page-wrap' );

			if( 'specifics' == condition_val ) {
				specific_page.slideDown( 300 );
			}
		} );

		jQuery('.bsf-target-rule-selector-wrapper select.target-rule-select2').each(function(index, el) {
			init_target_rule_select2( el );
		});

		jQuery('.bsf-target-rule-selector-wrapper').each(function() {
			update_close_button( jQuery(this) );
		})

		jQuery( document ).on( 'change', '.bsf-target-rule-condition select.target_rule-condition' , function( e ) {

			var $this 		= jQuery(this),
				this_val 	= $this.val(),
				field_wrap 	= $this.closest('.bsf-target-rule-wrapper');

			if( 'specifics' == this_val ) {
				$this.closest( '.bsf-target-rule-condition' ).next( '.target_rule-specific-page-wrap' ).slideDown( 300 );
			} else {
				$this.closest( '.bsf-target-rule-condition' ).next( '.target_rule-specific-page-wrap' ).slideUp( 300 );
			}

			update_target_rule_input( field_wrap );
		} );

		jQuery( '.bsf-target-rule-selector-wrapper' ).on( 'change', '.target-rule-select2', function(e) {
			var $this 		= jQuery( this ),
				field_wrap 	= $this.closest('.bsf-target-rule-wrapper');

			update_target_rule_input( field_wrap );
		});

		jQuery( '.bsf-target-rule-selector-wrapper' ).on( 'click', '.target_rule-add-rule-wrap a', function(e) {
			e.preventDefault();
			e.stopPropagation();
			var $this 	= jQuery( this ),
				id 		= $this.attr( 'data-rule-id' ),
				new_id 	= parseInt(id) + 1,
				type 	= $this.attr( 'data-rule-type' ),
				rule_wrap = $this.closest('.bsf-target-rule-selector-wrapper').find('.target_rule-builder-wrap'),
				template  = wp.template( 'bsf-target-rule-' + type + '-condition' ),
				field_wrap 		= $this.closest('.bsf-target-rule-wrapper');

			rule_wrap.append( template( { id : new_id, type : type } ) );

			init_target_rule_select2( '.bsf-target-rule-'+type+'-on .target-rule-select2' );

			$this.attr( 'data-rule-id', new_id );

			update_close_button( field_wrap );
		});

		jQuery( '.bsf-target-rule-selector-wrapper' ).on( 'click', '.target_rule-condition-delete', function(e) {
			var $this 			= jQuery( this ),
				rule_condition 	= $this.closest('.bsf-target-rule-condition'),
				field_wrap 		= $this.closest('.bsf-target-rule-wrapper');
				cnt 			= 0,
				data_type 		= field_wrap.attr( 'data-type' ),
				optionVal 		= $this.siblings('.target_rule-condition-wrap').children('.target_rule-condition').val();

			if ( 'exclude' == data_type && field_wrap.find( '.bsf-target-rule-condition' ).length == 1 ) {

				field_wrap.find('.target_rule-condition').val('');
				field_wrap.find('.target_rule-specific-page').val('');
				field_wrap.find('.target_rule-condition').trigger('change');
				update_exclusion_button( false, true );

			} else {

				$this.parent('.bsf-target-rule-condition').next('.target_rule-specific-page-wrap').remove();
				rule_condition.remove();
			}

			field_wrap.find('.bsf-target-rule-condition').each(function(i) {
				var condition       = jQuery( this ),
					old_rule_id     = condition.attr('data-rule'),
					select_location = condition.find('.target_rule-condition'),
					select_specific = condition.find('.target_rule-specific-page'),
					location_name   = select_location.attr( 'name' );

				condition.attr( 'data-rule', i );

				select_location.attr( 'name', location_name.replace('['+old_rule_id+']', '['+i+']') );

				condition.removeClass('bsf-target-rule-'+old_rule_id).addClass('bsf-target-rule-'+i);

				cnt = i;
			});

			field_wrap.find('.target_rule-add-rule-wrap a').attr( 'data-rule-id', cnt )

			update_close_button( field_wrap );
			update_target_rule_input( field_wrap );
		});

		jQuery( '.bsf-target-rule-selector-wrapper' ).on( 'click', '.target_rule-add-exclusion-rule a', function(e) {
			e.preventDefault();
			e.stopPropagation();
			update_exclusion_button( true );
		});

	});

}(jQuery, window));
