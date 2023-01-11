var gulp = require("gulp");
var zip = require("gulp-zip");
var rsync = require("gulp-rsync");
var fs = require('fs');
var tap = require('gulp-tap');
var cheerio = require('cheerio');
const debug = require('gulp-debug');

var config = global.config;
var env = global.env;
var pluginContentFiles = [
    "src/plugins/content/**/*"
]

watchFiles = global.watchFiles;

watchFiles = watchFiles.concat(pluginContentFiles);

gulp.task('deploy:plugin:content', function(){
return gulp.src(pluginContentFiles)
        .pipe(debug({title: 'Files to copy', showFiles: true}))
		.pipe(rsync({
			root: 'src/plugins/content/',
			hostname: config.sshData[env].hostname,
			destination: config.sshData[env].destination + 'plugins/content/',
			archive: true,
			compress: true,
			progress: true,
			recursive: true
		}));
});

gulp.task("zip:plugin:content", function () {
  var xml = fs.readFileSync('./src/plugins/content/readingtime.xml');

  var $ = cheerio.load(xml, { xmlMode: true });
  var version   = $('version').text();
  var dirs = fs.readdirSync('src/plugins/content/');

  return gulp
    .src(pluginContentFiles)
    .pipe(zip("plg_content_readingtime_" + version + ".zip"))
    .pipe(gulp.dest("release"));
});

gulp.task('watch:plugin:content', function() {
	gulp.watch(watchFiles, gulp.series('deploy:plugin:content'));
});
