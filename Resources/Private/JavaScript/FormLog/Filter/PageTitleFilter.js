define([
  'jquery',
  '../Settings',
], function($, settings) {
  $(function() {
    var $filterForm = $('#filter-form');
    var $selectField = $('#pagetitle')
      .prop('disabled', true)
      .on('change', function() {
        $filterForm.submit();
      });

    $.post(
      settings.suggestUri,
      {
        property: 'page.title',
      },
      function(data) {
        $selectField.prop('disabled', false);

        var selectedValue = $selectField.data('value');

        data.forEach(function(value) {
          $('<option>', {
            text: value,
            selected: value === selectedValue,
          }).appendTo($selectField);
        });
      }
    );

    $('#pagetitle-filter')
      .popover({
        content: '...',
        placement: 'bottom',
      }).on('inserted.bs.popover', function() {
        $(this).next('.popover').children('.popover-content')
          .empty()
          .append($selectField);
      });
  });
});
