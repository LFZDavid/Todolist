<?php

namespace AppBundle\Security;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TaskVoter extends Voter
{
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports($attr, $subject)
    {
        if(!in_array($attr, [self::EDIT, self::DELETE])){
            return false;
        }

        if(!$subject instanceof Task) {
            return false;
        }
    
        return true;
        
    }

    protected function voteOnAttribute($attribute, $task, TokenInterface $token): bool
    {
        
        $user = $token->getUser();

        if(!$user instanceof User){
            return false;
        }
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($task, $user);
                break;
            case self::DELETE:
                return $this->canDelete($task, $user);
                break;
        }

        throw new \LogicException("This code should not be reached!");
        
    }

    private function canEdit(Task $task, User $user): bool
    {
        return $task->getAuthor() == $user;
    }

    private function canDelete(Task $task, User $user): bool
    {
        return $task->getAuthor() == $user;
    }



}