<?php

namespace Pantheon\Terminus\UnitTests\Collections;

use Pantheon\Terminus\Collections\OrganizationUserMemberships;
use Pantheon\Terminus\Collections\Workflows;
use Pantheon\Terminus\Models\Organization;

/**
 * Class OrganizationUserMembershipsTest
 * Testing class for Pantheon\Terminus\Collections\OrganizationUserMemberships
 * @package Pantheon\Terminus\UnitTests\Collections
 */
class OrganizationUserMembershipsTest extends CollectionTestCase
{
    public function testCreate()
    {
        $workflows = $this->getMockBuilder(Workflows::class)
            ->disableOriginalConstructor()
            ->getMock();

        $organization = $this->getMockBuilder(Organization::class)
            ->disableOriginalConstructor()
            ->getMock();

        $organization->expects($this->once())
            ->method('getWorkflows')
            ->willReturn($workflows);

        $organization->id = '123';

        $workflows->expects($this->once())
            ->method('create')
            ->with(
                'add_organization_user_membership',
                ['params' => ['user_email' => 'dev@example.com', 'role' => 'team_member']]
            );

        $org_site_membership = new OrganizationUserMemberships(['organization' => $organization]);
        $org_site_membership->create('dev@example.com', 'team_member');
    }
}
