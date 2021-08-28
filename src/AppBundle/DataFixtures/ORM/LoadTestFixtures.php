<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;

/**
 * @codeCoverageIgnore
 */
class LoadTestFixtures implements FixtureInterface
{
    public function load(ObjectManager $manager, $test = false)
    {
        if (!$test) {
            return;
        }
        Fixtures::load(
            __DIR__.'/task_test.yml',
            $manager,
            [
                'providers' => [$this]
            ]);
        
        Fixtures::load(
            __DIR__.'/user_test.yml',
            $manager
        );
    }

}