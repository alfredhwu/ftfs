<?php

namespace FTFS\CrudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


abstract class CrudController extends Controller
{
    protected $vendor;
    protected $bundle;
    protected $entity;

    public function __construct($entityNameSpace)
    {
        $args = preg_split("/\//", $entityNameSpace);
        if(count($args)!=3) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Entity namespace "'.$entityNameSpace.'" cannot be resolved correctly; Entity namespace must be of the form "[vendor]/[bundle]/[entity]".');
        }
        $this->vendor = $args[0];
        $this->bundle = $args[1];
        $this->entity = $args[2];
    }
    protected function getEntity()
    {
        $entityClass = '\\'.$this->vendor.'\\'.$this->bundle.'\\Entity\\'.$this->entity;
        return new $entityClass;
    }

    protected function getEntityType()
    {
        $entityTypeClass = '\\'.$this->vendor.'\\'.$this->bundle.'\\Form\\'.$this->entity.'Type';
        return new $entityTypeClass;
    }

    protected function getPrefix()
    {
        return strtolower($this->vendor.'_'.$this->bundle.'_'.$this->entity);
    }

    protected function getViewIndex()
    {
        return $this->vendor.$this->bundle.':'.$this->entity.':index.html.twig';
    }

    protected function getViewNew()
    {
        return $this->vendor.$this->bundle.':'.$this->entity.':new.html.twig';
    }


    public function indexAction()
    {
        return $this->render($this->getViewIndex());
    }

    public function newAction()
    {

        $entity = $this->getEntity();

        $form = $this->createForm($this->getEntityType(), $entity);

        return $this->render($this->getViewNew(), array(
            'entity' => $entity,
            'form' => $form->createView(),
            'prefix' => $this->getPrefix(),
        ));
    }
}
