<?php

namespace Tests\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\DefaultControllerTest;

class SecurityControllerTest extends DefaultControllerTest
{
    public function testGetLoginForm():void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form>input#username');
        $this->assertSelectorExists('form>input#password');
    }

    public function testLogin():void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $client->followRedirects();
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Se connecter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form();
        $form['username'] = 'login';
        $form['password'] = 'test';
        $crawler = $client->submit($form);

        /** No login error */
        $this->assertSelectorNotExists('div.alert-danger');

        /** Check content of homepage */
        $this->assertSelectorTextContains('.pull-right.btn-danger', 'Se déconnecter');
        $this->assertSelectorTextContains('h1', "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !");
    }

    public function testWrongLogin():void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $client->followRedirects();
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Se connecter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            'username' => 'wrongUsername',
            'password'  => 'wrongPass',
        ]);
        $crawler = $client->submit($form);
        /** Check for error messages */
        $this->assertSelectorExists('div.alert-danger');
    }

    public function testWrongLoginWithGoodUsername():void
    {
        $user = $this->getUser('login');
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $client->followRedirects();
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Se connecter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            'username' => $user->getUsername(),
            'password'  => 'wrongPass',
        ]);
        $crawler = $client->submit($form);
        /** Check for error messages */
        $this->assertSelectorExists('div.alert-danger');
    }

}