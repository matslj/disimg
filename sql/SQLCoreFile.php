<?php
// ===========================================================================================
//
// File: SQLCoreFile.php
//
// Description: SQL statements for storing files.
// 
// @author Mats Ljungquist
//
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

$tBildIntresse = DBT_BildIntresse;
$tFile         = DBT_File;
$tFolder       = DBT_Folder;
$tFolderUser   = DBT_FolderUser;
$tUser         = DBT_User;
$tGroup        = DBT_Group;
$tGroupMember  = DBT_GroupMember;

// Files
$spInsertFile = DBSP_InsertFile;
$spFileUpdateUniqueName = DBSP_FileUpdateUniqueName;
$spFileDetails = DBSP_FileDetails;
$spFileByIdDetails = DBSP_FileByIdDetails;
$spFileDetailsUpdate = DBSP_FileDetailsUpdate;
$spListFiles = DBSP_ListFiles;
$spListFilesInFolder = DBSP_ListFilesInFolder;
$spListFilesXXX = DBSP_ListFilesXXX;
$spListFilesInFolderXXX = DBSP_ListFilesInFolderXXX;
$spUseReferenceToListFiles = DBSP_UseReferenceToListFiles;
$spFileDetailsDeleted = DBSP_FileDetailsDeleted;
$udfFileCheckPermission = DBUDF_FileCheckPermission;
$udfFileDelete = DBUDF_FileDelete;

// Folders
$spInsertFolder = DBSP_InsertFolder;
$spInsertFolderUser = DBSP_InsertFolderUser;
$spListFolders = DBSP_ListFolders;
$spListFoldersByUser = DBSP_ListFoldersByUser;
$spListFoldersByUserOnly = DBSP_ListFoldersByUserOnly;
$spListAllAccessedFiles = DBSP_ListAllAccessedFiles;
$spListAllAccessedFilesInFolder = DBSP_ListAllAccessedFilesInFolder;
$spDetailFolder = DBSP_DetailFolder;
$spDeleteFolderUser = DBSP_DeleteFolderUser;
$udfFileOfInterest = DBUDF_FFileOfInterest;
$udfFileUpdateFolder = DBUDF_FileUpdateFolder;
$udfNumberOfFilesInFolder = DBUDF_NumberOfFilesInFolder;
$udfFolderDelete = DBUDF_FolderDelete;

// Misc
$fCheckUserIsAdmin = DBUDF_CheckUserIsAdmin;

// Create the query
$query = <<<EOD

-- =============================================================================================
--
-- SQL for Folders & Files
--
-- =============================================================================================

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- Table that connects folders with users. A user can be have many folders and a folder can
-- have many users.
--
DROP TABLE IF EXISTS {$tFolderUser};

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- Table for Folder
-- Observe that there are no tree hierarki only a totaly flat folder structure.
--
DROP TABLE IF EXISTS {$tFolder};
CREATE TABLE {$tFolder} (

	-- Primary key(s)
	idFolder INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
	
	-- Attributes
        nameFolder VARCHAR({$fileDef['CSizeFileName']}) NOT NULL
);

CREATE TABLE {$tFolderUser} (

        -- Primary key(s)
        --
        -- The PK is the combination of the two foreign keys, see below.
        --

        -- Foreign keys
        FolderUser_idUser INT NOT NULL,
        FolderUser_idFolder INT NOT NULL,

        FOREIGN KEY (FolderUser_idUser) REFERENCES {$tUser}(idUser),
        FOREIGN KEY (FolderUser_idFolder) REFERENCES {$tFolder}(idFolder),

        PRIMARY KEY (FolderUser_idUser, FolderUser_idFolder)
);

-- ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- Table for File
--
-- uniqueNameFile must be unique in combination with the userid.
--
DROP TABLE IF EXISTS {$tFile};
CREATE TABLE {$tFile} (

	-- Primary key(s)
	idFile INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
	
	-- Foreign keys
        -- Creator of the file
	File_idUser INT UNSIGNED NOT NULL,
	FOREIGN KEY (File_idUser) REFERENCES {$tUser}(idUser),
        -- Folder containing the file
        File_idFolder INT NULL,
	FOREIGN KEY (File_idFolder) REFERENCES {$tFolder}(idFolder),
	
	-- Attributes
	nameFile VARCHAR({$fileDef['CSizeFileName']}) NOT NULL,
	pathToDiskFile VARCHAR({$fileDef['CSizePathToDisk']}) NOT NULL,
	uniqueNameFile VARCHAR({$fileDef['CSizeFileNameUnique']}) NULL UNIQUE,
	sizeFile INT UNSIGNED NOT NULL,
	mimetypeFile VARCHAR({$fileDef['CSizeMimetype']}) NOT NULL,
	createdFile DATETIME NOT NULL,
	modifiedFile DATETIME NULL,
	deletedFile DATETIME NULL,
        description VARCHAR(256) NULL,

	-- Index
	INDEX (File_idUser),
	INDEX (uniqueNameFile)

) ENGINE MyISAM CHARACTER SET {$fileDef['DefaultCharacterSet']} COLLATE {$fileDef['DefaultCollate']};


-- Y++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to insert new file. 
--
-- The unique key is created from caller. First the file entry is inserted. Then try to add the
-- unique key. 
--
-- @aStatus contains the following values:
-- 0 Success to update unique key.
-- 1 failed to update unique key.
-- 
-- If failed, the caller must then create a new unique key and update it.
-- This key is commonly used as a unique value to identify the file, a value that can be used 
-- in urls.
--
DROP PROCEDURE IF EXISTS {$spInsertFile};
CREATE PROCEDURE {$spInsertFile}
(
	IN aUserId INT UNSIGNED,
        IN aFolderId INT UNSIGNED,
	IN aFilename VARCHAR({$fileDef['CSizeFileName']}), 
	IN aPathToDisk VARCHAR({$fileDef['CSizePathToDisk']}), 
	IN aUniqueFilename VARCHAR({$fileDef['CSizeFileNameUnique']}),
	IN aSize INT UNSIGNED,
	IN aMimetype VARCHAR({$fileDef['CSizeMimetype']}),
	OUT aFileId INT UNSIGNED,
	OUT aStatus TINYINT UNSIGNED
)
BEGIN
	-- Insert the file
	INSERT INTO {$tFile}	
			(File_idUser, File_idFolder, nameFile, pathToDiskFile, sizeFile, mimetypeFile, createdFile) 
		VALUES 
			(aUserId, aFolderId, aFilename, aPathToDisk, aSize, aMimetype, NOW());
	
	SELECT LAST_INSERT_ID() INTO aFileId;
	
	-- Try to update the unique key, will succeed most of the times
	UPDATE IGNORE {$tFile} 
		SET	uniqueNameFile = aUniqueFilename
		WHERE idFile = LAST_INSERT_ID() LIMIT 1;

	-- 1 if inserted, 0 if duplicate key
	SELECT (ROW_COUNT()+1) MOD 2 INTO aStatus;

END;

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to update unique key for file. 
--
-- Use this to update the unique key if it failed during insertion of a new file. 
-- You may call this procedure until it succeeds.
--
-- @aStatus contains the following values:
-- 0 Success to update unique key.
-- 1 failed to update unique key.
--
DROP PROCEDURE IF EXISTS {$spFileUpdateUniqueName};
CREATE PROCEDURE {$spFileUpdateUniqueName}
(
	IN aFileId INT UNSIGNED,
	IN aUniqueFilename VARCHAR({$fileDef['CSizeFileNameUnique']}),
	OUT aStatus TINYINT UNSIGNED
)
BEGIN
	-- Try to update the unique key
	UPDATE IGNORE {$tFile} 
		SET	uniqueNameFile = aUniqueFilename
		WHERE idFile = aFileId;

	-- 1 if inserted, 0 if duplicate key
	SELECT (ROW_COUNT()+1) MOD 2 INTO aStatus;

