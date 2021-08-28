<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;

/**
 * @codeCoverageIgnore
 */
class LoadFixtures implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        Fixtures::load(
            __DIR__.'/task.yml',
            $manager,
            [
                'providers' => [$this]
            ]);
        
        Fixtures::load(
            __DIR__.'/user.yml',
            $manager
        );
    }

    public function nameForTests()
    {
        $genera = [
            'Dockerize project',
            'Write test',
            'Add fixs',
            'Add fixtures',
            'Make test coverage report',
            'Make perf report',
            'Prepare migration',
            'Make migration'
        ];

        $key = array_rand($genera);

        return $genera[$key];
    }
}