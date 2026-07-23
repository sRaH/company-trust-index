<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Review;
use App\Validator\Constraints\NoHtml;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<Review>
 */
class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', CompanyAutocompleteType::class, [
                'label' => 'form.review.company_name',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 255),
                    new NoHtml(),
                ],
            ])
            ->add('rating', HiddenType::class)
            ->add('reviewText', TextareaType::class, [
                'label' => 'form.review.review_text',
            ])
            ->add('authorEmail', EmailType::class, [
                'label' => 'form.review.author_email',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
            'translation_domain' => 'messages',
        ]);
    }
}
