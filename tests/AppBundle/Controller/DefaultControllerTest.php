<?php

namespace Tests\AppBundle\Controller;

use DateTime;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use App\DataFixtures\LoadTestFixtures;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser as Client;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
        $container = self::$container;
        $this->em = $container->get('doctrine')->getManager();
        $this->userRepo = $container->get('doctrine')->getRepository(User::class);
        $this->taskRepo = $container->get('doctrine')->getRepository(Task::class);
        $this->fixturesLoader = new LoadTestFixtures($container->get(UserPasswordEncoderInterface::class));
        $this->fixturesLoader->load($this->em, true);
        self::ensureKernelShutdown();
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
        return static::createClient([], [
            'PHP_AUTH_USER' => $type,
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
        $client = static::createClient();
        $client->request('GET', '/');
        $client->followRedirects();
        /** Guest is redirected to login page */
        $this->assertResponseRedirects('/login');
    }

    public final function testGetHomepageWhileLoggedIn():void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => "logged",
            'PHP_AUTH_PW'   => 'test',
        ]);
        $client->request('GET', '/');
        /** Authenticate user is not redirected to login page */
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        /** Looking for unique message from homepage */
        $this->assertSelectorTextContains('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");
    }
}
