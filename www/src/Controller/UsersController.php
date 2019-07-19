<?php

namespace App\Controller;

use \Core\Controller\Controller;
use \Core\Controller\URLController;
use \Core\Controller\MailController;
use \App\Model\Table\UserTable;
use Core\Controller\FormController;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->loadModel('user');
        $this->loadModel('UserInfos');
    }

    public function login(): string
    {

        $form = new FormController();
        $form->field('mail', ["require"])
            ->field('password', ["require"]);

        $errors =  $form->hasErrors();

        //verifier si post
        if (!isset($errors["post"])) {
            $datas = $form->getDatas();

            //verifier si erreurs
            if (empty($errors)) {
                //verifier que user existe
                //verifier que user et password
                $user = $this->user->getUser($datas["mail"], $datas["password"]);
                if ($user) {
                    // machin connecter
                    // message bien connecter
                    // machin redirection
                    $this->flash()->addSuccess("le POST est super top");
                } else {
                    $this->flash()->addAlert("pas cool");
                }
            } else {
                $this->flash()->addAlert("appprend a remplir un formulaire");
            }
            unset($datas['password']);
        }
        //erreur afficher message
        return $this->render('user/login', compact("datas"));
    }

    /**
     * Affichage de la vu d'inscription 
     * et du traitement du formulaire inscription
     *
     * @return string
     */
    public function subscribe(): string
    {

        //Création d'un tableau regroupant les champs requis
        $form = new FormController();
        //ajouter des champs avec contraintes
        $form->field('mail', ["require", "verify"])
            ->field('password', ["require", "verify", "length" => 8]);

        //recuperer errors du formulaire
        $errors =  $form->hasErrors();
        //verifier si il y a une action de POST
        if (!isset($errors["post"])) {
            //recuperer datas du formulaire
            $datas = $form->getDatas();
            //Verifie qu'il ny ai pas d'erreur dans les contraintes
            //des champs
            if (empty($errors)) {
                //recuperer la table userTable
                /**@var UserTable $userTable */
                $userTable = $this->user;
                //verifier que l'adresse mail n'existe pas en base de donné
                if ($userTable->find($datas["mail"], "mail")) {
                    // lève une exception qui devra être  gérée
                    //TODO : message flash + redirection?
                    throw new \Exception("utilisateur existe deja");
                }
                //crypter password via un méhtode globale pour tout le site?
                $datas["password"] = password_hash($datas["password"], PASSWORD_BCRYPT);
                //cree token via un méhtode globale pour tout le site?
                $datas["token"] = substr(md5(uniqid()), 0, 10);
                //cree une nouvelle utilisatrice en base de donnée
                //avec les données du tableau data
                if (!$userTable->newUser($datas)) {
                    //erreure de sauvegarde en base de donnée
                    //TODO : cree une page 500??
                    throw new \Exception("erreur de base de donné");
                }
                //Message flash pour prevenir du bon l'enregistrement
                $this->flash()->addSuccess("vous êtes bien enregistré");
                //envoyer mail de confirmation avec le token
                $mail = new MailController();
                //écriture du sujet et du mail
                $mail->object("validez votre compte")
                    ->to($datas["mail"])
                    ->message('confirmation', compact("datas"))
                    ->send();
                //informer le client via un message flash 
                //qu'il var devoir valider son adresse mail
                $this->flash()->addSuccess("vous avez reçu un mail");
                //rediriger le client sur la pgae de connexion grace au generateur d'url
                //TODO : Methode Globale 
                //$this->app->location($this->generateUrl("usersLogin"), 'code erreur')
                //qui fait le exit aussi!!!!
                header('location: ' . $this->generateUrl("usersLogin"));
                //stoper l'execution du code php
                exit();
                //fin de la partie sans erreurs
            }
            //supression du mot de passe dans le tableau datas
            unset($datas["password"]);
        } else {
            //supression du tableau errors si il n'y a pas eu de post
            unset($errors);
        }
        //appel du ficher twig grace a la methe render
        //qui prend en paramètre le chemin de la vue 
        //et en 2eme paramètre les variables pour la vue
        return $this->render('user/subscribe', compact("errors", "datas"));
        //fin de ma methode subscribe
    }

    public function profile($message = null)
    {
        if (null !== $_SESSION['user'] && $_SESSION['user']) {
            $file = 'profile';
            $page = 'Mon profil';
            $userInfos = $this->UserInfos->getUserInfosByid($_SESSION['user']->getId());
        } else {
            $file = 'login';
            $page = 'Connexion';
            $userInfos = false;
        }
        return $this->render('user/' . $file, [
            'page' => $page,
            'message' => $message,
            'userInfos' => $userInfos
        ]);
    }

    public function updateUser()
    {

        if (count($_POST) > 0) {
            $id = (int) array_pop($_POST); //Stockage de la dernière case de $_POST dans $id
            //Mise à jours bdd grace à methode update de /core/Table.php
            $bool = $this->UserInfos->update($id, 'user_id', $_POST);
            //Mise à jours de la SESSION['user']
            $user = $this->user->getUserByid($id);
            $_SESSION['user'] = $user;

            //Appel de la methode profile de ce controller pour redirection
            $this->profile('Votre profil a bien été mis à jour');

            exit();
        }
    }

    public function changePassword()
    {
        if (count($_POST) > 0) {
            $user = $this->user->getUserById(htmlspecialchars($_POST['id']));
            //Vérification de l'ancien mot de passe mots de passes
            if (password_verify(htmlspecialchars($_POST['old_password']), $user->getPassword())) {
                //Vérification correspondance des mots de passe
                if (htmlspecialchars($_POST['password']) == htmlspecialchars($_POST['veriftyPassword'])) {
                    //Hashage du password
                    $password = password_hash(htmlspecialchars(htmlspecialchars($_POST['password'])), PASSWORD_BCRYPT);

                    //Mise à jour de la bdd grace à methode update de /core/Table.php
                    if ($this->user->update($_POST['id'], 'id', ['password' => $password])) {
                        $message = 'Votre mot de passe a bien été modifié';
                    } else {
                        $message = 'Une erreur s\'est produite';
                    }
                } else {
                    $message = 'Les mots de passes ne correspondent pas';
                }
            } else {
                $message = 'Mot de passe erroné';
            }
            return $this->profile($message); //Appel de la methode profile de ce controller pour redirection
            exit();
        }
    }
}
