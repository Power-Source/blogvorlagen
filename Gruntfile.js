module.exports = function(grunt) {
    require('load-grunt-tasks')(grunt);
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        checktextdomain: {
            options:{
                report_missing: false,
                text_domain: 'blog_templates',
                keywords: [
                    '__:1,2d',
                    '_e:1,2d',
                    '_x:1,2c,3d',
                    'esc_html__:1,2d',
                    'esc_html_e:1,2d',
                    'esc_html_x:1,2c,3d',
                    'esc_attr__:1,2d',
                    'esc_attr_e:1,2d',
                    'esc_attr_x:1,2c,3d',
                    '_ex:1,2c,3d',
                    '_n:1,2,4d',
                    '_nx:1,2,4c,5d',
                    '_n_noop:1,2,3d',
                    '_nx_noop:1,2,3c,4d'
                ]
            },
            files: {
                src:  [
                    '**/*.php', // Include all files
                    '!node_modules/**', // Exclude node_modules/
                    '!blogtemplatesfiles/externals/**',
                    '!tests/**', // Exclude tests/
                    '!admin/assets/shared-ui/**', // Exclude WPMU DEV Shared UI
                    '!externals/**'
                ],
                expand: true
            }
        },

        copy: {
            main: {
                src:  [
                    '**',
                    '!npm-debug.log',
                    '!node_modules/**',
                    '!build/**',
                    '!bin/**',
                    '!.git/**',
                    '!Gruntfile.js',
                    '!package.json',
                    '!.gitignore',
                    '!.gitmodules',
                    '!sourceMap.map',
                    '!phpunit.xml.dist',
                    '!travis.yml',
                    '!tests/**',
                    '!**/Gruntfile.js',
                    '!**/package.json',
                    '!**/README.md',
                    '!lite-vs-pro.txt',
                    '!composer.json',
                    '!vendor/**',
                    '!tmp/**',
                    '!phpunit.xml',
                    '!**/*~'
                ],
                dest: 'build/<%= pkg.name %>/'
            }
        },

        // Generate POT files.
        makepot: {
            options: {
                type: 'wp-plugin',
                domainPath: 'lang',
                potHeaders: {
                    'report-msgid-bugs-to': 'https://wpmudev.org',
                    'language-team': 'LANGUAGE <EMAIL@ADDRESS>'
                }
            },
            dist: {
                options: {
                    potFilename: 'blog_templates.pot',
                    exclude: [
                        'tests/.*',
                        'node_modules/.*',
                        'externals/*'
                    ]
                }
            }
        },

        clean: {
            main: ['build/*']
        },

        compress: {
            main: {
                options: {
                    mode: 'zip',
                    archive: './build/<%= pkg.name %>-<%= pkg.version %>.zip'
                },
                expand: true,
                cwd: 'build/<%= pkg.name %>/',
                src: ['**/*'],
                dest: '<%= pkg.name %>/'
            }
        },

    });

    grunt.registerTask('version-compare', function() {
        var pkg = grunt.file.readJSON('package.json');
        if (!pkg.main) {
            grunt.fail.fatal("main field is not defined in package.json. Please, add the plugin main file on that field.");
        }
        if (!grunt.file.exists(pkg.main)) {
            grunt.fail.fatal("Plugin main file does not exist: " + pkg.main);
        }

        var plugin = grunt.file.read(pkg.main);
        var version = plugin.match(/^[ \t\/*#@]*Version:(.*)$/mi);
        if (!version) {
            grunt.fail.fatal("Version Plugin header not found in " + pkg.main);
        }
        if (version[1].trim() !== pkg.version) {
            grunt.fail.fatal("Plugin version does not match with package.json version. Please, fix.");
        }
    });

    grunt.registerTask('build', [
        'version-compare',
        'clean',
        'checktextdomain',
        'makepot',
        'copy',
        'compress'
    ]);
};