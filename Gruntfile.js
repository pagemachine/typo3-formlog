module.exports = function(grunt) {
  var path = require('path');

  require('load-grunt-config')(grunt, {
    data: {
      paths: {
        resources: {
          private: 'Resources/Private/',
          public: 'Resources/Public/',
        },
      },
    },
    configPath: [
      path.join(process.cwd(), '.grunt'),
    ],
  });
};
