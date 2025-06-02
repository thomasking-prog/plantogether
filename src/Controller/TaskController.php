<?php

namespace App\Controller;

use App\Entity\Affect;
use App\Entity\Log;
use App\Entity\Priority;
use App\Entity\Project;
use App\Entity\Statut;
use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\ProjectRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/task', name: 'task')]
final class TaskController extends AbstractController
{

    public function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $formField = $error->getOrigin()->getName();
            $errors[$formField][] = $error->getMessage();
        }

        return $errors;
    }
    #[Route('/create', name: '_create')]
    public function create(Request $request, EntityManagerInterface $em, ProjectRepository $projectRepo): JsonResponse
    {
        $task = new Task();

        $projectId = $request->request->all()['task']['project'] ?? null;

        if ($projectId) {
            $project = $projectRepo->find($projectId);
            if (!$project) {
                throw new NotFoundHttpException('Projet non trouvé.');
            }
            $task->setProject($project); // on assigne manuellement
        } else {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Pas de projet fdp.',
            ], 400);
        }

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Données invalides.',
                'errors' => $this->getFormErrors($form),
            ], 400);
        }

        // Données valides : enregistrer la tâche
        $task->setCreatedAt(new \DateTimeImmutable());

        $log = new Log();
        $log->setTask($task)
            ->setMember($this->getUser())
            ->setAction('create')
            ->setData($form->getData());

        $em->persist($task);
        $em->persist($log);
        $em->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Tâche créée avec succès !'
        ]);
    }


    #[Route('/edit', name: '_edit', methods: ['POST', 'GET'])]
    public function edit(Request $request, EntityManagerInterface $em, NotificationService $notifier): JsonResponse
    {
        $taskData = $request->get('task');
        $task = $em->getRepository(Task::class)->find($taskData['id'] ?? 0);

        if (!$task) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'Tâche introuvable'
                ], 404);
        }

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);


        foreach ($task->getMembers() as $affect) {
            $notifier->notifyModification($affect->getMember(), $task);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $log = new Log();
            $log->setTask($task)
                ->setMember($this->getUser())
                ->setAction('edit')
                ->setData($taskData);

            $em->persist($log);
            $em->flush();
            return new JsonResponse(
                [
                    'status' => 'success',
                    'message' => 'Les modifications sur la tâche ont bien été prises en compte.'
                ]);
        }

        return new JsonResponse([
            'status' => 'error',
            'message' => (string) $form->getErrors(true, false)
        ]);
    }

    #[Route('/{id}/add-member/{memberId}', name: 'task_add_member', methods: ['POST'])]
    public function addMember(Task $task, int $memberId, EntityManagerInterface $em, NotificationService $notifier): JsonResponse
    {
        $member = $em->getRepository(User::class)->find($memberId);
        if (!$member) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Utilisateur introuvable'
            ], 404);
        }

        $existing = $em->getRepository(Affect::class)->findOneBy([
            'task' => $task,
            'member' => $member,
        ]);

        if ($existing) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Membre déjà affecté à cette tâche'
            ], 400);
        }

        $assignment = new Affect();
        $assignment->setTask($task)
            ->setMember($member);
        $task->addMember($assignment);

        $assignmentArray = [
            'member_id' => $assignment->getMember()->getId(),
            'action' => 'add-member',
        ];

        $log = new Log();
        $log->setTask($task)
            ->setMember($this->getUser())
            ->setAction('edit')
            ->setData($assignmentArray);

        $em->persist($assignment);
        $em->persist($log);
        $em->flush();

        $notifier->notifyAssignment($member, $task);

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Membre affecté à la tâche',
            'id' => $member->getId(),
            'name' => $member->getName(),
        ]);
    }

    #[Route('/{id}/remove-member/{memberId}', name: 'task_remove_member', methods: ['POST'])]
    public function removeMember(Task $task, int $memberId, EntityManagerInterface $em): JsonResponse
    {
        $member = $em->getRepository(User::class)->find($memberId);

        if (!$member) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Utilisateur introuvable'
            ], 404);
        }

        $assignment = $em->getRepository(Affect::class)->findOneBy([
            'task' => $task,
            'member' => $member
        ]);

        if (!$assignment) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Affectation introuvable'
            ], 404);
        }

        $task->removeMember($assignment);

        $assignmentArray = [
            'member_id' => $assignment->getMember()->getId(),
            'action' => 'remove-member',
        ];

        $log = new Log();
        $log->setTask($task)
            ->setMember($this->getUser())
            ->setAction('edit')
            ->setData($assignmentArray);

        $em->persist($log);
        $em->remove($assignment);
        $em->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Membre désaffecté de la tâche'
        ]);
    }
}
