<?php

namespace Bigfoot\Bundle\ContextBundle\Security\Core\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Doctrine\ORM\EntityManager;

use Bigfoot\Bundle\ContextBundle\Entity\Context;

/**
 * ContextVoter
 */
class ContextVoter implements VoterInterface
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return $class instanceof Context;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object !== 'context') {
            return VoterInterface::ACCESS_DENIED;
        }

        $user    = $token->getUser();
        $context = $this->entityManager->getRepository('BigfootContextBundle:Context')->findOneByEntityIdEntityClass($user->getId(), get_class($user));

        if (!$this->supportsClass($context)) {
            return VoterInterface::ACCESS_DENIED;
        }

        $allowedContexts   = $context->getContextValues();
        $values            = explode('.', $attributes[0]);
        $testedContexts    = array($values[0] => array($values[1]));

        if ($allowedContexts) {
            $contextsIntersect = array_udiff($testedContexts, $allowedContexts, array($this, 'intersectContexts'));

            if (count($contextsIntersect)) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function intersectContexts($chosenContext, $allowedContext)
    {
        return array_intersect($chosenContext, $allowedContext);
    }
}
