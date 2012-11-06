<?php

namespace FTFS\SystemBundle\Container;

use Doctrine\ORM\EntityManager;

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
        $this->system_config = $entityManager->getRepository('FTFSSystemBundle:Configuration');
        $this->user_config = $entityManager->getRepository('FTFSSystemBundle:UserConfiguration');
    }

    private function getConfig($name, $user = null)
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

    public function get($name, $user = null)
    {
        $config = $this->getConfig($name, $user);
        if(!$config) {
            // if no user config, return default config
            $config = $this->getConfig($name);
        }

        // get config value for given name
        if($config) {
            return $config->getValue();
        }
        return null;
    }

    public function set($name, $value, $user = null)
    {
        if($user) { // user config setting
            $config = $this->getConfig($name, $user);
            if($config) {
                $config->setValue($value);
            }else{
                $config = new \FTFS\SystemBundle\Entity\UserConfiguration;
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
                $config = new \FTFS\SystemBundle\Entity\Configuration;
                $config->setName($name);
                $config->setValue($value);
                $this->em->persist($config);
            }
            $this->em->flush();
            return array($name => $this->get($name));
        }
    }

}
