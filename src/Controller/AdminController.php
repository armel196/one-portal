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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
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
   
    public function register(ManagerRegistry $manager,Request $request): Response
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
        }else {
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
    public function edit(AppsRepository $AppsRepository,$id,Request $request,ManagerRegistry $manager): Response
    {
        $apps = $AppsRepository->find($id);
            dd($apps);
        if (!$apps ) {
            throw $this->createNotFoundException(
                'No Apps found for id '.$id
            );
        }

        return $this->render('admin/edit.html.twig', [
            'apps' =>  $apps,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_delete')]
    public function delete(AppsRepository $AppsRepository,$id,ManagerRegistry $manager): Response
    {   
        $em =$manager->getManager();;
        $apps = $AppsRepository->find($id);
        if (!$apps ) {
            throw $this->createNotFoundException(
                'No Apps found for id '.$id
            );
        }

        $em->remove($apps);
        $em->flush();

        return new RedirectResponse($this->urlGenerator->generate('app_liste'));

  
    }

}
