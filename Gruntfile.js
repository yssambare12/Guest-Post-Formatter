/**
 * Gruntfile for Guest Post Frontend Submitter
 *
 * @package Guest_Post_Frontend_Submitter
 */

module.exports = function(grunt) {
    'use strict';

    // Load all grunt tasks
    require('load-grunt-tasks')(grunt);

    // Project configuration
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        // Clean the build directory
        clean: {
            build: ['build/'],
            release: ['release/']
        },

        // Copy files to build directory
        copy: {
            build: {
                expand: true,
                src: [
                    '**',
                    '!node_modules/**',
                    '!build/**',
                    '!release/**',
                    '!.git/**',
                    '!.github/**',
                    '!.gitignore',
                    '!.DS_Store',
                    '!Gruntfile.js',
                    '!package.json',
                    '!package-lock.json',
                    '!composer.json',
                    '!composer.lock',
                    '!phpcs.xml',
                    '!phpunit.xml',
                    '!README.md',
                    '!CONTRIBUTING.md',
                    '!CHANGELOG.md',
                    '!.eslintrc',
                    '!.eslintignore',
                    '!.editorconfig',
                    '!src/**',
                    '!tests/**',
                    '!bin/**',
                    '!.travis.yml',
                    '!.gitlab-ci.yml',
                    '!.env',
                    '!.env.example',
                    '!.npmrc',
                    '!.nvmrc',
                    '!.babelrc',
                    '!webpack.config.js',
                    '!postcss.config.js',
                    '!tailwind.config.js'
                ],
                dest: 'build/'
            }
        },

        // Make POT file for translations
        makepot: {
            target: {
                options: {
                    domainPath: '/languages',
                    mainFile: 'guest-post-frontend-submitter.php',
                    potFilename: 'guest-post-frontend-submitter.pot',
                    potHeaders: {
                        poedit: true,
                        'x-poedit-keywordslist': true
                    },
                    type: 'wp-plugin',
                    updateTimestamp: true
                }
            }
        },

        // Create zip file for distribution
        compress: {
            main: {
                options: {
                    archive: 'guest-post-frontend-submitter-<%= pkg.version %>.zip',
                    mode: 'zip'
                },
                expand: true,
                cwd: 'build/',
                src: ['**/*'],
                dest: 'guest-post-frontend-submitter/'
            }
        }
    });

    // Register tasks
    grunt.registerTask('build', [
        'clean:build',
        'clean:release',
        'makepot',
        'copy:build',
        'compress'
    ]);

    grunt.registerTask('default', ['build']);
};
