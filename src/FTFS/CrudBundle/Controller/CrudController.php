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
            throw $this->createNotFoundException('Entity namespace "'.$entityNameSpace.'" cannot be resolved correctly; Entity namespace must be of the form "[vendor]/[bundle]/[entity]".');
        }
        $this->vendor = $args[0];
        $this->bundle = $args[1];
        $this->entity = $args[2];
    }

    protected function initEntity($entity)
    {
        return $entity;
    }

    protected function getEntity()
    {
        $entityClass = '\\'.$this->vendor.'\\'.$this->bundle.'\\Entity\\'.$this->entity;
        return $this->initEntity(new $entityClass);
    }

    protected function getEntityType(array $options)
    {
        $entityTypeClass = '\\'.$this->vendor.'\\'.$this->bundle.'\\Form\\'.$this->entity.'Type';
        return new $entityTypeClass($options);
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
        return $this->render($this->getNamespace().':index.html.twig', array(
            'entities' => $entities,
            'prefix' => $this->getPrefix(),
        ));
    }

    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository($this->getNamespace())->find($id);

        if(!$entity)
        {
            throw $this->createNotFoundException('Entity not found !');        
        }

        $form = $this->createForm($this->getEntityType(array('view' => 'show')), $entity);

        return $this->render($this->getNamespace().':show.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'prefix' => $this->getPrefix(),
        ));
    }

    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository($this->getNamespace())->find($id);

        if(!$entity)
        {
            throw $this->createNotFoundException('Entity not found !');        
        }

        $form = $this->createForm($this->getEntityType(array( 'view' => 'edit' )), $entity);

        $request = $this->get('request');

        if('POST'===$request->getMethod())
        {
            $form->bindRequest($request);
            if($form->isValid())
            {
                $em->flush();         
                $this->get('session')->setFlash('ftfs.crud.flash.succeed', 'ftfs.crud.flash.updated'); 
                return $this->redirect($this->generateUrl($this->getPrefix().'_show', array('id' => $id)));
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
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository($this->getNamespace())->find($id);

        if(!$entity)
        {
            throw $this->createNotFoundException('Entity not found !');        
        }

        $em->remove($entity);
        $em->flush();

        $this->get('session')->setFlash('ftfs.crud.flash.succeed', 'ftfs.crud.flash.deleted'); 
        return $this->redirect($this->generateUrl($this->getPrefix().'_index'));
    }

    public function newAction()
    {
        $entity = $this->getEntity();
        $form = $this->createForm($this->getEntityType(array('view' => 'new')), $entity);

        $request = $this->get('request');

        if('POST'===$request->getMethod())
        {
            $form->bindRequest($request);
            if($form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();         
                $this->get('session')->setFlash('ftfs.crud.flash.succeed', 'ftfs.crud.flash.created'); 
                return $this->redirect($this->generateUrl($this->getPrefix().'_show', array('id' => $entity->getId())));
            }
        }
        return $this->render($this->getNamespace().':new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'prefix' => $this->getPrefix(),
        ));
    }
}
