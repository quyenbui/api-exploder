<?php

namespace ApiExploder;

use ApiExploder\Connector\Controller;
use GuzzleHttp\Client;
use phpFastCache\CacheManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ApiExploderServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $pimple)
    {
        $pimple['service_manager.ctrl'] = function ($pimple) {
            return new ServiceManagerController($pimple);
        };

        $pimple['forward.ctrl'] = function ($pimple) {
            return new Controller($pimple);
        };

        $pimple['guzzle'] = function () {
            return new Client();
        };

        $pimple['app.cache'] = function ($pimple) {
            return CacheManager::getInstance($pimple['cache.options']['driver'], $pimple['cache.options']);
        };
    }
}