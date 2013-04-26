<?php

// ===========================================================================================
// user_CUserRepositoryOLD
//
// Description: 
// This class is responsible for storing all active users in a session variable
// for system wide access. If no users are present in session memory, users will be
// read into memory from the database.
//
// This class asumes that the number of users in the system is low. For systems with
// a large number of users another approach may be needed.
// 
// REMEMBER: __autoload before session_start(); Otherwise odd errors might occur.
//
// Author: Mats Ljungquist
//
class user_CUserRepositoryOLD {
    
    private $users;

    private function __construct($theUsers) {
        $this->users = $theUsers;
    }

    public function __destruct() {
        ;
    }
    
    public static function getInstance($theDatabase) {
        // DB connection is required
        if (empty($theDatabase)) {
            return null;
        }

        // Check if the list of users are stored in the session. If not
        // populate session with ursers from the database, and set
        // users in this handler class.
        if (!isset($_SESSION[__CLASS__]['ur'])) {
            self::repopulateWithUsersFromDB($theDatabase);
        }
        $tempUsers = $_SESSION[__CLASS__]['ur'];
        return new self($tempUsers);
    }

    public static function repopulateWithUsersFromDB($theDatabase) {
        // Get the tablenames
        $tableUser       = DBT_User;
        $tableGroup      = DBT_Group;
        $tableGroupMember  = DBT_GroupMember;

        $query = <<< EOD
            SELECT
                    idUser,
                    accountUser,
                    nameUser,
                    lastLoginUser,
                    emailUser,
                    avatarUser,
                    idGroup,
                    nameGroup
            FROM {$tableUser} AS U
                    INNER JOIN {$tableGroupMember} AS GM
                            ON U.idUser = GM.GroupMember_idUser
                    INNER JOIN {$tableGroup} AS G
                            ON G.idGroup = GM.GroupMember_idGroup
            WHERE deletedUser = FALSE;
EOD;

         $res = $theDatabase->Query($query);
         $tempUsers = array();
         while($row = $res->fetch_object()) {
             $tempUsers[$row->idUser] = new user_CUserData($row->idUser, $row->accountUser, $row->nameUser, $row->emailUser, $row->avatarUser, $row->idGroup);
         }

         // Store in session
         $_SESSION[__CLASS__]['ur'] = $tempUsers;
    }

    public function getUser($idUser) {
        return $users[$idUser];
    }

    public function getUsers() {
        return $this->users;
    }
    
}

?>