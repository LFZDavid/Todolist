<?php

namespace Tests\AppBundle\Controller;

use DateTime;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Nelmio\Alice\Loader\NativeLoader;
use App\DataFixtures\LoadTestFixtures;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
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
        self::bootKernel();
        $this->guestClient = static::createClient();
        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->userRepo = $this->em->getRepository(User::class);
        $this->taskRepo = $this->em->getRepository(Task::class);
        $this->authClient = $this->getAuthenticateClient();
        $this->fixturesLoader = new LoadTestFixtures();
        $this->fixturesLoader->load($this->em);
    }

    public function tearDown(): void
    {
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
        $loader = new NativeLoader();
        $objectSet = $loader->loadFile('src/DataFixtures/user_test.yml');
        $user = $objectSet->getObjects()['user_'.$type];

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
            'PHP_AUTH_PW'   => '',
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
        $loader = new NativeLoader();
        $objectSet = $loader->loadFile('src/DataFixtures/task_test.yml');
        $task = $objectSet->getObjects()['task_'.$type];

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
        $this->assertEquals(Response::HTTP_FOUND, $this->guestClient->getResponse()->getStatusCode());
        $this->guestClient->followRedirects();
        $this->assertContains('Redirecting to http://localhost/login',$this->guestClient->getResponse()->getContent());
    }
    
    public final function testGetHomepageWhileLoggedIn():void
    {
        $this->authClient->request('GET', '/');
        $response = $this->authClient->getResponse();
        /** Authenticate user is not redirected to login page */
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNotEquals(Response::HTTP_FOUND, $response->getStatusCode());
        /** Looking for unique message from homepage */
        $this->assertContains(
            "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !", 
            $response->getContent()
        );
    }
}
