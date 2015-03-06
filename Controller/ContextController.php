<?php

namespace Bigfoot\Bundle\ContextBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

use Bigfoot\Bundle\CoreBundle\Controller\BaseController;

/**
 * Context controller.
 *
 * @Cache(maxage="0", smaxage="0", public="false")
 * @Route("/context")
 */
class ContextController extends BaseController
{
    /**
     * Switch Context.
     *
     * @Route("/switch/{context}/{values}", name="bigfoot_context_switch")
     */
    public function switchAction(Request $request, $context, $values)
    {
        $values         = explode(',', $values);
        $chosenContexts = array($context => $values);
        $user           = $this->getUser();

        if ($this->getContextManager()->isEntityContextualizable(get_class($user), $context)) {
            $context = $this->getEntityManager()->getRepository('BigfootContextBundle:Context')->findOneByEntityIdEntityClass($user->getId(), get_class($user));

            if ($context) {
                $allowedContexts       = $context->getContextValues();
                $contextsIntersect     = array_udiff($chosenContexts, $allowedContexts, array($this, 'intersectContexts'));
                $sessionChosenContexts = $this->getSession()->get('bigfoot/context/chosen_contexts');

                if ($sessionChosenContexts) {
                    $contextsDiff = array_udiff($contextsIntersect, $sessionChosenContexts, array($this, 'diffContexts'));
                }

                if (isset($contextsDiff) && !$contextsDiff) {
                    $this->getSession()->set('bigfoot/context/chosen_contexts', null);
                } else {
                    $this->getSession()->set('bigfoot/context/chosen_contexts', $contextsIntersect);
                }
            } else {
                $this->getSession()->set('bigfoot/context/chosen_contexts', $chosenContexts);
            }
        } else {
            $this->getSession()->set('bigfoot/context/chosen_contexts', $chosenContexts);
        }

        return $this->redirect($request->headers->get('referer'));
    }

    public function intersectContexts($chosenContext, $allowedContext)
    {
        return array_intersect($chosenContext, $allowedContext);
    }

    public function diffContexts($contextsIntersect, $sessionChosenContext)
    {
        $firstDiff  = array_diff($contextsIntersect, $sessionChosenContext);
        $secondDiff = array_diff($sessionChosenContext, $contextsIntersect);

        if (count($firstDiff) || count($secondDiff)) {
            return true;
        }

        return false;
    }
}
