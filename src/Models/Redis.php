<?php

namespace Pantheon\Terminus\Models;

/**
 * Class Redis
 * @package Pantheon\Terminus\Models
 */
class Redis extends TerminusModel
{
    /**
     * @var Site
     */
    public $site;

    /**
     * @inheritdoc
     */
    public function __construct($attributes = null, array $options = [])
    {
        parent::__construct($attributes, $options);
        $this->site = $options['site'];
    }

    /**
     * Clears the Redis cache on the named environment
     *
     * @param Environment $env An object representing the environment on which to clear the Redis cache
     * @return Workflow
     */
    public function clear($env)
    {
        // @Todo: Change this when the env model conversion is merged
        return $env->workflows->create('clear_redis_cache');
    }

    /**
     * Disables Redis caching
     */
    public function disable()
    {
        $this->setStatus(false);
    }

    /**
     * Enables Redis caching
     */
    public function enable()
    {
        $this->setStatus(true);
    }

    /**
     * Sets the site's allow_cacheserver setting to this value
     *
     * @param boolean $status True to enable Solr, false to disable
     */
    private function setStatus($status)
    {
        $this->request()->request(
            "sites/{$this->site->id}/settings",
            ['method' => 'put', 'form_params' => ['allow_cacheserver' => $status,],]
        );
    }
}
