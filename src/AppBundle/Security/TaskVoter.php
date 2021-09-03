<?php

namespace AppBundle\Security;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class TaskVoter extends Voter
{
    const DELETE = 'delete';

    protected function supports($attr, $subject)
    {
        if(!in_array($attr, [self::DELETE])){
            return false;
        }

        if(!$subject instanceof Task) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }
    
        return true;
        
    }

    protected function voteOnAttribute($attribute, $task, TokenInterface $token): bool
    {
        
        $user = $token->getUser();

        
        if(!$user instanceof User){
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        switch ($attribute) {
            case self::DELETE:
                return $this->canDelete($task, $user);
                break;
        }
        // @codeCoverageIgnoreStart
        throw new \LogicException("This code should not be reached!");
        // @codeCoverageIgnoreEnd
        
    }

    private function canDelete(Task $task, User $user): bool
    {
        return $task->getAuthor() == $user;
    }



}