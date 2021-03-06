var gulp = require('gulp'),

// ZIP compress files
zip = require('gulp-zip'),

// Utility functions for gulp plugins
gutil = require('gulp-util')
//notify = require("gulp-notify"),

// File systems
fs    = require('fs'),
path  = require('path'),
merge = require('merge-stream'),

// Gulp Configuration
config = require('./gulp-config.json')

// iJoomer Extension Configuration file
extensionConfig = require('./package.json');

function getFolders(dir) {
    return fs.readdirSync(dir)
      .filter(function(file) {
        return fs.statSync(path.join(dir, file)).isDirectory();
      });
}

// Creating zip files for iJoomer Extensions
gulp.task('release:extensions', function() {

	// Source directory for read and prepare for zip
	var srcFolder = gutil.env.folder || './plugins',

	// Read all the folders in given source directory
	folders   = getFolders(srcFolder),

	// Extension package name suffix
	extSuffix = gutil.env.suffix || 'plg_';

	// Display log
	gutil.log(gutil.colors.white.bgBlue(folders.length) + gutil.colors.blue.bold(' extensions are ready for release'));

	// Loop through the folders and create zip files for each of them.
	var tasks = folders.map(function(folder) {

		// Display name of the folder
		gutil.log(gutil.colors.blue.bold.italic(folder));

		return gulp.src(
				path.join(srcFolder, folder, '**')
			)
		    .pipe(
		    	zip(
		    		extSuffix + config.releasePkgName + folder + '_' + 'v' + config.version + '.zip'
		    	)
		    )
			.pipe(
				gulp.dest(
					path.join(config.packageDir, srcFolder.split(path.sep)[1])
				)
			);
	});

	return merge(tasks);
});

// Creating zip files for iJoomer Component
gulp.task('release:component', function() {

	if (!config.packageFiles || (config.packageFiles && config.packageFiles.length <= 0))
	{
		gutil.log(
			gutil.colors.white.bgRed(
				'ERROR: Please specify `packageFiles` in gulp-config.json or make sure you have added files list'
			)
		);

		return false;
	}

	gutil.log(gutil.colors.white.bgGreen('Preparing release for component'));

	gulp.src(config.packageFiles, {base: '.'})
		.pipe(zip(config.releasePkgName + 'component_v' + config.version + '_' + config.joomlaVersion + '.zip'))
		.pipe(gulp.dest(config.packageDir));

	gutil.log(gutil.colors.white.bgGreen('Component packages are ready at ' + config.packageDir))
});

gulp.task(
	'release',
	[
		'release:component',
		'release:extensions'
	],
	function() {

});

gulp.task('default', function() {
	showHelp();
});

function showHelp(){
	gutil.log(
		gutil.colors.white.bold.bgMagenta(
			'\n\n\nFollowing tasks and switches are available:\n\n\t 1. gulp release:component \n\t\t Use this command to release component. Version and other information can be set in gulp-config.json file. \n\n\t 2. gulp release:extensions \n\t\t This command is to release the extensions.\n\t\t This command will read the base directory and create zip files for each of the folder. \n\t\t === Switches === \n\t --folder {source direcory}  Default: "./plugins" \n\t --suffix {text of suffix}   Default: "plg_"\n\n\t Example Usage: \n\t\t gulp release:extensions --folder ./extensions/src --suffix ext_ \n\n\n'
		)
	);
}
