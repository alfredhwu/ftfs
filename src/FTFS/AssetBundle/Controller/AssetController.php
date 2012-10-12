<?php

namespace FTFS\AssetBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class AssetController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/AssetBundle/Asset"
        ));
    }

    public function deviceAddAction(\FTFS\AssetBundle\Entity\Asset $asset)
    {
        $entity = new \FTFS\AssetBundle\Entity\Device;
        $entity->setInstalledAt(new \DateTime('now'));
        $form = $this->createForm(new \FTFS\AssetBundle\Form\DeviceType, $entity);
        /*
        $form = $this->createFormBuilder($entity)
                ->add('serial')
                ->add('product')
                ->add('installed_at', null, array(
                    'widget' => 'single_text',
                ))
                ->add('observation')
                ->getForm()
                ;
         */

        if($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if($form->isValid()) {
                $entity->setAsset($asset);
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();
            }
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $asset->getId(),
            )));
        }

        return $this->render($this->getViewPath().'Device:entry_add_form.html.twig', array(
            'id' => $asset->getId(),
            'prefix' => $this->getRoutingPrefix(),
            'entry_add_form' => $form->createView(),
            'action' => $this->generateUrl($this->getRoutingPrefix().'_device_add', array('id' => $asset->getId())),
        ));
    }

    public function deviceEditAction($device_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $device = $em->getRepository('FTFSAssetBundle:Device')->find($device_id);
        //throw new \Exception($device_id);
        if(!$device) {
            throw $this->createNotFoundException('device not found');
        }
        $asset = $device->getAsset();

        $form = $this->createForm(new \FTFS\AssetBundle\Form\DeviceType, $device);
        /*
        $form = $this->createFormBuilder($device)
                ->add('serial')
                ->add('product')
                ->add('installed_at', null, array(
                    'widget' => 'single_text',
                ))
                ->add('observation')
                ->getForm()
                ;
         */

        if($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if($form->isValid()) {
                $em->flush();
            }
            return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
                'id' => $asset->getId(),
            )));
        }

        return $this->render($this->getViewPath().'Device:entry_add_form.html.twig', array(
            'id' => $asset->getId(),
            'prefix' => $this->getRoutingPrefix(),
            'entry_add_form' => $form->createView(),
            'action' => $this->generateUrl($this->getRoutingPrefix().'_device_edit', array('device_id' => $device_id)),
        ));
    }
    public function deviceDeleteAction($device_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $device = $em->getRepository('FTFSAssetBundle:Device')->find($device_id);
        //throw new \Exception($device_id);
        if(!$device) {
            throw $this->createNotFoundException('device not found');
        }
        $id = $device->getAsset()->getId();
        $em->remove($device);
        $em->flush();

        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_show', array(
            'id' => $id,
        )));
    }
}
