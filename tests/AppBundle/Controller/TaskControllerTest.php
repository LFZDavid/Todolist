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
        $client = $this->getAuthenticateClient('logged');
        $crawler = $client->request('GET','/tasks');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        /** Get total count task */
        $taskCount = count($this->taskRepo->findAll());
        /** Looking for as much "delete task btn" as task exists*/
        $this->assertEquals(
            $taskCount, 
            $crawler->filter('div.thumbnail>.caption>.pull-right>.glyphicon')->count()
        );

        /** If no task in db looking for message */
        if($taskCount == 0){
            $this->assertSelectorTextContains('alert-warning', "Il n'y a pas encore de tâche enregistrée.");
        }
        
    }

    public function testAuthorIsDisplay():void
    {
        $client = $this->getAuthenticateClient();
        $crawler = $client->request('GET','/tasks');
        $this->assertGreaterThan(0, $crawler->filter('p.author')->count());
    }

    public function testDisplayOnlyToDoTasks():void
    {
        $client = $this->getAuthenticateClient();
        $crawler = $client->request('GET','/tasks_todo');
        $this->assertEquals(0, $crawler->filter('span.glyphicon-ok')->count());
    }

    public function testDisplayOnlyDoneTasks():void
    {
        $client = $this->getAuthenticateClient();
        $crawler = $client->request('GET', '/tasks_done');
        $this->assertEquals(0, $crawler->filter('span.glyphicon-remove')->count());
    }

    public function testGuestCantAccessListOrCreate():void
    {
        $client = static::createClient();
        $routes = ['/tasks', '/tasks/create'];
        foreach ($routes as $route) {
            $client->request('GET', '/tasks');
            $response = $client->getResponse();
            /** Authenticate user is redirected to login page */
            $this->assertResponseRedirects();
        }
    }

    public function testCreate():void
    {
        /** get form with authClient */
        $client = $this->getAuthenticateClient('logged');
        $crawler = $client->request('GET','/tasks/create');
        
        /** when auth client isn't redirected */
        $this->assertResponseIsSuccessful();
        
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

        $crawler = $client->submit($form);
        /** Check for error messages */
        $this->assertEquals(0, $crawler->filter('div.has-error')->count());

        $response = $client->getResponse();
        /** Check for success message */
        $this->assertEquals(
            Response::HTTP_FOUND, 
            $response->getStatusCode()
        );

        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success',"La tâche a été bien été ajoutée.");

        /** Check if task has been created in db */
        $persistedTask = $this->taskRepo->findOneBy(['title' => 'create']);
        $this->assertTrue(!!$persistedTask);
        $this->assertSame(
            (new DateTime())->format('Y-m-d H:i'),
            $persistedTask->getCreatedAt()->format('Y-m-d H:i')
        );

        /** Check if author is associate to current user*/
        $this->assertSame(
            $this->getUser('logged')->getId(),
            $persistedTask->getAuthor()->getId()
        );
    }

    public function testCreateErrorEmpty():void
    {
        /** get form with authClient */
        $client = $this->getAuthenticateClient();
        $crawler = $client->request('GET','/tasks/create');
        
        $response = $client->getResponse();
        
        /** when auth client isn't redirected */
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        /** Check field are presents*/
        $this->assertEquals(1, $crawler->filter('input#task_title')->count());
        $this->assertEquals(1, $crawler->filter('textarea#task_content')->count());
        $this->assertEquals(1, $crawler->selectButton('Ajouter')->count());

        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Ajouter');
        /** Fill fields */
        $form = $buttonCrawlerNode->form([
            'task[title]' => '',
            'task[content]' => '',
        ]); // Empty !

        $crawler = $client->submit($form);

        /** Check for error message */
        $this->assertSelectorTextContains('label[for*="task_title"]>.invalid-feedback>span>.form-error-message', "Vous devez saisir un titre.");
        
        $this->assertSelectorTextContains('label[for*="task_content"]>.invalid-feedback>span>.form-error-message', "Vous devez saisir du contenu.");
        
    }
    
    public function testEdit():void
    {
        $task = $this->getTask('edit');
        $newTitle = $task->getTitle().'_updated';
        $newContent = $task->getContent().' updated!';
        /** get form with authClient */
        $client = $this->getAuthenticateClient();
        $crawler = $client->request(
            'GET',
            '/tasks/'.$task->getId().'/edit'
        );

        $response = $client->getResponse();
        /** when auth client isn't redirected */
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        
        /** Form is correctly filled */
        $this->assertCount(1, $crawler->filter('button:contains("Modifier")'));
        /** Check task infos */
        $this->assertContains(
            $task->getTitle(), 
            $crawler->filter("#task_title")->first()->extract(['value']));
        $this->assertSelectorTextContains('#task_content', $task->getContent());
        
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Modifier');
        
        /** Complete fields */
        $form = $buttonCrawlerNode->form([
            'task[title]' => $newTitle,
            'task[content]' => $newContent,
        ]);

        $crawler = $client->submit($form);
        /** Check for error messages */
        $this->assertEquals(0, $crawler->filter('div.has-error')->count());

        $response = $client->getResponse();
        $this->assertEquals(
            Response::HTTP_FOUND, 
            $response->getStatusCode()
        );
        
        /** Check for success message */
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "La tâche a bien été modifiée.");

        /** Check if task has been updated in db */
        $updatedTask = $this->taskRepo->findOneBy(['title' => $newTitle]);
        $this->assertTrue(!!$updatedTask);
        
    }

    public function testEditErrorEmpty():void
    {
        $task = $this->getTask('edit');
        /** get form with authClient */
        $client = $this->getAuthenticateClient();
        $crawler = $client->request(
            'GET',
            '/tasks/'.$task->getId().'/edit'
        );
        
        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        
        /** Select form */
        $buttonCrawlerNode = $crawler->selectButton('Modifier');
        /** Send empty form */
        $form = $buttonCrawlerNode->form([
            'task[title]' => '',
            'task[content]' => '',
        ]);

        $crawler = $client->submit($form);
        /** Check for error message */
        $this->assertSelectorTextContains('label[for*="task_title"]>.invalid-feedback>span>.form-error-message', "Vous devez saisir un titre.");
        
        $this->assertSelectorTextContains('label[for*="task_content"]>.invalid-feedback>span>.form-error-message', "Vous devez saisir du contenu.");
        
        /** Check if task hasn't been modified in db */
        $task = $this->taskRepo->findOneBy(['title' => 'edit']);
        $this->assertTrue(!!$task);
        
    }
    
    public function testGetEditFromOnTitleClidk():void
    {
        $task = $this->getTask('find');
        /** @var Crawler $crawler */
        $client = $this->getAuthenticateClient();
        $crawler = $client->request('GET','/tasks');
        $link = $crawler->selectLink($task->getTitle())->link();
        $crawler = $client->click($link);
        /** Form is correctly filled */
        $this->assertCount(1, $crawler->filter('button:contains("Modifier")'));
        /** Check task infos */
        $this->assertContains(
            $task->getTitle(), 
            $crawler->filter("#task_title")->first()->extract(['value']));
        $this->assertSelectorTextContains('#task_content', $task->getContent());
    }

    public function testToggleTask():void
    {
        $task = $this->getTask('toToggle');
        $client = $this->getAuthenticateClient();
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
        $this->assertSelectorTextContains('.alert-success',"La tâche toToggle a bien été marquée comme faite.");
    }
    
    public function testDelete():void
    {
        $user = $this->getUser('author');
        $task = $this->taskRepo->findOneBy(['author' => $user->getId()]);
        $client = $this->getAuthenticateClient($user->getUsername());
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

        /** Check if task isn't in tasklist anymore */
        $crawler = $client->request('GET','/tasks');
        $this->assertEquals(
            0,
            $crawler->filter('a:contains('.$task->getTitle().')')->count()
        );
    }

    /**
     * display delete btn only if current user can delete task
     *
     * @return void
     */
    public function testDeleteBtnDisplayForAuthor():void
    {
        $user = $this->getUser('author');
        $author_task = $this->taskRepo->findOneBy(['author' => $user->getId()]);
        $client = $this->getAuthenticateClient($user->getUsername());
        $crawler = $client->request('GET','/tasks');
        $baseUri = $client->getRequest()->getUri().'/';
        
        /** check author's task */
        $btns = $crawler->selectButton('Supprimer')->reduce(function ($node, $i) use($author_task, $baseUri){
            if($node->form()->getUri() != $baseUri.$author_task->getId().'/delete'){
                return false;
            }
        });
        $this->assertGreaterThan(0,$btns->count());

        $else_task = $this->getTask('withoutAuthor');
        /** check else's task */
        $btns = $crawler->selectButton('Supprimer')->reduce(function ($node, $i) use($else_task, $baseUri){
            if($node->form()->getUri() != $baseUri.$else_task->getId().'/delete'){
                return false;
            }
        });
        $this->assertEquals(0,$btns->count());
    }

    public function testDeleteBtnDisplayForAdmin():void
    {
        $user = $this->getUser('admin');
        $client = $this->getAuthenticateClient($user->getUsername());
        $crawler = $client->request('GET','/tasks');
        $btns = $crawler->selectButton('Supprimer');
        /** Check that every task has a btn */
        $this->assertEquals(
            $btns->count(),
            $crawler->filter('div.thumbnail')->count()
        );
    }

    public function testAuthorCanDeleteOwnTask():void
    {
        $user = $this->getUser('author');
        $task = $this->taskRepo->findOneBy(['author' => $user->getId()]);
        $client = $this->getAuthenticateClient($user->getUsername());
        $client->request('POST','/tasks/'.$task->getId().'/delete');
        
        
        /** Check if task is deleted from in db */
        $crawler = $client->request('GET','/tasks');
        $this->assertEquals(
            0,
            $crawler->filter('a:contains('.$task->getTitle().')')->count()
        );
    }

    public function testUserCannotDeleteSomeoneElseTask():void
    {
        $task = $this->getTask('toDelete');
        $client = $this->getAuthenticateClient('Logged');
        $crawler = $client->request('POST','/tasks/'.$task->getId().'/delete');
        
        /** Check if task still present in db */
        $crawler = $client->request('GET','/tasks');
        $this->assertGreaterThan(
            0,
            $crawler->filter('a:contains('.$task->getTitle().')')->count()
        );
        
    }

    public function testAdminCanDeleteAnyTask():void
    {
        $user = $this->getUser('admin');
        $task = $this->getTask('with_author');
        $client = $this->getAuthenticateClient($user->getUsername());
        $client->request('POST','/tasks/'.$task->getId().'/delete');
        
        /** Check if task is deleted from in db */
        $crawler = $client->request('GET','/tasks');
        $this->assertEquals(
            0,
            $crawler->filter('a:contains('.$task->getTitle().')')->count()
        );
    }

    public function testAdminCanDeleteAnonymusTask():void
    {
        $user = $this->getUser('admin');
        $task = $this->getTask('withoutAuthor');
        $client = $this->getAuthenticateClient($user->getUsername());
        $client->request('POST','/tasks/'.$task->getId().'/delete');
        
        /** Check if task is deleted from in db */
        $crawler = $client->request('GET','/tasks');
        $this->assertEquals(
            0,
            $crawler->filter('a:contains('.$task->getTitle().')')->count()
        );
    }
}