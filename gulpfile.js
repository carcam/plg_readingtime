// Replace plugin_name with name of the plugin
// Replace plugin_family with family of the plugin
var gulp = require("gulp");

var config = require("./gulp/config");
var rename = require("gulp-rename");
var run = require('gulp-run');
var remove = require('del');
var zip = require("gulp-zip");

var browserSync = require("browser-sync").create();
var reload = browserSync.reload;

var rsync = require("gulp-rsync");
const { del } = require("object-path");
var fs = require("fs");
//var cheerio = require('gulp-cheerio');
var cheerio = require('cheerio');

var joomlaFiles = [
    "src/**/*",
];

gulp.task("copyToTemp:joomla", function () {
  return gulp.src(joomlaFiles).pipe(gulp.dest("temp/plugins/content/"));
});

gulp.task("copy",
	gulp.series('copyToTemp:joomla')
);

gulp.task("zip", function () {
  var xml = fs.readFileSync('./src/readingtime.xml');

  var $ = cheerio.load(xml, { xmlMode: true });
  var version   = $('version').text();

  return gulp
    .src(["./src/**"])
    .pipe(zip("plg_content_readingtime_" + version +".zip"))
    .pipe(gulp.dest("release"));
});

gulp.task("clean", function () {
  return remove(['temp/**']);
});


gulp.task('deploy-plugin',function(){
	return gulp.src(backendFiles)
		.pipe(rsync({
			root: 'temp/plugins/content/plg_content_readingtime',
			hostname: config.sshData.ssh1.hostname,
			destination: config.sshData.ssh1.destination + 'plugins/content/readingtime/',
			archive: true,
			compress: true,
			progress: true,
			recursive: true
		}));
});

gulp.task('create-temp',
  gulp.series("copy")
);

gulp.task('deploy-joomla',
	gulp.series('deploy-plugin')
);

gulp.task('deploy-to-rsync', gulp.series('create-temp', 'deploy-joomla'));


gulp.task('watch-joomla', function() {
	gulp.watch(joomlaFiles, gulp.series('copyToTemp:joomla', 'deploy-joomla'));
});

gulp.task('watch', gulp.series('watch-joomla'));

gulp.task(
  "default",
  gulp.series("copy", "zip", "clean")
);
