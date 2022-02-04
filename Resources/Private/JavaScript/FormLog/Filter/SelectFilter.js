define([
  'jquery',
  '../Settings',
], function($, settings) {
  $(function() {
    var $filterForm = $('#filter-form');

    $filterForm.find('.formlog-select-filter').each(function() {
      var $selectField = $(this).find('select')
        .prop('disabled', true)
        .on('change', function() {
          $filterForm.submit();
        });

        $.post(
          settings.suggestUri,
          {
            property: $selectField.data('property'),
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

      var $toggleButton = $(this).find('button');

      $toggleButton
        .popover({
          container: $toggleButton.parent(),
          content: '...',
          placement: 'bottom',
        }).on('inserted.bs.popover', function() {
          $(this).siblings('.popover').children('.popover-content, .popover-body')
            .empty()
            .append($selectField);
        });
    });
  });
});
