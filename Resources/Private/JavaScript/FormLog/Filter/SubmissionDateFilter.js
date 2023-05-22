define([
  'jquery',
  'TYPO3/CMS/Formlog/moment',
  '../Settings',
  'TYPO3/CMS/Formlog/knockout-daterangepicker/daterangepicker'
], function($, moment, settings) {
  $(function() {
    var $filterForm = $("#filter-form");
    var $dateFilterButton = $('#submissiondate-filter');
    var $startDateField = $($dateFilterButton.data('startDateField'));
    var $endDateField = $($dateFilterButton.data('endDateField'));
    var w3cDateFormat = 'YYYY-MM-DDTHH:mm:ssZ'; // See PHP DateTime::W3C
    var translations = $dateFilterButton.data('translations');
    var ranges = {
      last30days: [moment().subtract(29, 'days'), moment()],
      lastYear: [moment().subtract(1, 'year').add(1,'day'), moment()],
      other: 'custom',
    };
    var localizedRanges = {};

    for (var range in ranges) {
      var localizedRange = range;

      if (range in translations.ranges) {
        localizedRange = translations.ranges[range];
      }

      localizedRanges[localizedRange] = ranges[range];
    }

    moment.locale(settings.language);

    var originalTitleFunction = $.fn.daterangepicker.Period.title;
    $.fn.daterangepicker.Period.title = function(period) {
      if (period in translations.periods) {
        return translations.periods[period];
      }

      return originalTitleFunction(period);
    };

    $dateFilterButton.daterangepicker({
      timeZone: settings.timeZone,
      locale: translations.labels,
      firstDayOfWeek: moment.localeData().firstDayOfWeek(),
      startDate: $startDateField.val(),
      endDate: $endDateField.val(),
      ranges: localizedRanges,
      callback: function(startDate, endDate, period) {
        $startDateField.val(startDate.format(w3cDateFormat));
        $endDateField.val(endDate.format(w3cDateFormat));

        $filterForm.submit();
      }
    });
  });
});
