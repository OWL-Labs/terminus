<?php

namespace Pantheon\Terminus\Commands\Upstream\Updates;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;

/**
 * Class ListCommand
 * @package Pantheon\Terminus\Commands\Upstream\Updates
 */
class ListCommand extends UpdatesCommand
{
    /**
     * Displays a list of new code commits available from the upstream for a site's development environment.
     *
     * @authorize
     *
     * @command upstream:updates:list
     *
     * @field-labels
     *     site: Site
     *     env: Environment
     *     hash: Commit ID
     *     datetime: Timestamp
     *     message: Message
     *     author: Author
     * @return RowsOfFields
     *
     * @param string $site_env Site & development environment
     * @option boolean $all Target all sites
     *
     * @usage <site>.<env> Displays a list of new code commits available from the upstream for <site>'s <env> environment.
     * @usage --all Displays a list of new code commits available for every development environment of every site.
     */
    public function listUpstreamUpdates($site_env = null, array $options = ['all' => false,])
    {
        $all = isset($options['all']) ? $options['all'] : false;
        $targets = $this->getUpdateTargets($site_env, $all);

        $data = [];
        foreach ($targets as $env) {
            foreach ($this->getUpstreamUpdatesLog($env) as $commit) {
                $data[] = [
                    'site' => $env->getSite()->get('name'),
                    'env' => $env->id,
                    'hash' => $commit->hash,
                    'datetime' => $commit->datetime,
                    'message' => $commit->message,
                    'author' => $commit->author,
                ];
            }
        }

        // Return the output data.
        return new RowsOfFields($data);
    }
}
