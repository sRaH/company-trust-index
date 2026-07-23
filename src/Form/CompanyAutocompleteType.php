<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CompanyAutocompleteType extends TextType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);
        $view->vars['autocomplete_url'] = $options['autocomplete_url'];
        $view->vars['min_length'] = $options['min_length'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'autocomplete_url' => '/companies/search',
            'min_length' => 1,
            'attr' => ['autocomplete' => 'off'],
        ]);

        $resolver->setAllowedTypes('autocomplete_url', 'string');
        $resolver->setAllowedTypes('min_length', 'int');
    }

    public function getBlockPrefix(): string
    {
        return 'company_autocomplete';
    }
}
