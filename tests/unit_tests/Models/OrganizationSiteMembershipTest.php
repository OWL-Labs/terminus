<?php

namespace Pantheon\Terminus\UnitTests\Models;

use League\Container\Container;
use Pantheon\Terminus\Collections\Tags;
use Pantheon\Terminus\Collections\Workflows;
use Pantheon\Terminus\Models\Organization;
use Pantheon\Terminus\Models\OrganizationSiteMembership;
use Pantheon\Terminus\Models\Site;
use Pantheon\Terminus\Models\Workflow;

/**
 * Class OrganizationSiteMembershipTest
 * Testing class for Pantheon\Terminus\Models\Organization
 * @package Pantheon\Terminus\UnitTests\Models
 */
class OrganizationSiteMembershipTest extends ModelTestCase
{
    /**
     * @var OrganizationSiteMembership
     */
    protected $model;
    /**
     * @var Organization
     */
    protected $organization;
    /**
     * @var array
     */
    protected $site_data;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->organization = $this->getMockBuilder(Organization::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->site_data = ['id' => 'site id', 'name' => 'site name', 'label' => 'site label',];

        $this->model = new OrganizationSiteMembership(
            (object)['site' => $this->site_data, 'tags' => (object)[],],
            ['collection' => (object)['organization' => $this->organization,],]
        );
    }

    /**
     * Tests the UserSiteMemberships::__toString() function.
     */
    public function testToString()
    {
        $org_name = 'org name';
        $this->organization->id = '123';
        $this->organization->expects($this->once())
            ->method('getName')
            ->with()
            ->willReturn($org_name);

        $this->assertEquals("{$this->organization->id}: $org_name", (string)$this->model);
    }

    /**
     * Tests the UserSiteMemberships::delete() function.
     */
    public function testDelete()
    {
        $site_data = ['site_id' => '123',];
        $container = new Container();

        $site = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->getMock();
        $site->id = $site_data['site_id'];
        $container->add(Site::class, $site);
        $container->add(Tags::class);

        $workflows = $this->getMockBuilder(Workflows::class)
            ->disableOriginalConstructor()
            ->getMock();
        $workflow = $this->getMockBuilder(Workflow::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->organization->expects($this->once())
            ->method('getWorkflows')
            ->with()
            ->willReturn($workflows);
        $workflows->expects($this->once())
            ->method('create')
            ->with(
                'remove_organization_site_membership',
                ['params' => $site_data,]
            )
            ->willReturn($workflow);

        $this->model->setContainer($container);
        $out = $this->model->delete();
        $this->assertEquals($workflow, $out);
    }

    /**
     * Tests the UserSiteMemberships::getOrganization() function.
     */
    public function testGetOrganization()
    {
        $this->assertEquals($this->organization, $this->model->getOrganization());
    }

    /**
     * Tests the OrganizationSiteMemberships::getReferences() function.
     */
    public function testGetReferences()
    {
        $site = $this->expectGetSite();
        $site->expects($this->once())
            ->method('getReferences')
            ->with()
            ->willReturn($this->site_data);

        $out = $this->model->getReferences();
        $this->assertEquals(array_merge([$this->model->id,], $this->site_data), $out);
    }

    /**
     * Tests the UserSiteMemberships::getSite() function.
     */
    public function testGetSite()
    {
        $site = $this->expectGetSite();
        $this->assertEquals($site, $this->model->getSite());
    }

    /**
     * Prepares the test case for the getSite() function.
     *
     * @return Site The site object getSite() will return
     */
    protected function expectGetSite()
    {
        $container = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->getMock();
        $site = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tags = $this->getMockBuilder(Tags::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container->expects($this->at(0))
            ->method('get')
            ->with(
                $this->equalTo(Site::class),
                $this->equalTo([$this->site_data,])
            )
            ->willReturn($site);
        $container->expects($this->at(1))
            ->method('get')
            ->willReturn($tags);

        $this->model->setContainer($container);
        return $site;
    }
}
