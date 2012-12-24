<?php
// ===========================================================================================
//
// File: SQLCoreFile.php
//
// Description: SQL statements for storing files.
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

$tFile         = DBT_File;
$tUser         = DBT_User;
$tGroup        = DBT_Group;
$tGroupMember  = DBT_GroupMember;

$spInsertFile = DBSP_InsertFile;
$spFileUpdateUniqueName = DBSP_FileUpdateUniqueName;
$spFileDetails = DBSP_FileDetails;
$spFileByIdDetails = DBSP_FileByIdDetails;
$spFileDetailsUpdate = DBSP_FileDetailsUpdate;
$spListFiles = DBSP_ListFiles;
$spUseReferenceToListFiles = DBSP_UseReferenceToListFiles;
$spFileDetailsDeleted = DBSP_FileDetailsDeleted;
$udfFileCheckPermission = DBUDF_FileCheckPermission;
$udfFileDelete = DBUDF_FileDelete;

$fCheckUserIsAdmin = DBUDF_CheckUserIsAdmin;

// Create the query
$query = <<<EOD

-- =============================================================================================
--
-- SQL for File
--
-- =============================================================================================


-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
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
	File_idUser INT UNSIGNED NOT NULL,
	FOREIGN KEY (File_idUser) REFERENCES {$tUser}(idUser),
	
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


-- +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
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
			(File_idUser, nameFile, pathToDiskFile, sizeFile, mimetypeFile, createdFile) 
		VALUES 
			(aUserId, aFilename, aPathToDisk, aSize, aMimetype, NOW());
	
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
-- SP to list all files
--
DROP PROCEDURE IF EXISTS {$spListFiles};
CREATE PROCEDURE {$spListFiles}
(
	IN aUserId INT UNSIGNED
)
BEGIN
	SELECT 
		File_idUser AS owner, 
		nameFile AS name, 
		uniqueNameFile AS uniquename,
		pathToDiskFile AS path, 
		sizeFile AS size, 
		mimetypeFile AS mimetype, 
		createdFile AS created,
		modifiedFile AS modified,
		deletedFile AS deleted
	FROM {$tFile}
	WHERE
		File_idUser = aUserId AND
		deletedFile IS NULL;
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
        
        DELETE FROM {$tFile} WHERE idFile = fileid;
        
        -- Below I return 0 even though it is not entierly correct to do so.
	RETURN 0;
END wrap;


EOD;


?>