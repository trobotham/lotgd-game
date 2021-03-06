//-- Dependencies
var gulp = require('gulp')
var replace = require('gulp-replace')
var rename = require('gulp-rename')
var print = require('gulp-print').default
var plumber = require('gulp-plumber')
var del = require('del')

//-- Configuration
var configTasks = require('../../config/tasks')
var log = configTasks.log
var themeName = configTasks.theme()

module.exports = function (callback)
{
    console.info('Creation theme: ' + themeName)

    del('semantic/src/theme.config')

    return gulp.src('semantic/src/theme.config.default')
        .pipe(plumber())
        .pipe(replace('default', themeName, { skipBinary: false }))
        .pipe(rename('theme.config'))
        .pipe(plumber.stop())
        .pipe(gulp.dest('semantic/src'))
        .pipe(print(log.copied))
}
