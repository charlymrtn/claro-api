const { mix } = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
	// JS generales de la aplicación
	.js('resources/assets/js/app.js', 'public/js')

   // Datatables.
    .scripts([
        'node_modules/datatables.net/js/jquery.dataTables.js',
        'node_modules/datatables.net-bs/js/dataTables.bootstrap.js',
        'node_modules/datatables.net-responsive/js/dataTables.responsive.min.js',
        'node_modules/datatables.net-responsive-bs/js/responsive.bootstrap.min.js',
    ], 'public/js/mix/datatables.js')
    .styles([
        'node_modules/datatables.net-bs/css/dataTables.bootstrap.css',
        'node_modules/datatables.net-responsive-bs/css/responsive.bootstrap.min.css',
        'node_modules/datatables.net-buttons-bs/css/buttons.bootstrap.min.css',
    ], 'public/css/mix/datatables.css')

    // Forms
    .scripts([
        'node_modules/cropper/dist/cropper.min.js',
        'node_modules/jquery.dirtyforms/jquery.dirtyforms.min.js',
        'node_modules/jquery.dirtyforms.dialogs.bootstrap/jquery.dirtyforms.dialogs.bootstrap.min.js',
        'node_modules/bootstrap-show-password/bootstrap-show-password.min.js',
        'node_modules/select2/dist/js/select2.min.js',
        'node_modules/clipboard/dist/clipboard.min.js',
    ], 'public/js/mix/forms.js')

    .styles([
        'node_modules/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css',
        'node_modules/cropper/dist/cropper.min.css',
        'node_modules/titatoggle/dist/titatoggle-dist-min.css',
        'node_modules/select2/dist/css/select2.min.css',
        'resources/assets/sass/forms.scss',
        'node_modules/ion-rangeslider/csss/ion.rangeSlider.css', // ion.rangeSlider
        'node_modules/ion-rangeslider/csss/ion.rangeSlider.skinModern.css', // ion.rangeSlider
    ], 'public/css/mix/forms.css')

    // Charts
    .scripts([
        'node_modules/chart.js/dist/Chart.bundle.min.js',
    ], 'public/js/mix/charts.js')
    .styles([
        'resources/assets/sass/charts.scss',
    ], 'public/css/mix/charts.css')

    // CSS Fonts
    .sass('resources/assets/sass/fonts.scss', 'public/css/fonts.css')
    .sass('resources/assets/sass/ui.scss', 'public/css/ui.css')

    // UI - JQuery, Bootstrap, FA, Ion icons,
    .scripts([
        'node_modules/jquery/dist/jquery.min.js',
        'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
        //'vendor/jeroennoten/laravel-adminlte/resources/assets/dist/js/app.min.js',
        // PNotify
        'node_modules/pnotify/dist/pnotify.js',
        'node_modules/pnotify/dist/pnotify.animate.js',
        'node_modules/pnotify/dist/pnotify.buttons.js',
        'node_modules/pnotify/dist/pnotify.callbacks.js',
        'node_modules/pnotify/dist/pnotify.confirm.js',
        'node_modules/pnotify/dist/pnotify.desktop.js',
        'node_modules/pnotify/dist/pnotify.history.js',
        'node_modules/pnotify/dist/pnotify.mobile.js',
        'node_modules/pnotify/dist/pnotify.nonblock.js',
    ], 'public/js/mix/ui.js')

    .styles([
        'node_modules/bootstrap/css/bootstrap.min.css',
        'node_modules/bootstrap/css/bootstrap-theme.min.css',
        'node_modules/font-awesome/css/font-awesome.min.css',
        'node_modules/Ionicons/css/ionicons.min.css',
        // PNotify
        'node_modules/pnotify/dist/pnotify.css',
        'node_modules/pnotify/dist/pnotify.buttons.css',
        'node_modules/pnotify/dist/pnotify.history.css',
        'node_modules/pnotify/dist/pnotify.mobile.css',
        'node_modules/pnotify/dist/pnotify.nonblock.css',
    ], 'public/css/mix/ui.css')

    .copy('node_modules/font-awesome/fonts', 'public/css/fonts')
    .copy('node_modules/ionicons-npm/fonts', 'public/css/fonts')
    .copy('node_modules/material-design-icons-iconfont/dist/fonts/MaterialIcons*', 'public/css/fonts')

    // CSS de vendor.
    .sass('resources/assets/sass/vendor.scss', 'public/css')
    // CSS de la aplicación.
    .sass('resources/assets/sass/app.scss', 'public/css')
    // CSS Login.
    .sass('resources/assets/sass/login.scss', 'public/css')
   ;
