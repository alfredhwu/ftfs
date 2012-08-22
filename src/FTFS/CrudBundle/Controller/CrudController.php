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

    protected function getEntityType($is_show_mode=false)
    {
        $entityTypeClass = '\\'.$this->vendor.'\\'.$this->bundle.'\\Form\\'.$this->entity.'Type';
        $is_show_mode = (! $is_show_mode) ? false : true;
        return new $entityTypeClass($is_show_mode);
    }

    protected function getPrefix()
    {
        return strtolower($this->vendor.'_'.$this->bundle.'_'.$this->entity);
    }

    protected function getNamespace()
    {
        return $this->vendor.$this->bundle.':'.$this->entity;
    }

    public function indexAction()
    {
        $entities = $this->getDoctrine()->getEntityManager()->getRepository($this->getNamespace())->findAll();
        return $this->render($this->getNamespace().':index.html.twig', array('entities' => $entities));
    }

    public function showAction($id)
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('showAction called');
    }

    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository($this->getNamespace())->find($id);

        if(!$entity)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('entity not found');
        }

        $form = $this->createForm($this->getEntityType(), $entity);

        $request = $this->get('request');

        if('POST'===$request->getMethod())
        {
            $form->bindRequest($request);
            if($form->isValid())
            {
                $em->flush();         
                return $this->indexAction();
            }
        }
        return $this->render($this->getNamespace().':edit.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'prefix' => $this->getPrefix(),
        ));
    }

    public function deleteAction($id)
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('deleteAction called');
    }

    public function newAction()
    {
        $entity = $this->getEntity();
        $form = $this->createForm($this->getEntityType(), $entity);

        $request = $this->get('request');

        if('POST'===$request->getMethod())
        {
            $form->bindRequest($request);
            if($form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();         
                return $this->indexAction();
            }
        }
        return $this->render($this->getNamespace().':new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'prefix' => $this->getPrefix(),
        ));
    }
}
