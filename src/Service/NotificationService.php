<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Task;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class NotificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function notifyAssignment(User $user, Task $task): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@tonapp.com', 'Gestionnaire de tâches'))
            ->to($user->getEmail())
            ->subject('Nouvelle tâche assignée')
            ->htmlTemplate('emails/task_assigned.html.twig')
            ->context([
                'user' => $user,
                'task' => $task,
                'link' => 'test',
            ]);

        $this->mailer->send($email);
    }

    public function notifyModification(User $user, Task $task): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@tonapp.com', 'Gestionnaire de tâches'))
            ->to($user->getEmail())
            ->subject('Tâche modifiée')
            ->htmlTemplate('emails/task_modified.html.twig')
            ->context([
                'user' => $user,
                'task' => $task,
            ]);


        //$this->mailer->send($email);
    }
}