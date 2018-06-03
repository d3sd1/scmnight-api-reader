<?php
namespace RestBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id');
        $builder->add('email', EmailType::class);
        $builder->add('plainPassword'); 
        $builder->add('firstname');
        $builder->add('lastname');
        $builder->add('dni');
        $builder->add('address');
        $builder->add('telephone');
        $builder->add('langCode');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'DataBundle\Entity\User',
            'csrf_protection' => false
        ]);
    }
}