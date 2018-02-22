module.exports = {
  js: {
    files: [{
      expand: true,
      cwd: '<%= paths.resources.private %>JavaScript/',
      src: '**/*.js',
      dest: '<%= paths.resources.public %>JavaScript/',
    }],
  },
};
