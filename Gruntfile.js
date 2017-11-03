'use strict';

module.exports = function (grunt) {
    // load all grunt tasks
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.initConfig({
        watch: {
            // if any .less file changes in directory "html/css/" run the "less"-task.
            files: ["less/*.less"],
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
            bs_fonts: {
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
            translate_locale:{
              cwd: 'vendor/fccn/webapp-tools/translate/locale',
              src: '**/*',
              dest: 'locale',
              expand: true
            },
            translate_utils:{
              cwd: 'vendor/fccn/webapp-tools/translate',
              src: 'utils/*',
              dest: '.',
              expand: true
            }
        }
    });
    grunt.registerTask('default', ['watch']);
    grunt.registerTask('dist', ['less:dist']);
    grunt.registerTask('bootstrap',['copy:bs_fonts','copy:fa_fonts','copy:translate_locale','copy:translate_utils']);
    grunt.registerTask('initlocale',['copy:translate_locale','copy:translate_utils']);

};
