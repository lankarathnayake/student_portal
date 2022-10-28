<?php

namespace App\Form;

use App\Entity\SubjectGrade;
use App\Entity\Subject;
use App\Entity\Student;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class MarksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mark')
            ->add('student', EntityType::class, [
				'class' => Student::class,
				'choice_label' => 'registrationID'
			])
            ->add('subject', EntityType::class, [
				'class' => Subject::class,
				'choice_label' => 'name'
			])
			->add('save', SubmitType::class, [
				'label'	=> 'Submit',
				'attr' => [
					'class' => 'btn btn-primary w-100'
				]
			])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubjectGrade::class,
        ]);
    }
}
