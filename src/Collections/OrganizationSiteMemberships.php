<?php

namespace Pantheon\Terminus\Collections;

use Pantheon\Terminus\Models\Organization;
use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Models\OrganizationSiteMembership;

/**
 * Class OrganizationSiteMemberships
 * @package Pantheon\Terminus\Collections
 */
class OrganizationSiteMemberships extends TerminusCollection
{
    /**
     * @var string
     */
    protected $collected_class = OrganizationSiteMembership::class;
    /**
     * @var Organization
     */
    public $organization;
    /**
     * @var boolean
     */
    protected $paged = true;

    /**
     * Instantiates the collection
     *
     * @param array $options To be set
     * @return OrganizationSiteMemberships
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->organization = $options['organization'];
        $this->url = "organizations/{$this->organization->id}/memberships/sites";
    }

    /**
     * Adds a site to this organization
     *
     * @param Site $site Site object of site to add to this organization
     * @return Workflow
     */
    public function create($site)
    {
        return $this->getOrganization()->getWorkflows()->create(
            'add_organization_site_membership',
            ['params' => ['site_id' => $site->id, 'role' => 'team_member',],]
        );
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return string[]
     */
    public function getReferences()
    {
        return array_merge([$this->id,], $this->getSite()->getReferences());
    }

    /**
     * Retrieves the matching site from model members
     *
     * @param string $site_id ID or name of desired site
     * @return Site $membership->site
     * @throws TerminusException
     */
    public function getSite($site_id)
    {
        if (is_null($membership = $this->get($site_id))) {
            throw new TerminusException(
                'This user is not a member of an organization identified by {id}.',
                ['id' => $site_id,]
            );
        }
        return $membership->getSite();
    }

    /**
     * Determines whether a site is a member of this collection
     *
     * @param Site $site Site to determine membership of
     * @return bool
     */
    public function siteIsMember($site)
    {
        $is_member = !is_null($this->get($site));
        return $is_member;
    }
}
