<?php

namespace {{ namespace }}\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
{% if 'annotation' == format -%}
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
{%- endif %}

/**
 * {{ controller }} controller.
 *
{% if 'annotation' == format %}
 * @Route("/")
{% endif %}
 */
class {{ controller }}Controller extends Controller
{
}
