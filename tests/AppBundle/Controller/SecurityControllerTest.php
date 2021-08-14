<?php

namespace Tests\AppBundle\Controller;

use Tests\AppBundle\Controller\DefaultControllerTest;

class SecurityControllerTest extends DefaultControllerTest
{
    public function testIndexGuestRedirectToLogin():void
    {
        return;
    }

    public function loginTest():void
    {
        $crawler = $this->guestClient->request('GET', '/login');
        $this->assertEquals(200, $this->guestClient->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form:contains("Nom d\'utilisateur :")'));
        $this->assertCount(1, $crawler->filter('form:contains("Mot de passe :")'));

    }
}