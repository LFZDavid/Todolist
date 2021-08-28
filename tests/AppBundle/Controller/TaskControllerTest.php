<?php

namespace Tests\AppBundle\Controller;

use DateTime;
use Symfony\Component\DomCrawler\Crawler;
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

        /** Check if task has been created in db */
        $persistedTask = $this->taskRepo->findOneBy(['title' => 'create']);
        $this->assertTrue(!!$persistedTask);
        $this->assertSame(
            (new DateTime())->format('Y-m-d H:i'),
            $persistedTask->getCreatedAt()->format('Y-m-d H:i')
        );

        // var_dump($this->authClient);
        /** Check if author is associate to current user*/
        // $this->assertSame(
        //     $this->getUser('logged')->getId(),
        //     $persistedTask->getauthor()->getId()
        // );
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
    
    public function testEdit():void
    {
        $task = $this->getTask('edit');
        $newTitle = $task->getTitle().'_updated';
        $newContent = $task->getContent().' updated!';
        /** get form with authClient */
        $crawler = $this->authClient->request(
            'GET',
            '/tasks/'.$task->getId().'/edit'
        );
        
        $response = $this->authClient->getResponse();
        
        /** when auth client isn't redirected */
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        
        /** Form is correctly filled */
        $this->assertCount(1, $crawler->filter('button:contains("Modifier")'));
        /** Check task infos */
        $this->assertContains(
            $task->getTitle(), 
            $crawler->filter("#task_title")->first()->extract('value'));
        $this->assertContains(
            $task->getContent(), 
            $crawler->filter("#task_content")->text());
        
        
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Modifier');
        
        /** Complete fields */
        $form = $buttonCrawlerNode->form([
            'task[title]' => $newTitle,
            'task[content]' => $newContent,
        ]);

        $crawler = $this->authClient->submit($form);
        /** Check for error messages */
        $this->assertEquals(0, $crawler->filter('div.has-error')->count());

        $response = $this->authClient->getResponse();
        $this->assertEquals(
            Response::HTTP_FOUND, 
            $response->getStatusCode()
        );
        
        /** Check for success message */
        $crawler = $this->authClient->followRedirect();
        $this->assertContains(
            'La tâche a bien été modifiée.',
            $crawler->filter('.alert-success')->text()
        );

        /** Check if task has been updated in db */
        $updatedTask = $this->taskRepo->findOneBy(['title' => $newTitle]);
        $this->assertTrue(!!$updatedTask);
        
    }

    public function testEditErrorEmpty():void
    {
        $task = $this->getTask('edit');
        /** get form with authClient */
        $crawler = $this->authClient->request(
            'GET',
            '/tasks/'.$task->getId().'/edit'
        );
        
        $response = $this->authClient->getResponse();
        
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Modifier');
        
        /** Send empty form */
        $form = $buttonCrawlerNode->form([
            'task[title]' => '',
            'task[content]' => '',
        ]);

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
        
        /** Check if task hasn't been modified in db */
        $task = $this->taskRepo->findOneBy(['title' => 'edit']);
        $this->assertTrue(!!$task);
        
    }
    
    public function testGetEditFromOnTitleClidk():void
    {
        $task = $this->getTask('find');
        /** @var Crawler $crawler */
        $client = $this->authClient;
        $crawler = $client->request('GET','/tasks');
        $link = $crawler->selectLink($task->getTitle())->link();
        $crawler = $client->click($link);
        /** Form is correctly filled */
        $this->assertCount(1, $crawler->filter('button:contains("Modifier")'));
        /** Check task infos */
        $this->assertContains(
            $task->getTitle(), 
            $crawler->filter("#task_title")->first()->extract('value'));
        $this->assertContains(
            $task->getContent(), 
            $crawler->filter("#task_content")->text());
    }

    public function testToggleTask():void
    {
        $task = $this->getTask('toToggle');
        $client = $this->authClient;
        /** Go to task list */
        $crawler = $client->request('GET','/tasks');

        $baseUri = $client->getRequest()->getUri().'/';
        /** Get task toggle form */
        $form = $crawler->selectButton("Marquer comme faite")->reduce(function ($node, $i) use($task, $baseUri){
            if($node->form()->getUri() != $baseUri.$task->getId().'/toggle'){
                return false;
            }
        })->form();

        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertContains(
            'La tâche toToggle a bien été marquée comme faite.',
            $client->getResponse()->getContent()
        );
    }
    
    public function testDelete():void
    {
        $task = $this->getTask('toDelete');
        $client = $this->authClient;
        /** Go to task list */
        $crawler = $client->request('GET','/tasks');

        /** Check if task is present in tasklist*/
        $this->assertEquals(
            1,
            $crawler->filter('a:contains('.$task->getTitle().')')->count()
        );
        
        $baseUri = $client->getRequest()->getUri().'/';
        /** Get task toggle form */
        $form = $crawler->selectButton('Supprimer')->reduce(function ($node, $i) use($task, $baseUri){
            if($node->form()->getUri() != $baseUri.$task->getId().'/delete'){
                return false;
            }
        })->form();

        $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertContains(
            'La tâche a bien été supprimée.',
            $client->getResponse()->getContent()
        );

        /** Check if task isn't in tasklist anymore */
        $crawler = $client->request('GET','/tasks');
        $this->assertEquals(
            0,
            $crawler->filter('a:contains('.$task->getTitle().')')->count()
        );
    }
}