<?php

namespace Pantheon\Terminus\Collections;

use Pantheon\Terminus\Exceptions\TerminusNotFoundException;
use Pantheon\Terminus\Models\Organization;
use Pantheon\Terminus\Models\OrganizationSiteMembership;
use Pantheon\Terminus\Models\SiteOrganizationMembership;
use Pantheon\Terminus\Models\Workflow;

/**
 * Class SiteOrganizationMemberships
 * @package Pantheon\Terminus\Collections
 */
class SiteOrganizationMemberships extends SiteOwnedCollection
{
    /**
     * @var string
     */
    protected $collected_class = SiteOrganizationMembership::class;
    /**
     * @var boolean
     */
    protected $paged = true;
    /**
     * @var string
     */
    protected $url = 'sites/{site_id}/memberships/organizations';

    /**
     * Adds this org as a member to the site
     *
     * @param Organization $organization Organization being added as a site member
     * @param string $role Role for supporting organization to take
     * @return Workflow
     **/
    public function create(Organization $organization, $role)
    {
        return $this->getSite()->getWorkflows()->create(
            'add_site_organization_membership',
            ['params' => ['organization_name' => $organization->getName(), 'role' => $role,],]
        );
    }

    /**
     * Retrieves the model with organization of the given UUID or name
     *
     * @param string $id UUID, label, or name of desired site membership instance
     * @return OrganizationSiteMembership
     */
    public function get($id)
    {
        $models = $this->getMembers();
        if (isset($models[$id])) {
            return $models[$id];
        } else {
            foreach ($models as $membership) {
                $org = $membership->getOrganization();
                if (in_array($id, [$org->id, $org->getName(), $org->getLabel(),])) {
                    return $membership;
                }
            }
        }
        throw new TerminusNotFoundException(
            'Could not find an association for {org} organization with {site}.',
            ['org' => $id, 'site' => $this->site->getName(),]
        );
    }
}
