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
     * get an entity object
     * namespace defined by 'model' entry in constructer
     * by default, \Vendor\Bundle\Entity\EntityName
     */
    protected function getEntity($action, $id = -1)
    {
        if($id == -1)
        {
            // if $id not set, by default, this is called by new action
            $entityClass = '\\'.$this->namespaces['model']['vendor'].'\\'.$this->namespaces['model']['bundle'].'\\Entity\\'.$this->namespaces['model']['entity'];
            $entity = new $entityClass;
            $this->initNewEntity($entity);
        }else{
            // if $id is set, find the entity related
            $entity = $this->getDoctrine()
                            ->getEntityManager()
                            ->getRepository($this->getEntityPath())
                            ->find($id);
            if(!$entity)
            {
                throw $this->createNotFoundException('Entity not found !');        
            }
        }
        return $entity;
    }

    protected function initNewEntity($entity)
    {
        // Todo: add your new entity init code here
    }

    /**
     * get entity list for index Action
     */
    protected function getEntityList()
    {
        // ToDo: add some filter here
        return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findAll();
    }

    /**
     * notification filter
     * notify the result of an action
     *
     */
    protected function notify($action, $status='success')
    {
        $this->get('session')->setFlash(
            'ftfs.crud.notification.'.$status, 
            $this->getRoutingPrefix().'.notification.action.'.$action.'.'.$status
        ); 
    }

    /**
     * flush entity filter
     * do some post flush auto settings here
     *
     */
    protected function flushEntity($entity, $action, $request=null)
    {
        // Todo add flush optiion for other actions here
        //

        // ToDo add preflushed here
        //
        // flush options for new, edit, delete
        $em = $this->getDoctrine()->getEntityManager();
        switch($action)
        {
            case 'new':
                $em->persist($entity);
                break;
            case 'edit':
                break;
            case 'delete':
                $em->remove($entity);
                break;
        }
        $em->flush();         
        $this->notify($action);

        // Todo add redirect here
    }

    public function indexAction()
    {
        // set index into session
        $request = $this->getRequest();
        $request->getSession()->set('index',$request->getRequestUri());
        $request->getSession()->set('bodymenu',$request->getRequestUri());

        // general twig rendering
        return $this->render($this->getViewPath().':index.html.twig', array(
            'entities' => $this->getEntityList(),
            'prefix' => $this->getRoutingPrefix(),
        ));
    }

    public function showAction($id)
    {
        $entity = $this->getEntity('show', $id);
        $form = $this->createForm($this->getEntityType(array('view' => 'show')), $entity);

        // test notification **********************************************************************
        $actor = $this->get('fos_user.user_manager')->findUserByUsername('agent');
        $subject = $entity;
        $this->container->get('merk_notification.notifier')->trigger('event.key', $subject, 'viewed by', $actor);
        // test notification ***********************************************************************

        return $this->render($this->getViewPath().':show.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'prefix' => $this->getRoutingPrefix(),
        ));
    }

    public function newAction()
    {
        $request = $this->get('request');
        $request->getSession()->set('bodymenu',$request->getRequestUri());

        $entity = $this->getEntity('new');

        $form = $this->createForm($this->getEntityType(array('view' => 'new')), $entity);

        if('POST'===$request->getMethod())
        {
            $form->bindRequest($request);
            if($form->isValid())
            {
                $this->flushEntity($entity, 'new', $request);
                return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_index'));
            }
        }
        return $this->render($this->getViewPath().':new.html.twig', array(
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
                $this->flushEntity($entity, 'edit', $request);
                return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array( 'id' => $entity->getId())));
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
        $this->flushEntity($entity, 'delete');
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_index'));
    }
}
