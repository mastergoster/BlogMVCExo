<?php
namespace App\Model\Entity;

use Core\Model\Entity;

use Core\Controller\Helpers\TextController;

class UserEntity extends Entity
{
    private $id;

    private $mail;

    private $lastname;

    private $firstname;

    private $address;

    private $city;

    private $zipCode;

    private $country;

    private $phone;

    private $password;

    private $token;

    private $createdAt;

    private $verify;

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of name
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Get the value of slug
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Get the value of content
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Get the value of created_at
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return new \DateTime($this->createdAt);
    }

    public function getCity() {
        return $this->city;
    }

    public function getZipCode() {
        return $this->zipCode;
    }

    public function getCountry() {
        return $this->country;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getMail() {
        return $this->mail;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getVerify() {
        return $this->verify;
    }

    public function setVerify() {
        return $this->verify;
    }

    public function setMail(string $mail) {
        $this->mail = $mail;
    }

    public function setPassword(string $password) {
        $password = password_hash(htmlspecialchars($password), PASSWORD_BCRYPT);
        $this->password = $password;
    }
}
