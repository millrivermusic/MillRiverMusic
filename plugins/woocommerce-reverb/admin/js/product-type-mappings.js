var PTM = {Models: {}, Collections: {}, Views: {}}
jQuery(function() {

	PTM.Views.App = Backbone.View.extend({
		el: "body",
		initialize: function() {
			_.bindAll(this,'render');
			jQuery(".reverb_cat_select").select2({
				placeholder: "Select",
				data: vars.reverb_cats,
				allowClear: true
			})
			

			jQuery(".shipping_profile_select").select2({
				placeholder: "Use Default Shipping Profile",
				data: vars.shipping_profiles,
				allowClear: true
			})

		},
		render: function(e){
			
		},
		events: {
			'change .reverb_cat_select' : 'update_reverb_cat',
			'change .shipping_profile_select' : 'update_shipping_profile',

		},
		update_reverb_cat: function(e){
			var item = jQuery(e.currentTarget);

			jQuery.ajax({
                cache: false,
                type: 'POST',
                async: false,
                url: vars.ajax_url,
                data: {
                    action: vars.update_term_action,
                    'term_id' : jQuery(item).data('term_id'),
                    'reverb_cat_id' : jQuery(item).val()
                },
                dataType: 'json',
                error: function(msg) {
                    console.log('we have an error');
                },
                success: function(data) {
                    
                }
            });
		},
		update_shipping_profile: function(e){
			var item = jQuery(e.currentTarget);

			jQuery.ajax({
                cache: false,
                type: 'POST',
                async: false,
                url: vars.ajax_url,
                data: {
                    action: vars.update_term_sp_action,
                    'term_id' : jQuery(item).data('term_id'),
                    'shipping_profile_id' : jQuery(item).val()
                },
                dataType: 'json',
                error: function(msg) {
                    console.log('we have an error');
                },
                success: function(data) {
                    
                }
            });
		}


	});

	var App = new PTM.Views.App();
});