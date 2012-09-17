<?php

namespace FTFS\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use FTFS\UserBundle\Form\DataTransformer\InvitationToCodeTransformer;

/**
 * 
 **/
class InvitationFormType extends AbstractType
{
    private $invitationTransformer;

    function __construct(InvitationToCodeTransformer $invitationTransformer)
    {
        $this->invitationTransformer = $invitationTransformer;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->prependClientTransformer($this->invitationTransformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'FTFS\UserBundle\Entity\Invitation',
            'required' => true,
        ));
    }

    public function getParent(array $options)
    {
        return 'text';
    }

    public function getName()
    {
        return 'ftfs_user_invitation_type';
    }
}
