<?php

namespace Bigfoot\Bundle\ContextBundle\Form\Type;

use Bigfoot\Bundle\ContextBundle\Entity\ContextRepository;
use Bigfoot\Bundle\ContextBundle\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchIndexException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationReader;

use Bigfoot\Bundle\ContextBundle\Service\ContextService;
use Bigfoot\Bundle\ContextBundle\Manager\ContextManager;

class ContextType extends AbstractType
{
    private $entityManager;
    private $session;

    /** @var AuthorizationChecker $securityAuthorizationChecker */
    private $securityAuthorizationChecker;
    private $contextService;
    private $contextManager;
    private $contexts;

    /**
     * @param EntityManager $entityManager
     * @param SessionInterface $session
     * @param AuthorizationChecker $securityAuthorizationChecker
     * @param ContextService $contextService
     * @param ContextManager $contextManager
     */
    public function __construct(EntityManager $entityManager, SessionInterface $session, AuthorizationChecker $securityAuthorizationChecker, ContextService $contextService, ContextManager $contextManager)
    {
        $this->entityManager   = $entityManager;
        $this->session         = $session;
        $this->securityAuthorizationChecker = $securityAuthorizationChecker;
        $this->contextService  = $contextService;
        $this->contextManager  = $contextManager;
        $this->contexts        = $contextService->getContexts();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Bigfoot\Bundle\ContextBundle\Exception\InvalidConfigurationException
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
                    /** @var ContextRepository $contextRepo */
                    $contextRepo    = $entityManager->getRepository('BigfootContextBundle:Context');
                    $entityContexts = $contextRepo->findOneByEntityIdEntityClass($data->getId(), $entityClass);
                    $contextValues  = ($entityContexts) ? $entityContexts->getContextValues() : null;

                    foreach ($contexts as $key => $context) {
                        $propertyAccessor = new PropertyAccessor();

                        try {
                            $contextValue = $propertyAccessor->getValue($context, '[value]');
                        } catch (NoSuchIndexException $e) {
                            throw new InvalidConfigurationException(sprintf('Contextualized entities configuration should define a value. Check your yml or annotation configuration for class %s.', $entityClass), '02001', $e);
                        }

                        $constraints = isset($context['required']) && $context['required'] ? array(new \Symfony\Component\Validator\Constraints\NotBlank()) : array();

                        $data = null;
                        if ($contextValues && isset($contextValues[$contextValue])) {
                            $data = $context['multiple'] ? $contextValues[$contextValue] : $contextValues[$contextValue][0];
                        }

                        if ($this->securityAuthorizationChecker->isGranted('ROLE_ADMIN') or (isset($this->contexts[$context]) && (isset($allowedContexts) && count($allowedContexts[$contextValue])))) {
                            $form->add(
                                $contextValue,
                                ChoiceType::class,
                                array(
                                    'choices'     => array_flip($this->handleContextValues($allowedContexts, $contextValue, $this->contexts[$contextValue]['values'])),
                                    'data'        => $data,
                                    'multiple'    => isset($context['multiple']) && $context['multiple'],
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
                    /** @var ContextRepository $contextRepo */
                    $contextRepo     = $entityManager->getRepository('BigfootContextBundle:Context');
                    $entityContexts  = $contextRepo->findOneByEntityIdEntityClass($data->getId(), $entityClass);
                    $dbContextValues = ($entityContexts) ? $entityContexts->getContextValues() : null;

                    foreach ($contexts as $key => $context) {
                        if ($this->securityAuthorizationChecker->isGranted('ROLE_ADMIN') or (isset($allowedContexts) && count($allowedContexts[$context['value']]))) {
                            $contextValues[$context['value']] = $form->get($context['value'])->getData();

                            foreach ($contextValues as &$contextValue) {
                                if (!is_array($contextValue)) {
                                    $contextValue = array($contextValue);
                                }
                            }

                            if ((!$dbContextValues && $data->getId()) || ($dbContextValues && (array_diff($contextValues[$context['value']], $dbContextValues[$context['value']]) || array_diff($dbContextValues[$context['value']], $contextValues[$context['value']])))) {
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

    /**
     * @param $allowedContexts
     * @param $context
     * @param $contextValues
     * @return array
     */
    public function handleContextValues($allowedContexts, $context, $contextValues)
    {
        $nContextValues = array();

        foreach ($contextValues as $key => $contextValue) {
            $nContextValues[$contextValue['value']] = $contextValue['label'];
        }

        if ($allowedContexts) {
            foreach ($nContextValues as $key => $value) {
                if (!in_array($key, $allowedContexts[$context]) && !$this->securityAuthorizationChecker->isGranted('ROLE_ADMIN')) {
                    unset($nContextValues[$key]);
                }
            }
        }

        return $nContextValues;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
