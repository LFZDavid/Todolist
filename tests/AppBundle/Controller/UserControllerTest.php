<?php

namespace Tests\AppBundle\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\DefaultControllerTest;

class UserControllerTest extends DefaultControllerTest
{
    public function testList():void
    {
        $client = $this->getAuthenticateClient();
        $crawler = $client->request('GET', '/users');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('h1:contains("Liste des utilisateurs")'));
        
        /** Message if user table is empty */
        if(empty($this->userRepo->findAll())){
            $this->assertCount(1, $crawler->filter('div.alert:contains("Il n\'y a pas encore d\'utilisateur enregistré.")'));
        }
        
    }
    
    public function testUserCantAccesList():void
    {
        $client = static::createClient();
        $client->request('GET', '/users');
        $this->assertResponseRedirects();
    }

    public function testWrongSubmitCreateFrom():void
    {
        /** As guest */
        $client = static::createClient();
        /** Get create form */
        $crawler = $client->request('GET', '/users/create');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
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
        $this->assertSelectorExists('span.form-error-icon');
    }

    public function testCreate():void
    {
        /** @var User $userTest */
        $user = $this->getUser('create');

        /** As guest */
        $client = static::createClient();
        /** Get create form */
        $crawler = $client->request('GET', '/users/create');
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            'user[username]' => $user->getUsername(),
            'user[email]' => $user->getEmail(),
            'user[password][first]'  => 'test',
            'user[password][second]'  => 'test',
        ]);
        $crawler = $client->submit($form);
        /** Check for error messages */
        $this->assertEquals(0, $crawler->filter('div.has-error')->count());
        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        /** Check if user is created in db */
        $this->assertTrue(!!$this->userRepo->findOneBy(['username'=>$user->getUsername()]));

    }

    public function testEditFormIsCorrectlyFilled():void
    {
        $user = $this->getUser('edit');
        /** As guest */
        $client = $this->getAuthenticateClient();
        $crawler = $client->request('GET', '/users/'.$user->getId().'/edit');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('h1:contains("Modifier")'));

        /** Check user infos */
        $this->assertContains(
            $user->getUsername(), 
            $crawler->filter("#user_username")->first()->extract('value')
        );
        $this->assertContains(
            $user->getEmail(), 
            $crawler->filter("#user_email")->first()->extract('value')
        );
        /**Roles */
        $this->assertSame(
            in_array("ROLE_ADMIN", $user->getRoles()), 
            !empty($crawler->filter("#user_roles>label>input")->first()->extract('checked'))
        );
    }

    public function testWrongSubmitEditFrom():void
    {
        $user = $this->getUser('admin');
        $client = $this->getAuthenticateClient('admin');
        $crawler = $client->request('GET', '/users/'.$user->getId().'/edit');

        $this->assertResponseIsSuccessful();

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
        $this->assertSelectorExists('span.form-error-icon');
    }
    
    public function testUserCantAccesEdit():void
    {
        $user = $this->getUser('edit');
        $client = static::createClient();
        $client->request('GET', '/users/'.$user->getId().'/edit');
        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testEdit():void
    {
        $user = $this->getUser('edit');
        /** As guest */
        $client = $this->getAuthenticateClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/users/'.$user->getId().'/edit');

        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Modifier');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            'user[username]' => $user->getUsername().'_updated',
            'user[password][first]'  => 'test',
            'user[password][second]'  => 'test',
        ]);

        /** Submit form */
        $crawler = $client->submit($form);
        
        /** Check for error messages */
        $this->assertSelectorNotExists('span.form-error-icon');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert-success');
        /** Check if user is created in db */
        $this->assertTrue(
            !!$this->userRepo->findOneBy(
                ['username'=> $user->getUsername().'_updated']
            )
        );
    }

    public function testUserCantSetRoleOnCreation():void
    {
        $client = $this->getAuthenticateClient('edit');
        $crawler = $client->request('GET','/users/create');
        $this->assertEquals(
            0,
            $crawler->filter('#user_roles')->count()
        );
    }
    
    public function testAdminCanSetRoleOnCreation():void
    {
        $client = $this->getAuthenticateClient('admin');
        $crawler = $client->request('GET','/users/create');
        $this->assertEquals(
            1,
            $crawler->filter('#user_roles')->count()
        );
    }
    
    public function testAdminCanSetRoleOnEdit():void
    {
        $user = $this->getUser('edit');
        $client = $this->getAuthenticateClient('admin');
        $crawler = $client->request('GET','/users/'.$user->getId().'/edit');
        $this->assertSelectorExists('#user_roles');
    }
}