<?php
// ===========================================================================================
//
// SAdminList.php
//
// SQL statements to login a user and validate to database.
//
// WARNING: Do not forget to check input variables for SQL injections.
//
// Author: Mats Ljungquist
//

// Get the tablenames
$tableUser       = DBT_User;
$tableGroup      = DBT_Group;
$tableGroupMember  = DBT_GroupMember;

global $orderStr;
$orderStr = $mysqli->real_escape_string($orderStr);

$query = <<< EOD
SELECT
	idUser,
	accountUser,
        nameUser,
        lastLoginUser,
	emailUser,
	idGroup,
	nameGroup
FROM {$tableUser} AS U
	INNER JOIN {$tableGroupMember} AS GM
		ON U.idUser = GM.GroupMember_idUser
	INNER JOIN {$tableGroup} AS G
		ON G.idGroup = GM.GroupMember_idGroup
WHERE deletedUser = FALSE
{$orderStr}
EOD;

?>