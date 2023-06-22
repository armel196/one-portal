<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Apps;
use App\Repository\AppsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Security;


#[IsGranted('ROLE_USER')]
class UseController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(Security $security,AppsRepository $appsRepository): Response
    {     
        $apps = $appsRepository->findByRole($security->getUser()->getRoles());   
        // dd($apps);     
        return $this->render('use/index.html.twig', [
            'controller_name' => 'UseController',
            'apps'=>$apps,
        ]);
    }
}
