module.exports = {
  daterangepicker: {
    src: '<%= paths.resources.public %>/JavaScript/daterangepicker/daterangepicker.js',
    overwrite: true,
    replacements: [{
      from: "define(['moment'",
      to: "define(['TYPO3/CMS/Formlog/moment'",
    }],
  },
};
