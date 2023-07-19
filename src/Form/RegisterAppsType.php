<?php

namespace App\Form;

use App\Entity\Apps;
use App\Service\KeycloakHttpRequest;
use GuzzleHttp\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\StringType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RegisterAppsType extends AbstractType
{
    public function __construct(private KeycloakHttpRequest $keycloakHttpRequest)
    {
        
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       $clientId = [];
        // dd($this->keycloakHttpRequest->getAllClientOfTheRealm($this->keycloakHttpRequest->getToken()));
        foreach ($this->keycloakHttpRequest->getAllClientOfTheRealm($this->keycloakHttpRequest->getToken()) as $client) {
            
            $clientId[$client['clientId']] = $client['clientId'];
        }
            $builder
                ->add('nom')
                ->add('url')
                ->add('description',TextareaType::class)    
                ->add('client', ChoiceType::class, [
                    'choices' => $clientId,
                    'placeholder' => 'Choisir un client'
                ])
                ->add('imageFile',VichImageType::class,
                [
                  
                ])
                // ->add('save', SubmitType::class)
            ;
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Apps::class,
        ]);
    }
}
