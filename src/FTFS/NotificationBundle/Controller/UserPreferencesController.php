<?php

namespace FTFS\NotificationBundle\Controller;

use merk\NotificationBundle\Controller\UserPreferencesController as BaseController;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Preference Controller
 *
 */
class UserPreferencesController extends BaseController
{
    /**
     * if User Preferences not exist, create an empty one
     *
     * Returns the user preferences object for the supplied user. If no user
     * is supplied, it uses the currently logged in user.
     *
     * @param null|\Symfony\Component\Security\Core\User\UserInterface $user
     * @return \merk\NotificationBundle\Model\UserPreferencesInterface
     */
    protected function getUserPreferences(UserInterface $user = null)
    {
        if (null === $user) {
            $token = $this->container->get('security.context')->getToken();

            if (!$token->getUser() instanceof UserInterface) {
                throw new \RuntimeException('No user found in the security context');
            }

            $user = $token->getUser();
        }

        //return $this->getUserPreferencesManager()->findByUser($user);
        $userPreferences = $this->getUserPreferencesManager()->findByUser($user);
        if(!$userPreferences)
        {
            $userPreferences = $this->getUserPreferencesManager()->create();
            $userPreferences->setUser($user);
        }
        return $userPreferences;
    }
}
