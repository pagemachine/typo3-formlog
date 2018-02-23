module.exports = {
  bower: {
    files: 'bower.json',
    tasks: [
      'clean:css',
      'clean:js',
      'bower',
      'umd',
      'uglify',
    ],
  },
  less: {
    files: '<%= paths.resources.private %>Less/**/*.less',
    tasks: 'less',
  },
  js: {
    files: '<%= paths.resources.private %>JavaScript/**/*.js',
    tasks: 'uglify',
  },
};
