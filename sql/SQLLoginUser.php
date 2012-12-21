<?php
// ===========================================================================================
//
// SQLLoginUser.php
//
// SQL statements to login a user and validate to database.
//
// WARNING: Do not forget to check input variables for SQL injections.
//
// Author: Mikael Roos
//


// Get the tablenames
$tUser 			= DBT_User;
$tGroup 		= DBT_Group;
$tGroupMember 	= DBT_GroupMember;

// Prevent SQL injections
global $user, $password;
$user 		= $mysqli->real_escape_string($user);
$password 	= $mysqli->real_escape_string($password);

// Create the query
$query .= <<< EOD
SELECT
	idUser AS id,
	accountUser AS account,
	GroupMember_idGroup AS groupid
FROM {$tUser} AS U
	INNER JOIN {$tGroupMember} AS GM
		ON U.idUser = GM.GroupMember_idUser
WHERE
	accountUser		= '{$user}' AND
	passwordUser 	= md5('{$password}')
;
EOD;


?>