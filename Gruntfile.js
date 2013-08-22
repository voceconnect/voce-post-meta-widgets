/*
 * Voce Post Meta Widgets
 * https://github.com/voceplatforms/Afterburner
 * Copyright (c) 2013
 * Licensed under the MIT license.
 */



module.exports = function(grunt) {
  'use strict';  
  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    // Lint JavaScript
    jshint: {
      all: ['js/voce-post-meta-widgets.js'],
       options:{
        "forin": true, 
        "noarg": true, 
        "noempty": true, 
        "eqeqeq": true, 
        "bitwise": true, 
        "undef": true, 
        "unused": false, 
        "curly": true, 
        "browser": true, 
        "strict":  false
      }
    },
    uglify: {
        all: {
            options: {
                preserveComments: "some"
            },
            files: {
                "js/voce-post-meta-widgets.min.js": [
                    "js/voce-post-meta-widgets.js",
                ],
            }
        }
    }
  });

  // Load npm plugins to provide necessary tasks.
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  // Default tasks to be run.
  grunt.registerTask('default', [
    'jshint', 'uglify'
  ]);

};
