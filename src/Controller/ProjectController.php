<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Statut;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/project', name: 'project')]
final class ProjectController extends AbstractController
{

    #[Route('/', name: '_index')]
    public function index(): Response
    {
        return $this->render('project/index.html.twig', [
            'controller_name' => 'ProjectController',
        ]);
    }

    #[Route('/create', name: '_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['label'])) {
            return new JsonResponse(['error' => 'Le nom du projet est requis.'], Response::HTTP_BAD_REQUEST);
        }

        // Logique pour créer un projet (ex. enregistrer dans la base de données)
        $project = new Project();
        $statut = $em->getRepository(Statut::class)->findOneBy(['label' => 'Non définis']);

        $project->setLabel($data['label'])
            ->setCreator($this->getUser())
            ->setStatut($statut);

        $em->persist($project);
        $em->flush();


        return new JsonResponse([
            'success' => true,
            'message' => "Projet $data[label] créé avec succès.",
        ]);
    }

    #[Route('/{id}', name: '_show')]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/delete/{id}', name: '_delete', methods: ['DELETE'])]
    public function delete(Project $project, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($project);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Le projet a été supprimé avec succès.']);
    }
}
