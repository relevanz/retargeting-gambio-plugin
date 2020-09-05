<?php
/* -----------------------------------------------------------
Copyright (c) 2019 Releva GmbH - https://www.releva.nz
Released under the MIT License (Expat)
[https://opensource.org/licenses/MIT]
--------------------------------------------------------------
*/
namespace Releva\Retargeting\Base;

/**
 * This is loosely based on the composer autoloader.
 */
class ClassLoader
{
    protected $classMap = [];

    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     *
     * @var array
     */
    protected $prefixDirsPsr4 = [];

    /**
     * Initialize and register this class loader.
     * @return The class loader instance.
     */
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
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix The namespace prefix.
     * @param string $baseDir A base directory for class files in the
     *        namespace.
     * @param bool $prepend If true, prepend the base directory to the stack
     *        instead of appending it; this causes it to be searched first
     *        rather than last.
     * @return this
     */
    public function addNamespace($prefix, $baseDir, $prepend = false) {
        // normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // normalize the base directory with a trailing separator
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';

        // initialize the namespace prefix array
        if (isset($this->prefixDirsPsr4[$prefix]) === false) {
            $this->prefixDirsPsr4[$prefix] = [];
        }

        // retain the base directory for the namespace prefix
        if ($prepend) {
            array_unshift($this->prefixDirsPsr4[$prefix], $baseDir);
        } else {
            array_push($this->prefixDirsPsr4[$prefix], $baseDir);
        }
        return $this;
    }

    /**
     * Registers a map of PSR-4 directories for a given namespace,
     * extending any others previously set for this namespace.
     *
     * @param array $map Key is the namespace, value is an array with possible
     *        psr4 paths.
     * @param bool $prepend If true, prepend the base directory to the stack
     *        instead of appending it; this causes it to be searched first
     *        rather than last.
     * @return this
     */
    public function addPsr4Map(array $map, $prepend = false) {
        foreach ($map as $ns => $paths) {
            foreach ((array)$paths as $path) {
                $this->addNamespace($ns, $path, $prepend);
            }
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
     * @param  string $class The name of the class
     * @return bool   True if loaded, false otherwise
     */
    public function loadClass($class) {
        if (($file = $this->findFile($class, '.php')) !== null) {
            relevanzTrackingIncludeFile($file);
            return true;
        }
        return false;
    }

    /**
     * Can be overridden for unit testing.
     * @param string $file The absolute path to a possible php file.
     * @return bool True if file exists, false otherwise.
     */
    protected function fileExists($file) {
        return file_exists($file);
    }

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return ?string The path if found, null otherwise
     */
    public function findFile($class, $ext = '') {
        // class map lookup
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }

        // PSR-4 lookup
        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;

        $subPath = $class;
        while (($lastPos = strrpos($subPath, '\\')) !== false) {
            $subPath = substr($subPath, 0, $lastPos);
            $search = $subPath . '\\';
            if (isset($this->prefixDirsPsr4[$search])) {
                $pathEnd =  substr($logicalPathPsr4, $lastPos + 1);
                foreach ($this->prefixDirsPsr4[$search] as $dir) {
                    if ($this->fileExists($file = $dir . $pathEnd)) {
                        $this->classMap[$class] = $file;
                        return $file;
                    }
                }
            }
        }

        // PSR-4 fallback dirs
        if (isset($this->prefixDirsPsr4['\\'])) {
            foreach ($this->prefixDirsPsr4['\\'] as $dir) {
                if ($this->fileExists($file = $dir . $logicalPathPsr4)) {
                    $this->classMap[$class] = $file;
                    return $file;
                }
            }
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
