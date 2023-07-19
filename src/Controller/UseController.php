<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Apps;
use App\Repository\AppsRepository;
use App\Service\KeycloakHttpRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Security;


#[IsGranted('ROLE_USER')]
#[Route('/Utilisateur')]
class UseController extends AbstractController
{
    #[Route('/Application', name: 'app_user')]
    public function index(
        Security $security,
        AppsRepository $appsRepository,
        Request $request,
        KeycloakHttpRequest $KeycloakHttpRequest,
    ): Response {

        $session = $request->getSession();

        $userClient = $KeycloakHttpRequest->getAllUserClient($KeycloakHttpRequest->getToken(), $session->get('accessToken')['id_token']);
        $clients = [];

        foreach (json_decode(json_encode($userClient))->clientMappings as  $clientName) {
            $clients[] = $clientName->client;
        }
        //  dd($clients);

        $apps = $appsRepository->findByClient($clients);
        //  dd($apps);

        return $this->render('use/index.html.twig', [
            'controller_name' => 'UseController',
            'apps' => $apps,

        ]);
    }

    #[Route('/{id}/detail', name: 'app_detail')]
    public function details(
        Security $security,
        AppsRepository $appsRepository,
        $id
    ): Response {
        $apps = $appsRepository->find($id);


        return $this->render('use/details.html.twig', [
            'controller_name' => 'UseController',
            'apps' => $apps,
        ]);
    }

    #[Route('/{id}/visite', name: 'app_visite')]
    public function visite(Security $security, Apps $app, AppsRepository $appsRepository, $id): Response
    {

        $apps = $appsRepository->findUrl($id);


        return $this->redirect($app->getUrl());
    }
}
