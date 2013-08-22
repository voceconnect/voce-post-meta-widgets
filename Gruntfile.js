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
                    "js/*.js",
                    "!js/*.min.js",
                ],
            }
        }
    },
    cssmin: {
       minify: {
       expand: true,
       cwd: 'css/',
       src: [
        '*.css', 
        '!*.min.css'
       ],
       dest: 'css/',
       ext: '.min.css'
      }
    }
  });

  // Load npm plugins to provide necessary tasks.
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-cssmin');

  // Default tasks to be run.
  grunt.registerTask('default', [
    'jshint', 'uglify', 'cssmin'
  ]);

};
