<?php

namespace Tests\AppBundle\Controller;

use AppBundle\AppBundle;
use Tests\AppBundle\Controller\DefaultControllerTest;

class TaskControllerTest extends DefaultControllerTest
{
    public function testList():void
    {
        $crawler = $this->authClient->request('GET','/tasks');
        $response = $this->authClient->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        /** Authenticate user is not redirected to login page */
        $this->assertNotEquals(302, $response->getStatusCode());
        
        /** Get total count task */
        $taskCount = count($this->em->getRepository('AppBundle:Task')->findAll());

        /** Looking for as much "delete task btn" as task exists*/
        $this->assertEquals(
            $taskCount, 
            $crawler->filter('button.btn-danger')->count()
        );

        /** If no task in db looking for message */
        if($taskCount == 0){
            $this->assertContains(
                "Il n'y a pas encore de tâche enregistrée.",
                $response->getContent()
            );
        }
        
    }

    public function testGuestCantAccessListOrCreate():void
    {
        $routes = ['/tasks', '/tasks/create'];
        foreach ($routes as $route) {
            $this->guestClient->request('GET', '/tasks');
            $response = $this->guestClient->getResponse();
            /** Authenticate user is redirected to login page */
            $this->assertEquals(302, $response->getStatusCode());
            $this->assertContains(
                'Redirecting to http://localhost/login',
                $response->getContent()
            );
        }
    }

    public function testGetCreateForm():void
    {
        $crawler = $this->authClient->request('GET','/tasks/create');
        $response = $this->authClient->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $crawler->filter('input#task_title')->count());
        $this->assertEquals(1, $crawler->filter('textarea#task_content')->count());
        $this->assertEquals(1, $crawler->selectButton('Ajouter')->count());

    }

}