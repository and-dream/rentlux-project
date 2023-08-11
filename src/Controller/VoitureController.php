<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
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
      $order = new Commande;
      $form =$this->createForm(CommandeType::class, $order);
      $form->handleRequest($rq);
      
      if($form->isSubmitted() && $form->isValid())
  {

      $prixJournalier = new \DateTime();

      $interval = ($order->getDateHeureFin())->diff($order->getDateHeureDepart());
      $days = $interval->days;
      
      
      $order
          ->setPrixTotal($voiture->getPrixJournalier()* $days)
          ->setMembre($this->getUser())
          ->setVehicule($voiture)
          ->setDateEnregistrement(new \DateTime);

          $manager->persist($order);
          $manager->flush();
          $this->addFlash('success', "Votre commande est prise en compte");

          return $this->redirectToRoute('voiture_single');
  }
      return $this->render('voiture/single.html.twig', [
        'vehicule'=>$voiture,
        'formOrders'=>$form,
        ]);
    }
}
