<?php

namespace Pantheon\Terminus\Commands\Upstream\Updates;

/**
 * Class ApplyCommand
 * @package Pantheon\Terminus\Commands\Upstream\Updates
 */
class ApplyCommand extends UpdatesCommand
{

    /**
     * Applies upstream updates to a site's development environment.
     *
     * @authorize
     *
     * @command upstream:updates:apply
     *
     * @param string $site_env Site & development environment
     * @option boolean $all Target all sites
     * @option boolean $updatedb Run update.php after update (Drupal only)
     * @option boolean $accept-upstream Attempt to automatically resolve conflicts in favor of the upstream
     *
     * @usage <site>.<env> Applies upstream updates to <site>'s <env> environment.
     * @usage <site>.<env> --updatedb Applies upstream updates to <site>'s <env> environment and runs update.php after update.
     * @usage <site>.<env> --accept-upstream Applies upstream updates to <site>'s <env> environment and attempts to automatically resolve conflicts in favor of the upstream.
     * @usage --all Applies upstream updates to every development environment of every site.
     */
    public function applyUpstreamUpdates($site_env = null, $options = ['all' => false, 'updatedb' => false, 'accept-upstream' => false,])
    {
        $all = isset($options['all']) ? $options['all'] : false;
        $targets = $this->getUpdateTargets($site_env, $all);

        foreach ($targets as $env) {
            $update_count = $this->getNumberOfUpdates($env);
            if ($env->get('connection_mode') === 'sftp') {
                $this->log()->warning(
                    '{site}.{env} has {count} updates, but they cannot be applied because the environment is in SFTP mode.',
                    ['site' => $env->getSite()->get('name'), 'env' => $env->id, 'count' => $update_count,]
                );
                continue;
            }

            $this->log()->notice('Applying {count} upstream update(s) to the {env} environment of {site_id}...', [
                'count' => $update_count,
                'env' => $env->id,
                'site_id' => $env->getSite()->get('name'),
            ]);

            $workflow = $env->applyUpstreamUpdates(
                isset($options['updatedb']) ? $options['updatedb'] : false,
                isset($options['accept-upstream']) ? $options['accept-upstream'] : false
            );

            while (!$workflow->checkProgress()) {
                // @TODO: Add Symfony progress bar to indicate that something is happening.
            }
            $this->log()->notice($workflow->getMessage());
        }
    }
}
