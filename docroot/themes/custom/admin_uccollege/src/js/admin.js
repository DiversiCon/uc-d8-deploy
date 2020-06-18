(function ($) {
  Drupal.behaviors.admin_uccollege= {
    attach: function (context, settings) {

      // create a toggle to make it easier for authors to focus on editing content
      $('#authoring-details-toggle', context).once('admin_uccollege').click(function () {
        $('.layout-region-node-secondary').slideToggle();
        $(this).toggleClass('active');
      });

      // if the toggle element doesn't exist, make sure to show the hidden info
      if (!$('#authoring-details-toggle').length > 0) {
        $('.layout-region-node-secondary').show();
      }
    }
  };
})(jQuery);
