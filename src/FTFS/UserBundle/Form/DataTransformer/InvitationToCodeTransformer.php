<?php

namespace FTFS\UserBundle\Form\DataTransformer;

use FTFS\UserBundle\Entity\Invitation;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Transforms an invitaion to an invitation code
 **/
class InvitationToCodeTransformer implements DataTransformerInterface
{
    private $entityManager;
    
    function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($value)
    {
        if(null === $value) {
            return null;
        }

        if(!$value instanceof Invitation) {
            throw new UnexpectedTypeException($value, 'FTFS\UserBundle\Entity\Invitation');
        }

        return $value->getCode();
    }

    public function reverseTransform($value)
    {
        if(null === $value || '' === $value) {
            return null;
        }
         
        if(!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        return $this->entityManager
                    ->getRepository('FTFS\UserBundle\Entity\Invitation')
                    ->findOneBy(array(
                        'code' => $value,
                        'user' => null,
                    ));
    }
}
