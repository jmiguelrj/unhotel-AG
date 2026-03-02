const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cleanCSS = require('gulp-clean-css');
const sourcemaps = require('gulp-sourcemaps');
const rename = require('gulp-rename');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const browserSync = require('browser-sync').create();

// Paths
const paths = {
    scss: {
        src: 'assets/src/scss/**/*.scss',
        dest: 'assets/dist/css'
    },
    blocks: {
        src: 'blocks/**/[!_]*.scss',  // Compile blocks SCSS (exclude partials)
        dest: 'blocks',  // Output to same directory as source
        exclude: 'blocks/**/*.css' // Exclude generated CSS files from watch
    },
    js: {
        src: ['assets/src/js/main.js', 'assets/src/js/scripts.js'],
        dest: 'assets/dist/js'
    },
    images: {
        src: 'assets/src/images/**/*',
        dest: 'assets/dist/images'
    },
    php: '**/*.php'
};

// BrowserSync proxy - update with your local development URL
const localURL = 'http://localhost:3100';

// Compile Sass to CSS
function styles() {
    return gulp.src(paths.scss.src, { allowEmpty: true })
        .pipe(sourcemaps.init())
        .pipe(sass({
            outputStyle: 'expanded'
        }).on('error', function(err) {
            console.error('Sass error:', err.message);
            console.error('File:', err.file);
            this.emit('end');
        }))
        .pipe(postcss([autoprefixer()]))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(paths.scss.dest))
        .pipe(cleanCSS({
            compatibility: 'ie8'
        }))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(paths.scss.dest))
        .pipe(browserSync.stream());
}

// Process JavaScript
function scripts() {
    return gulp.src(paths.js.src)
        .pipe(sourcemaps.init())
        .pipe(concat('main.js'))
        .pipe(gulp.dest(paths.js.dest))
        .pipe(uglify())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(paths.js.dest))
        .pipe(browserSync.stream());
}

// Compile Block Styles (Frontend & Editor)
function blockStyles() {
    return gulp.src(paths.blocks.src, { base: '.', allowEmpty: true })
        .pipe(sourcemaps.init())
        .pipe(sass({
            outputStyle: 'expanded',
            includePaths: ['assets/src/scss'],
            quietDeps: true,
            verbose: false,
            precision: 10,
            sourceComments: false
        }).on('error', function(err) {
            console.error('Sass compilation error:', err.message);
            console.error('File:', err.file);
            console.error('Line:', err.line);
            console.error('Column:', err.column);
            // Emit end to continue processing other files
            this.emit('end');
        }))
        .on('error', function(err) {
            console.error('Pipeline error:', err.message);
            this.emit('end');
        })
        .pipe(postcss([autoprefixer({
            cascade: false
        })]))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('.'))
        .on('error', function(err) {
            console.error('Write error:', err.message);
            this.emit('end');
        })
        .on('end', function() {
            // Only reload browser sync if not in watch mode to prevent loops
            if (process.env.NODE_ENV !== 'watch') {
                browserSync.stream();
            }
        });
}

// Copy images
function images() {
    return gulp.src(paths.images.src)
        .pipe(gulp.dest(paths.images.dest))
        .pipe(browserSync.stream());
}

// Copy Flickity CSS from node_modules to assets/dist/css
function vendorCss() {
    return gulp.src('node_modules/flickity/dist/flickity.min.css')
        .pipe(gulp.dest('assets/dist/css'));
}

// BrowserSync
function serve() {
    browserSync.init({
        proxy: localURL,
        notify: false,
        open: false
    });
}

// Watch files with delay to prevent infinite loops
function watchFiles() {
    gulp.watch(paths.scss.src, { 
        ignoreInitial: false,
        delay: 300 
    }, styles);
    
    // Watch blocks SCSS but exclude generated CSS to prevent loops
    gulp.watch(paths.blocks.src, { 
        ignoreInitial: false,
        delay: 300,
        ignored: paths.blocks.exclude
    }, blockStyles);
    
    gulp.watch(paths.js.src, { 
        ignoreInitial: false,
        delay: 300 
    }, scripts);
    
    gulp.watch(paths.images.src, { 
        ignoreInitial: false,
        delay: 300 
    }, images);
    
    gulp.watch(paths.php).on('change', browserSync.reload);
}

// Complex tasks
const watch = gulp.parallel(watchFiles, serve);
const build = gulp.series(gulp.parallel(styles, scripts, images, blockStyles, vendorCss));

// Export tasks
exports.styles = styles;
exports.blockStyles = blockStyles;
exports.scripts = scripts;
exports.images = images;
exports.vendorCss = vendorCss;
exports.watch = watch;
exports.build = build;
exports.default = watch;
