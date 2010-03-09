<?php
/**
 * WORDPRESS SERVICE v0.2
 * For CakePHP Wordpress Component to connect to the Wordpress API.
 * 
 * Copyright (C) 2010 Kyle Robinson Young
 * 
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 * 
 * 
 * @author Kyle Robinson Young <kyle at kyletyoung.com>
 * @copyright 2010 Kyle Robinson Young
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 0.2
 * @link http://www.kyletyoung.com/code/cakephp_wordpress_component
 * 
 */

/**
 * PASSWORD
 * Set this password to the same as the 
 * PASSWORD var in the components/wordpress.php file.
 */
$PASSWORD = 'ChangeThisPassword';



/* - - DO NOT EDIT BELOW THIS LINE - - */

// CHECK PASSWORD
if (md5($PASSWORD) != preg_replace('/[^A-Za-z0-9]/', '', $_REQUEST['passwd'])) 
{
    die("Not Authorized. Please update the PASSWORD in 'webroot/wordpress_service.php' and 'controllers/components/wordpress.php' to match.");
} // passwords dont match 

// GET INPUT
$_WPSERVICE['path'] = urldecode($_REQUEST['path']);
$_WPSERVICE['func'] = urldecode($_REQUEST['func']);
$_WPSERVICE['args'] = urldecode($_REQUEST['args']);

// CALL WP
global $wpdb;
define('WP_USE_THEMES', false);
require_once $_WPSERVICE['path'].'wp-config.php';

ob_start();
$return = call_user_func_array($_WPSERVICE['func'], json_decode($_WPSERVICE['args']));
$ob = ob_get_clean();

// DETERMINE WHICH TO RETURN
if (empty($return)) echo json_encode(array('output' => $ob));
else echo json_encode(array('output' => $return));
?>