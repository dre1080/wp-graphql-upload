<?php

/**
 * Plugin Name: WPGraphQL Upload
 * Plugin URI: https://github.com/dre1080/wp-graphql-upload
 * Description: Adds file upload support for the WPGraphQL plugin. Requires WPGraphQL version 1.0
 * or newer.
 * Author: ando
 * Author URI: http://github.com/dre1080
 * Version: 0.1
 * Requires PHP: 5.4
 * License: MIT
 * License URI: http://opensource.org/licenses/mit-license.html
 *
 * @package  WPGraphQL\Upload
 * @category WPGraphQL
 * @author   ando
 * @version  0.1
 */

namespace WPGraphQL;

$autoload = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoload)) {
    require_once($autoload);
} else {
    require_once(__DIR__ . '/src/Request/BodyParser.php');
    require_once(__DIR__ . '/src/Type/Upload.php');
}

require_once(__DIR__ . '/init.php');
