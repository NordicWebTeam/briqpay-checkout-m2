'use strict';

var gulp = require('gulp'),
    less = require('gulp-less'),
    autoPrefixer = require('gulp-autoprefixer'),
    cleanCss = require('gulp-clean-css'),
    watch = require('gulp-watch');

var config = {
    less: {
        source: './view/frontend/web/css/source/briqpay.less',
        dist: './view/frontend/web/css'
    }
};

gulp.task('less', function () {
    return gulp.src(config.less.source)
        .pipe(less().on('error', function(err) {
            console.log(err);
        }))
        .pipe(autoPrefixer('last 2 versions'))
        .pipe(cleanCss())
        .pipe(gulp.dest(config.less.dist));
});


gulp.task('watch', function(){
    return gulp.watch('./view/frontend/web/css/source/**/*.less', gulp.series('less'));
});
