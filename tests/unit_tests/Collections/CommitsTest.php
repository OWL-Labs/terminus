<?php

namespace Pantheon\Terminus\UnitTests\Collections;

use Pantheon\Terminus\Collections\Commits;
use Pantheon\Terminus\Models\Commit;
use Pantheon\Terminus\Models\Environment;

/**
 * Class CommitsTest
 * Testing class for Pantheon\Terminus\Collections\Commits
 * @package Pantheon\Terminus\UnitTests\Collections
 */
class CommitsTest extends CollectionTestCase
{
    /**
     * @var Commits
     */
    protected $commits;
    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->environment = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->commits = new Commits(['environment' => $this->environment,]);
    }

    /**
     * Tests the Commits::filterByReadyToCopy() function when the environment has no parent environment
     */
    public function testFilterByReadyToCopyNoParentEnv()
    {
        $this->environment->expects($this->once())
            ->method('getParentEnvironment')
            ->with()
            ->willReturn(null);

        $this->assertEquals([], $this->commits->getReadyToCopy());
    }

    /**
     * Tests the Commits::filterByReadyToCopy() function when the environment is outdated
     */
    public function testFilterByReadyToCopyOutdated()
    {
        $parent_commits = $this->mockGetCommits();
        $commit = $this->getMockBuilder(Commit::class)
            ->disableOriginalConstructor()
            ->getMock();

        $parent_commits->expects($this->once())
            ->method('all')
            ->with()
            ->willReturn([$commit,]);
        $commit->expects($this->once())
            ->method('get')
            ->with($this->equalTo('labels'))
            ->willReturn([]);

        $this->assertEquals([$commit,], $this->commits->getReadyToCopy());
    }

    /**
     * Tests the Commits::filterByReadyToCopy() function when the environment is up-to-date
     */
    public function testFilterByReadyToCopyUpToDate()
    {
        $this->environment->id = 'env';

        $parent_commits = $this->mockGetCommits();
        $commit = $this->getMockBuilder(Commit::class)
            ->disableOriginalConstructor()
            ->getMock();

        $parent_commits->expects($this->once())
            ->method('all')
            ->with()
            ->willReturn([$commit,]);
        $commit->expects($this->once())
            ->method('get')
            ->with($this->equalTo('labels'))
            ->willReturn([$this->environment->id,]);

        $this->assertEquals([], $this->commits->getReadyToCopy());
    }

    public function testGetURL()
    {
        $this->environment->site = (object)['id' => 'abc',];
        $this->environment->id = 'dev';
        $this->assertEquals('sites/abc/environments/dev/code-log', $this->commits->getUrl());
    }

    /**
     * @return Commits
     */
    protected function mockGetCommits()
    {
        $parent_env = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parent_commits = $this->getMockBuilder(Commits::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->environment->expects($this->once())
            ->method('getParentEnvironment')
            ->with()
            ->willReturn($parent_env);
        $parent_env->expects($this->once())
            ->method('getCommits')
            ->with()
            ->willReturn($parent_commits);

        return $parent_commits;
    }
}
