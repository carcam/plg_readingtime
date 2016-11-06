var gulp = require('gulp');

var config = require('../../../gulp-config.json');

// Dependencies
//var minifyCSS   = require('gulp-minify-css');
var rename      = require('gulp-rename');
var rm          = require('gulp-rimraf');
var uglify      = require('gulp-uglify');
var zip         = require('gulp-zip');

var baseTask  = 'plugins.content.readingtime';
var extPath   = './readingtime';
var mediaPath = extPath + '/media';

// Compile scripts
gulp.task('scripts:' + baseTask, function () {
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

// Watch
gulp.task('watch:' + baseTask,
	[
		'watch:plugins.content.readingtime:scripts'
	],
	function() {
});
