<?php
namespace App\Controller;

use \Core\Controller\Controller;

class UsersController extends Controller
{
    public function __construct() {
        $this->loadModel('user');
    }

    public function login(): void
    {
        $message = false;
        if(count($_POST) > 1) {
            $password = htmlspecialchars($_POST['password']);
            $user = $this->user->getUser(htmlspecialchars($_POST['mail']), $password);
            if($user) {
                $_SESSION['user'] = $user;
                header('location: /');
                exit();
            }
            else {
                $message = "Adresse mail ou mot de passe incorrect";
            }
        }
        $this->profile($message);
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        header('location: /');
        exit();
    }

    public function subscribe() {
        if(count($_POST) > 0) {
            //Création d'un tableau regroupant mes champs requis
            $requiredFields=['lastname', 'firstname', 'address', 'city', 'zipCode',
            'country', 'phone', 'mail', 'password'];
            
            //On boucle sur le tableau requiredFields
            foreach($requiredFields as $key => $value) {
                //On verifie que $_POST["firstname"](si $value="firstname) existe.
                if(!$_POST[$value]) {
                    //Si n'existe pas redirection vers page d'inscription
                    header('location: /inscription');
                    exit();// PAS OUBLIERRRRRRRRRRR!!!!!!!!
                }
                //On Sécurise chaque donnée de $_POST et on les stocke dans $fields[]
                $fields[$value] = htmlspecialchars($_POST[$value]);
            }

            if($fields['mail'] == $_POST["mailVerify"]) {// Comparaison d'égalité
                if($fields['password'] == $_POST["passwordVerify"]) {// Comparaison d'égalité
                    //Hashage du password $fields["password]
                    $fields['password'] = password_hash($fields['password'], PASSWORD_BCRYPT);
                    //Création d'un token
                    $token = substr(md5(uniqid()), 0, 10);

                    //Création d'une date
                    $date = new \DateTime('NOW');
                    $date = $date->format('Y-m-d H:i:s');
        
                    $fields['token']= $token;//Stockage du token dans $fieds["token"]
                    $fields['createdAt']= $date;//Stockage de date dans $fields['createdAt']
                    //Appel de la methode create de la Table Parente (core/Table.php)
                    if($this->user->create($fields)) {
                        $_SESSION['success'] = "Votre inscription à bien été prise en compte";
                    }
                    else {
                        $_SESSION['error'] = 'une erreur s\'est produite';
                    }
                    header('location: /login');
                    exit();
                }
            }
        }
        return $this->render('user/subscribe');
    }

    public function profile($message = null) {
        if(null !== $_SESSION['user'] && $_SESSION['user']) {
            $file = 'profile';
            $page = 'Mon profil';
        }
        else {
            $file = 'login';
            $page = 'Connexion';
        }
        return $this->render('user/'.$file, [
            'page' => $page,
            'message' => $message
        ]);
    }

    public function updateUser() {
        if(count($_POST) > 0) {
            $id = (int) array_pop($_POST);//Stockage de la dernière case de $_POST dans $id
            //Mise à jours bdd grace à methode update de /core/Table.php
            $bool = $this->user->update($id, 'id', $_POST);
            //Mise à jours de la SESSION['user']
            $user = $this->user->getUserByid($id);
            $_SESSION['user'] = $user;

            $this->profile('Votre profil a bien été mis à jour');//Appel de la methode profile de ce controller pour redirection
            exit();
        }
    }

    public function changePassword() {
        if(count($_POST) > 0) {
            $user = $this->user->getUserById(htmlspecialchars($_POST['id']));
            //Vérification de l'ancien mot de passe mots de passes
            if(password_verify(htmlspecialchars($_POST['old_password']), $user->getPassword())) {
                //Vérification correspondance des mots de passe
                if(htmlspecialchars($_POST['password']) == htmlspecialchars($_POST['veriftyPassword'])) {
                    //Hashage du password
                    $password = password_hash(htmlspecialchars(htmlspecialchars($_POST['password'])), PASSWORD_BCRYPT);

                    //Mise à jour de la bdd grace à methode update de /core/Table.php
                    if($this->user->update($_POST['id'], 'id', ['password' => $password])) {
                        $message = 'Votre mot de passe a bien été modifié';
                    }
                    else {
                        $message = 'Une erreur s\'est produite';
                    }
                }
                else {
                    $message = 'Les mots de passes ne correspondent pas';
                }
            }
            else {
                $message = 'Mot de passe erroné';
            }
            return $this->profile($message);//Appel de la methode profile de ce controller pour redirection
            exit();
        }
    }
}