END;

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to insert folder
--
DROP PROCEDURE IF EXISTS {$spInsertFolder};
CREATE PROCEDURE {$spInsertFolder}
(
    IN aName VARCHAR(100)
)
BEGIN
    INSERT INTO {$tFolder}
        (nameFolder)
    VALUES
        (aName);
END;
   
-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to connect folder and user
--
DROP PROCEDURE IF EXISTS {$spInsertFolderUser};
CREATE PROCEDURE {$spInsertFolderUser}
(
    IN aUserId INT UNSIGNED,
    IN aFolderId INT
)
BEGIN
    INSERT INTO {$tFolderUser}
        (FolderUser_idUser,FolderUser_idFolder)
    VALUES
        (aUserId,aFolderId);
END;
   
-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to delete the connection between folder and user
--
DROP PROCEDURE IF EXISTS {$spDeleteFolderUser};
CREATE PROCEDURE {$spDeleteFolderUser}
(
    IN aUserId INT UNSIGNED,
    IN aFolderId INT
)
BEGIN
    DELETE FROM {$tFolderUser}
    WHERE FolderUser_idFolder = aFolderId
          AND FolderUser_idUser = aUserId;
END;

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to obtain folder details
--
DROP PROCEDURE IF EXISTS {$spDetailFolder};
CREATE PROCEDURE {$spDetailFolder}
(
    IN aFolderId INT
)
BEGIN
    SELECT
        idFolder AS id,
        nameFolder AS name,
        {$udfNumberOfFilesInFolder}(idFolder) as facet
    FROM {$tFolder}
    WHERE idFolder = aFolderId;
END;

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to list all folders and the number of files in each folder (facet)
-- Supports dynamic ORDER BY.
--
DROP PROCEDURE IF EXISTS {$spListFolders};
CREATE PROCEDURE {$spListFolders}
(
    IN orderSQL varchar(100)
)
BEGIN
    -- Enter the dynamic SQL statement into the
    -- variable @SQLStatement
    SET @SQLStatement = CONCAT('SELECT A.idFolder as id, A.nameFolder as name, {$udfNumberOfFilesInFolder}(idFolder) as facet ',
                             'FROM {$tFolder} AS A ',orderSQL);

    PREPARE stmt FROM @SQLStatement;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;        

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to list all folders and, also, mark those folders that 'aUserId' has access to.
-- Also list number of files in each folder.
-- Supports dynamic ORDER BY.
--
DROP PROCEDURE IF EXISTS {$spListFoldersByUser};
CREATE PROCEDURE {$spListFoldersByUser}
(
    IN orderSQL varchar(100),
    IN aUserId INT UNSIGNED
)
BEGIN
    -- Enter the dynamic SQL statement into the
    -- variable @SQLStatement
    SET @SQLStatement = CONCAT('SELECT A.idFolder as id, A.nameFolder as name, {$udfNumberOfFilesInFolder}(idFolder) as facet, (FU.uid IS NOT NULL) as mark ',
                             'FROM {$tFolder} AS A LEFT OUTER JOIN (SELECT FolderUser_idUser as uid, FolderUser_idFolder AS id FROM {$tFolderUser} where FolderUser_idUser = ',aUserId,') AS FU on A.idFolder = FU.id ',orderSQL);

    PREPARE stmt FROM @SQLStatement;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;
   
-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to list all folders and, also, mark those folders that 'aUserId' has access to.
-- Also list number of files in each folder.
-- Supports dynamic ORDER BY.
--
DROP PROCEDURE IF EXISTS {$spListFoldersByUserOnly};
CREATE PROCEDURE {$spListFoldersByUserOnly}
(
    IN aUserId INT UNSIGNED
)
BEGIN
    SELECT
        A.idFolder as id, 
        A.nameFolder as name, 
        {$udfNumberOfFilesInFolder}(idFolder) as facet
    FROM {$tFolder} AS A 
        INNER JOIN {$tFolderUser} AS FU ON A.idFolder = FU.FolderUser_idFolder
    WHERE
        FU.FolderUser_idUser = aUserId;
