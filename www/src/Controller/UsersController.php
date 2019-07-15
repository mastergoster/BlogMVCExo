<?php
namespace App\Controller;

use \Core\Controller\Controller;
use \Core\Controller\MailController;
use \App\Model\Table\UserTable;
use \App\Model\Entity\UserEntity;
use \App\Model\Entity\UserInfosEntity;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->loadModel('user');
        $this->loadModel('UserInfos');
    }

    public function login(): string
    {
        $message = false;
        if (count($_POST) > 1) {
            $password = htmlspecialchars($_POST['password']);
            $user = $this->user->getUser(htmlspecialchars($_POST['mail']), $password);
            if ($user) {
                $_SESSION['user'] = $user;
                header('location: /');
                exit();
            } else {
                $message = "Adresse mail ou mot de passe incorrect";
            }
        }
        return $this->profile($message);
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        header('location: /');
        exit();
    }

    public function subscribe()
    {
        //Création d'un tableau regroupant mes champs requis
        $form = new \Core\Controller\FormController();
        $form->field('mail', ["require", "verify"])
            ->field('password', ["require", "verify", "length" => 8 ]);
        $errors =  $form->hasErrors();
        if (!isset($errors["post"])) {
            $datas = $form->getDatas();
            //verifier mail et mailverify 
            //verifier password et passwordverify
            if(empty($errors)){
                /**@var UserTable $userTable */
                $userTable = $this->user;
                //verifier que l'adresse mail n'existe pas
                if($userTable->find($datas["mail"], "mail")){
                    // sinon quoi faire?
                    throw new \Exception("utilisateur existe deja");
                }
                //crypter password
                $datas["password"] = password_hash($datas["password"], PASSWORD_BCRYPT);
                //cree token
                $datas["token"] = substr(md5(uniqid()), 0, 10);
                //persiter en bdd
                if(!$userTable->newUser($datas)){
                    throw new \Exception("erreur de base de donné");
                }
                //informer le client qu'il enregistré
                //envoyer mail de confirmation avec le token
                $mail = new MailController();
                $mail->object("validez votre compte")
                    ->to($datas["mail"])
                    ->message('confirmation', compact("datas"))
                    ->send();
                //informer le client quil var devoir valider son adresse mail
                header('location: '.$this->generateUrl("usersLogin"));
                exit();
            }
            unset($datas["password"]);
        }else{
            unset($errors);
        }
        return $this->render('user/subscribe', compact("errors", "datas"));
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
        return $this->render('user/'.$file, [
            'page' => $page,
            'message' => $message,
            'userInfos' => $userInfos
        ]);
    }

    public function updateUser()
    {

        if (count($_POST) > 0) {
            $id = (int) array_pop($_POST);//Stockage de la dernière case de $_POST dans $id
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
            return $this->profile($message);//Appel de la methode profile de ce controller pour redirection
            exit();
        }
    }
}
