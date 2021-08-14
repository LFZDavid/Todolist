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

    // public function getUserToCreate(): User
    // {
    //     $user = new User();
    //     $user->setEmail("create@test.com");
    //     $user->setUsername("create");
    //     return $user;
    // }

    // public function getUserToEdit(): User
    // {
    //     $user = $this->userRepo
    //         ->findOneBy(
    //             ['username' => 'edit']
    //         );
    //     if(!$user){
    //         $user = new User();
    //         $user->setEmail("edit@test.com");
    //         $user->setUsername("edit");
    //         $user->setPassword('$2y$13$mE8tNa5eKMbf7sWVFKTwbONcV29ZjU.siPJmd9reab4185AIQGwle');//edit
    //         $this->em->persist($user);
    //         $this->em->flush();
    //     }

    //     return $user;
    // }

    /************** Tests *******************/

    public function testIndexGuestRedirectToLogin()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirects();
        $this->assertContains('Redirecting to http://localhost/login',$client->getResponse()->getContent());
    }
}
