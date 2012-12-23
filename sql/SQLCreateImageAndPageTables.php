<?php
// ===========================================================================================
//
// SQLCreateArticleTable.php
//
// SQL statements to create the tables for the Article tables.
//
// WARNING: Do not forget to check input variables for SQL injections.
//
// Author: Mats Ljungquist
//

// File definitions
$fileDef = Array(
	'CSizeFileName' 	=> 256,
	'CSizeFileNameUnique' 	=> 13, // Smallest size of PHP uniq().
	'CSizePathToDisk' 	=> 256,
	
	 // Max 127 chars according http://tools.ietf.org/html/rfc4288#section-4.2
	'CSizeMimetype'		=> 127,
    
        // Character encoding
        'DefaultCharacterSet'	=> 'utf8',
	'DefaultCollate'	=> 'utf8_unicode_ci',
);


// Get the tablenames
$tSida                  = DBT_Sida;
$tBildIntresse          = DBT_BildIntresse;
$tBildgrupp             = DBT_Bildgrupp;
$tFile                  = DBT_File;
$tUser 			= DBT_User;
$tGroup 		= DBT_Group;
$tGroupMember           = DBT_GroupMember;

// Get the SP names
$spPInsertOrUpdateSida	= DBSP_PInsertOrUpdateSida;
$spPGetSidaDetails	= DBSP_PGetSidaDetails;
$spPGetSidaDetailsById  = DBSP_PGetSidaDetailsById;
$spPInsertBildIntresse	= DBSP_PInsertBildIntresse;
$spPInsertBildgrupp     = DBSP_PInsertBildgrupp;
$spPListBildIntresse    = DBSP_PListBildIntresse;
$spPListBildgrupp       = DBSP_PListBildgrupp;

// Get the UDF names
$udfFCheckUserIsOwnerOrAdminOfSida = DBUDF_FCheckUserIsOwnerOrAdmin;

// Create the query
$query = <<<EOD
  
--
-- Table for Sida
--
DROP TABLE IF EXISTS {$tSida};
CREATE TABLE {$tSida} (

  -- Primary key(s)
  idSida INT AUTO_INCREMENT NOT NULL PRIMARY KEY,

  -- Foreign keys
  Sida_idUser INT NOT NULL,
  FOREIGN KEY (Sida_idUser) REFERENCES {$tUser}(idUser),

  -- Attributes
  pageNameSida VARCHAR(100) NOT NULL,
  titleSida VARCHAR(256) NOT NULL,
  contentSida BLOB NOT NULL,
  createdSida DATETIME NOT NULL,
  modifiedSida DATETIME NULL
);
  
--
-- This table is used for marking interest in a picture (file)
--
DROP TABLE IF EXISTS {$tBildIntresse};
CREATE TABLE {$tBildIntresse} (

  -- Primary key(s)
  idBildIntresse INT AUTO_INCREMENT NOT NULL PRIMARY KEY,

  -- Foreign keys
  BildIntresse_idUser INT NOT NULL,
  FOREIGN KEY (BildIntresse_idUser) REFERENCES {$tUser}(idUser),
  BildIntresse_idFile INT NOT NULL,
  FOREIGN KEY (BildIntresse_idFile) REFERENCES {$tFile}(idFile)
) ENGINE MyISAM CHARACTER SET {$fileDef['DefaultCharacterSet']} COLLATE {$fileDef['DefaultCollate']};
  
--
-- This table is used for grouping images for a certain user.
--
DROP TABLE IF EXISTS {$tBildgrupp};
CREATE TABLE {$tBildgrupp} (

  -- Primary key(s)
  idBildgrupp INT AUTO_INCREMENT NOT NULL PRIMARY KEY,

  -- Foreign keys
  Bildgrupp_idUser INT NOT NULL,
  FOREIGN KEY (Bildgrupp_idUser) REFERENCES {$tUser}(idUser),
  Bildgrupp_idFile INT NOT NULL,
  FOREIGN KEY (Bildgrupp_idFile) REFERENCES {$tFile}(idFile),
  
  -- Attributes
  nameBildgrupp VARCHAR(256) NOT NULL
) ENGINE MyISAM CHARACTER SET {$fileDef['DefaultCharacterSet']} COLLATE {$fileDef['DefaultCollate']};

--
-- SP to insert or update article
-- If article id is 0 then insert, else update
--
DROP PROCEDURE IF EXISTS {$spPInsertOrUpdateSida};
CREATE PROCEDURE {$spPInsertOrUpdateSida}
(
	INOUT aSidaId INT,
	IN aUserId INT,
        IN aPageName VARCHAR(100),
	IN aTitle VARCHAR(256),
	IN aContent BLOB
)
BEGIN
	IF aSidaId = 0 THEN
	BEGIN
		INSERT INTO {$tSida}
			(Sida_idUser, pageNameSida, titleSida, contentSida, createdSida)
			VALUES
			(aUserId, aPageName, aTitle, aContent, NOW());
		SET aSidaId = LAST_INSERT_ID();
	END;
	ELSE
	BEGIN
		UPDATE {$tSida} SET
			titleSida       = aTitle,
			contentSida 	= aContent,
			modifiedSida	= NOW()
		WHERE
			idSida = aSidaId  AND
			{$udfFCheckUserIsOwnerOrAdminOfSida}(aSidaId, aUserId)
		LIMIT 1;
	END;
	END IF;
