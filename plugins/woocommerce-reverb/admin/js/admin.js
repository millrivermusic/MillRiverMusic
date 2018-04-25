var WooCommerce_Reverb_Admin = {
  Models: {},
  Collections: {},
  Views: {}
}
jQuery(function($) {

WooCommerce_Reverb_Admin.Views.App = Backbone.View.extend({
  el: "body",
  initialize: function() {
    _.bindAll(this, 'render');
    $("input.switch").bootstrapSwitch();

    // Reverb Product Data Options
    $('input#_reverb_sync').change(function() {
      if ($(this).is(':checked')) {
        $('p.reverb_fields').show();
      } else {
        $('p.reverb_fields').hide();
      }
    }).change();

    $('#the-list').on('click', '.editinline', function() {
      var post_id = $(this).closest('tr').attr('id');
      post_id = post_id.replace('post-', '');

      var $wc_reverb_inline_data = $('#woocommerce_reverb_inline_' + post_id);
      var reverb_sync = $wc_reverb_inline_data.find('.reverb_sync').text(),
        reverb_make = $wc_reverb_inline_data.find('.reverb_make').text(),
        reverb_model = $wc_reverb_inline_data.find('.reverb_model ').text(),
        reverb_condition = $wc_reverb_inline_data.find('.reverb_condition').text(),
        reverb_upc = $wc_reverb_inline_data.find('.reverb_upc').text(),
        reverb_finish = $wc_reverb_inline_data.find('.reverb_finish').text(),
        reverb_year = $wc_reverb_inline_data.find('.reverb_year').text(),
        reverb_seller_cost = $wc_reverb_inline_data.find('.reverb_seller_cost').text()
      reverb_accept_offers = $wc_reverb_inline_data.find('.reverb_accept_offers').text()
      reverb_sync_woo_title = $wc_reverb_inline_data.find('.reverb_sync_woo_title').text()

      if ('yes' === reverb_sync) {
        $('input[name="_reverb_sync"]', '.inline-edit-row').attr('checked', 'checked');
      } else {
        $('input[name="_reverb_sync"]', '.inline-edit-row').removeAttr('checked');
      }
      if ('yes' === reverb_sync) {
        $('.reverb_fields', '.inline-edit-row').show().removeAttr('style');
        $('input[name="_reverb_sync"]', '.inline-edit-row').attr('checked', 'checked');
      } else {
        $('.reverb_fields', '.inline-edit-row').hide();
        $('input[name="_reverb_sync"]', '.inline-edit-row').removeAttr('checked');
      }
      $('input[name="_reverb_make"]', '.inline-edit-row').val(reverb_make);
      $('input[name="_reverb_model"]', '.inline-edit-row').val(reverb_model);
      $('select[name="_reverb_condition"] option:selected', '.inline-edit-row').attr('selected', false).change();
      $('select[name="_reverb_condition"] option[value="' + reverb_condition + '"]').attr('selected', 'selected').change();
      $('input[name="_reverb_upc"]', '.inline-edit-row').val(reverb_upc);
      $('input[name="_reverb_finish"]', '.inline-edit-row').val(reverb_finish);
      $('input[name="_reverb_year"]', '.inline-edit-row').val(reverb_year);
      $('input[name="_reverb_seller_cost"]', '.inline-edit-row').val(reverb_seller_cost);
      if ('yes' === reverb_accept_offers) {
        $('input[name="_reverb_accept_offers"]', '.inline-edit-row').attr('checked', 'checked');
      } else {
        $('input[name="_reverb_accept_offers"]', '.inline-edit-row').removeAttr('checked');
      }
      if ('yes' === reverb_sync_woo_title) {
        $('input[name="_reverb_sync_woo_title"]', '.inline-edit-row').attr('checked', 'checked');
      } else {
        $('input[name="_reverb_sync_woo_title"]', '.inline-edit-row').removeAttr('checked');
      }
    })

    $('#the-list').on('change', '.inline-edit-row input[name="_reverb_sync"]', function() {

      if ($(this).is(':checked')) {
        $('.reverb_fields', '.inline-edit-row').show().removeAttr('style');
      } else {
        $('.reverb_fields', '.inline-edit-row').hide();
      }

    });


  },
  render: function(e) {
    console.log('backbone view rendered');
  },
  events: {
    'click a.start-over': 'start_over',

  },
  start_over: function(e) {}


});

var App = new WooCommerce_Reverb_Admin.Views.App();
});