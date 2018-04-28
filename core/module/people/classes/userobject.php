<?php

namespace Module\People\Classes;

class UserObject
extends \Natty\ORM\EntityObject {
    
    /**
     * Checks whether the user is allowed to perform the given action.
     * @param string $aid ID of the action being performed.
     * @param boolean $throw_exception Whether to throw an exception if the
     * action is denied (instead of returning a FALSE).
     * @return boolean TRUE if allowed, FALSE if denied
     * @throws \Natty\Core\ControllerException
     */
    public function can($aid, $throw_exception = FALSE) {
        return UserHandler::canUser($aid, NULL, $throw_exception);
    }
    
}