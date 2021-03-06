/**
 * Grunt copy task config
 * @package Qazana
 */
const getBuildFiles = [
	'**',
	'!node_modules/**',
	'!docs/**',
	'!build/**',
	'!bin/**',
	'!.git/**',
	'!tests/**',
	'!.github/**',
	'!.travis.yml',
	'!.jscsrc',
	'!.jshintignore',
	'!.jshintrc',
	'!ruleset.xml',
	'!README.md',
	'!phpunit.xml',
	'!vendor/**',
	'!Gruntfile.js',
	'!package.json',
	'!package-lock.json',
	'!npm-debug.log',
	'!composer.json',
	'!composer.lock',
	'!.gitignore',
	'!.gitmodules',
	'!yarn.lock',

	'!assets/dev/**',
	'!assets/**/*.map',
	'!*~'
];
/**
 * @type {{main: {src: string[], expand: boolean, dest: string}, secondary: {src: string[], expand: boolean, dest: string}}}
 */
const copy = {
	main: {
		src: getBuildFiles,
		expand: true,
		dest: 'build/'
	},
	secondary: {
		src: getBuildFiles,
		expand: true,
		dest: '/tmp/qazana-builds/<%= pkg.version %>/'
	}
};

module.exports = copy;
