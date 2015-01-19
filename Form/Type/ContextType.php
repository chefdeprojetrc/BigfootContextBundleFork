<?php

namespace Bigfoot\Bundle\ContextBundle\Form\Type;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationReader;

use Bigfoot\Bundle\ContextBundle\Service\ContextService;
use Bigfoot\Bundle\ContextBundle\Manager\ContextManager;

class ContextType extends AbstractType
{
    private $entityManager;
    private $session;
    private $securityContext;
    private $contextService;
    private $contextManager;
    private $contexts;

    /**
     * @param Array $contexts
     */
    public function __construct(EntityManager $entityManager, SessionInterface $session, SecurityContextInterface $securityContext, ContextService $contextService, ContextManager $contextManager)
    {
        $this->entityManager   = $entityManager;
        $this->session         = $session;
        $this->securityContext = $securityContext;
        $this->contextService  = $contextService;
        $this->contextManager  = $contextManager;
        $this->contexts        = $contextService->getContexts();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager   = $this->entityManager;
        $contextManager  = $this->contextManager;
        $contexts        = $options['contexts'];
        $entityClass     = $options['entityClass'];
        $allowedContexts = $this->session->get('bigfoot/context/allowed_contexts');

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($entityManager, $entityClass, $contexts, $allowedContexts) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($data && $contexts) {
                    $entityContexts = $entityManager->getRepository('BigfootContextBundle:Context')->findOneByEntityIdEntityClass($data->getId(), $entityClass);
                    $contextValues  = ($entityContexts) ? $entityContexts->getContextValues() : null;

                    foreach ($contexts as $key => $context) {

                        $constraints = $context->required ? array(new \Symfony\Component\Validator\Constraints\NotBlank()) : array();

                        $data = null;
                        if ($contextValues && isset($contextValues[$context->value])) {
                            $data = $context->multiple ? $contextValues[$context->value] : $contextValues[$context->value][0];
                        }

                        if ($this->securityContext->isGranted('ROLE_ADMIN') or (isset($this->contexts[$context]) && (isset($allowedContexts) && count($allowedContexts[$context->value])))) {
                            $form->add(
                                $context->value,
                                'choice',
                                array(
                                    'choices'     => $this->handleContextValues($allowedContexts, $context->value, $this->contexts[$context->value]['values']),
                                    'data'        => $data,
                                    'multiple'    => $context->multiple,
                                    'mapped'      => false,
                                    'required'    => false,
                                    'constraints' => $constraints,
                                )
                            );
                        }
                    }
                }
            });

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($entityManager, $contextManager, $entityClass, $contexts, $allowedContexts) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($data) {
                    $contextValues   = array();
                    $entityContexts  = $entityManager->getRepository('BigfootContextBundle:Context')->findOneByEntityIdEntityClass($data->getId(), $entityClass);
                    $dbContextValues = ($entityContexts) ? $entityContexts->getContextValues() : null;

                    foreach ($contexts as $key => $context) {
                        if ($this->securityContext->isGranted('ROLE_ADMIN') or (isset($allowedContexts) && count($allowedContexts[$context->value]))) {
                            $contextValues[$context->value] = $form->get($context->value)->getData();

                            foreach ($contextValues as &$contextValue) {
                                if (!is_array($contextValue)) {
                                    $contextValue = array($contextValue);
                                }
                            }

                            if ((!$dbContextValues && $data->getId()) || ($dbContextValues && (array_diff($contextValues[$context->value], $dbContextValues[$context->value]) || array_diff($dbContextValues[$context->value], $contextValues[$context->value])))) {
                                $contextManager->updateContext($data);
                            }
                        }
                    }

                    if (count($contextValues)) {
                        $this->contextService->addToQueue($entityClass, $contextValues);
                    }
                }
            });
    }

    public function handleContextValues($allowedContexts, $context, $contextValues)
    {
        $nContextValues = array();

        foreach ($contextValues as $key => $contextValue) {
            $nContextValues[$contextValue['value']] = $contextValue['label'];
        }

        if ($allowedContexts) {
            foreach ($nContextValues as $key => $value) {
                if (!in_array($key, $allowedContexts[$context]) && !$this->securityContext->isGranted('ROLE_ADMIN')) {
                    unset($nContextValues[$key]);
                }
            }
        }

        return $nContextValues;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'mapped'          => false,
                'auto_initialize' => false,
                'contexts'        => null,
                'entityClass'     => null,
                'label'           => false,
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_context';
    }
}
