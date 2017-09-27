'use strict';

module.exports = function (grunt) {
    // load all grunt tasks
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.initConfig({
        watch: {
            // if any .less file changes in directory "html/css/" run the "less"-task.
            files: ["less/app.less","less/bootstrap/*.less"],
            tasks: ["less:dev"]
        },
        // "less"-task configuration
        less: {
            dev: {
                options: {
                    paths: ["less/"]
                },
                files: {
                    "html/css/app.css": "less/app.less"
                }
            },
            dist:{
                options: {
                    paths: ["less/"],
                    cleancss: true
                },
                files: {
                    "html/css/app.css": "less/app.less"
                }
            }
        },
        copy: {
            js: {
                cwd: 'vendor/components/bootstrap/js',
                src: 'bootstrap.min.js',
                dest: 'html/js',
                expand: true
            },
            fonts: {
                cwd: 'vendor/components/bootstrap/fonts',
                src: '**/*',
                dest: 'html/assets/fonts',
                expand: true
            },
            fa_fonts: {
                cwd: 'vendor/components/font-awesome/fonts',
                src: '**/*',
                dest: 'html/assets/fonts',
                expand: true
            },
            jquery: {
                cwd: 'vendor/components/jquery',
                src: 'jquery.min.js',
                dest: 'html/js',
                expand: true
            },
        }
    });
    grunt.registerTask('default', ['watch']);
    grunt.registerTask('dist', ['less:dist']);
    grunt.registerTask('bootstrap',['copy:js','copy:fonts','copy:jquery','copy:fa_fonts'])
};
