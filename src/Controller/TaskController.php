<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{

    private TaskRepository $repo; // class de symfony qui contient plusieurs methodes pour ajouter, supprimer, modifier 

    public function __construct(TaskRepository $repo)
    {
        $this->repo = $repo;
    }

    #[Route('/', name: 'task.create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {

        $submit = $request->get('submit'); // quand on a clique sur le bouton on recupere les donnees ce qui a ete envoye par formulaire 
        $tasks = $this->repo->findAll(); // je recupere la lsite des taches, c'est à dire les donnees presentes dans la BD, c'est le repository qui recupere depuis la BD

        if (!isset($submit)) {
            //affichage des données et du formulaire
            // avec !isset verification si la variable n'existe pas, je verifie si le bouton a ete clique
            // si je n'ai pas clique sur le bouton je renvoie à la vue avec le return mais si je clique je fais un traitement pour afficher les donnees
            return $this->render('task/create.html.twig', ['mytasks' => $tasks]); // le tableau associatif (cle => element) recupere ce qui a ete rentre dans la BD
        }

        //////// Traitement des informations ////////

        $nom = trim($request->get('nom')); // je recupere le champ de l'input qui a ete envoye // trim supprime les espaces en debut et en fin de chaine de caractere

        // empty : je  verifie si le champ est vide si le champ est vide c'est une erreur alors  je renvoie à la page de creation, j'envoie une erreur et la liste des taches
        if(empty($nom)){
            return $this->render('task/create.html.twig', [
                'error' => 'La tache est requise !',
                'mytasks' => $tasks
            ]);

        } 

        $task = new Task(); // creation d'un nouvel objet vide
        $task->setNom($nom)->setComplete(false); // cet objet tu peux lui passer la propriete de l'objet Task avec setNom pour recuperer le champs de l'input declaré au dessus dans la variable $nom
        $this->repo->add($task, true); // pour integrer les donnes dans la bases de donnes

        return $this->redirect('/'); // une fois que j'ai ajoute les donnes dans la BD, je fais une redirection vers le meme url
    }

    #[Route('/tasks/edit/{id}', methods: ['POST'])]
    public function update($id) { // Je recupere le id dans la BD

        $task = $this->repo->find($id); // je vais chercher dans la BD la tache qui rcorrespin a cette id

        if($task->isComplete()){ // je modifie son statur complete fait ou pas complete pas fait
            $task->setComplete(false); // si c'est pas fait dans la BD complete est à 0
        }else{
            $task->setComplete(true); // si c'est fait dans la BD complete est à 1
        }
        // $task->setComplete(!$task->isComplete()); // negation j'inverse ce qui est dans la BD

        $this->repo->update(); // methode update pour pousser/inserer les donnes les modifications dans la BD, ava,t creation de l amethode update dans le taskrepository
        return $this->redirect('/'); // une fois la modification faite, redirection vers la paged d'accueil, la racine 

    }

    // on recupere l'id pour savoir quel bouton a ete clique
    #[Route('/tasks/{id}', methods: ['POST'])]
    public function delete($id) { // methode delete pour supprimer une tache avec la poubelle
        $task = $this->repo->find($id); // je recupere la ligne dans la BD avce l'id
        $this->repo->remove($task, true); // si je ne le met pas a vrai il ne fera pas un flush dans la BD

        return $this->redirect('/'); // une fois la suppresion faite en BD je redirige la vue vers la page d'accueil

    }

    #[Route('/tasks/delete/all', methods: ['POST'])] // le bouton qui efface tout 
    public function deleteAll(){

        $this->repo->deleteAll();
        return $this->redirect('/');
    }
}
