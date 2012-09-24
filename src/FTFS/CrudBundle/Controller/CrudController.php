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
     * register the event of access 
     *
     */
    protected function registerEvent($action, $entity, $verb=null)
    {
        // register event
        $actor = $this->get('security.context')->getToken()->getUser();
        switch($action) {
            default:
                $eventkey = 'event.'.$action;
                if(!$verb) {
                    $verb = $action;
                }
        }
        //$this->container->get('merk_notification.notifier')->trigger($eventkey, $entity, $verb, $actor);
    }

    /**
     * notification filter
     * notify the result of an action by flashing
     *
     */
    protected function notify($action, $status='success')
    {
        // flashing message
        $this->get('session')->setFlash(
            'ftfs.crud.notification.'.$status, 
            $this->getRoutingPrefix().'.notification.action.'.$action.'.'.$status
        ); 
    }

    /**
     * flush entity filter
     * do some post flush auto settings here
     * for new, edit, delete, and other user defined actions
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

        // register access event
        $this->registerEvent($action, $entity);

        // notify by flashing message
        $this->notify($action);

        // Todo add redirect here
    }

    public function indexAction()
    {
        // set index into session
        $request = $this->getRequest();
        $request->getSession()->set('index', $request->getRequestUri());

        // general twig rendering
        return $this->render($this->getViewPath().':index.html.twig', array(
            'entities' => $this->getEntityList(),
            'prefix' => $this->getRoutingPrefix(),
        ));
    }

    public function showAction($id)
    {
        // test config
        //
        //throw new \Exception($this->container->getParameter('ftfs_notification.foo').' '.$this->container->getParameter('ftfs_notification.bar'));

        /** test notification ...... **/
        /**
        $current_user = $this->get('security.context')->getToken()->getUser();

        $action = array();
        $action['name'] = 'show';
        $entity = $this->getEntity('show', $id);
        $metadata = $this->getDoctrine()->getEntityManager()->getClassMetadata(get_class($entity));
        $action['serviceticket_class'] = $metadata->getName();
        $action['serviceticket_id'] = $metadata->getIdentifierValues($entity);

        $this->get('ftfs_notification.notifier.event_registration_notifier')->register('event.serviceticket.show', $current_user, $action);
        */
        //
        //
        //$this->registerEvent('show', $entity);
        $entity = $this->getEntity('show', $id);
        return $this->render($this->getViewPath().':show.html.twig', array(
            'entity' => $entity,
            'prefix' => $this->getRoutingPrefix(),
        ));
    }

    public function newAction()
    {
        $request = $this->get('request');
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
