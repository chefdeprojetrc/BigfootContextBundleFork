<?php

namespace Bigfoot\Bundle\ContextBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;

use Bigfoot\Bundle\ContextBundle\Entity\ContextRepository;

class ContextExtension extends AbstractTypeExtension
{
    /**
     * @var ContextRepository
     */
    protected $contextRepository;

    /**
     * Construct ContextExtension
     */
    public function __construct(ContextRepository $contextRepository)
    {
        $this->contextRepository = $contextRepository;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $loader = function (Options $options) {
            if ($options['contextualize'] === true || $options['query_builder'] !== null) {
                $queryBuilder = ($options['contextualize'] === true) ? $this->contextRepository->createContextQueryBuilder($options['class']) : $options['query_builder'];

                return new ORMQueryBuilderLoader(
                    $queryBuilder,
                    $options['em'],
                    $options['class']
                );
            }

            return null;
        };

        $resolver->setOptional(array('contextualize'));

        $resolver->setDefaults(
            array(
                'contextualize' => false,
                'loader'        => $loader,
            )
        );
    }

    public function getExtendedType()
    {
        return 'entity';
    }
}