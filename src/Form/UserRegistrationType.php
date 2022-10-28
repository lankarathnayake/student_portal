<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Symfony\Component\Form\CallbackTransformer;

class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('password', RepeatedType::class, [
				'type' => PasswordType::class,
				'required' => true,
				'first_options' => ['label' => 'password'],
				'second_options' => ['label' => 'Confirm Password']
			])
			->add('first_login', HiddenType::class , [
				'attr' => [
					'value' => '0',
					'hidden' => true,
				]
			])
            ->add('roles', ChoiceType::class, [
                'required' => true,
                'choices'  => [
                  'Admin' => 'ROLE_ADMIN',
                ],
            ])
            ->add('register', SubmitType::class, [
				'attr' => [
					'class' => 'btn btn-primary w-100'
				]
			]);
			
			// Data transformer
			$builder->get('roles')
			->addModelTransformer(new CallbackTransformer(
				function ($rolesArray) {
					// transform the array to a string
					return count($rolesArray)? $rolesArray[0]: null;
				},
				function ($rolesString) {
					// transform the string back to an array
					return [$rolesString];
				}
			));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