END;

--
-- SP to get the contents of an article
--
DROP PROCEDURE IF EXISTS {$spPGetSidaDetails};
CREATE PROCEDURE {$spPGetSidaDetails}
(
	IN aPageName VARCHAR(100)
)
BEGIN
	SELECT
                A.idSida AS id,
		A.titleSida AS title,
		A.contentSida AS content,
		A.createdSida AS created,
		A.modifiedSida AS modified,
		COALESCE(A.modifiedSida, A.createdSida) AS latest,
		U.nameUser AS username,
                A.Sida_idUser AS userId
	FROM {$tSida} AS A
		INNER JOIN {$tUser} AS U
			ON A.Sida_idUser = U.idUser
	WHERE
		pageNameSida = aPageName
        LIMIT 1;
END;
                
--
-- SP to get the contents of an article
--
DROP PROCEDURE IF EXISTS {$spPGetSidaDetailsById};
CREATE PROCEDURE {$spPGetSidaDetailsById}
(
	IN aPageId INT
)
BEGIN
	SELECT
                A.idSida AS id,
		A.titleSida AS title,
		A.contentSida AS content,
		A.createdSida AS created,
		A.modifiedSida AS modified,
		COALESCE(A.modifiedSida, A.createdSida) AS latest,
		U.nameUser AS username,
                A.Sida_idUser AS userId
	FROM {$tSida} AS A
		INNER JOIN {$tUser} AS U
			ON A.Sida_idUser = U.idUser
	WHERE
		A.idSida = aPageId
        LIMIT 1;
END;
                
--
-- SP to insert bildintresse
-- If article id is 0 then insert, else update
--
DROP PROCEDURE IF EXISTS {$spPInsertBildIntresse};
CREATE PROCEDURE {$spPInsertBildIntresse}
(
	IN aUserId INT,
	IN aFileId INT
)
BEGIN
        INSERT INTO {$tBildIntresse}
                (BildIntresse_idUser, BildIntresse_idFile)
        VALUES (aUserId, aFileId);
END;
        
--
-- SP to insert bildgrupp
-- If article id is 0 then insert, else update
--
DROP PROCEDURE IF EXISTS {$spPInsertBildgrupp};
CREATE PROCEDURE {$spPInsertBildgrupp}
(
	IN aUserId INT,
	IN aFileId INT,
        IN aName VARCHAR(256)
)
BEGIN
        INSERT INTO {$tBildgrupp}
                (Bildgrupp_idUser, Bildgrupp_idFile, nameBildgrupp)
        VALUES (aUserId, aFileId, aName);
END;
        
--
-- SP to list bildintresse
--
DROP PROCEDURE IF EXISTS {$spPListBildIntresse};
CREATE PROCEDURE {$spPListBildIntresse}
(
	IN aUserId INT
)
BEGIN
        SELECT
            BildIntresse_idFile AS idFile
        FROM {$tBildIntresse}
        WHERE
            BildIntresse_idUser = aUserId
        ;
END;
        
--
-- SP to list bildgrupp
--
DROP PROCEDURE IF EXISTS {$spPListBildgrupp};
CREATE PROCEDURE {$spPListBildgrupp}
(
	IN aUserId INT
)
BEGIN
        SELECT
            nameBildgrupp AS name,
            Bildgrupp_idFile AS idFile
        FROM {$tBildgrupp}
        WHERE
            Bildgrupp_idUser = aUserId
        ;
END;

--
--  Create UDF that checks if user owns article or is member of group adm.
--
DROP FUNCTION IF EXISTS {$udfFCheckUserIsOwnerOrAdminOfSida};
CREATE FUNCTION {$udfFCheckUserIsOwnerOrAdminOfSida}
(
	aSidaId INT,
	aUserId INT
)
RETURNS BOOLEAN
READS SQL DATA
BEGIN
	DECLARE isAdmin INT;
	DECLARE isOwner INT;

	SELECT idUser INTO isAdmin
	FROM {$tUser} AS U
		INNER JOIN {$tGroupMember} AS GM
			ON U.idUser = GM.GroupMember_idUser
		INNER JOIN {$tGroup} AS G
			ON G.idGroup = GM.GroupMember_idGroup
	WHERE
		idGroup = 'adm' AND
		idUser = aUserId;

	SELECT idUser INTO isOwner
	FROM {$tUser} AS U
		INNER JOIN {$tSida} AS A
			ON U.idUser = A.Sida_idUser
	WHERE
		idSida = aSidaId AND
		idUser = aUserId;

	RETURN (isAdmin OR isOwner);
END;
                
-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- Insert some default pages
--

SET @aSidaId = 0;
CALL {$spPInsertOrUpdateSida}(@aSidaId, 2, 'PIndex.php', 'Ändra mig', 'Ändra mig');
SET @aSidaId = 0;
CALL {$spPInsertOrUpdateSida}(@aSidaId, 2, 'PAdminIndex.php', 'Ändra mig', 'Ändra mig');

EOD;


?>