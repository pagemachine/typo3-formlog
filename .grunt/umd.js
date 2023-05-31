module.exports = {
  daterangepicker: {
    src: '<%= paths.resources.public %>/JavaScript/knockout-daterangepicker/daterangepicker.js',
    deps: {
      default: ['$', 'moment', 'ko'],
      amd: ['jquery', 'TYPO3/CMS/Formlog/moment', 'TYPO3/CMS/Formlog/knockout/knockout'],
    },
  },
};
