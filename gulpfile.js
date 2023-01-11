var gulp = require("gulp");
var config = require("./gulp/config");
var argv = require('minimist')(process.argv.slice(2));
global.env = argv.env || 'test';
var gulpBeer = require('gulp-beer');

global.filesToIgnore = [
  "!node_modules/**/*",
  "!bower_components/**/*",
  "!*.swap",
  "!src/templates/**/media/css/**/*"
];

global.watchFiles = global.filesToIgnore;
global.config = config;

var reqDir = require('require-dir'), tasks = reqDir('gulp/');

var sourcemaps = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var sass = require('gulp-dart-sass');
var remove = require('del');

const debug = require('gulp-debug');

var rsync = require("gulp-rsync");

var filesToIgnore = [
]

var files = [
  "src/**/*.php",
  "src/**/*.css",
  "src/**/*.js",
  "src/**/*.html",
  filesToIgnore
];

var componentFiles = [
    'src/component/**/*',
    'src/component/admin/sql/*',
    'src/plugins/user/bwc/*',
    'src/plugins/user/bwc/**/*',
    'src/modules/**/*'
];

var watchFiles = componentFiles.concat(filesToIgnore);

gulp.task('template-sass',function(){

    return gulp.src('src/templates/cassiopeia_idazlehiaketa/media/scss/template.scss')
        .pipe(sourcemaps.init())
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(autoprefixer({
            // browsers: ['last 2 versions'],
            cascade: false
        }))
        .pipe(sourcemaps.write('maps'))
        .pipe(gulp.dest('src/templates/cassiopeia_idazlehiaketa/media/css'));
});


gulp.task("copyToTemp:component", function () {
  return gulp.src(componentFiles)
        .pipe(gulp.dest("temp/component"));
});

gulp.task("copy",
	gulp.series('copyToTemp:component')
);

gulp.task("zip", function () {
  return gulp
    .src(["temp/component/**"])
    .pipe(zip("com_bwc.zip"))
    .pipe(gulp.dest("release"));
});

gulp.task("clean", function () {
  return remove(['temp/**']);
});

gulp.task('deploy-backend',function(){
	return gulp.src(tempBackendFiles)
		.pipe(rsync({
			root: 'temp/component/admin',
			hostname: config.sshData[env].hostname,
			destination: config.sshData[env].destination + 'administrator/components/com_bwc/',
			archive: true,
			compress: true,
			progress: true,
			debug: true,
			recursive: true
		}));
});

gulp.task('deploy-frontend',function(){
	return gulp.src(tempFrontendFiles)
		.pipe(rsync({
			root: 'temp/component/site/',
			hostname: config.sshData[env].hostname,
			destination: config.sshData[env].destination + 'components/com_bwc/',
			archive: true,
			compress: true,
			progress: true,
			recursive: true
		}));
});

gulp.task('deploy-media',function(){
return gulp.src(tempMediaFiles)
		.pipe(rsync({
			root: 'temp/component/media/',
			hostname: config.sshData[env].hostname,
			destination: config.sshData[env].destination + 'media/com_bwc/',
			archive: true,
			compress: true,
			progress: true,
			recursive: true
		}));
});

gulp.task('deploy-user-plugins',function(){
return gulp.src(pluginBwcFiles)
        .pipe(debug({title: 'Files to copy', showFiles: true}))
		.pipe(rsync({
			root: 'src/plugins/user/',
			hostname: config.sshData[env].hostname,
			destination: config.sshData[env].destination + 'plugins/user/',
			archive: true,
			compress: true,
			progress: true,
			recursive: true
		}));
});

gulp.task('deploy-template-main',function(){
return gulp.src(templateMainFiles)
		.pipe(rsync({
			root: 'src/templates/cassiopeia_idazlehiaketa/template/',
			hostname: config.sshData[env].hostname,
			destination: config.sshData[env].destination + 'templates/cassiopeia_idazlehiaketa/',
			archive: true,
			compress: true,
			progress: true,
			recursive: true
		}));
});

gulp.task('deploy-template-media',function(){
return gulp.src(templateMediaFiles)
		.pipe(rsync({
			root: 'src/templates/cassiopeia_idazlehiaketa/media/',
			hostname: config.sshData[env].hostname,
			destination: config.sshData[env].destination + 'media/templates/site/cassiopeia_idazlehiaketa/',
			archive: true,
			compress: true,
			progress: true,
			recursive: true
		}));
});

gulp.task('deploy-modules',function(){
return gulp.src(modulesFiles)
        .pipe(debug({title: 'Files to copy', showFiles: true}))
		.pipe(rsync({
			root: 'src/modules',
			hostname: config.sshData[env].hostname,
			destination: config.sshData[env].destination + 'modules/',
			archive: true,
			compress: true,
			progress: true,
			recursive: true
		}));
});

gulp.task('create-temp',
  gulp.series("copy")
);

gulp.task('deploy-template',
	gulp.series('template-sass', 'deploy-template-main', 'deploy-template-media')
);

gulp.task('deploy-joomla',
	gulp.series('deploy-backend', 'deploy-frontend', 'deploy-media', 'deploy-user-plugins', 'deploy-template', 'deploy-modules')
);

gulp.task('deploy-to-rsync', gulp.series('create-temp', 'deploy-joomla'));


gulp.task('watch-joomla', function() {
	gulp.watch(watchFiles, gulp.series('copyToTemp:component', 'deploy-joomla'));
});

gulp.task('watch-production', function() {
    env = 'production';
	gulp.watch(watchFiles, gulp.series('copyToTemp:component', 'deploy-joomla'));
});

gulp.task('watch-full', function() {
	gulp.watch(watchFiles, gulp.series('clean', 'copyToTemp:component', 'deploy-joomla'));
});

gulp.task('watch', gulp.series('watch-full'));

gulp.task(
  "default",
  gulp.series("zip:plugin:content", "clean")
);
/*
gulp.task("browser-sync", function () {
  return browserSync.init(files, {
    server: {
      baseDir: "./src/",
      index: "screenshots/main.html",
    },
    notify: false,
    port: 8080,
    injectChanges: true,
  });
});
*/
