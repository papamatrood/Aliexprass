<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Carrier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder
            ->add('address', EntityType::class, [
                'class'   => Address::class,
                'choices' => $user->getAddresses(),
                'expanded' => true,
                'required' => true,
                'multiple' => false
            ])
            ->add('carrier', EntityType::class, [
                'class' => Carrier::class,
                'expanded' => true,
                'required' => true,
                'multiple' => false
            ])
            ->add('moreInformation', TextType::class, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user' => []
        ]);
    }
}
