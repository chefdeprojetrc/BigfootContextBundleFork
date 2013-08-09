<?php

namespace Bigfoot\Bundle\ContextBundle\Form;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContextualizableEntitiesType extends AbstractType
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entities = array();

        $toMatch = $this->container->getParameter('bigfoot_context_entities');
        $managers = $this->container->get('doctrine')->getManagers();
        foreach ($managers as $manager) {
            foreach($toMatch as $pattern) {
                $pattern = preg_replace('/\\\\+/', '\\\\\\\\', $pattern);
                $entities = array_merge($entities, preg_grep(sprintf('`^%s`', $pattern), $manager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames()));
            }
        }

        $choices = array();
        $doubles = array();
        foreach ($entities as $entity) {
            $shortName = substr(strrchr($entity, '\\'), 1);
            if (in_array($shortName, $choices)) {
                if (!in_array($shortName, $doubles)) {
                    $doubles[] = $shortName;
                    foreach ($choices as $key => $choice) {
                        if ($choice == $shortName) {
                            $choices[$key] = $key;
                            break;
                        }
                    }
                }
            }
            $choices[$entity] = in_array($shortName, $doubles) ? $entity : $shortName;
        }

        asort($choices);

        $builder
            ->add('context', 'text', array(
                'read_only' => true,
            ))
            ->add('entities', 'choice', array(
                'multiple'  => true,
                'choices'   => $choices,
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bigfoot\Bundle\ContextBundle\Entity\ContextualizableEntities'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_contextualizable_entities';
    }
}
