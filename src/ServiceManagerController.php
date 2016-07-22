<?php

namespace ApiExploder;

use Exception;

class ServiceManagerController
{
    /** @var  App */
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function getResourceDefinitions()
    {
        $cache = $this->app['app.cache']->getItem('app.resources.definition');
        if (!$resources = $cache->get()) {
            foreach ($this->app['services'] as $name => $options) {
                if (empty($options['resource_definition'])) {
                    throw new Exception(sprintf('Service %s setting is invalid.', $name));
                }
                $resources[$name] = json_decode($this->app['guzzle']->get($options['resource_definition'])->getBody()->getContents(), true);
                foreach ($resources[$name] as $name => $structure) {
                    if (empty($structure['dataStructure']['fields']) || empty($structure['endpoints'])) {
                        throw new Exception(sprintf('The resource %s definition is invalid.', $name));
                    }
                }
            }
            $this->app['app.cache']->save($cache->set($resources)->expiresAfter(60 * 60 * 24));
        }

        return !empty($resources) ? $resources : [];
    }

    public function getActiveResource()
    {
        $this->app['request']
    }
}
