module.exports = {
  options: {
    compress: true,
    ieCompat: false,
    // Make LESS files from components accessible
    paths: 'bower_components',
  },
  files: {
    cwd: '<%= paths.resources.private %>Less/',
    src: '*.less',
    dest: '<%= paths.resources.public %>Css/',
    ext: '.css',
    expand: true,
  },
};
