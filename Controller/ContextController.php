<?php

namespace Bigfoot\Bundle\ContextBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Controller\AdminControllerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Context controller.
 *
 * @Route("/admin/context")
 */
class ContextController extends Controller implements AdminControllerInterface
{
    /**
     * @return string Route to be used as the homepage for this controller
     */
    public function getControllerIndex()
    {
        return '';
    }

    /**
     * @return string Title to be used in the BackOffice for routes implemented by this controller
     */
    public function getControllerTitle()
    {
        return 'Context admin';
    }

    /**
     * @Route("/", name="admin_context")
     * @Template("BigfootCoreBundle:crud:index.html.twig")
     */
    public function indexAction()
    {
        $contextsConfig = $this->container->getParameter('bigfoot_contexts');

        foreach ($contextsConfig as $key => $context) {
            $context['id'] = $key;
            $contextsConfig[$key] = $context;
        }

        return array(
            'entities'              => $contextsConfig,
            'edit_route'            => 'admin_context_edit',
            'entity_label_plural'   => 'Contexts',
            'fields'                => array('id' => 'Slug', 'label' => 'Name'),
        );
    }

    /**
     * @Route("/{id}", name="admin_context_edit")
     * @Template("BigfootCoreBundle:crud:edit.html.twig")
     */
    public function editAction($id)
    {
        $managers = $this->getDoctrine()->getManagers();
        foreach ($managers as $manager) {
            $entities = $manager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        }var_dump($entities);
    }
}