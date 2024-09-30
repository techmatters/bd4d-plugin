module.exports = function (grunt) {
	const sass = require('sass');

	// Load all grunt tasks
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);
	grunt.loadNpmTasks('@lodder/grunt-postcss');

	// Project configuration
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// use legacy color notation until sass gets updated.
		// https://stackoverflow.com/questions/66825515/getting-error-in-css-with-rgb0-0-0-15
		// https://stylelint.io/user-guide/rules/list/color-function-notation/
		stylelint: {
			src: ['wp-content/plugins/bd4d/assets/css/src/**/*.scss'],
			options: {
				customSyntax: 'postcss-scss',
				fix: true,
				configFile: '.stylelintrc.json',
			},
		},

		sass: {
			theme: {
				options: {
					implementation: sass,
					imagePath: 'wp-content/plugins/bd4d/assets/images',
					outputStyle: 'expanded',
					sourceMap: true,
				},
				files: [
					{
						expand: true,
						cwd: 'wp-content/plugins/bd4d/assets/css/src',
						src: ['*.scss', '!_*.scss'],
						dest: 'wp-content/plugins/bd4d/assets/css',
						ext: '.src.css',
					},
				],
			},
		},

		/*
		 * Runs postcss plugins
		 */
		postcss: {
			/* Runs postcss + autoprefixer on the minified CSS. */
			theme: {
				options: {
					map: false,
					processors: [require('autoprefixer')()],
				},
				files: [
					{
						expand: true,
						cwd: 'wp-content/plugins/bd4d/assets/css',
						src: ['*.src.css'],
						dest: 'wp-content/plugins/bd4d/assets/css',
						ext: '.src.css',
					},
				],
			},
		},

		cssmin: {
			theme: {
				files: [
					{
						expand: true,
						cwd: 'wp-content/plugins/bd4d/assets/css',
						src: ['*.src.css'],
						dest: 'wp-content/plugins/bd4d/assets/css',
						ext: '.min.css',
					},
				],
			},
		},

		concat: {
			options: {
				stripBanners: true,
				sourceMap: true,
			},
			main: {
				src: ['wp-content/plugins/bd4d/assets/js/src/main.js'],
				dest: 'wp-content/plugins/bd4d/assets/js/main.src.js',
			},
		},

		uglify: {
			all: {
				files: {
					'wp-content/plugins/bd4d/assets/js/main.min.js': [
						'wp-content/plugins/bd4d/assets/js/main.src.js',
					],
				},
				options: {
					sourceMap: false,
				},
			},
		},

		eslint: {
			src: ['wp-content/plugins/bd4d/assets/js/src/**/*.js'],
			options: {
				fix: true,
			},
		},

		watch: {
			php: {
				files: ['wp-content/**/*.php', '!vendor/**', '!node_modules/**'],
				tasks: ['phplint', 'phpcbf'],
			},

			css: {
				files: ['wp-content/**/assets/css/src/**/*.scss'],
				tasks: ['css'],
				options: {
					debounceDelay: 500,
				},
			},

			scripts: {
				files: ['wp-content/**/assets/js/src/**/*.js'],
				tasks: ['js'],
				options: {
					debounceDelay: 500,
				},
			},
		},

		phplint: {
			phpArgs: {
				'-lf': null,
			},
			files: ['wp-content/**/*.php'],
		},

		git_modified_files: {
			options: {
				diffFiltered: 'ACMRTUXB', // Optional: default is 'AMC',
				regexp: /\.php$/, // Optional: default is /.*/
			},
		},

		phpcs: {
			application: {
				src: '<%= gmf.filtered %>',
			},
			options: {
				bin: 'vendor/bin/phpcs',
			},
		},
		phpcbf: {
			options: {
				bin: 'vendor/bin/phpcbf',
				noPatch: false,
			},
			files: {
				src: ['wp-content/**/*.php'],
			},
		},
	});

	// Set a default, so if phpcs is run directly it scans everything
	grunt.config.set('gmf.filtered', [
		'**/*.php',
		'!vendor/**',
		'!node_modules/**',
	]);
	grunt.registerTask('precommit', ['git_modified_files', 'maybe-phpcs']);
	grunt.registerTask(
		'maybe-phpcs',
		'Only run phpcs if git_modified_files has found changes.',
		function () {
			// Check all, because there's no default set for all and we can see if we have files
			var allModified = grunt.config.get('gmf.all');
			var matches = allModified.filter(function (str) {
				return -1 !== str.search(/\.php$/);
			});

			if (!matches.length) {
				grunt.log.writeln('No php files to sniff. Skipping phpcs.');
			} else {
				grunt.task.run('phpcs');
			}
		}
	);

	// PHP Only
	grunt.registerTask('php', ['phplint', 'phpcs']);

	// JS Only
	grunt.registerTask('js', ['eslint', 'concat', 'uglify']);

	// CSS Only
	grunt.registerTask('css', ['stylelint', 'sass', 'postcss', 'cssmin']);

	// Default task.
	// CSS & JS Only
	grunt.registerTask('css-js', ['css', 'js']);

	// Default task.
	grunt.registerTask('default', ['js', 'css', 'php']);
};
