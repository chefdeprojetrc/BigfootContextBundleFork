<?php

namespace Bigfoot\Bundle\ContextBundle\Annotation\Bigfoot;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 * @Attributes({
 *     @Attribute("value", required=true, type="string"),
 *     @Attribute("required", required=false, type="boolean"),
 *     @Attribute("multiple", required=false, type="boolean")
 * })
 */
final class Context extends Annotation
{
    /** @var string*/
    public $value;

    /** @var boolean */
    public $required = false;

    /** @var boolean */
    public $multiple = true;
}
