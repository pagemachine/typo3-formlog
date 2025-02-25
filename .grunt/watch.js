module.exports = {
  less: {
    files: '<%= paths.resources.private %>Less/**/*.less',
    tasks: 'less',
  },
  js: {
    files: '<%= paths.resources.private %>JavaScript/**/*.js',
    tasks: 'uglify',
  },
};
