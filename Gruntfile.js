/*
 * Afterburner
 * https://github.com/voceplatforms/Afterburner
 * Copyright (c) 2013
 * Licensed under the MIT license.
 */

'use strict';

module.exports = function(grunt) {
  grunt.util._.mixin(require('./src/helpers/mixins.js').init(grunt));

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    // Lint JavaScript
    jshint: {
      all: ['Gruntfile.js', 'src/helpers/*.js'],
      options: {
        jshintrc: '.jshintrc'
      }
    },

    // Build HTML from templates and data
    assemble: {
      options: {
        // Change stylesheet to "assemble" or "bootstrap"
        stylesheet: 'assemble',
        flatten: true,
        partials: ['src/includes/*.hbs'],
        helpers: ['src/helpers/helper-*.js'],
        layout: 'src/layouts/default.hbs',
        data: ['src/data/*.{json,yml}', 'package.json']
      },
      pages: {
        src: 'src/*.hbs',
        dest: 'build/'
      }
    },

    compass: {
        options: {
            config: "src/config.rb",
            basePath: "",
            force: true
        },
        production: {
            options: {
                environment: "production"
            }
        },
        staging: {
            options: {
                environment: "development"
            }
        }
	},

    // Prettify test HTML pages from Assemble task.
    prettify: {
      options: {
        prettifyrc: '.prettifyrc'
      },
      all: {
        expand: true,
        cwd: '<%= assemble.pages.src %>/',
        src: ['*.html'],
        dest: '<%= assemble.pages.src %>/',
        ext: '.html'
      }
    },

    uglify: {
        all: {
            options: {
                preserveComments: "some"
            },
            files: {
                "build/js/all.min.js": [
                    "src/js/*.js",
                ],
            }
        }
    },

    imagemin: {
      main: {
        files: [
          {
            expand: true,
            cwd: "src/img/",
            src: "**/*.{png,jpg}",
            dest: "build/img"
          }
        ]
      }
    },

    // Before generating any new files,
    // remove any previously-created files.
    clean: {
      example: ['build/**']
    },

    watch: {
      scripts: {
        files: ['src/js/**'],
        tasks: ['jshint', 'uglify']
      },
      content: {
        files: ['src/**/*.hbs', 'src/**/*.md'],
        tasks: ['assemble', 'prettify']
      },
      css: {
        files: ['src/sass/**'],
        tasks: ['compass:staging']
      },
      images: {
        files: ['src/img/**'],
        tasks: ['imagemin']
      }
    },

    build: {
      production: ['assemble', 'prettify', 'uglify', 'imagemin', 'compass:production'],
      uat: ['assemble', 'prettify', 'uglify', 'imagemin', 'compass:staging'],
      staging: ['clean', 'assemble', 'prettify', 'uglify', 'imagemin', 'compass:staging'],
      development: ['clean', 'assemble', 'prettify', 'uglify', 'imagemin', 'compass:staging']
    }
  });

  // Load npm plugins to provide necessary tasks.
  grunt.loadNpmTasks('assemble');
	grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-prettify');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-imagemin');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-build');

  // Default tasks to be run.
  grunt.registerTask('default', [
    'build:development'
  ]);

};
