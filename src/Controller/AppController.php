<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\MembreRepository;
use App\Repository\CommandeRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    #[Route('/', name: 'app')]
    public function index(VehiculeRepository $repo): Response
    {

        $vehicule = $repo->findAll();


        return $this->render('app/index.html.twig', [
            'vehicule' => $vehicule,
        ]);
    }

//-                                                                                                                                     - //
// $        COMMANDE
    #[Route('/commande/{id}', name: 'commande_vehicule')]
    public function commande($id, VehiculeRepository $repo, EntityManagerInterface $manager, Request $globals): Response
    {


        $vehicule = $repo->find($id);
        $commande = new Commande;
        // dd($colonnes);
        // $commande = $repo->findAll();
        

        // if($commande ==null):
        //     $commande = new Commande;
        // endif;


        $form= $this->createForm(CommandeType::class, $commande );
        $form->handleRequest($globals);
        // dump($vehicule);

        if($form->isSubmitted() && $form->isValid())
        {
            
            $depart = $commande->getDateHeureDepart();
            $fin = $commande->getDateHeureFin();
            $interval = $depart->diff($fin);
            $days = $interval->days;
            
            $prix = $vehicule->getPrixJournalier();
            $prix = $prix * $days;
            
            $commande->setDateEnregistrement(new \DateTime);
            $commande->setMembre($this->getUser());
            $commande->setVehicule($vehicule);
            $commande->setPrixTotal($prix);

            $manager->persist($commande);
            $manager->flush();
            $this->addFlash('success', "La commande a bien été enregistré !");

            return $this->redirectToRoute('app');
        }

        return $this->renderForm("app/commande.html.twig", [
            "formCommande" => $form,
            // "editMode" => $commande->getId() !== null,
            'commande' => $commande,
            'vehicule' => $vehicule
        ]);
        
    }
    
    //-                                                                                                                                     - //
    
    #[Route('/commande/show/{id}', name: 'commande_show')]
    public function commandeShow(CommandeRepository $repo, $id): Response
    {
        
        $commande = $repo->find($id);
        $depart = $commande->getDateHeureDepart();
        $fin = $commande->getDateHeureFin();
        $interval = $depart->diff($fin);
        $days = $interval->days;
        
        
        return $this->render('app/commande_show.html.twig', [
            'commande' => $commande,
            'duree' => $days
        ]);
    }


//-                                                                                                                                     - //
    
#[Route('/compte', name: 'compte')]
public function compte(EntityManagerInterface $manager, Request $globals, CommandeRepository $repo): Response
{

    $commandes = $repo->findAll();

    return $this->render('app/compte.html.twig', [
        'commandes' => $commandes
    ]);
}
    
//-                                                                                                                                     - //
// $        MEMBRE
    #[Route('/membre/show/{id}', name: 'admin_membre_show')]
    public function membreShow(MembreRepository $mRepo,CommandeRepository $cRepo, $id): Response
    {
        
        $membre = $mRepo->find($id);
        $commandes = $cRepo->findAll();


        return $this->render('app/membre_show.html.twig', [
            'membre' => $membre,
            'commandes' => $commandes
        ]);
    }

    
    
//-                                                                                                                                     - //
// $        VEHICULE
    #[Route('/vehicule/show/{id}', name: 'admin_vehicule_show')]
    public function vehiculeShow(VehiculeRepository $repo, $id): Response
    {
        
        $vehicule = $repo->find($id);
        
        return $this->render('app/vehicule_show.html.twig', [
            'vehicule' => $vehicule
        ]);
    }

    





}
