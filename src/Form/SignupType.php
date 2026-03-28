<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SignupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Full name',
                'constraints' => [new NotBlank()],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Account type',
                'choices' => [
                    'Participant' => User::TYPE_PARTICIPANT,
                    'Organizer' => User::TYPE_ORGANIZER,
                ],
                'help' => 'Organizer accounts require administrator approval before you can sign in.',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat password'],
                'invalid_message' => 'Passwords must match.',
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 6, max: 4096),
                ],
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Phone number',
                'required' => false,
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Address',
                'required' => false,
                'attr' => ['rows' => 2],
            ])
            ->add('profession', TextType::class, [
                'label' => 'Profession',
                'required' => false,
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date of birth',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('website', TextType::class, [
                'label' => 'Website (organizers)',
                'required' => false,
            ])
            ->add('facebook', TextType::class, [
                'label' => 'Facebook (URL or handle)',
                'required' => false,
            ])
            ->add('instagram', TextType::class, [
                'label' => 'Instagram',
                'required' => false,
            ])
            ->add('whatsapp', TextType::class, [
                'label' => 'WhatsApp',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
