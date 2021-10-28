<?php

namespace App\DataFixtures;

use Nelmio\Alice\Fixtures;
use Nelmio\Alice\Loader\NativeLoader;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @codeCoverageIgnore
 */
class LoadFixtures extends Fixture implements FixtureInterface
{

    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $loader = new NativeLoader();
        $objectSet = $loader->loadFile('src/DataFixtures/user.yml');
        foreach ($objectSet->getObjects() as $user) {
            $user->setPassword($this->encoder->encodePassword($user, 'test'));
            $manager->persist($user);
        }
        $objectSet = $loader->loadFile('src/DataFixtures/task.yml');
        foreach ($objectSet->getObjects() as $task) {
            $manager->persist($task);
        }
        $manager->flush();
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
