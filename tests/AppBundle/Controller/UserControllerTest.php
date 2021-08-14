<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    /**
     * @var Client $guestClient
     */
    protected $guestClient;
    
    
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
        $this->userTest = $this->getUserToCreate();
        $this->em = static::$kernel->getContainer()
        ->get('doctrine')
        ->getManager();
        $this->userRepo = $this->em->getRepository(User::class);
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

    public function getUserToCreate(): User
    {
        $user = new User();
        $user->setEmail("create@test.com");
        $user->setUsername("create");
        return $user;
    }

    public function getUserToEdit(): User
    {
        $user = $this->userRepo
            ->findOneBy(
                ['username' => 'edit']
            );
        if(!$user){
            $user = new User();
            $user->setEmail("edit@test.com");
            $user->setUsername("edit");
            $user->setPassword('$2y$13$mE8tNa5eKMbf7sWVFKTwbONcV29ZjU.siPJmd9reab4185AIQGwle');//edit
            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    /************** Tests *******************/

    public function testList()
    {
        $crawler = $this->guestClient->request('GET', '/users');
        $this->assertEquals(200, $this->guestClient->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('h1:contains("Liste des utilisateurs")'));
        
        /** Message if user table is empty */
        if(empty($this->userRepo->findAll())){
            $this->assertCount(1, $crawler->filter('div.alert:contains("Il n\'y a pas encore d\'utilisateur enregistré.")'));
        }
    }
    
    public function testWrongSubmitCreateFrom():void
    {
        /** As guest */
        $client = $this->guestClient;
        /** Get create form */
        $crawler = $client->request('GET', '/users/create');
        $this->assertEquals(200, $this->guestClient->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('h1:contains("Créer un utilisateur")'));

        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            'user[username]' => '',
            'user[email]' => '',
            'user[password][first]'  => '',
            'user[password][second]'  => '',
        ]);
        /** Submit form */
        $crawler = $client->submit($form);

        /** Check for error messages */
        $client->followRedirects();
        $this->assertGreaterThan(0, $crawler->filter('div.has-error')->count());
    }

    public function testCreate():void
    {
        /** @var User $userTest */
        $userTest = $this->getUserToCreate();

        /** As guest */
        $client = $this->guestClient;
        /** Get create form */
        $crawler = $client->request('GET', '/users/create');
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            'user[username]' => $userTest->getUsername(),
            'user[email]' => $userTest->getEmail(),
            'user[password][first]'  => 'test',
            'user[password][second]'  => 'test',
        ]);
        $crawler = $client->submit($form);
        /** Check for error messages */
        $this->assertEquals(0, $crawler->filter('div.has-error')->count());
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        /** Check if user is created in db */
        $createdUser = $this->userRepo->findOneBy(['username'=>'create']);
        $this->assertTrue(!!$createdUser);

    }

    public function testEditFormIsCorrectlyFilled():void
    {
        $user = $this->getUserToEdit();
        /** As guest */
        $client = $this->guestClient;
        $crawler = $client->request('GET', '/users/'.$user->getId().'/edit');
        $this->assertEquals(200, $this->guestClient->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('h1:contains("Modifier")'));

        /** Check user infos */
        $this->assertContains(
            $user->getUsername(), 
            $crawler->filter("#user_username")->first()->extract('value'));
        $this->assertContains(
            $user->getEmail(), 
            $crawler->filter("#user_email")->first()->extract('value'));
    }

    public function testWrongSubmitEditFrom():void
    {
        $user = $this->getUserToEdit();
        /** As guest */
        $client = $this->guestClient;
        $crawler = $client->request('GET', '/users/'.$user->getId().'/edit');

        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Modifier');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            'user[username]' => '',
            'user[email]' => '',
            'user[password][first]'  => '',
            'user[password][second]'  => '',
        ]);
        /** Submit form */
        $crawler = $client->submit($form);

        /** Check for error messages */
        $client->followRedirects();
        $this->assertGreaterThan(0, $crawler->filter('div.has-error')->count());
    }
    
    public function testEdit():void
    {
        $user = $this->getUserToEdit();
        /** As guest */
        $client = $this->guestClient;
        $crawler = $client->request('GET', '/users/'.$user->getId().'/edit');

        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Modifier');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            'user[username]' => $user->getUsername().'_updated',
            'user[password][first]'  => $user->getPassword(),
            'user[password][second]'  => $user->getPassword(),
        ]);
        /** Submit form */
        $crawler = $client->submit($form);
        $client->followRedirects();
        /** Check for error messages */
        $this->assertEquals(0, $crawler->filter('div.has-error')->count());
        /** Check if user is created in db */
        $this->assertTrue(
            !!$this->userRepo->findOneBy(
                ['username'=> $user->getUsername().'_updated']
            )
        );
    }
}