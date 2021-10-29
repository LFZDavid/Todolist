<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SaveUserListener
{
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    public function prePersist(User $user, LifecycleEventArgs $event): void
    {
        if ($_ENV['APP_ENV'] !== "test") {
            // @codeCoverageIgnoreStart
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
            // @codeCoverageIgnoreEnd
        }
    }
}
