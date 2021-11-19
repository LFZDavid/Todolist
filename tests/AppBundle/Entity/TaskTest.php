<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{

    /**
     * @return void
     */
    public function testIsDone():void
    {
        $task = new Task();
        $task->setIsDone(true);
        $this->assertTrue($task->isDone());

        $task->setIsDone(false);
        $this->assertFalse($task->isDone());
    }

    /**
     * @return void
     */
    public function testToggle():void
    {
        $task = new Task();

        // Test for done
        $task->toggle(true);
        $this->assertTrue($task->isDone());
        
        // Test for todo
        $task->toggle(false);
        $this->assertFalse($task->isDone());
    }
}