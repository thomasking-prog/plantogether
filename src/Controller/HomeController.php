<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Statut;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $em): Response
    {

        $projet = new Project();
        $statut = new Statut();
        $statut->setLabel('Non définis');
        $projet->setCreator($this->getUser())->setLabel('Test')->setStatut($statut);

        $projectRepository = $em->getRepository(Project::class);
        $projects = $projectRepository->findAll();

        //$em->persist($statut);
        //$em->persist($projet);
        //$em->flush();


        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'projects' => $projects,
        ]);
    }
}
