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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

        // Puis les projets correspondants
        $projects = array_map(fn($project) => $project->getProject(), $participations);

        //$em->persist($statut);
        //$em->persist($projet);
        //$em->flush();


        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'projects' => $projects,
        ]);
    }
}