END;

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to list all files
--
DROP PROCEDURE IF EXISTS {$spListFilesXXX};
CREATE PROCEDURE {$spListFilesXXX}
(
	IN aUserId INT UNSIGNED
)
BEGIN
	SELECT
                A.idFile AS id,
		A.File_idUser AS owner,
		A.nameFile AS name,
		A.uniqueNameFile AS uniquename,
		A.pathToDiskFile AS path,
		A.sizeFile AS size,
		A.mimetypeFile AS mimetype,
		A.createdFile AS created,
		A.modifiedFile AS modified,
		A.deletedFile AS deleted,
                U.accountUser AS account,
                IFNULL(F.nameFolder, "------") AS foldername
	FROM {$tFile} AS A
            INNER JOIN {$tUser} AS U
                    ON A.File_idUser = U.idUser
            LEFT OUTER JOIN {$tFolder} AS F
                    ON A.File_idFolder = F.idFolder
	WHERE
		A.File_idUser = aUserId AND
		deletedFile IS NULL
        ORDER BY createdFile DESC;
END;
   
DROP PROCEDURE IF EXISTS {$spListFiles};
CREATE PROCEDURE {$spListFiles}
(
	IN aUserId INT UNSIGNED,
        IN criteria varchar(100)
)
BEGIN
    -- Enter the dynamic SQL statement into the
    -- variable @SQLStatement
      
    SET @SQLStatement = CONCAT(
    
    'SELECT
            A.idFile AS id,
            A.File_idUser AS owner,
            A.nameFile AS name,
            A.uniqueNameFile AS uniquename,
            A.pathToDiskFile AS path,
            A.sizeFile AS size,
            A.mimetypeFile AS mimetype,
            A.createdFile AS created,
            A.modifiedFile AS modified,
            A.deletedFile AS deleted,
            U.accountUser AS account,
            IFNULL(F.nameFolder, "------") AS foldername
    FROM {$tFile} AS A
        INNER JOIN {$tUser} AS U
                ON A.File_idUser = U.idUser
        LEFT OUTER JOIN {$tFolder} AS F
                ON A.File_idFolder = F.idFolder
    WHERE
            A.File_idUser = ',aUserId,' AND
            deletedFile IS NULL
    ORDER BY createdFile DESC'
    
    ,criteria);

    PREPARE stmt FROM @SQLStatement;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;
   
-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to list all files in with a specific folder id
--
DROP PROCEDURE IF EXISTS {$spListFilesInFolderXXX};
CREATE PROCEDURE {$spListFilesInFolderXXX}
(
	IN aUserId INT UNSIGNED,
        IN aFolderId INT
)
BEGIN
	SELECT 
                A.idFile AS id,
		A.File_idUser AS owner,
		A.nameFile AS name,
		A.uniqueNameFile AS uniquename,
		A.pathToDiskFile AS path,
		A.sizeFile AS size,
		A.mimetypeFile AS mimetype,
		A.createdFile AS created,
		A.modifiedFile AS modified,
		A.deletedFile AS deleted,
                U.accountUser AS account,
                F.nameFolder AS foldername
	FROM {$tFile} AS A
            INNER JOIN {$tUser} AS U
                    ON A.File_idUser = U.idUser
            INNER JOIN {$tFolder} AS F
                    ON A.File_idFolder = F.idFolder
	WHERE
		A.File_idUser = aUserId AND
		deletedFile IS NULL AND
                A.File_idFolder = aFolderId
        ORDER BY createdFile DESC;
END;
   
