<?php

namespace App\Controller;

use App\Entity\Statut;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\StatutRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(UserRepository $userRepository, ProjectRepository $projectRepository, StatutRepository $statutRepository, TaskRepository $taskRepository): Response
    {
        $projects = $projectRepository->findAll();

        $users = $userRepository->findAll();

        $taskPerUser = [];

        foreach ($users as $user) {
            $affectations = $user->getAffectations();
            foreach ($affectations as $affectation) {
                $userId = $user->getId();
                $task = $affectation->getTask();
                $project = $task->getProject();

                $taskPerUser[$userId][$project->getId()][] = [
                    'id' => $task->getId(),
                    'label' => $task->getLabel(),
                    'statutId' => $task->getStatut()->getId(),
                    'statut' => $task->getStatut()->getLabel(),
                    'project' => $project->getLabel(),
                ];
            }
        }

        $usersArray = array_map(function($user) {
            return ['id' => $user->getId(), 'username' => $user->getUsername()];
        }, $users);

        $projectsArray = array_map(function($project) {
            return ['id' => $project->getId(), 'label' => $project->getLabel()];
        }, $projects);

        $allTasks = [];

        foreach ($taskRepository->findAll() as $task) {
            $allTasks[] = [
                'id' => $task->getId(),
                'label' => $task->getLabel(),
                'statutId' => $task->getStatut()->getId(),
                'statut' => $task->getStatut()->getLabel(),
                'projectName' => $task->getProject()->getLabel(),
                'projectId' => $task->getProject()->getId(),
                'users' => array_map(fn($u) => $u->getMember()->getId(), $task->getMembers()->toArray()),
            ];
        }

        return $this->render('admin/dashboard.html.twig', [
            'projects' => $projects,
            'taskPerUser' => $taskPerUser,
            'users' => $users,
            'usersArray' => $usersArray,
            'projectsArray' => $projectsArray,
            'statuts' => $statutRepository->findAll(),
            'allTasks' => $allTasks,
        ]);
    }

    #[Route('/admin/recherche', name: 'admin_recherche')]
    public function recherche(Request $request, TaskRepository $tacheRepository, UserRepository $userRepository, ProjectRepository $projetRepository): Response
    {
        $statut = $request->query->get('statut');
        $projetId = $request->query->get('projet');
        $utilisateurId = $request->query->get('utilisateur');
        $terme = $request->query->get('terme');

        $resultats = $tacheRepository->findByFiltres($statut, $projetId, $utilisateurId, $terme);

        return $this->render('admin/recherche.html.twig', [
            'resultats' => $resultats,
            'utilisateurs' => $userRepository->findAll(),
            'projets' => $projetRepository->findAll(),
        ]);
    }

    #[Route('/admin/stats/project/{id}/task-status', name: 'admin_project_task_status')]
    public function projectTaskStatus(
        int $id,
        ProjectRepository $projectRepo,
        StatutRepository $statutRepo
    ): JsonResponse {
        $project = $projectRepo->find($id);
        if (!$project) {
            return new JsonResponse(['error' => 'Projet introuvable'], 404);
        }

        $tasks = $project->getTasks();
        $statuts = $statutRepo->findAll();

        // Initialise tous les statuts à 0
        $stats = [];
        foreach ($statuts as $statut) {
            $stats[$statut->getLabel()] = 0;
        }

        // Compte les tâches par statut
        $total = 0;
        foreach ($tasks as $task) {
            $label = $task->getStatut()->getLabel();
            $stats[$label]++;
            $total++;
        }

        return new JsonResponse([
            'stats' => $stats,
            'total' => $total,
        ]);
    }


}
