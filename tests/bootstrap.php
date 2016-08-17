<?php
/**
 * simple templatetests
 *
 * @package     SimpleTemplate
 * @author      Björn Bartels <coding@bjoernbartels.earth>
 * @link        https://gitlab.bjoernbartels.earth/groups/zf2
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @copyright   copyright (c) 2016 Björn Bartels <coding@bjoernbartels.earth>
 */

namespace SimpleTemplateTest;

use RuntimeException;

/**
 * set working directory
 * @var string $projectRoot
 */
$projectRoot = dirname(__DIR__);
chdir($projectRoot);

/**
 * mocked 'gettext' function
 */
if (!function_exists("gettext")) {
	function gettext($message, $domain = "default") {
		return $message;
	}
}

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{

    public static function init()
    {
        static::initAutoloader();
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (file_exists($vendorPath.'/autoload.php')) {
            include $vendorPath.'/autoload.php';
        }

    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
        		throw new RuntimeException('Unable to locate project root');
            }
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }

}

Bootstrap::init();

