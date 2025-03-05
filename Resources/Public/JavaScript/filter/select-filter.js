import $ from 'jquery';
import { Popover } from 'bootstrap';
import settings from '../settings.js';

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

    var toggleButton = this.querySelectorAll('button')[0];
    toggleButton.addEventListener('inserted.bs.popover', function() {
      $(this).siblings('.popover').children('.popover-content, .popover-body')
        .empty()
        .append($selectField);
    });

    new Popover(toggleButton, {
      container: toggleButton.parentElement,
      content: '...',
      placement: 'bottom',
    });
  });
});
