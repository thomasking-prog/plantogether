<?php

namespace App\Controller;

use App\Entity\Participate;
use App\Entity\Priority;
use App\Entity\Project;
use App\Entity\Statut;
use App\Entity\Task;
use App\Entity\User;
use App\Form\ProjectType;
use App\Form\TaskType;
use App\Repository\ParticipateRepository;
use App\Repository\UserRepository;
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
    private function denyIfNotProjectMember(Project $project): void
    {
        $user = $this->getUser();

        $isParticipant = $project->getMembers()->exists(fn($key, $p) => $p->getMember() === $user);

        if (!$isParticipant && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à ce projet.");
        }
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

        $paritipation  = new Participate();
        $paritipation->setProject($project)
            ->setMember($this->getUser());

        $em->persist($project);
        $em->persist($paritipation);
        $em->flush();


        return new JsonResponse([
            'success' => true,
            'message' => "Projet $data[label] créé avec succès.",
        ]);
    }

    #[Route('/{id}', name: '_show')]
    public function show(Project $project, EntityManagerInterface $em): Response
    {
        $this->denyIfNotProjectMember($project);

        $statuts = $em->getRepository(Statut::class)->findAll();

        $taskArray = [];
        foreach ($project->getTasks()->toArray() as $task) {
            $taskArray[$task->getId()] = [
                'id' => $task->getId(),
                'label' => $task->getLabel(),
                'statut' => [
                    'id' => $task->getStatut()?->getId(),
                    'label' => $task->getStatut()?->getLabel(),
                ],
                'priority' => [
                    'id' => $task->getPriority()?->getId(),
                    'label' => $task->getPriority()?->getLabel(),
                ],
                'project' => [
                    'id' => $project->getId(),
                    'label' => $project->getLabel(),
                ],
                'estimatedTime' => $task->getEstimatedTime(),
                'formatTime' => $task->getFormatTime(),
                'assignedMembers' => array_map(fn($assigment) => [
                    'id' => $assigment->getMember()?->getId(),
                    'name' => $assigment->getMember()?->getName(),
                ], $task->getMembers()->toArray()),
            ];
        }

        $taskForm = $this->createForm(TaskType::class);

        $projectMembers = [];
        foreach ($project->getMembers()->toArray() as $participation) {
            $member = $participation->getMember();
            $projectMembers[] = [
                'id' => $member->getId(),
                'name' => $member->getName(),
            ];
        }

        $projectMembers[] = [
            'id' => '2',
            'name' => 'Je fait un test',
        ];
        $allUsers = [];
        foreach ($em->getRepository(User::class)->findAll() as $user) {
            $allUsers[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
            ];
        }
        $allUsers[] = [
            'id' => '2',
            'name' => 'Je fait un test',
        ];

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'statuts' => $statuts,
            'tasks' => $taskArray,
            'taskForm' => $taskForm->createView(),
            'projectMembers' => $projectMembers,
            'allUsers' => $allUsers,
        ]);
    }

    #[Route('/{id}/add-member/{userId}', name: '_project_add_member', methods: ['POST', 'GET'])]
    public function addMember(Project $project, int $userId, UserRepository $userRepo, ParticipateRepository $repo, EntityManagerInterface $em)
    {
        $this->denyIfNotProjectMember($project);

        $user = $userRepo->find($userId);

        if (!$user) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Utilisateur introuvable'
            ], 404);
        }
        $existing = $repo->findOneBy([
            'project' => $project,
            'member' => $user
        ]);

        if ($existing) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Membre déjà affecté à ce projet'
            ], 400);
        }

        $participation = new Participate();
        $participation->setProject($project)->setMember($user);
        $project->addMember($participation);
        $em->persist($participation);
        $em->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Membre affecté au projet',
            'id' => $user->getId(),
            'name' => $user->getName(),
        ]);
    }

    #[Route('/{id}/remove-member/{userId}', name: '_project_remove_member', methods: ['POST', 'GET'])]
    public function removeMember(Project $project, int $userId, ParticipateRepository $repo, UserRepository $userRepo, EntityManagerInterface $em)
    {
        $this->denyIfNotProjectMember($project);

        $user = $userRepo->find($userId);
        if (!$user) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Utilisateur introuvable'
            ], 404);
        }

        $participation = $repo->findOneBy(['project' => $project, 'member' => $userId]);

        if (!$participation) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Affectation introuvable'
            ], 404);
        }

        $project->removeMember($participation);
        $em->remove($participation);
        $em->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Membre désaffecté du projet',
        ]);
    }


    #[Route('/delete/{id}', name: '_delete', methods: ['DELETE'])]
    public function delete(Project $project, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyIfNotProjectMember($project);

        $entityManager->remove($project);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Le projet a été supprimé avec succès.']);
    }
}
