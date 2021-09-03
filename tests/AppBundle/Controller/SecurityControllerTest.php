<?php

namespace Tests\AppBundle\Controller;

use Tests\AppBundle\Controller\DefaultControllerTest;

class SecurityControllerTest extends DefaultControllerTest
{
    public function getLoginFormTest():void
    {
        $crawler = $this->guestClient->request('GET', '/login');
        $this->assertEquals(200, $this->guestClient->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form:contains("Nom d\'utilisateur :")'));
        $this->assertCount(1, $crawler->filter('form:contains("Mot de passe :")'));

    }

    public function loginTest():void
    {
        $user = $this->getUser('login');
        $client = $this->guestClient;
        $crawler = $client->request('GET', '/login');
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Se connecter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            '_username' => $user->getUsername(),
            '_password'  => $user->getPassword(),
        ]);
        $crawler = $client->submit($form);
        /** Check for error messages */
        $this->assertEquals(0, $crawler->filter('div.has-error')->count());
        /** Check content of homepage */
        $client->followRedirects();
        $this->assertContains('Bienvenue sur Todo List,', $client->getResponse()->getContent());
    }

    public function wrongLoginTest():void
    {
        $client = $this->guestClient;
        $crawler = $client->request('GET', '/login');
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            '_username' => 'wrongUsername',
            '_password'  => 'wrongPass',
        ]);
        $crawler = $client->submit($form);
        /** Check for error messages */
        $this->assertGreaterThan(0, $crawler->filter('div.has-error')->count());
    }

    public function wrongLoginWithGoodUsernameTest():void
    {
        $user = $this->getUser('login');
        $client = $this->guestClient;
        $crawler = $client->request('GET', '/login');
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            '_username' => $user->getUsername(),
            '_password'  => 'wrongPass',
        ]);
        $crawler = $client->submit($form);
        /** Check for error messages */
        $this->assertGreaterThan(0, $crawler->filter('div.has-error')->count());
    }

}