-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to list all files in with a specific folder id
--
DROP PROCEDURE IF EXISTS {$spListFilesInFolder};
CREATE PROCEDURE {$spListFilesInFolder}
(
	IN aUserId INT UNSIGNED,
        IN aFolderId INT,
        IN criteria varchar(100)
)
BEGIN
    -- Enter the dynamic SQL statement into the
    -- variable @SQLStatement
    
    SET @SQLStatement = CONCAT(
    
    'SELECT 
        A.idFile AS id,
        A.File_idUser AS owner,
        A.nameFile AS name,
        A.uniqueNameFile AS uniquename,
        A.pathToDiskFile AS path,
        A.sizeFile AS size,
        A.mimetypeFile AS mimetype,
        A.createdFile AS created,
        A.modifiedFile AS modified,
        A.deletedFile AS deleted,
        U.accountUser AS account,
        F.nameFolder AS foldername
    FROM {$tFile} AS A
        INNER JOIN {$tUser} AS U
                ON A.File_idUser = U.idUser
        INNER JOIN {$tFolder} AS F
                ON A.File_idFolder = F.idFolder
    WHERE
            A.File_idUser = ',aUserId,' AND
            deletedFile IS NULL AND
            A.File_idFolder = ',aFolderId,
    ' ORDER BY createdFile DESC'
    
    ,criteria);
    
    PREPARE stmt FROM @SQLStatement;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to list all files which can be accessed by the user through the FolderUser-table.
--
DROP PROCEDURE IF EXISTS {$spListAllAccessedFiles};
CREATE PROCEDURE {$spListAllAccessedFiles}
(
	IN aUserId INT UNSIGNED,
        IN criteria varchar(100)
)
BEGIN
    -- Enter the dynamic SQL statement into the
    -- variable @SQLStatement
      
    SET @SQLStatement = CONCAT(
    
    'SELECT 
                A.idFile AS id,
		A.File_idUser AS owner,
		A.nameFile AS name,
		A.uniqueNameFile AS uniquename,
		A.pathToDiskFile AS path,
		A.sizeFile AS size,
		A.mimetypeFile AS mimetype,
		A.createdFile AS created,
		A.modifiedFile AS modified,
		A.deletedFile AS deleted,
                U.accountUser AS account,
                IFNULL(F.nameFolder, "------") AS foldername,
                {$udfFileOfInterest}(',aUserId,',A.idFile) AS interest
	FROM {$tFile} AS A
            INNER JOIN {$tUser} AS U
                    ON A.File_idUser = U.idUser
            INNER JOIN {$tFolder} AS F
                    ON A.File_idFolder = F.idFolder
            INNER JOIN {$tFolderUser} AS FU
                    ON A.File_idFolder = FU.FolderUser_idFolder
	WHERE
		FU.FolderUser_idUser = ',aUserId,' AND
		deletedFile IS NULL
        ORDER BY createdFile DESC'
        
    ,criteria);
    
    PREPARE stmt FROM @SQLStatement;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;
   
