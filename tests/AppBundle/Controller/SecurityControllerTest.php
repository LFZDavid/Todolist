<?php

namespace Tests\AppBundle\Controller;

use Tests\AppBundle\Controller\DefaultControllerTest;

class SecurityControllerTest extends DefaultControllerTest
{
    public function testGetLoginForm():void
    {
        $crawler = $this->guestClient->request('GET', '/login');
        $this->assertEquals(200, $this->guestClient->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form:contains("Nom d\'utilisateur :")'));
        $this->assertCount(1, $crawler->filter('form:contains("Mot de passe :")'));

    }

    public function testLogin():void
    {
        $user = $this->getUser('login');
        $client = $this->guestClient;
        $crawler = $client->request('GET', '/login');
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Se connecter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            '_username' => $user->getUsername(),
            '_password'  => 'test',
        ]);
        $crawler = $client->submit($form);
        
        /** Check content of homepage */
        $client->followRedirect();
        $this->assertContains('Bienvenue sur Todo List,', $client->getResponse()->getContent());
    }

    public function testWrongLogin():void
    {
        $client = $this->guestClient;
        $crawler = $client->request('GET', '/login');
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Se connecter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            '_username' => 'wrongUsername',
            '_password'  => 'wrongPass',
        ]);
        $crawler = $client->submit($form);
        /** Check for error messages */
        $crawler = $client->followRedirect();
        $this->assertGreaterThan(0, $crawler->filter('div.alert-danger')->count());
    }

    public function testWrongLoginWithGoodUsername():void
    {
        $user = $this->getUser('login');
        $client = $this->guestClient;
        $crawler = $client->request('GET', '/login');
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Se connecter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            '_username' => $user->getUsername(),
            '_password'  => 'wrongPass',
        ]);
        $crawler = $client->submit($form);
        /** Check for error messages */
        $crawler = $client->followRedirect();
        $this->assertGreaterThan(0, $crawler->filter('div.alert-danger')->count());
    }

}