<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',TextType::class,[
                'attr'=>
                    ['class'=>'form-control']
            ])
            ->add('email', EmailType::class,[
                'attr'=>
                    ['class'=>'form-control']
            ])
            ->add('password', RepeatedType::class, array(

                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Password',
                    'attr'=>['class'=>'form-control']
                ),
                'second_options' => array('label' => 'Repeat Password',
                    'attr'=>['class'=>'form-control']),
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}