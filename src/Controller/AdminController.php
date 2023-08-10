<?php

namespace App\Controller;

use App\Entity\Membre;
use Datetime;
use App\Entity\Vehicule;
use App\Form\GestionVehiculesType;
use App\Form\RegistrationFormType;
use App\Repository\MembreRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index():Response
    {
        return $this->render('admin/index.html.twig', [
        ]);
    }

    #[Route('/voiture/modifier/{id}', name:"voiture_modifier")]
    #[Route('/voiture/ajouter', name: "voiture_ajouter")]
    public function form(Request $globals, EntityManagerInterface $manager, Vehicule $voiture =null, VehiculeRepository $repo, SluggerInterface $slugger) :Response
    {    
        if($voiture == null)
        {
            $voiture = new Vehicule;
        }

        
        $editMode = $voiture->getId() !== null;
        $voitures = $repo->findAll();
        $form = $this->createForm(GestionVehiculesType::class, $voiture);
    
        $form->handleRequest($globals);

        if($form->isSubmitted() && $form ->isValid())
        {
            $imageFile = $form->get('photo')->getData();

            if($imageFile){
                $originalFilename = pathinfo($imageFile->getClientOriginalName(),PATHINFO_FILENAME); 
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    try{
                        $imageFile->move(
                            $this->getParameter('img_upload'), 
                            $newFilename
                        );
                    }catch (FileException $e){

                    }
                    $voiture->setPhoto($newFilename);
            } //!fin traitement image
            
            // $voiture->setCreatedAt(new \Datetime);
            $manager->persist($voiture); 
            $manager->flush();

            if($editMode)
            {
                $this->addFlash('success', "La modification a été faite");
            }else{
               
                $this->addFlash('success', "Vous avez bien ajouté une nouvelle voiture");              
            }
            return $this->redirectToRoute('voiture_ajouter');
        } 

        return $this->render('admin/gestion.html.twig', [
            'formGestion' => $form,
            'editMode' => $editMode,
            'voitures' => $voitures,
        ]);
    }


#[Route('/voiture/supprimer/{id}', name: 'voiture_supprimer')]
public function delete(EntityManagerInterface $manager, Vehicule $voitures)
{
    $manager->remove($voitures);
    $manager->flush();
    return $this->redirectToRoute('vehicule_gestion');
}

// Gestion des membres

#[Route('/membre/modifier/{id}', name:"membre_modifier")]
#[Route('/membre/ajouter', name: "membre_ajouter")]
public function formMembres(Request $globals, EntityManagerInterface $manager, Membre $membre =null, MembreRepository $repo) :Response
{    
    if($membre == null)
    {
        $membre = new Membre;
    }
   
    $editMode = $membre->getId() !== null;
    // $membre = $repo->findAll();
    $form = $this->createForm(RegistrationFormType::class, $membre);

    $form->handleRequest($globals);

    if($form->isSubmitted() && $form ->isValid())
    {
        $membre->setDateEnregistrement(new \Datetime);
        $manager->persist($membre);
        $manager->flush();

        if($editMode)
        {
           $this->addFlash('success', 'Le membre a bien été modifié');
        }else{
            $this->addFlash('success', "Vous avez bien ajouté un nouveau membre");
        }
        $this->addFlash('success', "Vous avez bien ajouté un nouveau membre");

        return $this->redirectToRoute('home');
        
    }

    return $this->render('admin/membres/gestion.html.twig', [
        'membres' => $membre,
        'formMembres' => $form,
        'editMode' => $editMode,
    ]);
}


#[Route('/membre/supprimer/{id}', name: 'membre_supprimer')]
        public function remove(Membre $membre, EntityManagerInterface $manager)   

        {
            $manager->remove($membre);   
            $manager->flush();     
            return $this->redirectToRoute('membre_ajouter');
        }

}
