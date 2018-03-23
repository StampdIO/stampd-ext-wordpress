/**
 * Gulp index
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

//var js = require('./tasks/js').js();
//var jsMin = require('./tasks/js').jsMin();
//var jsHint = require('./tasks/js').jsHint();
var singleJSMin = require('./tasks/js').singleJSMin();
var sassCompile = require('./tasks/sass').sassCompile();
var sassMin = require('./tasks/sass').sassMin();
var sass = require('./tasks/sass').sass();
var browserSyncInit = require('./tasks/browserSyncInit').browserSyncInit();

// default task
var defaultInit = require('./tasks/defaultInit').defaultInit();