<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadTestFixtures;
use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /**
     * @var Client $guestClient
     */
    protected $guestClient;
    
    /**
     * @var Client $authClient
     */
    protected $authClient;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EntityRepository
     */
    protected $userRepo;
    
    /**
     * @var EntityRepository
     */
    protected $taskRepo;

    /**
     * @var LoadTestFixtures
     */
    protected $fixturesLoader;

    public function setUp():void
    {
        parent::setUp();
        $this->guestClient = static::createClient();
        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->userRepo = $this->em->getRepository(User::class);
        $this->taskRepo = $this->em->getRepository(Task::class);
        (new LoadTestFixtures())->load($this->em, true); // Load Fixtures
        
        $this->authClient = $this->getAuthenticateClient();
    }

    public function tearDown(): void
    {
        /** Delete user and task from db */
        $entities = [
            $this->userRepo->findAll(),
            $this->taskRepo->findAll(),
        ];

        array_map(function($embedTypes){
            foreach ($embedTypes as $entity) {
                $this->em->remove($entity);
            }
        },$entities);

        $this->em->flush();

        parent::tearDown();
        $this->em->close();
        $this->em = null; // avoid memory leaks
    }

    /************** tools *******************/

    /**
     * Provide user for tests
     *
     * @param string $type
     * @return User
     */
    public function getUser(string $type): User
    {
        $user = $this->userRepo->findOneBy(
                ['username' => $type]
            );
        if($type == 'create'){
            $user = new User();
            $user->setEmail($type."@test.com");
            $user->setUsername($type);
            $user->setPassword('$2y$13$UyLMbc7BG.ViQlfaItfD7.piuPWtRxSDIPfTwiBXUSd6v.uzbLTSO');//test
        }

        return $user;
    }

    /**
     * Provide authenticate user for tests
     *
     * @return Client
     */
    public function getAuthenticateClient(string $type = 'admin'): Client
    {
        $user = $this->getUser($type);
        return static::createClient([], [
            'PHP_AUTH_USER' => $user->getUsername(),
            'PHP_AUTH_PW'   => 'test',
        ]);
    }

    /**
     * Provide task for tests
     *
     * @param string $type
     * @return Task
     */
    public function getTask(string $type): Task
    {
        $task = $this->taskRepo->findOneBy(['title' => $type]);

        if($type == 'create'){
            $task = new Task();
            $task->setTitle($type);
            $task->setContent("Content of $type task");
            $task->setCreatedAt(new DateTime());
            if($type == 'toToggle'){
                $task->toggle(false);
            }
        }

        return $task;
    }

    /************** Tests *******************/

    public final function testIndexGuestRedirectToLogin():void
    {
        $this->guestClient->request('GET', '/');
        /** Guest is redirected to login page */
        $this->assertEquals(302, $this->guestClient->getResponse()->getStatusCode());
        $this->guestClient->followRedirects();
        $this->assertContains('Redirecting to http://localhost/login',$this->guestClient->getResponse()->getContent());
    }
    
    public final function testGetHomepageWhileLoggedIn():void
    {
        $this->authClient->request('GET', '/');
        $response = $this->authClient->getResponse();
        /** Authenticate user is not redirected to login page */
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals(302, $response->getStatusCode());
        /** Looking for unique message from homepage */
        $this->assertContains(
            "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !", 
            $response->getContent()
        );
    }
}
