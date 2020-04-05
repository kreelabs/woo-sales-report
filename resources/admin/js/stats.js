jQuery(document).ready(function ($) {
  /**
   * Confirmation for bulk delete action.
   */
  $('.wsr-table .bulkactions .action').on('click', function (event) {
    var selectBox = $(this).parent().find('select');

    if(selectBox.val() === 'delete' && !confirm('Are you sure?')) {
      event.preventDefault();
      selectBox.val(-1);
    }
  });

  $('.wsr-toggle').on('click', function () {
    $(this).parent().find('span.wsr-details').fadeToggle(100);
  })
});
