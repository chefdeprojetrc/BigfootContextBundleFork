<?php

namespace Bigfoot\Bundle\ContextBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

use Bigfoot\Bundle\CoreBundle\Controller\CrudController;
use Bigfoot\Bundle\CoreBundle\Controller\AdminControllerInterface;
use Bigfoot\Bundle\ContextBundle\Entity\ContextualizableEntities;
use Bigfoot\Bundle\ContextBundle\Entity\ContextualizableEntity;

/**
 * Context controller.
 *
 * @Cache(maxage="0", smaxage="0", public="false")
 * @Route("/context")
 */
class ContextController extends CrudController
{
    public function getEntity()
    {
        return 'BigfootContextBundle:ContextualizableEntities';
    }

    public function getName()
    {
        return 'admin_context';
    }

    public function getFields()
    {
        return array(
            'id'    => 'Slug',
            'label' => 'Label',
        );
    }

    protected function getEntityLabelPlural()
    {
        return 'Contextualizable Entities';
    }

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
            'list_items'    => $contextsConfig,
            'list_title'    => $this->getEntityLabelPlural(),
            'list_fields'   => $this->getFields(),
            'actions'       => $this->getActions(),
            // 'globalActions' => $this->getGlobalActions(),
            'breadcrumbs'   => array(
                array(
                    'label' => $this->getEntityLabelPlural(),
                    'url'   => $this->generateUrl($this->getRouteNameForAction('index')),
                )
            ),
        );

        // return array(
        //     'list_items'      => $contextsConfig,
        //     'list_edit_route' => $this->getRouteNameForAction('edit'),
        //     'list_title'      => $this->getEntityLabelPlural(),
        //     'list_fields'     => $this->getFields(),
        //     'breadcrumbs'     => array(
        //         array(
        //             'url'   => $this->container->get('router')->generate($this->getRouteNameForAction('index')),
        //             'label' => $this->getEntityLabelPlural()
        //         ),
        //     ),
        //     'actions' => array(
        //         array(
        //             'href'  => $this->container->get('router')->generate($this->getRouteNameForAction('edit'), array('id' => '__ID__')),
        //             'icon'  => 'pencil',
        //         )
        //     )
        // );
    }

    /**
     * Displays a form to create a new ContextualizableEntities entity.
     *
     * @Route("/new/{context}", name="admin_context_new")
     * @Method("GET")
     * @Template("BigfootCoreBundle:crud:form.html.twig")
     */
    public function newAction($context)
    {
        $entity = new ContextualizableEntities();
        $entity->setContext($context);
        $form = $this->container->get('form.factory')->create('bigfoot_contextualizable_entities', $entity);

        return array(
            'form'          => $form->createView(),
            'form_title'    => sprintf('%s creation', $this->getEntityLabel()),
            'form_action'   => $this->container->get('router')->generate($this->getRouteNameForAction('create')),
            'form_submit'   => 'Create',
            'cancel_route'  => $this->getRouteNameForAction('index'),
            'isAjax'        => $this->container->get('request')->isXmlHttpRequest(),
            'breadcrumbs'       => array(
                array(
                    'url'   => $this->container->get('router')->generate($this->getRouteNameForAction('index')),
                    'label' => $this->getEntityLabelPlural()
                ),
                array(
                    'url'   => $this->container->get('router')->generate($this->getRouteNameForAction('new'), array('context' => $context)),
                    'label' => sprintf('%s creation', $this->getEntityLabel())
                ),
            ),
        );
    }

    /**
     * Creates a new ContextualizableEntities entity.
     *
     * @Route("/", name="admin_context_create")
     * @Method("POST")
     * @Template("BigfootCoreBundle:crud:form.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new ContextualizableEntities();
        $form = $this->container->get('form.factory')->create('bigfoot_contextualizable_entities', $entity);

        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->container->get('doctrine')->getManager();
            $em->persist($entity);
            $em->flush();

            $this->container->get('session')->getFlashBag()->add(
                'success',
                $this->container->get('templating')->render('BigfootCoreBundle:includes:flash.html.twig', array(
                    'icon' => 'ok',
                    'heading' => 'Success!',
                    'message' => sprintf('The %s has been created.', $this->getEntityName()),
                    'actions' => array(
                        array(
                            'route' => $this->container->get('router')->generate($this->getRouteNameForAction('index')),
                            'label' => 'Back to the listing',
                            'type'  => 'success',
                        ),
                    )
                ))
            );

            return new RedirectResponse($this->container->get('router')->generate('admin_context'));
        }

        return array(
            'form'          => $form->createView(),
            'form_title'    => sprintf('%s creation', $this->getEntityLabel()),
            'form_action'   => $this->container->get('router')->generate($this->getRouteNameForAction('create')),
            'form_submit'   => 'Create',
            'cancel_route'  => $this->getRouteNameForAction('index'),
            'isAjax'        => $this->container->get('request')->isXmlHttpRequest(),
            'breadcrumbs'       => array(
                array(
                    'url'   => $this->container->get('router')->generate($this->getRouteNameForAction('index')),
                    'label' => $this->getEntityLabelPlural()
                ),
                array(
                    'url'   => $this->container->get('router')->generate($this->getRouteNameForAction('new')),
                    'label' => sprintf('%s creation', $this->getEntityLabel())
                ),
            ),
        );
    }

    /**
     * @Route("/{id}", name="admin_context_edit")
     * @Method("GET")
     * @Template("BigfootCoreBundle:crud:form.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->container->get('doctrine')->getManager();

        $entity = $em->getRepository('BigfootContextBundle:ContextualizableEntities')->findOneBy(array('context' => $id));

        if (!$entity) {
            return new RedirectResponse($this->container->get('router')->generate('admin_context_new', array('context' => $id)));
        }

        $editForm = $this->container->get('form.factory')->create('bigfoot_contextualizable_entities', $entity);

        return array(
            'form'              => $editForm->createView(),
            'form_method'       => 'PUT',
            'form_action'       => $this->container->get('router')->generate($this->getRouteNameForAction('update'), array('id' => $entity->getId())),
            'form_cancel_route' => $this->getRouteNameForAction('index'),
            'form_title'        => sprintf('%s edit', $this->getEntityLabel()),
            'isAjax'            => $this->container->get('request')->isXmlHttpRequest(),
            'breadcrumbs'       => array(
                array(
                    'url'   => $this->container->get('router')->generate($this->getRouteNameForAction('index')),
                    'label' => $this->getEntityLabelPlural()
                ),
                array(
                    'url'   => $this->container->get('router')->generate($this->getRouteNameForAction('edit'), array('id' => $entity->getId())),
                    'label' => sprintf('%s edit', $this->getEntityLabel())
                ),
            ),
        );
    }

    /**
     * Edits an existing ContextualizableEntities entity.
     *
     * @Route("/{id}", name="admin_context_update")
     * @Method("PUT")
     * @Template("BigfootCoreBundle:crud:form.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->container->get('doctrine')->getManager();

        $entity = $em->getRepository('BigfootContextBundle:ContextualizableEntities')->find($id);

        if (!$entity) {
            throw new NotFoundHttpException(sprintf('Unable to find %s entity.', 'ContextualizableEntities'));
        }

        $editForm = $this->container->get('form.factory')->create('bigfoot_contextualizable_entities', $entity);
        $editForm->submit($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->container->get('session')->getFlashBag()->add(
                'success',
                $this->container->get('templating')->render('BigfootCoreBundle:includes:flash.html.twig', array(
                    'icon' => 'ok',
                    'heading' => 'Success!',
                    'message' => sprintf('The %s has been updated.', $this->getEntityName()),
                    'actions' => array(
                        array(
                            'route' => $this->container->get('router')->generate($this->getRouteNameForAction('index')),
                            'label' => 'Back to the listing',
                            'type'  => 'success',
                        ),
                    )
                ))
            );

            return new RedirectResponse($this->container->get('router')->generate('admin_context_edit', array('id' => $entity->getContext())));
        }

        return array(
            'form'              => $editForm->createView(),
            'form_method'       => 'PUT',
            'form_action'       => $this->container->get('router')->generate($this->getRouteNameForAction('update'), array('id' => $entity->getId())),
            'form_cancel_route' => $this->getRouteNameForAction('index'),
            'form_title'        => sprintf('%s edit', $this->getEntityLabel()),
            'isAjax'            => $this->container->get('request')->isXmlHttpRequest(),
            'breadcrumbs'       => array(
                array(
                    'url'   => $this->container->get('router')->generate($this->getRouteNameForAction('index')),
                    'label' => $this->getEntityLabelPlural()
                ),
                array(
                    'url'   => $this->container->get('router')->generate($this->getRouteNameForAction('edit'), array('id' => $entity->getId())),
                    'label' => sprintf('%s edit', $this->getEntityLabel())
                ),
            ),
        );
    }
}
