<?php

namespace Pantheon\Terminus\Commands\Upstream\Updates;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Models\Environment;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;
use Pantheon\Terminus\Exceptions\TerminusException;

/**
 * Class UpdatesCommand
 * @package Pantheon\Terminus\Commands\Upstream\Updates
 */
abstract class UpdatesCommand extends TerminusCommand implements SiteAwareInterface
{
    use SiteAwareTrait;

    /**
     * @param Environment $environment
     * @return int
     */
    protected function getNumberOfUpdates(Environment $environment)
    {
        return count($this->getUpstreamUpdatesLog($environment));
    }

    /**
     * Returns the target(s) of this command run
     *
     * @param string $site_env Site & development environment in <site>.<env> format
     * @param boolean $all True to target all sites
     * @return Environment[]
     * @throws TerminusException
     */
    protected function getUpdateTargets($site_env = null, $all = false)
    {
        $targets = [];
        if ($all) {
            $this->log()->warning('Retrieving all updates for all your sites\' environments may take a while.');
            $sites = $this->sites()->all();
            foreach ($sites as $site) {
                $environments = $site->getEnvironments()->all();
                foreach ($environments as $environment) {
                    if ($this->canApplyUpdatesTo($environment)) {
                        $targets[$site->get('name') . '.' . $environment->id] = $environment;
                    }
                }
            }
            ksort($targets);
        } else if (!is_null($site_env)) {
            list(, $env) = $this->getSiteEnv($site_env, 'dev');
            if (!$this->canApplyUpdatesTo($env)) {
                throw new TerminusException(
                    'Upstream updates cannot be applied to the {env} environment',
                    ['env' => $env->id,]
                );
            }
            $targets[] = $env;
        }

        if (empty($targets)) {
            $this->log()->warning('There are no available updates for the targeted environment(s).');
        }
        return $targets;
    }

    /**
     * Return the upstream for the given site
     *
     * @param Environment $env
     * @return object The upstream information
     * @throws TerminusException
     */
    protected function getUpstreamUpdates(Environment $environment)
    {
        if (empty($upstream = $environment->getUpstreamStatus()->getUpdates())) {
            throw new TerminusException('There was a problem checking your upstream status. Please try again.');
        }
        return $upstream;
    }

    /**
     * Get the list of upstream updates for a site
     *
     * @param Environment $environment
     * @return array The list of updates
     * @throws TerminusException
     */
    protected function getUpstreamUpdatesLog(Environment $environment)
    {
        $updates = $this->getUpstreamUpdates($environment);
        return isset($updates->update_log) ? (array)$updates->update_log : [];
    }

    /**
     * Determines whether an environment is eligible to have upstream updates applied to it
     *
     * @param Environment $environment
     * @return boolean
     */
    protected function canApplyUpdatesTo(Environment $environment)
    {
        return !in_array($environment->id, ['test', 'live',])
            && (boolean)$this->getNumberOfUpdates($environment);
    }
}
