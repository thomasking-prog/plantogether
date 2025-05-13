<?php

namespace App\Controller;

use App\Entity\Priority;
use App\Entity\Project;
use App\Entity\Statut;
use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/task', name: 'task')]
final class TaskController extends AbstractController
{
    #[Route('/create', name: '_create')]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $task = new Task();

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Données invalides.',
            ], 400);
        }

        // Données valides : enregistrer la tâche
        $task->setCreatedAt(new \DateTimeImmutable());

        $em->persist($task);
        $em->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Tâche créée avec succès !'
        ]);
    }


    #[Route('/edit', name: '_edit', methods: ['POST'])]
    public function edit(Request $request, EntityManagerInterface $em): JsonResponse
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

        if ($form->isSubmitted() && $form->isValid()) {
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
}
