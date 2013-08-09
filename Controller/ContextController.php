<?php

namespace Bigfoot\Bundle\ContextBundle\Controller;

use Bigfoot\Bundle\ContextBundle\Entity\ContextualizableEntities;
use Bigfoot\Bundle\ContextBundle\Entity\ContextualizableEntity;
use Bigfoot\Bundle\CoreBundle\Controller\AdminControllerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
     * @Method("GET")
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
     * Displays a form to create a new ContextualizableEntities entity.
     *
     * @Route("/new/{context}", name="admin_context_new")
     * @Method("GET")
     * @Template("BigfootCoreBundle:crud:new.html.twig")
     */
    public function newAction($context)
    {
        $entity = new ContextualizableEntities();
        $entity->setContext($context);
        $form = $this->createForm('bigfoot_contextualizable_entities', $entity);

        return array(
            'entity'        => $entity,
            'form'          => $form->createView(),
            'create_route'  => 'admin_context_create',
            'index_route'   => 'admin_context',
            'entity_label'  => 'Contextualizable entities',
        );
    }

    /**
     * Creates a new ContextualizableEntities entity.
     *
     * @Route("/", name="admin_context_create")
     * @Method("POST")
     * @Template("BigfootCoreBundle:crud:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new ContextualizableEntities();
        $form = $this->createForm('bigfoot_contextualizable_entities', $entity);

        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_context'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * @Route("/{id}", name="admin_context_edit")
     * @Method("GET")
     * @Template("BigfootCoreBundle:crud:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BigfootContextBundle:ContextualizableEntities')->findOneBy(array('context' => $id));

        if (!$entity) {
            return $this->redirect($this->generateUrl('admin_context_new', array('context' => $id)));
        }

        $editForm = $this->createForm('bigfoot_contextualizable_entities', $entity);

        return array(
            'entity'        => $entity,
            'edit_form'     => $editForm->createView(),
            'update_route'  => 'admin_context_update',
            'index_route'   => 'admin_context',
            'entity_label'  => 'Contextualizable entities',
        );
    }

    /**
     * Edits an existing ContextualizableEntities entity.
     *
     * @Route("/{id}", name="admin_context_update")
     * @Method("PUT")
     * @Template("BigfootCoreBundle:crud:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BigfootContextBundle:ContextualizableEntities')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException(sprintf('Unable to find %s entity.', 'ContextualizableEntities'));
        }

        $editForm = $this->createForm('bigfoot_contextualizable_entities', $entity);
        $editForm->submit($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_context_edit', array('id' => $entity->getContext())));
        }

        return array(
            'entity'        => $entity,
            'edit_form'     => $editForm->createView(),
        );
    }
}
