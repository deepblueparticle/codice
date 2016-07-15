<?php

namespace Codice\Plugins;

use App;
use Codice\Support\Traits\Singleton;
use File;
use Illuminate\Support\Str;
use Lang;
use View;

class Manager {
    use Singleton;

    /**
     * The application instance, since Plugins are an extension of a Service Provider
     */
    protected $app;

    /**
     * Container object used for storing plugin information objects.
     */
    protected $plugins;

    /**
     * Storage holding enabled plugins.
     *
     * Key is plugin directory (referenced as "identifier" in code) and the value is Fully Qualified Name
     * of the respective Plugin class (referenced as "class" in the code).
     */
    protected $storage;

    /**
     * @var bool Check if all plugins have had the register() method called.
     */
    protected $registered = false;

    /**
     * @var bool Check if all plugins have had the boot() method called.
     */
    protected $booted = false;

    /**
     * Initializes the plugin manager.
     */
    protected function init()
    {
        $this->app = App::make('app');

        if (!file_exists(storage_path('app/plugins.json'))) {
            $this->setStorage([]);
        }

        $this->storage = $this->getStorage();

        $this->loadPlugins();
    }

    /**
     * Find enabled plugins and load them into the $this->plugins array.
     *
     * @return array
     */
    public function loadPlugins()
    {
        $this->plugins = [];

        foreach ($this->storage as $identifier => $class) {
            $this->plugins[$identifier] = $this->loadPlugin($class);
        }

        return $this->plugins;
    }

    /**
     * Load all plugins found in the directory, regardless of their status.
     *
     * @return Plugin[]
     */
    public function loadAllPlugins()
    {
        $directories = glob(plugin_path('*'));
        $plugins = [];

        foreach ($directories as $directory) {
            $tmp = explode('/', $directory);
            $identifier = end($tmp);

            $plugin = $this->loadPlugin($identifier);

            if ($plugin) {
                $plugins[$identifier] = $plugin;
            }
        }

        return $plugins;
    }

    /**
     * Loads a single plugin into the manager.
     *
     * @param string $identifier
     * @return bool|Plugin
     */
    public function loadPlugin($identifier)
    {
        require plugin_path($identifier . '/Plugin.php');

        $class = $this->findClassByIdentifier($identifier);

        // Not a valid plugin!
        if (!class_exists($class)) {
            return false;
        }

        $pluginObject = new $class($this->app);

        // Check if plugin class inherits Plugin and therefore an interface
        if (!$pluginObject instanceof Plugin) {
            return false;
        }

        return $pluginObject;
    }

    /**
     * Run the register() method on all plugins. Can only be called once.
     * @return void
     */
    public function registerAll()
    {
        if ($this->registered) {
            return;
        }

        foreach ($this->plugins as $pluginId => $plugin) {
            $this->registerPlugin($plugin, $pluginId);
        }

        $this->registered = true;
    }

    /**
     * Register a single plugin object.
     *
     * @param Plugin $plugin
     * @param string $identifier
     * @return void
     */
    public function registerPlugin(Plugin $plugin, $identifier)
    {
        if (!$plugin) {
            return;
        }

        $pluginPath = plugin_path($identifier);
        $pluginNamespace = strtolower($identifier);

        $plugin->register();

        /*
         * Register language namespaces
         */
        $langPath = $pluginPath . '/lang';
        if (File::isDirectory($langPath)) {
            Lang::addNamespace($pluginNamespace, $langPath);
        }

        /*
         * Register configuration path
         */
        // @FIXME Config::package() doesn't exists

        /*
         * Register views path
         */
        $viewsPath = $pluginPath . '/views';
        if (File::isDirectory($viewsPath)) {
            View::addNamespace($pluginNamespace, $viewsPath);
        }

        /*
         * Add init, if available
         */
        $initFile = $pluginPath . '/init.php';
        if (File::exists($initFile)) {
            require $initFile;
        }

        /*
         * Add routes, if available
         */
        $routesFile = $pluginPath . '/routes.php';
        if (File::exists($routesFile)) {
            require $routesFile;
        }
    }

    /**
     * Run the boot() method on all plugins. Can only be called once.
     */
    public function bootAll()
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->plugins as $plugin) {
            $this->bootPlugin($plugin);
        }

        $this->booted = true;
    }

    /**
     * Boot a single plugin object.
     *
     * @param Plugin $plugin
     * @return void
     */
    public function bootPlugin(Plugin $plugin)
    {
        $plugin->boot();
    }

    /**
     * Enable a single plugin.
     *
     * @param string $identifier Plugin's identifier (its directory)
     */
    public function enable($identifier)
    {
        $this->storage[$identifier] = $this->findClassByIdentifier($identifier);
        $this->setStorage($this->storage);
    }

    /**
     * Disable a single plugin.
     *
     * @param string $identifier Plugin's identifier (its directory)
     */
    public function disable($identifier)
    {
        unset($this->storage[$identifier]);
        $this->setStorage($this->storage);
    }

    /**
     * Determine if a plugin is disabled.
     *
     * @param string $identifier Plugin's identifier (its directory)
     * @return bool
     */
    public function isEnabled($identifier)
    {
        return isset($this->storage[$identifier]);
    }

    /**
     * Return database of plugin informations.
     *
     * @return array
     */
    protected function getStorage()
    {
        return json_decode(file_get_contents(storage_path('app/plugins.json')), true);
    }

    /**
     * Write database of plugin informations.
     *
     * @param  array $content Plugins data
     * @return bool
     */
    protected function setStorage($content)
    {
        return file_put_contents(storage_path('app/plugins.json'), json_encode($content));
    }

    /**
     * Return a Fully Qualified Name for plugin registration class based on its identifier.
     *
     * @param  string $identifier Plugin's identifier (its directory)
     * @return string
     */
    protected function findClassByIdentifier($identifier)
    {
        return "CodicePlugin\\" . Str::camel($identifier) . "\\Plugin";
    }

    /**
     * Return a plugin registration class based on its identifier.
     *
     * @param  string $identifier Plugin's identifier (its directory)
     * @return Plugin|null
     */
    protected function findObjectByIdentifier($identifier)
    {
        return $this->plugins[$identifier];
    }
}