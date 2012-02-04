<?php

namespace Acme\Hello\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Symfony\Component\Validator\Constraints\Collection as CollectionConstraint;
use Symfony\Component\Validator\Constraints\NotNull;

class ExampleType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('text', 'text', array(
                'label'    => 'Text input',
                'required' => false
            ))
            ->add('integer', 'integer', array(
                'label'    => 'Integer input',
                'required' => false
            ))
            ->add('money', 'money', array(
                'label'    => 'Money input',
                'required' => false
            ))
            ->add('percent', 'percent', array(
                'label'    => 'Percent input',
                'required' => false,
            ))
            ->add('search', 'search', array(
                'label'    => 'Search input',
                'required' => false,
            ))
            ->add('date', 'date', array(
                'label'    => 'Date input',
                'required' => false
            ))
            ->add('time', 'time', array(
                'label'    => 'Time input',
                'required' => false
            ))
            ->add('datetime', 'datetime', array(
                'label'    => 'Datetime input',
                'required' => false
            ))
            ->add('checkbox', 'checkbox', array(
                'label'    => 'Checkbox',
                'required' => false
            ))
            ->add('select', 'choice', array(
                'label'    => 'Select list',
                'choices'  => array(
                    'something', 2, 3, 4, 5
                ),
                'required' => false
            ))
            ->add('expanded_select', 'choice', array(
                'label'    => 'Expanded choice list',
                'expanded' => true,
                'choices'  => array(
                    1 => 'Uno', 2 => 'Duo', 3, 4, 5
                ),
                'required' => false
            ))
            ->add('multiselect', 'choice', array(
                'label'    => 'Multicon-select',
                'multiple' => true,
                'choices'  => array(
                    1, 2, 3, 4, 5
                ),
                'required' => false
            ))
            ->add('expanded_multiselect', 'choice', array(
                'label'    => 'Multicon-select',
                'multiple' => true,
                'expanded' => true,
                'choices'  => array(
                    1 => 'Uno', 2 => 'Duo', 3, 4, 5
                ),
                'required' => false
            ))
            ->add('file', 'file', array(
                'label'    => 'File input',
                'required' => false
            ))
            ->add('area', 'textarea', array(
                'label'    => 'Textarea',
                'required' => false
            ))
        ;
    }

    public function getDefaultOptions(array $defaults)
    {
        return array(
            'validation_constraint' => new CollectionConstraint(array(
                'text' => array(
                    new NotNull(array('message' => 'Text field is required'))
                ),
                'integer' => array(
                    new NotNull(array('message' => 'Integer field is required'))
                ),
                'money' => array(
                    new NotNull(array('message' => 'Money field is required'))
                ),
                'percent' => array(
                    new NotNull(array('message' => 'Percent field is required'))
                ),
                'search' => array(
                    new NotNull(array('message' => 'Search field is required'))
                ),
                'date' => array(
                    new NotNull(array('message' => 'Date field is required'))
                ),
                'time' => array(
                    new NotNull(array('message' => 'Time field is required'))
                ),
                'datetime'             => array(),
                'checkbox'             => array(),
                'select'               => array(),
                'expanded_select'      => array(),
                'multiselect'          => array(),
                'expanded_multiselect' => array(),
                'file'                 => array(),
                'area'                 => array(),
            ))
        );
    }

    public function getName()
    {
        return 'example';
    }
}
