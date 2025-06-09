<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Statut;
use App\Entity\User;
use App\Repository\ParticipateRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $em, Security $security, ParticipateRepository $participateRepo, ProjectRepository $projectRepo): Response
    {

        $projet = new Project();
        $statut = new Statut();
        $statut->setLabel('Non définis');
        $projet->setCreator($this->getUser())->setLabel('Test')->setStatut($statut);

        $user = $security->getUser();

        $participations = $participateRepo->findBy(['member' => $user]);

        $projects = array_filter(
            array_map(fn($p) => $p->getProject(), $participations),
            fn($project) => $project->getStatut()?->getLabel() !== 'Done'
        );


        //$em->persist($statut);
        //$em->persist($projet);
        //$em->flush();


        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'projects' => $projects,
        ]);
    }

    #[Route('/test-mail', name: 'test_mail')]
    public function testMail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('test@yourapp.com')
            ->to('test@example.com') // tu peux mettre n’importe quoi ici
            ->subject('Test Mail')
            ->text('Ceci est un test.');

        $mailer->send($email);

        return new JsonResponse(['status' => 'ok']);
    }

}
