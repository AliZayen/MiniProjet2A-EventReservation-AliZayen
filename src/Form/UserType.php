<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Full name',
            ])
            ->add('email', EmailType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Admin' => User::TYPE_ADMIN,
                    'Organizer' => User::TYPE_ORGANIZER,
                    'Participant' => User::TYPE_PARTICIPANT,
                ],
            ])
            // Not mapped: we hash it in controller
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Password',
                'help' => 'Set/Change the password (leave empty to keep current).',
                'constraints' => [
                    new Length(min: 6, max: 4096),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

