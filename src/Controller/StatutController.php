<?php

namespace App\Controller;

use App\Entity\Statut;
use App\Repository\StatutRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/statuts', name: 'admin_statuts')]
class StatutController extends AbstractController
{
    #[Route('', name: 'create_statut', methods: ['POST'])]
    public function create(Request $request, StatutRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $statut = new Statut();
        $statut->setLabel($data['label']);
        $em->persist($statut);
        $em->flush();

        return $this->json(['status' => 'success', 'id' => $statut->getId(), 'label' => $statut->getLabel()]);
    }

    #[Route('/{id}', name: 'update_statut', methods: ['PUT'])]
    public function update(Statut $statut, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $statut->setLabel($data['label']);
        $em->flush();

        return $this->json(['status' => 'success']);
    }

    #[Route('/{id}', name: 'delete_statut', methods: ['DELETE'])]
    public function delete(Statut $statut, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($statut);
        $em->flush();

        return $this->json(['status' => 'success']);
    }
}
