<?php

namespace App\Controller;

use App\Entity\Apps;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\StringType;
use  App\Form\RegisterAppsType;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use App\Repository\AppsRepository;
use App\Service\KeycloakHttpRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private KeycloakHttpRequest $keycloakHttpRequest
    ) {
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     *
     * @Route("/admin/register")
     */

    public function register(ManagerRegistry $manager, Request $request): Response
    {
        // creates a apps object and initializes some data for this example
        $entity = $manager->getManager();
        $apps = new Apps();
        $form = $this->createForm(RegisterAppsType::class, $apps);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $apps = $form->getData();

            $entity = $manager->getManager();
            $entity->persist($apps);
            $entity->flush();
            return $this->redirectToRoute('app_liste');
        } else {
            return $this->render('admin/register.html.twig', [
                'form' => $form->createView()

            ]);
        }
    }

    /**
     *
     * @Route("/liste")
     */
    public function AllApps(AppsRepository $AppsRepository): Response
    {
        $Apps = $AppsRepository
            ->findAll();

        return $this->render('admin/liste.html.twig', [
            'Apps' => $Apps
        ]);
    }

    #[Route('/edit/{id}', name: 'app_edit')]
    public function edit(AppsRepository $AppsRepository, $id, Request $request, ManagerRegistry $manager): Response
    {
        $apps = $AppsRepository->find($id);
        // dd($apps);
        if (!$apps) {
            throw $this->createNotFoundException(
                'No Apps found for id ' . $id
            );
        }

        return $this->render('admin/edit.html.twig', [
            'apps' =>  $apps,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_delete')]
    public function delete(AppsRepository $AppsRepository, $id, ManagerRegistry $manager): Response
    {
        $em = $manager->getManager();;
        $apps = $AppsRepository->find($id);
        if (!$apps) {
            throw $this->createNotFoundException(
                'No Apps found for id ' . $id
            );
        }

        $em->remove($apps);
        $em->flush();

        return new RedirectResponse($this->urlGenerator->generate('app_apps_index'));
    }
    #[Route('/Applications', name: 'app_card')]
    public function listeCard(
        Security $security,
        Request $request,
        AppsRepository $appsRepository,
        // $id

    ): Response {
        $session = $request->getSession();

        $userClient = $this->keycloakHttpRequest->getAllUserClient($this->keycloakHttpRequest->getToken(), $session->get('accessToken')['id_token']);
        $clients = [];

        foreach (json_decode(json_encode($userClient))->clientMappings as  $clientName) {
            $clients[] = $clientName->client;
        }
        // $application = $appsRepository->find($id);   

        $apps = $appsRepository->findByClient($clients);
        return $this->render('admin/card.html.twig', [
            'controller_name' => 'UseController',
            'apps' => $apps,
            // 'application'=> $application,

        ]);
    }

    #[Route('/{id}/detail', name: 'app_details')]
    public function details(Security $security, AppsRepository $appsRepository, $id): Response
    {

        // dd($id);
        $application = $appsRepository->find($id);
        //  dd($application);
        // $app=json_decode(json_encode($application));
        // dd($app);
        //  return $this->redirectToRoute('app_card');
        return $this->render('admin/details.html.twig', [
            'controller_name' => 'UseController',
            'application' => $application,
        ]);
    }

    #[Route('/{id}/visite', name: 'app_visites')]
    public function visite(Security $security, Apps $app, AppsRepository $appsRepository, $id): Response
    {
        $apps =$app->getUrl();
        // $apps ="http://google.com";
        $response = new Response('Hello world!', 302);
        $response->headers->set('Location', $apps );
        return $response;
    }
}
