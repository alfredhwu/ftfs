<?php

namespace FTFS\CrudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


abstract class CrudController extends Controller
{
    protected $namespaces;

    public function __construct(array $namespaces)
    {
        if(! array_key_exists('model', $namespaces))
        {
            throw $this->createInternalErrorException('error');
        }

        $namespace = $namespaces['model'];
        $this->namespaces['model'] = $this->interprate($namespace);

        if(array_key_exists('controller', $namespaces))
        {
            $namespace = $namespaces['controller'];
        }
        $this->namespaces['controller'] = $this->interprate($namespace);

        if(array_key_exists('view', $namespaces))
        {
            $namespace = $namespaces['view'];
        }
        $this->namespaces['view'] = $this->interprate($namespace);
    }

    private function interprate($namespace)
    {
        $args = preg_split("/\//", $namespace);
        if(count($args)!=3) 
        {
            throw $this->createNotFoundException('Namespace "'.$namespace.'" cannot be resolved correctly; Entity namespace must be of the form "[vendor]/[bundle]/[entity]".');
        }
        return array(
            'vendor' => $args[0],
            'bundle' => $args[1],
            'entity' => $args[2],
        );
    }

    /**
     * get an entity object
     * namespace defined by 'model' entry in constructer
     * by default, \Vendor\Bundle\Entity\EntityName
     */
    protected function getEntity($action, $id = -1)
    {
        if($id == -1)
        {
            $entityClass = '\\'.$this->namespaces['model']['vendor'].'\\'.$this->namespaces['model']['bundle'].'\\Entity\\'.$this->namespaces['model']['entity'];
            return new $entityClass;
        }
        $entity = $this->getDoctrine()
                        ->getEntityManager()
                        ->getRepository($this->getEntityPath())
                        ->find($id);
        if(!$entity)
        {
            throw $this->createNotFoundException('Entity not found !');        
        }
        return $entity;
    }

    /**
     * get an entity type object
     * namespace defined by 'model' entry in constructer
     * by default, \Vendor\Bundle\Form\EntityNameType
     */
    protected function getEntityType(array $options)
    {
        $entityTypeClass = '\\'.$this->namespaces['model']['vendor'].'\\'.$this->namespaces['model']['bundle'].'\\Form\\'.$this->namespaces['model']['entity'].'Type';
        return new $entityTypeClass($options);
    }

    /**
     * get entity rendering path
     * namespace defined by 'model' entry in constructer
     * by default, VendorBundle:Entity => Vendor/Bundle/Entity/
     * usage: VendorBundle:Entity
     */
    protected function getEntityPath()
    {
        return $this->namespaces['model']['vendor'].$this->namespaces['model']['bundle'].':'.$this->namespaces['model']['entity'];
    }

    /**
     * get routing path prefix
     * namespace defined by 'controller' entry in constructer
     * by default, vendor_bundle_entity
     */
    protected function getRoutingPrefix()
    {
        return strtolower($this->namespaces['controller']['vendor'].'_'.$this->namespaces['controller']['bundle'].'_'.$this->namespaces['controller']['entity']);
    }

    /**
     * get view rendering path
     * namespace defined by 'view' entry in constructer
     * by default, VendorBundle:Entity => Vendor/Bundle/Resources/views/Entity
     * usage: VendorBundle:Entity:index.html.twig
     */
    protected function getViewPath()
    {
        return $this->namespaces['view']['vendor'].$this->namespaces['view']['bundle'].':'.$this->namespaces['view']['entity'];
    }

    /**
     * pre init entity while construction
     */
    protected function preInitEntity($entity, $request){
        // ToDo: pre init entity
    }

    /**
     * post init entity while construction
     */
    protected function postInitEntity($entity, $request){
        // ToDo: post init entity
        $this->get('session')->setFlash('ftfs.crud.flash.success', 'ftfs.crud.flash.created.sucess'); 
    }

    /**
     * post update entity
     */
    protected function postUpdateEntity($entity, $request){
        // ToDo: post update entity
        $this->get('session')->setFlash('ftfs.crud.flash.success', 'ftfs.crud.flash.updated.sucess'); 
    }


    /**
     * get entity list for index Action
     */
    protected function getEntityList()
    {
        return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findAll();
    }

    public function indexAction()
    {
        return $this->render($this->getViewPath().':index.html.twig', array(
            'entities' => $this->getEntityList(),
            'prefix' => $this->getRoutingPrefix(),
        ));
    }

    public function showAction($id)
    {
        $entity = $this->getEntity('show', $id);
        $form = $this->createForm($this->getEntityType(array('view' => 'show')), $entity);

        return $this->render($this->getViewPath().':show.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'prefix' => $this->getRoutingPrefix(),
        ));
    }

    public function editAction($id)
    {
        $entity = $this->getEntity('edit', $id);
        $form = $this->createForm($this->getEntityType(array( 'view' => 'edit' )), $entity);
        $request = $this->get('request');

        if('POST'===$request->getMethod())
        {
            $form->bindRequest($request);
            if($form->isValid())
            {
                $this->postUpdateEntity($entity, $request);
                $this->getDoctrine()->getEntityManager()->flush();         
                return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array('id' => $id)));
            }
        }
        return $this->render($this->getViewPath().':edit.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'prefix' => $this->getRoutingPrefix(),
        ));
    }

    public function deleteAction($id)
    {
        $entity = $this->getEntity('delete', $id);

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($entity);
        $em->flush();

        $this->get('session')->setFlash('ftfs.crud.flash.success', 'ftfs.crud.flash.deleted.success'); 
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_index'));
    }

    public function newAction()
    {
        $request = $this->get('request');
        $entity = $this->getEntity('new');
        $this->preInitEntity($entity, $request);

        $form = $this->createForm($this->getEntityType(array('view' => 'new')), $entity);


        if('POST'===$request->getMethod())
        {
            $form->bindRequest($request);
            if($form->isValid())
            {
                $this->postInitEntity($entity, $request);
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();         
                return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array('id' => $entity->getId())));
            }
        }
        return $this->render($this->getViewPath().':new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'prefix' => $this->getRoutingPrefix(),
        ));
    }
}