-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to list all files which can be accessed by the user through the FolderUser-table and
-- which has a specific folder id.
--
DROP PROCEDURE IF EXISTS {$spListAllAccessedFilesInFolder};
CREATE PROCEDURE {$spListAllAccessedFilesInFolder}
(
	IN aUserId INT UNSIGNED,
        IN aFolderId INT,
        IN criteria varchar(100)
)
BEGIN
    -- Enter the dynamic SQL statement into the
    -- variable @SQLStatement
      
    SET @SQLStatement = CONCAT(
    
    'SELECT 
        A.idFile AS id,
        A.File_idUser AS owner,
        A.nameFile AS name,
        A.uniqueNameFile AS uniquename,
        A.pathToDiskFile AS path,
        A.sizeFile AS size,
        A.mimetypeFile AS mimetype,
        A.createdFile AS created,
        A.modifiedFile AS modified,
        A.deletedFile AS deleted,
        U.accountUser AS account,
        F.nameFolder AS foldername,
        {$udfFileOfInterest}(',aUserId,',A.idFile) AS interest
                
	FROM {$tFile} AS A
            INNER JOIN {$tUser} AS U
                    ON A.File_idUser = U.idUser
            INNER JOIN {$tFolder} AS F
                    ON A.File_idFolder = F.idFolder
            INNER JOIN {$tFolderUser} AS FU
                    ON A.File_idFolder = FU.FolderUser_idFolder
	WHERE
		FU.FolderUser_idUser = ',aUserId,' AND
		deletedFile IS NULL AND
                A.File_idFolder = ',aFolderId,
        ' ORDER BY createdFile DESC'
        
    ,criteria);
    
    PREPARE stmt FROM @SQLStatement;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;   

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to show details of a file
--
DROP PROCEDURE IF EXISTS {$spFileDetails};
CREATE PROCEDURE {$spFileDetails}
(
	IN aUserId INT UNSIGNED,
	IN aUniqueFilename VARCHAR({$fileDef['CSizeFileNameUnique']}),
	OUT aSuccess TINYINT UNSIGNED	
)
BEGIN
	DECLARE fileid INT UNSIGNED;
	
	-- Get the id of the file
	SELECT idFile INTO fileid FROM {$tFile}
	WHERE
		uniqueNameFile = aUniqueFilename;

	-- Check permissions
	SELECT {$udfFileCheckPermission}(fileid, aUserId) INTO aSuccess;
		
	-- Get details from file
	SELECT 
		idFile AS fileid, 
		File_idUser AS userid, 
		U.accountUser AS owner, 
		nameFile AS name, 
		uniqueNameFile AS uniquename,
		pathToDiskFile AS path, 
		sizeFile AS size, 
		mimetypeFile AS mimetype, 
		createdFile AS created,
		modifiedFile AS modified,
		deletedFile AS deleted
	FROM {$tFile} AS F
		INNER JOIN {$tUser} AS U
			ON F.File_idUser = U.idUser
	WHERE
		uniqueNameFile = aUniqueFilename;
END;

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to show details of a file
--
DROP PROCEDURE IF EXISTS {$spFileByIdDetails};
CREATE PROCEDURE {$spFileByIdDetails}
(
	IN aFileId INT UNSIGNED
)
BEGIN
	-- Get details from file
	SELECT 
		idFile AS fileid, 
		File_idUser AS userid, 
		U.accountUser AS owner, 
		nameFile AS name, 
		uniqueNameFile AS uniquename,
		pathToDiskFile AS path, 
		sizeFile AS size, 
		mimetypeFile AS mimetype, 
		createdFile AS created,
		modifiedFile AS modified,
		deletedFile AS deleted
	FROM {$tFile} AS F
	WHERE
		uniqueNameFile = aUniqueFilename;
END;
  
-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- Function which returns the number of files in a folder.
--
-- Return values:
--  0 if folder is empty
--  n where n is the number of files in the folder
--
DROP FUNCTION IF EXISTS {$udfNumberOfFilesInFolder};
CREATE FUNCTION {$udfNumberOfFilesInFolder}
(
	aFolderId INT UNSIGNED
)
RETURNS INT UNSIGNED
READS SQL DATA
BEGIN
	DECLARE i INT UNSIGNED;
	
	-- File exists and user have permissions to update file?
	SELECT COUNT(idFile) INTO i FROM {$tFile} 
	WHERE 
		File_idFolder = aFolderId;
        IF i IS NULL THEN
            RETURN 0;
	END IF;	
	RETURN i;
