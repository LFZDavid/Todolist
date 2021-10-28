<?php

namespace App\DataFixtures;

use App\Entity\User;
use Nelmio\Alice\Fixtures;
use Nelmio\Alice\Loader\NativeLoader;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @codeCoverageIgnore
 */
class LoadTestFixtures extends Fixture implements FixtureInterface
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager, $test = false)
    {
        if (!$test) {
            return;
        }
        $loader = new NativeLoader();
        $objectSet = $loader->loadFile('src/DataFixtures/user_test.yml');
        foreach ($objectSet->getObjects() as $user) {
            $user->setPassword($this->encoder->encodePassword($user, 'test'));
            $manager->persist($user);
        }
        $objectSet = $loader->loadFile('src/DataFixtures/task_test.yml');
        foreach ($objectSet->getObjects() as $item) {
            if ($item instanceof User){
                $item->setPassword($this->encoder->encodePassword($item, 'test'));
            }
            $manager->persist($item);
        }

        $manager->flush();
    }
}
