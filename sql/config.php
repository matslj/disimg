<?php
// ===========================================================================================
//
// config.php
//
// Config-file for database and SQL related issues. All SQL-statements are usually stored in this
// directory (TP_SQLPATH). This files contains global definitions for table names and so.
//
// Author: Mikael Roos, mos@bth.se
//

// -------------------------------------------------------------------------------------------
//
// Settings for the database connection
//
define('DB_HOST', 	'localhost');           // The database host
define('DB_USER', 	'mats');		// The username of the database
define('DB_PASSWORD', 	'hemligt');		// The users password
define('DB_DATABASE', 	'sanxion');		// The name of the database to use

//
// The following supports having many databases in one database by using table/view prefix.
//
define('DB_PREFIX', 'ddd_');    // Prefix to use infront of tablename and views

// -------------------------------------------------------------------------------------------
//
// Define the names for the database (tables, views, procedures, functions, triggers)
//
define('DBT_User', 		DB_PREFIX . 'User');
define('DBT_Group', 		DB_PREFIX . 'Group');
define('DBT_GroupMember',	DB_PREFIX . 'GroupMember');
define('DBT_Statistics',	DB_PREFIX . 'Statistics');
define('DBT_Article',		DB_PREFIX . 'Article');
define('DBT_BildIntresse',	DB_PREFIX . 'BildIntresse');
define('DBT_Bildgrupp',		DB_PREFIX . 'Bildgrupp');
define('DBT_File', 		DB_PREFIX . 'File');
define('DBT_Sida',		DB_PREFIX . 'Sida');


// Stored routines concerning articles/posts
define('DBSP_PGetArticleDetailsAndArticleList',         DB_PREFIX . 'PGetArticleDetailsAndArticleList');
define('DBSP_PGetArticleDetails',			DB_PREFIX . 'PGetArticleDetails');
define('DBSP_PInsertOrUpdateArticle',			DB_PREFIX . 'PInsertOrUpdateArticle');
define('DBSP_PGetLatestTopicsList',			DB_PREFIX . 'PGetLatestTopicsList');
define('DBSP_PGetTopicDetailsAndPosts',			DB_PREFIX . 'PGetTopicDetailsAndPosts');
define('DBSP_PGetTopicFirstEntryDetails',		DB_PREFIX . 'PGetTopicFirstEntryDetails');
define('DBSP_PGetTopicLastEntryDetails',		DB_PREFIX . 'PGetTopicLastEntryDetails');
define('DBSP_PGetArticleAndTopicDetails',		DB_PREFIX . 'PGetArticleAndTopicDetails');

// Stored routines concerning page and pictures
define('DBSP_PInsertOrUpdateSida',	DB_PREFIX . 'PInsertOrUpdateSida');
define('DBSP_PGetSidaDetails',		DB_PREFIX . 'PGetSidaDetails');
define('DBSP_PGetSidaDetailsById',		DB_PREFIX . 'PGetSidaDetailsById');
define('DBSP_PInsertBildIntresse',	DB_PREFIX . 'PInsertBildIntresse');
define('DBSP_PInsertBildgrupp',		DB_PREFIX . 'PInsertBildgrupp');
define('DBSP_PListBildIntresse',	DB_PREFIX . 'PListBildIntresse');
define('DBSP_PListBildgrupp',		DB_PREFIX . 'PListBildgrupp');
define('DBUDF_FCheckUserIsOwnerOrAdminOfSida',    DB_PREFIX . 'FCheckUserIsOwnerOrAdminOfSida');
define('DBUDF_CheckUserIsAdmin',	DB_PREFIX . 'FCheckUserIsAdmin');

// Stored routines concerning user
define('DBSP_AuthenticateUser',             DB_PREFIX . 'PAuthenticateUser');
define('DBSP_CreateUser',                   DB_PREFIX . 'PCreateUser');
define('DBSP_GetUserDetails',               DB_PREFIX . 'PGetUserDetails');
define('DBSP_SetUserDetails',               DB_PREFIX . 'PSetUserDetails');
define('DBSP_SetUserPassword',              DB_PREFIX . 'PSetUserPassword');
define('DBSP_SetUserEmail',                 DB_PREFIX . 'PSetUserEmail');
define('DBSP_UpdateLastLogin',              DB_PREFIX . 'PUpdateLastLogin');
define('DBSP_SetUserAvatar',                DB_PREFIX . 'PSetUserAvatar');
define('DBSP_SetUserGravatar',              DB_PREFIX . 'PSetUserGravatar');
define('DBUDF_FCheckUserIsOwnerOrAdmin',    DB_PREFIX . 'FCheckUserIsOwnerOrAdmin');
define('DBUDF_GetGravatarLinkFromEmail',    DB_PREFIX . 'FGetGravatarLinkFromEmail');
define('DBSP_SetUserNameAndEmail',          DB_PREFIX . 'PSetUserNameAndEmail');
define('DBSP_CreateUserAccountOrEmail',     DB_PREFIX . 'PCreateUserAccountOrEmail');
define('DBSP_DeleteUser',                   DB_PREFIX . 'PDeleteUser');

// Stored routines concering file
define('DBSP_InsertFile',                   DB_PREFIX . 'PInsertFile');
define('DBSP_FileUpdateUniqueName',         DB_PREFIX . 'PFileUpdateUniqueName');
define('DBSP_FileDetails',                  DB_PREFIX . 'PFileDetails');
define('DBSP_FileByIdDetails',              DB_PREFIX . 'PFileByIdDetails');
define('DBSP_FileDetailsUpdate',            DB_PREFIX . 'PFileDetailsUpdate');
define('DBSP_ListFiles',                    DB_PREFIX . 'PListFiles');
define('DBSP_UseReferenceToListFiles',      DB_PREFIX . 'PUseReferenceToListFiles');
define('DBSP_FileDetailsDeleted',           DB_PREFIX . 'PFileDetailsDeleted');
define('DBUDF_FileCheckPermission',         DB_PREFIX . 'FFileCheckPermission');
define('DBUDF_FileDelete',                  DB_PREFIX . 'FFileDelete');

// Triggers
define('DBTR_TInsertUser',		DB_PREFIX . 'TInsertUser');
define('DBTR_TAddArticle',		DB_PREFIX . 'TAddArticle');
?>