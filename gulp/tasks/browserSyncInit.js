/**
 * Gulp browsersync
 *
 * @version 1.0.0
 * @author Hypermetron (Minas Antonios)
 * @copyright Copyright (c) 2018, Minas Antonios
 * @license http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 */

'use strict';

/**
 * Required Plugins
 * ----------------
 */

var configs     = require('../configs');
var gulp        = require('gulp');
var browserSync = require('browser-sync');


/**
 * Task Definitions
 * ----------------
 */

var browserSyncInit = function() {

    /**
     * Starts BrowserSync for all the files produced
     */
    gulp.task('browserSyncInit', function() {
        browserSync.init([configs.tasks.js.browserSync, configs.tasks.sass.browserSync], {
            ghostMode: {
                clicks: false,
                forms : false,
                scroll: false
            },
            logLevel : 'silent',
            notify   : false
        });
    });
};


/**
 * Exports
 * -------
 */

module.exports.browserSyncInit = browserSyncInit;