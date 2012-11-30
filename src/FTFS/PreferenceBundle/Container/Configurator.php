<?php

namespace FTFS\PreferenceBundle\Container;

use Doctrine\ORM\EntityManager;
use FTFS\UserBundle\Entity\User;

/**
 *
 */
class Configurator
{
    private $em;
    private $system_config;
    private $user_config;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->system_config = $entityManager->getRepository('FTFSPreferenceBundle:Configuration');
        $this->user_config = $entityManager->getRepository('FTFSPreferenceBundle:UserConfiguration');
    }

    // get config named $name
    // return null if not found
    private function getConfig($name, User $user = null)
    {
        if($user === null) {
            // system default config
            $config = $this->system_config->findOneByName($name);
        }else{
            // user config
            $config = $this->user_config->findOneBy(array(
                'name' => $name,
                'user' => $user->getId(),
            ));
        }
        return $config;
    }

    public function get($name, User $user = null)
    {
        $config = $this->getConfig($name, $user);

        // if no user config, return default config
        if(!$config) {
            $config = $this->getConfig($name);
        }

        // get config value for given name
        if($config) {
            return $config->getValue();
        }
        return null;
    }

    public function set($name, array $value, User $user = null)
    {
        if($user) { // user config setting
            $config = $this->getConfig($name, $user);
            if($config) {
                $config->setValue($value);
            }else{
                $config = new \FTFS\PreferenceBundle\Entity\UserConfiguration;
                $config->setName($name);
                $config->setValue($value);
                $config->setUser($user);
                $this->em->persist($config);
            }
            $this->em->flush();
            return array($name => $this->get($name, $user));
        }else{ // system config setting
            $config = $this->getConfig($name);
            if($config) {
                $config->setValue($value);
            }else{
                $config = new \FTFS\PreferenceBundle\Entity\Configuration;
                $config->setName($name);
                $config->setValue($value);
                $this->em->persist($config);
            }
            $this->em->flush();
            return array($name => $this->get($name));
        }
    }
}
