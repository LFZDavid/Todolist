<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
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

    public function setUp():void
    {
        $this->guestClient = static::createClient();
        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->userRepo = $this->em->getRepository(User::class);
        $this->authClient = $this->getAuthenticateClient();
    }

    public function tearDown(): void
    {
        /** Delete userTest from db */
        $users = $this->userRepo->findBy(
            [
                'username' => [
                    'create',
                    'edit_updated'
                ]
            ]
        );
        
        foreach ($users as $user) {
            $this->em->remove($user);
        }
        $this->em->flush();

        parent::tearDown();
        $this->em->close();
        $this->em = null; // avoid memory leaks
    }

    /************** tools *******************/

    public function getUser(string $type): User
    {
        $user = $this->userRepo->findOneBy(
                ['username' => $type]
            );
        if(!$user){
            $user = new User();
            $user->setEmail($type."@test.com");
            $user->setUsername($type);
            $user->setPassword('$2y$13$UyLMbc7BG.ViQlfaItfD7.piuPWtRxSDIPfTwiBXUSd6v.uzbLTSO');//test
            if($type != 'create') {
                $this->em->persist($user);
                $this->em->flush();
            }
        }

        return $user;
    }

    public function getAuthenticateClient(): Client
    {
        $user = $this->getUser('logged');
        return static::createClient([], [
            'PHP_AUTH_USER' => $user->getUsername(),
            'PHP_AUTH_PW'   => 'test',
        ]);
    }

    /************** Tests *******************/

    public final function testIndexGuestRedirectToLogin():void
    {
        $crawler = $this->guestClient->request('GET', '/');
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
