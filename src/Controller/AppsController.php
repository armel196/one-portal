<?php

namespace App\Controller;

use App\Entity\Apps;
use App\Form\AppsType;
use App\Repository\AppsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;



#[IsGranted('ROLE_ADMIN')]
#[Route('/apps')]
class AppsController extends AbstractController
{
    #[Route('/', name: 'app_apps_index', methods: ['GET'])]
    public function index(AppsRepository $appsRepository): Response
    {
        return $this->render('apps/index.html.twig', [
            'apps' => $appsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_apps_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AppsRepository $appsRepository): Response
    {
        $app = new Apps();
        $form = $this->createForm(AppsType::class, $app);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $appsRepository->save($app, true);

            return $this->redirectToRoute('app_apps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('apps/new.html.twig', [
            'app' => $app,
            'form' => $form,
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
        $form = $this->createForm(AppsType::class, $app);
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
        if ($this->isCsrfTokenValid('delete'.$app->getId(), $request->request->get('_token'))) {
            $appsRepository->remove($app, true);
        }

        return $this->redirectToRoute('app_apps_index', [], Response::HTTP_SEE_OTHER);
    }
}
