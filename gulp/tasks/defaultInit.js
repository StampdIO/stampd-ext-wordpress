/**
 * Gulp default
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

var gulp = require('gulp');


/**
 * Task Definitions
 * ----------------
 */

var defaultInit = function() {

    /**
     * Build and start BrowserSync
     */
    gulp.task('default', function() {
        gulp.start('singleJSMin', 'sass', 'browserSyncInit');
    });
};


/**
 * Exports
 * -------
 */

module.exports.defaultInit = defaultInit;