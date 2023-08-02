<?php

namespace App\Controller;

use App\Entity\Apps;
use App\Form\AppsType;
use App\Form\RegisterAppsType;
use App\Form\RegisType;
use App\Repository\AppsRepository;
use App\Service\KeycloakHttpRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Twig\Environment;


#[IsGranted('ROLE_ADMIN')]
#[Route('/apps')]
class AppsController extends AbstractController
{
    #[Route('/', name: 'app_apps_index', methods: ['GET'])]
    public function index(
        Environment $twig,
        AppsRepository $appsRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {

        // $offset = max(0, $request->query->getInt('offset', 0));
        // +       $paginator = $appsRepository->getCommentPaginator($apps, $offset);
        // $pagination = $paginator->paginate(
        //     $appsRepository->findAll(),
        //     $request->query->getInt('page', 1), 
        //     5
        // );
        
        return $this->render('apps/index.html.twig', [
            'apps' => $appsRepository->findAll(),
            // 'pagination' => $pagination
        //     'comments' => $paginator,
        //     'previous' => $offset - AppsRepository::PAGINATOR_PER_PAGE,
        //    'next' => min(count($paginator), $offset + AppsRepository::PAGINATOR_PER_PAGE),
        ]);
    }

    #[Route('/new', name: 'app_apps_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AppsRepository $appsRepository, KeycloakHttpRequest $KeycloakHttpRequest): Response
    {
        $app = new Apps();
        $form = $this->createForm(RegisterAppsType::class, $app);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $appsRepository->save($app, true);

            return $this->redirectToRoute('app_apps_index', [], Response::HTTP_SEE_OTHER);
        }
        $tok = $KeycloakHttpRequest->getToken();
        $client = $KeycloakHttpRequest->getAllClientOfTheRealm($tok);

        //  dd($client);
        return $this->renderForm('apps/new.html.twig', [
            'app' => $app,
            'form' => $form,
            'client' => $client
        ]);
    }

    #[Route('/{id}', name: 'app_apps_show', methods: ['GET'])]
    public function show(Apps $app): Response
    {
        return $this->render('apps/show.html.twig', [
            'app' => $app,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_apps_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Apps $app, AppsRepository $appsRepository): Response
    {
        $form = $this->createForm(RegisterAppsType::class, $app);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $appsRepository->save($app, true);

            return $this->redirectToRoute('app_apps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('apps/edit.html.twig', [
            'app' => $app,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_apps_delete', methods: ['POST'])]
    public function delete(Request $request, Apps $app, AppsRepository $appsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $app->getId(), $request->request->get('_token'))) {
            $appsRepository->remove($app, true);
        }

        return $this->redirectToRoute('app_apps_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/delete/{id}', name: 'apps_delete', methods: ['GET'])]
    public function deleteApps(Request $request, Apps $app, AppsRepository $appsRepository): Response
    {
        // dd($request);

        return $this->redirectToRoute('app_apps_index', [], Response::HTTP_SEE_OTHER);
    }
}
