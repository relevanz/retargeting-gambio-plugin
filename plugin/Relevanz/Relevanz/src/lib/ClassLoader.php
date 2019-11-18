<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace RelevanzTracking\Lib;

/**
 * This is loosely based on the composer autoloader.
 */
class ClassLoader
{
    protected $classMap = [];

    public static function init() {
        $loader = new self();
        $loader->register(true);
        return $loader;
    }

    /**
     * Sets the classmap.
     *
     * @param array $map An array of classes to filenames.
     * @param string $baseDir used to prefix the filenames.
     *
     * @return this
     */
    public function addClassMap(array $map, $baseDir = '') {
        if (!empty($baseDir)) {
            $baseDir = rtrim($baseDir, '/').'/';
        }
        foreach ($map as $class => $file) {
            $this->classMap[$class] = $baseDir.$file;
        }
        return $this;
    }

    /**
     * Registers this instance as an autoloader.
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     */
    protected function register($prepend = false) {
        spl_autoload_register([$this, 'loadClass'], true, $prepend);
    }

    /**
     * Loads the given class or interface.
     *
     * @param  string    $class The name of the class
     * @return bool|null True if loaded, null otherwise
     */
    public function loadClass($class) {
        if (($file = $this->findFile($class)) !== null) {
            relevanzTrackingIncludeFile($file);
            return true;
        }
        return false;
    }

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return ?string The path if found, null otherwise
     */
    public function findFile($class) {
        // class map lookup
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }
        return null;
    }

}


/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 */
function relevanzTrackingIncludeFile($file) {
    include $file;
}
