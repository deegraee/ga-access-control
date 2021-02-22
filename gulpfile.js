var gulp = require('gulp');
var concat = require('gulp-concat');
// Specify the Source files
var SRC_JS = './public/js/*.js';

gulp.task('scripts', function() {
    gulp.src([
            'node_modules/jquery/dist/jquery.slim.min.js',
            'node_modules/popper.js/dist/umd/popper.min.js',
            'node_modules/bootstrap/dist/js/bootstrap.min.js',
            'node_modules/datatables.net/js/jquery.dataTables.min.js',
            'node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js',
            'public/js/index.js'
        ])
        .pipe(concat('vendor.min.js'))
        .pipe(gulp.dest('./public/js'));
});

gulp.task('styles', function() {
    gulp.src([
        'node_modules/bootstrap/dist/css/bootstrap.min.css',
        'public/css/dataTables.bootstrap4.min.css'
    ])
        .pipe(concat('vendor.min.css'))
        .pipe(gulp.dest('./public/css'));
});

gulp.task('default', ['scripts', 'styles'], function() {
});