END;   

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- Function to check if file exists and if user has permissions to use it.
--
-- Return values:
--  0 success
--  1 no permission to update file
--  2 file does not exists
--
DROP FUNCTION IF EXISTS {$udfFileCheckPermission};
CREATE FUNCTION {$udfFileCheckPermission}
(
	aFileId INT UNSIGNED,
	aUserId INT UNSIGNED
)
RETURNS TINYINT UNSIGNED
READS SQL DATA
BEGIN
	DECLARE i INT UNSIGNED;
	
	-- File exists and user have permissions to update file?
	SELECT idFile INTO i FROM {$tFile} 
	WHERE 
		idFile = aFileId AND
		(
			{$fCheckUserIsAdmin}(aUserId) OR
			File_idUser = aUserId
		);
	IF i IS NOT NULL THEN
		RETURN 0;
	END IF;	

	-- Does file exists?
	SELECT idFile INTO i FROM {$tFile} WHERE idFile = aFileId;
	IF i IS NULL THEN
		RETURN 2;
	END IF;

	-- So, file exists but user has no permissions to use/update file.
	RETURN 1;
END;
   
-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to delete folder
--
DROP FUNCTION IF EXISTS {$udfFolderDelete};
CREATE FUNCTION {$udfFolderDelete}
(
	aFolderId INT UNSIGNED
)
RETURNS TINYINT UNSIGNED
DETERMINISTIC
wrap: BEGIN
	DECLARE i INT UNSIGNED;
	
	-- Check permissions
	SELECT {$udfNumberOfFilesInFolder}(aFolderId) INTO i;

        -- If the return value from the udf is greater than zero, then the folder is not empty and may not be deleted.
        IF i>0 THEN
            RETURN 1;
	END IF;
        
        DELETE FROM {$tFolder} WHERE idFolder = aFolderId;
        
        -- Delete ok
	RETURN 0;
END wrap;

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to delete file
-- See funktion {$udfFileCheckPermission} for return values.
--
DROP FUNCTION IF EXISTS {$udfFileDelete};
CREATE FUNCTION {$udfFileDelete}
(
	aUniqueFilename VARCHAR({$fileDef['CSizeFileNameUnique']}),
	aUserId INT UNSIGNED
)
RETURNS TINYINT UNSIGNED
DETERMINISTIC
wrap: BEGIN
	DECLARE i INT UNSIGNED;
        DECLARE fileid INT UNSIGNED;
	
	-- Get the id of the file
	SELECT idFile INTO fileid FROM {$tFile}
	WHERE
		uniqueNameFile = aUniqueFilename;
	
	-- Check permissions
	SELECT {$udfFileCheckPermission}(fileid, aUserId) INTO i;

        -- If the return value from the udf is greater than zero something is wrong
        IF i>0 THEN
            RETURN i;
	END IF;
        
        -- Delete the file. But first delete all interest in the file.
        DELETE FROM {$tBildIntresse} WHERE BildIntresse_idFile = fileid;
        DELETE FROM {$tFile} WHERE idFile = fileid;
        
        -- Below I return 0 even though it is not entierly correct to do so.
	RETURN 0;
END wrap;

-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
--
-- SP to delete file
-- See funktion {$udfFileCheckPermission} for return values.
--
DROP FUNCTION IF EXISTS {$udfFileUpdateFolder};
CREATE FUNCTION {$udfFileUpdateFolder}
(
	aUniqueFilename VARCHAR({$fileDef['CSizeFileNameUnique']}),
	aUserId INT UNSIGNED,
        aFolderId INT UNSIGNED
)
RETURNS TINYINT UNSIGNED
DETERMINISTIC
wrap: BEGIN
	DECLARE i INT UNSIGNED;
        DECLARE fileid INT UNSIGNED;
	
	-- Get the id of the file
	SELECT idFile INTO fileid FROM {$tFile}
	WHERE
		uniqueNameFile = aUniqueFilename;
	
	-- Check permissions
	SELECT {$udfFileCheckPermission}(fileid, aUserId) INTO i;

        -- If the return value from the udf is greater than zero something is wrong
        IF i>0 THEN
            RETURN i;
	END IF;
        
        UPDATE {$tFile} 
            SET File_idFolder = aFolderId
            WHERE idFile = fileid;
        
        -- Below I return 0 even though it is not entierly correct to do so.
	RETURN 0;
END wrap;

CALL {$spInsertFolder}('Sommar');
CALL {$spInsertFolder}('Vinter');

EOD;

?>