<?php

namespace App\Controller;

use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VoitureController extends AbstractController
{
    
    #[Route('/', name: 'home')]
    public function index(VehiculeRepository $repo) :Response
    {
        $voitures = $repo->findAll();       
        return $this->render('voiture/index.html.twig', [
            "voitures" =>$voitures
        ]);
    }
   

    #[Route('/voiture/single/{id}', name:"voiture_single")]
    public function single($id, VehiculeRepository $repo, Request $rq, EntityManagerInterface $manager)
    {
      $voiture = $repo->find($id);
    
      return $this->render('voiture/single.html.twig', ['vehicule'=>$voiture]);
    }
}
