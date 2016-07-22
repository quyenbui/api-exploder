<?php

namespace ApiExploder;

use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;

class App extends Application
{
    private $environment;

    public function __construct($environment)
    {
        $this->environment = $environment;

        parent::__construct($this->loadConfigurations());

        $this->register(new ApiExploderServiceProvider());
        $this->register(new ServiceControllerServiceProvider());

        $this->mount('', new ControllerProvider());
    }

    public function getRootDir()
    {
        return __DIR__ . '/../';
    }

    private function loadConfigurations()
    {
        $file = strtr($this->getRootDir() . 'config.env.php', [
            'env' => $this->environment
        ]);

        return is_file($file) ? require $file : require $this->getRootDir() . 'config.default.php';
    }
}
