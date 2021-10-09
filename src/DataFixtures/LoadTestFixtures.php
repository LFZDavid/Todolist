<?php

namespace App\DataFixtures;

use Nelmio\Alice\Fixtures;
use Nelmio\Alice\Loader\NativeLoader;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;

/**
 * @codeCoverageIgnore
 */
class LoadTestFixtures extends Fixture implements FixtureInterface
{
    public function load(ObjectManager $manager, $test = false)
    {
        if (!$test) {
            return;
        }
        $loader = new NativeLoader();
        $objectSet = $loader->loadFile('src/DataFixtures/user_test.yml');
        foreach ($objectSet->getObjects() as $user) {
            $manager->persist($user);
        }
        $objectSet = $loader->loadFile('src/DataFixtures/task_test.yml');
        foreach ($objectSet->getObjects() as $task) {
            $manager->persist($task);
        }

        $manager->flush();
    }

}