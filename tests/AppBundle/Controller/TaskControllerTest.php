<?php

namespace Tests\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\DefaultControllerTest;

class TaskControllerTest extends DefaultControllerTest
{
    
    public function testList():void
    {
        $crawler = $this->authClient->request('GET','/tasks');
        $response = $this->authClient->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        /** Authenticate user is not redirected to login page */
        $this->assertNotEquals(Response::HTTP_FOUND, $response->getStatusCode());
        
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
            $this->assertEquals(
                Response::HTTP_FOUND, 
                $response->getStatusCode());
            $this->assertContains(
                'Redirecting to http://localhost/login',
                $response->getContent()
            );
        }
    }

    public function testCreate():void
    {
        /** get form with authClient */
        $crawler = $this->authClient->request('GET','/tasks/create');
        
        $response = $this->authClient->getResponse();
        
        /** when auth client isn't redirected */
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        /** Check field are presents*/
        $this->assertEquals(1, $crawler->filter('input#task_title')->count());
        $this->assertEquals(1, $crawler->filter('textarea#task_content')->count());
        $this->assertEquals(1, $crawler->selectButton('Ajouter')->count());

        
        $task = $this->getTask('create');
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            'task[title]' => $task->getTitle(),
            'task[content]' => $task->getContent(),
        ]);

        $crawler = $this->authClient->submit($form);
        /** Check for error messages */
        $this->assertEquals(0, $crawler->filter('div.has-error')->count());

        $response = $this->authClient->getResponse();
        /** Check for success message */
        $this->assertEquals(
            Response::HTTP_FOUND, 
            $response->getStatusCode()
        );

        $crawler = $this->authClient->followRedirect();
        $this->assertContains(
            'La tâche a été bien été ajoutée.',
            $crawler->filter('.alert-success')->text()
        );

    }

    public function testCreateErrorEmpty():void
    {
        /** get form with authClient */
        $crawler = $this->authClient->request('GET','/tasks/create');
        
        $response = $this->authClient->getResponse();
        
        /** when auth client isn't redirected */
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        /** Check field are presents*/
        $this->assertEquals(1, $crawler->filter('input#task_title')->count());
        $this->assertEquals(1, $crawler->filter('textarea#task_content')->count());
        $this->assertEquals(1, $crawler->selectButton('Ajouter')->count());

        
        $task = $this->getTask('create');
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([]); // Empty !

        $crawler = $this->authClient->submit($form);
        /** Check for error messages */
        $this->assertGreaterThan(0, $crawler->filter('div.has-error')->count());

        /** Check for error message */
        $response = $this->authClient->getResponse();
        $this->assertContains(
            '<span class="glyphicon glyphicon-exclamation-sign"></span> Vous devez saisir un titre.',
            $response->getContent()
        );
        $this->assertContains(
            '<span class="glyphicon glyphicon-exclamation-sign"></span> Vous devez saisir du contenu.',
            $response->getContent()
        );
    }
    
    // todo : test getEditForm
    // todo : test edit
    // todo : test edit error (empty)
    // todo : test edit error (not valid)
    // todo : test toggleTask
    // todo : test delete
}