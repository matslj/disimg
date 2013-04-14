<?php

// ===========================================================================================
// CUserData
//
// Description: 
// Class for storing user data.
//
// Author: Mats Ljungquist
//
class user_CUserData {
    
    private $id;
    private $account;
    private $name;
    private $email;
    private $avatar;
    private $idGroup;

    public function __construct($idUser, $accountUser, $nameUser, $emailUser, $avatarUser, $idGroup) {
        $this -> id = $idUser;
        $this -> account = $accountUser;
        $this -> name = $nameUser;
        $this -> email = $emailUser;
        $this -> avatar = $avatarUser;
        $this -> idGroup = $idGroup;
    }

    public function __destruct() {
        ;
    }
    
    public function getId() {
        return $this -> id;
    }

    public function setId($id) {
        $this -> id = $id;
    }

    public function getAccount() {
        return $this -> account;
    }

    public function setAccount($account) {
        $this -> account = $account;
    }

    public function getName() {
        return $this -> name;
    }

    public function setName($name) {
        $this -> name = $name;
    }

    public function getEmail() {
        return $this -> email;
    }

    public function setEmail($email) {
        $this -> email = $email;
    }

    public function getAvatar() {
        return $this -> avatar;
    }

    public function setAvatar($avatar) {
        $this -> avatar = $avatar;
    }

    public function getIdGroup() {
        return $this -> idGroup;
    }

    public function setIdGroup($idGroup) {
        $this -> idGroup = $idGroup;
    }
    
    public function isAdmin() {
        return strcmp($this->idGroup, 'adm') == 0;
    }
    
    public function isUser($aUserId) {
        return $aUserId === $this -> id;
    }

}

?>