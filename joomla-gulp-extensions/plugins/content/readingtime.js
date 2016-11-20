var gulp = require('gulp');

var config = require('../../../gulp-config.json');

// Dependencies
//var minifyCSS   = require('gulp-minify-css');
var rename      = require('gulp-rename');
var rm          = require('gulp-rimraf');
var uglify      = require('gulp-uglify');
var zip         = require('gulp-zip');
var runSequence	= require('run-sequence');

var baseTask  = 'plugins.content.readingtime';
var extPath   = './readingtime';
var mediaPath = extPath + '/media';

// Compile scripts
gulp.task('minimize:' + baseTask, function () {
	return gulp.src([
			mediaPath + '/js/**/*.js',
			'!' + mediaPath + '/js/**/*.min.js',
			'!' + mediaPath + '/js/**/*-min.js'
		])
		.pipe(gulp.dest(extPath + '/media/js'))
		.pipe(uglify())
		.pipe(rename(function (path) {
				path.basename += '.min';
		}))
		.pipe(gulp.dest(mediaPath + '/js'))
});

// Build Package
gulp.task('zip:' + baseTask, function () {
	return gulp.src( extPath + '/**')
				.pipe(zip('plg_readingtime.zip'))
				.pipe(gulp.dest(extPath + '/../releases'));
});

gulp.task('release:' + baseTask, function (callback) {
	runSequence(
		'minimize:' + baseTask,
		'zip:' + baseTask,
		function (error) {
			if (error) {
				console.log(error.message);
			} else {
				console.log('RELEASE FINISHED SUCCESSFULLY');
			}
			callback(error);
		});
}); 

// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:plugins.content.readingtime:scripts'
	],
	function() {
});
