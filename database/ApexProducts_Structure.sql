CREATE TABLE `Accreditations` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `OrganizationID` int unsigned DEFAULT NULL,
  `DepartmentID` int unsigned DEFAULT NULL,
  `ACCChestPain` enum('Y','N') NOT NULL DEFAULT 'N',
  `ACCHeartFailure` enum('Y','N') NOT NULL DEFAULT 'N',
  `ACCAtrialFib` enum('Y','N') NOT NULL DEFAULT 'N',
  `ACCCardiacCathLab` enum('Y','N') NOT NULL DEFAULT 'N',
  `ACCHeartCARE` enum('Y','N') NOT NULL DEFAULT 'N',
  `AHAMissionLifeline` enum('Y','N') NOT NULL DEFAULT 'N',
  `DNVStrokeReady` enum('Y','N') NOT NULL DEFAULT 'N',
  `DNVPrimarySC` enum('Y','N') NOT NULL DEFAULT 'N',
  `DNVCompSC` enum('Y','N') NOT NULL DEFAULT 'N',
  `HFAPStrokeReady` enum('Y','N') NOT NULL DEFAULT 'N',
  `HFAPPrimaryStroke` enum('Y','N') NOT NULL DEFAULT 'N',
  `HFAPCompStroke` enum('Y','N') NOT NULL DEFAULT 'N',
  `Magnet` enum('Y','N') NOT NULL DEFAULT 'N',
  `StateCertifiedSC` enum('Y','N') NOT NULL DEFAULT 'N',
  `TJCPrimarySC` enum('Y','N') NOT NULL DEFAULT 'N',
  `TJCCompSC` enum('Y','N') NOT NULL DEFAULT 'N',
  `TJCHeartFailure` enum('Y','N') NOT NULL DEFAULT 'N',
  `TJCStrokeRdy` enum('Y','N') NOT NULL DEFAULT 'N',
  `TJCSepsis` enum('Y','N') NOT NULL DEFAULT 'N',
  `TJCChestPain` enum('Y','N') NOT NULL DEFAULT 'N',
  `TJCThrombectomyCapable` enum('Y','N') NOT NULL DEFAULT 'N',
  `DNVStrokePrimaryPlus` enum('Y','N') NOT NULL DEFAULT 'N',
  `HFAPThrombSC` enum('Y','N') NOT NULL DEFAULT 'N',
  `ACCChestPainCPCenter` enum('Y','N') NOT NULL DEFAULT 'N',
  `ACCChestPainCPwPCI` enum('Y','N') NOT NULL DEFAULT 'N',
  `ACCChestPainCriticalAccess` enum('Y','N') NOT NULL DEFAULT 'N',
  `ACCChestPainFreeStandED` enum('Y','N') NOT NULL DEFAULT 'N',
  `ACCChestPainCPwResus` enum('Y','N') NOT NULL DEFAULT 'N',
  `TJCChestPainHAReady` enum('Y','N') NOT NULL DEFAULT 'N',
  `TJCChestPainPrimaryC` enum('Y','N') NOT NULL DEFAULT 'N',
  `TJCChestPainCompCenter` enum('Y','N') NOT NULL DEFAULT 'N',
  `ACCHeartFailureHFwOutput` enum('Y','N') NOT NULL DEFAULT 'N',
  `DNVHeartFailure` enum('Y','N') NOT NULL DEFAULT 'N',
  `StateCertifiedCP` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'State Certified Chest Pain',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `OrganizationID_UNIQUE` (`OrganizationID`),
  UNIQUE KEY `DepartmentID_UNIQUE` (`DepartmentID`)
) ENGINE=InnoDB AUTO_INCREMENT=1349 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Accreditors` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Name` varchar(10) NOT NULL COMMENT 'The short name of the accreditor',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ace_ContentElements` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `PageID` varchar(32) DEFAULT NULL COMMENT 'the page that this content is related to',
  `ContentType` varchar(32) DEFAULT NULL COMMENT 'the will be a class applied to this content',
  `Content` text,
  `MoreContent` text,
  `URL` varchar(255) DEFAULT NULL,
  `PublishedBy` varchar(64) DEFAULT NULL COMMENT 'the person actually publishing this content',
  `PublishedDate` datetime DEFAULT NULL COMMENT 'the date and time of publication',
  `Active` varchar(3) DEFAULT NULL COMMENT 'either Y or N',
  PRIMARY KEY (`ID`),
  KEY `active` (`Active`) /*!80000 INVISIBLE */,
  KEY `pageID` (`PageID`,`Active`),
  KEY `contentType` (`ContentType`,`PageID`),
  FULLTEXT KEY `fullText` (`PageID`)
) ENGINE=InnoDB AUTO_INCREMENT=478935 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ace_ContentHistory` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `ContentID` int DEFAULT NULL COMMENT 'original content ID',
  `PageID` varchar(32) DEFAULT NULL COMMENT 'the page that this content is related to',
  `ContentType` varchar(32) DEFAULT NULL COMMENT 'the will be a class applied to this content',
  `Content` text COMMENT 'the actual content for this content area',
  `MoreContent` text,
  `PublishedBy` varchar(64) DEFAULT NULL COMMENT 'the person actually creating or editing this content',
  `PublishedDate` datetime DEFAULT NULL COMMENT 'the date and time of the creation or edit',
  `ArchivedBy` varchar(64) DEFAULT NULL COMMENT 'the person actually publishing the content',
  `ArchivedDate` datetime DEFAULT NULL COMMENT 'the date and time the content was published',
  `Active` varchar(3) DEFAULT NULL COMMENT 'either Y or N',
  PRIMARY KEY (`ID`),
  KEY `product` (`PageID`)
) ENGINE=InnoDB AUTO_INCREMENT=790645 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ace_ContentRevisions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `PageID` varchar(32) DEFAULT NULL COMMENT 'the page that this revision is related to',
  `ContentID` int DEFAULT NULL COMMENT 'the specific content this is related to (ID in ace_PageContent)',
  `ContentRevision` varchar(512) DEFAULT NULL COMMENT 'the actual revision for this content area',
  `RevisionBy` varchar(64) DEFAULT NULL COMMENT 'the person actually creating or editing this revision',
  `RevisionDate` datetime DEFAULT NULL COMMENT 'the date and time of the revision',
  `Active` varchar(3) DEFAULT NULL COMMENT 'either Y or N',
  PRIMARY KEY (`ID`),
  KEY `product` (`PageID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ace_ContentStaging` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `PageID` varchar(32) DEFAULT NULL COMMENT 'the page that this content is related to',
  `ContentID` int unsigned DEFAULT NULL COMMENT 'the specific content this is related to (ID in ace_PageContent)',
  `ContentType` varchar(32) DEFAULT NULL COMMENT 'the will be a class applied to this content',
  `Content` text COMMENT 'the actual content for this content area',
  `MoreContent` text,
  `URL` varchar(255) DEFAULT NULL,
  `EditedBy` varchar(64) DEFAULT NULL COMMENT 'the person actually creating or editing this content',
  `EditedDate` datetime DEFAULT NULL COMMENT 'the date and time of the creation or edit',
  `ApprovedBy` varchar(64) DEFAULT NULL COMMENT 'the person actually publishing the content',
  `ApprovedDate` datetime DEFAULT NULL COMMENT 'the date and time the content was published',
  `Active` varchar(3) DEFAULT NULL COMMENT 'either Y or N',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ContentID_UNIQUE` (`ContentID`),
  KEY `product` (`PageID`) /*!80000 INVISIBLE */,
  KEY `contentID` (`ContentID`) /*!80000 INVISIBLE */,
  FULLTEXT KEY `fulltext` (`PageID`)
) ENGINE=InnoDB AUTO_INCREMENT=452281 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ACEChatLogs` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `Room` varchar(45) NOT NULL,
  `Message` varchar(255) NOT NULL,
  `CreationDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `UserKey` (`UserID`,`CreationDate`),
  KEY `Creation` (`CreationDate`,`UserID`),
  CONSTRAINT `UserKey` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `AdditionalProductInformation` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ProductID` int NOT NULL,
  `AdditionalInformation` text NOT NULL,
  `InfoType` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Adjectives` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Adjective` varchar(45) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Adjective_UNIQUE` (`Adjective`)
) ENGINE=InnoDB AUTO_INCREMENT=28480 DEFAULT CHARSET=utf8mb3 COMMENT='used to create random strings';

CREATE TABLE `AdminEvents` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `EmployeeID` int unsigned NOT NULL,
  `EventTypeID` int NOT NULL,
  `IPAddress` varchar(15) NOT NULL,
  `Notes` text,
  `EventDate` datetime NOT NULL,
  `EventInfo` text,
  PRIMARY KEY (`ID`),
  KEY `AdminEvents_dbfk_1_idx` (`EmployeeID`),
  KEY `AdminEvents_dbfk_2_idx` (`EventTypeID`),
  KEY `DateIndex` (`EventDate` DESC),
  CONSTRAINT `AdminEvents_dbfk_1` FOREIGN KEY (`EmployeeID`) REFERENCES `Employees` (`ID`),
  CONSTRAINT `AdminEvents_dbfk_2` FOREIGN KEY (`EventTypeID`) REFERENCES `EventTypes` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=347933 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `AiccLog` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Command` varchar(255) DEFAULT NULL,
  `Response` varchar(1000) DEFAULT NULL,
  `UserID` int DEFAULT NULL,
  `LMSServer` varchar(255) DEFAULT NULL,
  `LogDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Sync` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `UserIndex` (`UserID`),
  KEY `LMSServerIndex` (`LMSServer`)
) ENGINE=InnoDB AUTO_INCREMENT=38381391 DEFAULT CHARSET=utf8mb3 COMMENT='Used to track down what exactly is happening here....\n';

CREATE TABLE `Animals` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Animal` varchar(45) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1494 DEFAULT CHARSET=utf8mb3 COMMENT='Table for genearting random names';

CREATE TABLE `avgCourseTimes` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CourseID` int NOT NULL,
  `TimeInCourse` float NOT NULL,
  `NumUsers` int NOT NULL,
  `Name` varchar(45) NOT NULL,
  `prod` varchar(45) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1024 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `avgMonthlyCourseTimes` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CourseID` int NOT NULL,
  `TimeInCourse` float NOT NULL,
  `NumUsers` int NOT NULL,
  `Name` varchar(45) NOT NULL,
  `prod` varchar(45) NOT NULL,
  `Date` date NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=17523 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `avgPageTimes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `PageID` int unsigned NOT NULL,
  `CourseID` int unsigned NOT NULL,
  `SumSeconds` int unsigned NOT NULL,
  `UserCount` int unsigned NOT NULL,
  `AvgSeconds` decimal(11,4) unsigned NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7473 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `avgTestResults` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CourseID` int NOT NULL,
  `Name` varchar(45) NOT NULL,
  `Attempts` int DEFAULT NULL,
  `Average` float DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=1406 DEFAULT CHARSET=utf8mb3 COMMENT='Rather than probing the database for results that aren''t goi';

CREATE TABLE `avgTestResults_MaxScore` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CourseID` int NOT NULL,
  `Name` varchar(45) NOT NULL,
  `Attempts` int DEFAULT NULL,
  `Average` float DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1852 DEFAULT CHARSET=utf8mb3 COMMENT='Rather than probing the database for results';

CREATE TABLE `BatchAddresses` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `EmailAddress` varchar(255) NOT NULL,
  `MassEmailID` int NOT NULL,
  `SendDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=40875 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `BlobBridges` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `ContentPageID` varchar(32) DEFAULT NULL,
  `BlobID` decimal(10,0) DEFAULT NULL,
  `Description` varchar(256) DEFAULT NULL,
  `target` smallint DEFAULT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `AutoPlay` varchar(32) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=896 DEFAULT CHARSET=utf8mb3 COMMENT='The naming conventions for the Blob stuff is really getting ';

CREATE TABLE `BouncedEmails` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `Email` varchar(254) NOT NULL COMMENT 'The email address in question',
  `VouchedFor` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Has this email address been vouched for by the user?',
  `Confirmed` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Has this email address been confirmed as bad via a second bounce?',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Email` (`Email`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB AUTO_INCREMENT=1412 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CannedEmails` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Mnemonic` varchar(75) NOT NULL COMMENT 'Summary of the reply body below for quick lookup',
  `Body` varchar(2048) NOT NULL COMMENT 'Contents of the body of the e-mail to send',
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `Mnemonic` (`Mnemonic`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CEBadges` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Content` varchar(1024) DEFAULT NULL,
  `Type` varchar(45) DEFAULT NULL,
  `PathToBadge` varchar(256) NOT NULL DEFAULT 'images/Badges/apex_logo.gif',
  `BadgeWidth` int unsigned NOT NULL DEFAULT '88',
  `BadgeHeight` int unsigned NOT NULL DEFAULT '36',
  `Alt` varchar(256) DEFAULT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb3 COMMENT='CE Breakdown badges';

CREATE TABLE `CEBrokerCredentials` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(15) NOT NULL,
  `LongName` varchar(50) DEFAULT NULL,
  `ProviderCode` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb3 COMMENT='CE Broker table for credentials.';

CREATE TABLE `CEBrokerLicenses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `LicenseNumber` varchar(20) NOT NULL,
  `LicenseCredentialID` int unsigned NOT NULL,
  `Valid` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB AUTO_INCREMENT=126551 DEFAULT CHARSET=utf8mb3 COMMENT='CE Broker table for user license information.';

CREATE TABLE `CEBrokerUploadLogs` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `CourseID` int NOT NULL,
  `UploadDate` datetime NOT NULL,
  `TestCompletedDate` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `TestCompletedDate` (`TestCompletedDate`)
) ENGINE=InnoDB AUTO_INCREMENT=237489 DEFAULT CHARSET=utf8mb3 COMMENT='CE Broker table for user uploads.';

CREATE TABLE `CECBEMSLogs` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `TestResultID` int unsigned NOT NULL,
  `Response` varchar(255) NOT NULL,
  `DateSent` datetime NOT NULL,
  `SessionID` varchar(255) DEFAULT NULL,
  `Success` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=25252 DEFAULT CHARSET=utf8mb3 COMMENT='Store CECBEMS audit information';

CREATE TABLE `CEDates` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `ProductID` int unsigned DEFAULT NULL,
  `BadgeID` int unsigned DEFAULT NULL,
  `Original` datetime DEFAULT NULL,
  `Last` datetime DEFAULT NULL,
  `Expire` datetime DEFAULT NULL,
  `Label` varchar(45) DEFAULT NULL,
  `BadgeOrder` int NOT NULL,
  `PendingApproval` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=269 DEFAULT CHARSET=utf8mb3 COMMENT='CE Breakdown badge dates and connection';

CREATE TABLE `CEHCredentials` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `DateCheck` datetime NOT NULL,
  `Valid` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=6543 DEFAULT CHARSET=utf8mb3 COMMENT='Table which determines if a users CEH credential is valid or';

CREATE TABLE `CEHNumbers` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `CourseID` int unsigned NOT NULL,
  `CEHNumber` varchar(45) NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=232 DEFAULT CHARSET=utf8mb3 COMMENT='Store all the PAPIN numbers';

CREATE TABLE `CEHours` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `CourseID` int unsigned NOT NULL COMMENT 'Foreign key into Courses table',
  `AccreditorID` int unsigned NOT NULL COMMENT 'Foreign key into Accreditors table',
  `Hours` decimal(4,2) unsigned NOT NULL COMMENT 'The number of CE hours this course receives from this accreditor',
  `StartDate` datetime NOT NULL,
  `EndDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `AccreditorID` (`AccreditorID`),
  KEY `CourseID` (`CourseID`),
  CONSTRAINT `CEHours_ibfk_2` FOREIGN KEY (`AccreditorID`) REFERENCES `Accreditors` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1264 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CertificateTypes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Name` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'The name of this certificate type',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Communities` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `OrganizationID` int DEFAULT NULL,
  `LogoURL` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CommunityAdmins` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `CommunityID` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CompletionCertificates` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `CourseID` int unsigned NOT NULL,
  `Level` varchar(5) NOT NULL,
  `Header` varchar(255) DEFAULT NULL,
  `Prologue` varchar(255) NOT NULL,
  `Margin` decimal(4,2) NOT NULL,
  `OffsetX` decimal(4,2) DEFAULT '0.00',
  `OffsetY` decimal(4,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=171 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CompletionObjectives` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `CompletionCertificateID` int NOT NULL,
  `Objective` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=710 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ContentBlobs` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `CreationDate` datetime DEFAULT NULL,
  `Type` varchar(100) DEFAULT NULL,
  `Size` float DEFAULT NULL,
  `Data` longblob,
  `UUID` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=18182 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Countries` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Abbreviation` varchar(2) NOT NULL COMMENT 'The abbreviation associated with this Country (e.g. US, UK)',
  `Name` varchar(25) NOT NULL COMMENT 'The name of this Country',
  `Latitude` varchar(75) DEFAULT NULL,
  `Longitude` varchar(75) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Abbreviation` (`Abbreviation`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=254 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CourseBookmarks` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `CourseID` int unsigned NOT NULL COMMENT 'Foreign key into Courses table',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `PageLocator` varchar(128) NOT NULL COMMENT 'Relative URL of last page User vistied in the Course',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`UserID`,`CourseID`),
  KEY `CourseID` (`CourseID`),
  CONSTRAINT `CourseBookmarks_ibfk_1` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`),
  CONSTRAINT `CourseBookmarks_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=241041 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CourseComments` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `CourseID` int unsigned NOT NULL COMMENT 'Foreign key into Courses table',
  `Submitted` datetime NOT NULL COMMENT 'The date/time this comment was submitted',
  `PageName` varchar(50) NOT NULL COMMENT 'The name of the page they''re commenting on',
  `PageURL` varchar(100) NOT NULL COMMENT 'The URL of the page they''re commenting on',
  `Comment` varchar(1024) NOT NULL COMMENT 'The user''s comments',
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `CourseID` (`CourseID`),
  CONSTRAINT `CourseComments_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`),
  CONSTRAINT `CourseComments_ibfk_2` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3078 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CourseDescriptions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `CourseID` int unsigned NOT NULL,
  `Description` text,
  `EducationalObjective` text,
  `Keywords` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `CourseDescription_FK_idx` (`CourseID`),
  CONSTRAINT `CourseDescription_FK` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb3 COMMENT='		';

CREATE TABLE `CourseInstructionLevels` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Type` varchar(45) NOT NULL,
  `CourseID` int unsigned DEFAULT NULL,
  `CredentialID` int unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COMMENT='Holds the course ID and the instruction level type for each credential';

CREATE TABLE `CourseObjectives` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `CourseID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Courses table',
  `Name` varchar(512) NOT NULL COMMENT 'The name of this course objective (include course and level)',
  `MinQuestionsOnTest` tinyint unsigned NOT NULL DEFAULT '3' COMMENT 'The minimum number of questions that should appear on the test related to this objective',
  PRIMARY KEY (`ID`),
  KEY `CourseID` (`CourseID`),
  KEY `Name` (`Name`(255)),
  CONSTRAINT `CourseObjectives_ibfk_1` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=992 DEFAULT CHARSET=utf8mb3 COMMENT='Allows certain number of questions on tests related to objec';

CREATE TABLE `CoursePages` (
  `PageID` int unsigned NOT NULL,
  `CourseID` int unsigned NOT NULL,
  PRIMARY KEY (`CourseID`,`PageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='A table that has all pages and their associated courses';

CREATE TABLE `Courses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `ProductID` int unsigned NOT NULL DEFAULT '1' COMMENT 'Foreign key into Products table - the Apex Innovations product under which this course falls',
  `Name` varchar(50) NOT NULL COMMENT 'The short name of this Course, as seen on My Curriculum page',
  `LongName` varchar(100) DEFAULT NULL COMMENT 'The full name of this course, as seen in courseware',
  `Level` int unsigned NOT NULL COMMENT 'The order of the Course within the Product',
  `NIH` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this course use the NIH Stroke Scale test?',
  `IPCEEvaluation` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this course use the NIH Stroke Scale test?',
  `IPCECertificate` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this course use the NIH Stroke Scale test?',
  `QuestionsOnTest` tinyint unsigned NOT NULL COMMENT 'The number of questions (randomly selected from the pool of all questions) that appear on the test for this Course',
  `DefaultPassingScore` tinyint unsigned NOT NULL COMMENT 'The default passing score (percentage) required for users to pass this Course',
  `ContentHours` decimal(4,2) unsigned DEFAULT NULL COMMENT 'Estimated number of hours to complete the course content',
  `TestMinutesAllowed` tinyint unsigned NOT NULL COMMENT 'The number of minutes a user is allowed for this Course''s test',
  `MaxCMEHours` decimal(4,2) unsigned DEFAULT NULL COMMENT 'Maximum number of CME hours that can be claimed',
  `MaxCNEHours` decimal(4,2) unsigned DEFAULT NULL COMMENT 'Maximum number of CNE hours that can be claimed',
  `MaxCEHHours` decimal(4,2) unsigned DEFAULT NULL COMMENT 'Maximum number of CEH hours that can be claimed',
  `MaxCAPTHours` decimal(4,2) unsigned DEFAULT NULL COMMENT 'Maximum number of CAPT hours that can be claimed',
  `MaxOHPTHours` decimal(4,2) unsigned DEFAULT NULL COMMENT 'Maximum number of OHPT hours that can be claimed',
  `MaxFLCEHHours` decimal(4,2) unsigned DEFAULT NULL,
  `MaxCPEHours` decimal(4,2) unsigned DEFAULT NULL,
  `CEHNumber` varchar(50) DEFAULT NULL COMMENT 'The identifier (required by CECBEMS) of this course',
  `CEHType` varchar(25) DEFAULT NULL,
  `CETrackingNumber` varchar(45) DEFAULT NULL,
  `Enabled` enum('Y','N') NOT NULL DEFAULT 'Y',
  `MinTimeInCourse` tinyint unsigned NOT NULL DEFAULT '30',
  `MaxAttempts` tinyint unsigned NOT NULL DEFAULT '3',
  `Clears` tinyint unsigned NOT NULL DEFAULT '2',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ProductID_2` (`ProductID`,`Level`),
  KEY `idx_level` (`Level`),
  CONSTRAINT `Courses_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `Products` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=568 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CourseSessions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `CourseID` int unsigned NOT NULL COMMENT 'Foreign key into Courses table',
  `TimeIn` datetime NOT NULL COMMENT 'Date/Time user began this course session',
  `SecondsIn` int unsigned NOT NULL DEFAULT '0' COMMENT 'Number of seconds that the user has been in the course session',
  `LMSVersion` tinyint unsigned DEFAULT NULL COMMENT 'The version of the LMS interface the user came in on',
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `CourseID` (`CourseID`),
  KEY `TimeIn` (`TimeIn`),
  CONSTRAINT `CourseSessions_ibfk_8` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=64636603 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CourseTerminology` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CourseID` int NOT NULL,
  `Abbreviation` varchar(50) NOT NULL,
  `Term` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CourseTranslations` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `FromCourseID` int unsigned NOT NULL,
  `ToCourseID` int unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ToCourseID_index` (`ToCourseID`) /*!80000 INVISIBLE */,
  KEY `FromCourseID_index` (`FromCourseID`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3 COMMENT='Course to course translations';

CREATE TABLE `CPECredentials` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `NABPePID` varchar(10) NOT NULL,
  `DOB` date NOT NULL,
  `DateCheck` datetime DEFAULT NULL,
  `Valid` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3925 DEFAULT CHARSET=utf8mb3 COMMENT='Store information for CPE Monitor - Pharmacy';

CREATE TABLE `CPELogs` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `TestResultID` int unsigned DEFAULT NULL,
  `NIHSS` enum('Y','N') NOT NULL DEFAULT 'N',
  `Response` text NOT NULL,
  `DateSent` datetime NOT NULL,
  `Success` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=10753 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CPENumbers` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `CourseID` int unsigned NOT NULL,
  `UAN` varchar(45) NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb3 COMMENT='Store all the UANs for CPE';

CREATE TABLE `CredentialLicenseTypes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Name` varchar(20) NOT NULL COMMENT 'Name of the License Type',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Credentials` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Name` varchar(35) NOT NULL COMMENT 'The name of this Credential (e.g. Ph.D., M.D.)',
  `CEH` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this Credential receive CEH credit?',
  `CNE` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this Credential receive CNE credit?',
  `CME` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this Credential receive CME credit?',
  `CAPT` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this Credential receive Physical Therapy Board of California credit?',
  `OHPT` enum('Y','N') NOT NULL DEFAULT 'N',
  `PNPT` enum('Y','N') NOT NULL DEFAULT 'N',
  `CPE` enum('Y','N') NOT NULL DEFAULT 'N',
  `AccreditorID` int unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Accreditor` (`AccreditorID`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CriticalQuestions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `LicenseConfigurationID` int unsigned NOT NULL COMMENT 'Foreign key into LicenseConfigurations table',
  `TestQuestionID` int unsigned NOT NULL COMMENT 'Foreign key into TestQuestions table - this test question cannot be missed and the user still pass the test',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`LicenseConfigurationID`,`TestQuestionID`),
  KEY `TestQuestion` (`TestQuestionID`),
  CONSTRAINT `CriticalQuestions_ibfk_3` FOREIGN KEY (`LicenseConfigurationID`) REFERENCES `LicenseConfigurations` (`ID`),
  CONSTRAINT `CriticalQuestions_ibfk_4` FOREIGN KEY (`TestQuestionID`) REFERENCES `TestQuestions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=29520 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CustomerQuotes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Users table',
  `ProductID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Products table',
  `EmployeeID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Employees table',
  `Quote` varchar(256) NOT NULL COMMENT 'The actual customer quote',
  `Date` datetime DEFAULT NULL COMMENT 'The date the quote was made',
  `Attribution` varchar(256) DEFAULT NULL COMMENT 'The line of text appearing below the quote (usually customer intials or name and/or org)',
  `Source` varchar(50) DEFAULT NULL COMMENT 'How did we obtain the quote?',
  `CanAttribute` enum('Y','N') DEFAULT 'N' COMMENT 'Can we show the attributed to with the quote?',
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `ProductID` (`ProductID`),
  KEY `EmployeeID` (`EmployeeID`),
  CONSTRAINT `CustomerQuotes_ibfk_3` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`),
  CONSTRAINT `CustomerQuotes_ibfk_4` FOREIGN KEY (`ProductID`) REFERENCES `Products` (`ID`),
  CONSTRAINT `CustomerQuotes_ibfk_5` FOREIGN KEY (`EmployeeID`) REFERENCES `Employees` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=251 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CustomProducts` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ProductID` int unsigned NOT NULL,
  `ProductName` varchar(45) NOT NULL,
  `OrganizationID` int unsigned NOT NULL,
  `CopiedProductID` int unsigned DEFAULT NULL,
  `DepartmentID` int unsigned DEFAULT NULL,
  `LevelsMerged` char(1) DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `ProductID` (`ProductID`),
  KEY `OrganizationID` (`OrganizationID`),
  KEY `CustomProducts_ibfk_3` (`CopiedProductID`),
  KEY `CustomProducts_ibfk_4` (`DepartmentID`),
  CONSTRAINT `CustomProducts_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `Products` (`ID`),
  CONSTRAINT `CustomProducts_ibfk_2` FOREIGN KEY (`OrganizationID`) REFERENCES `Organizations` (`ID`),
  CONSTRAINT `CustomProducts_ibfk_3` FOREIGN KEY (`CopiedProductID`) REFERENCES `Products` (`ID`),
  CONSTRAINT `CustomProducts_ibfk_4` FOREIGN KEY (`DepartmentID`) REFERENCES `Departments` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `CustomReports` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `ReportID` int NOT NULL,
  `Parameters` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COMMENT='Listing of custom reports and their given json params';

CREATE TABLE `DeletedEssayDocuments` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `EssayQuestionID` int unsigned NOT NULL,
  `TestResultID` int unsigned NOT NULL,
  `UUID` varchar(36) DEFAULT NULL,
  `Size` int unsigned DEFAULT NULL,
  `Type` varchar(25) DEFAULT NULL,
  `Name` varchar(260) DEFAULT 'Untitled',
  `URL` varchar(255) DEFAULT NULL,
  `DeletedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `DemographicAnswerChoices` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `AnswerText` varchar(255) NOT NULL,
  `DemographicQuestionID` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=latin1;

CREATE TABLE `DemographicQuestions` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `QuestionText` varchar(300) NOT NULL,
  `DemographicQuestionTypeID` int NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'N',
  `Notes` varchar(400) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

CREATE TABLE `DemographicQuestionTypes` (
  `idDemographicQuestionTypes` int NOT NULL AUTO_INCREMENT,
  `Type` varchar(45) NOT NULL,
  PRIMARY KEY (`idDemographicQuestionTypes`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

CREATE TABLE `Demographics` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `DemographicQuestionID` int unsigned NOT NULL,
  `DemographicAnswer` varchar(300) NOT NULL,
  `CreationDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=259057 DEFAULT CHARSET=latin1;

CREATE TABLE `DemographicsForProducts` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DemographicQuestionID` int NOT NULL,
  `ProductID` int NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Questions` (`DemographicQuestionID`),
  CONSTRAINT `Questions` FOREIGN KEY (`DemographicQuestionID`) REFERENCES `DemographicQuestions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `DepartmentAdmins` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `DepartmentID` int unsigned NOT NULL COMMENT 'Foreign key into Departments table',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `SeeEvaluations` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Does this admin want to see course evaluations? 0 - No; 1 - Bad only; 2 - All',
  `SeeNewUsers` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Does this admin want to see new users?',
  `examFailEmail` enum('Y','N') NOT NULL DEFAULT 'N',
  `MaxAttempts` enum('Y','N') NOT NULL DEFAULT 'Y',
  `EssayEmail` enum('Y','N') NOT NULL DEFAULT 'N',
  `TestCompleted` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_user` (`DepartmentID`,`UserID`),
  KEY `Users` (`UserID`),
  CONSTRAINT `DepartmentAdmins_ibfk_4` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=20540 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `DepartmentEvents` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DepartmentID` int unsigned NOT NULL,
  `EventID` int NOT NULL,
  `Notes` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=9340 DEFAULT CHARSET=utf8mb3 COMMENT='If an admin event involves a department';

CREATE TABLE `DepartmentIPs` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DepartmentID` int NOT NULL,
  `OrganizationID` int NOT NULL,
  `IP` int unsigned NOT NULL,
  `Hostname` varchar(255) DEFAULT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=241536185 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Departments` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `OrganizationID` int unsigned NOT NULL COMMENT 'Foreign key into Organizations table - the organization to which this department belongs',
  `Name` varchar(60) NOT NULL COMMENT 'The name of this department',
  `LMSRestrictionID` int unsigned DEFAULT NULL COMMENT 'Foreign key into LMSRestrictions table',
  `CurriculumNotice` varchar(600) DEFAULT NULL COMMENT 'The notice to put up on department users'' MyCurriculum page',
  `CurriculumDate` datetime DEFAULT NULL,
  `CommunityNotice` varchar(600) DEFAULT NULL COMMENT 'The notice to put up on department users'' MyCommunity page',
  `CommunityDate` datetime DEFAULT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is this an active department?',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`,`OrganizationID`),
  KEY `LMSRestrictionID` (`LMSRestrictionID`),
  KEY `Active` (`Active`),
  KEY `idx_Name` (`Name`),
  CONSTRAINT `Departments_ibfk_23` FOREIGN KEY (`LMSRestrictionID`) REFERENCES `LMSRestrictions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=17781 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `DepartmentSeatBlocks` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `DepartmentID` int unsigned NOT NULL COMMENT 'Foreign key into Departments table',
  `LicenseID` int unsigned NOT NULL COMMENT 'Foreign key into Licenses table',
  `NumSeats` int unsigned NOT NULL COMMENT 'The number of seats blocked out for this department on this license',
  PRIMARY KEY (`ID`),
  KEY `LicenseID` (`LicenseID`),
  CONSTRAINT `DepartmentSeatBlocks_ibfk_2` FOREIGN KEY (`LicenseID`) REFERENCES `Licenses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4101 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `distinctUsers` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `SoFarToday` int NOT NULL,
  `Past24Hours` int NOT NULL,
  `PastWeek` int NOT NULL,
  `PastMonth` int NOT NULL,
  `PastYear` int NOT NULL,
  `ActiveNIHSSCE` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COMMENT='In order to speed up the admin page, this temporary table is';

CREATE TABLE `DownloadLogs` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `UploadID` int NOT NULL,
  `DownloadDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IPAddress` int unsigned DEFAULT NULL,
  `Referrer` text,
  `UserID` int DEFAULT NULL,
  `UserAgent` text,
  `FileName` varchar(255) DEFAULT NULL,
  `UploadTypeID` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2698 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ehac_password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Login` varchar(100) NOT NULL,
  `token` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Secondary` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `EhacAdmins` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  PRIMARY KEY (`ID`,`UserID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `EmployeeDepartments` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_UNIQUE` (`ID`),
  UNIQUE KEY `Name_UNIQUE` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `EmployeeDismissLicenses` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `EmployeeID` int unsigned NOT NULL,
  `LicenseID` int unsigned NOT NULL,
  `DismissDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1922 DEFAULT CHARSET=utf8mb3 COMMENT='Connecting table for licenses to employees for snoozing feat';

CREATE TABLE `EmployeeLevels` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COMMENT='Levels of admins';

CREATE TABLE `EmployeeResets` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `EmployeeID` int NOT NULL,
  `UUID` varchar(255) NOT NULL,
  `CreationDate` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `EmployeeID_UNIQUE` (`EmployeeID`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb3 COMMENT='Stores unique ID for reseting employee passwords	';

CREATE TABLE `Employees` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `FirstName` varchar(25) NOT NULL COMMENT 'First name of Apex Employee',
  `LastName` varchar(25) NOT NULL COMMENT 'Last  name of Apex Employee',
  `Email` varchar(50) NOT NULL COMMENT 'Employee''s email address',
  `Password` varchar(60) NOT NULL DEFAULT '36903b4db385551b6d114d659dc37d3b' COMMENT 'Hashed password for this Employee',
  `PasswordLastChanged` datetime NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is this still and active employee?',
  `SalesRep` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this Employee a sales rep?',
  `UseTrax` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is this Employee able to  use Trax?',
  `UseTimeKeeper` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this Employee need to keep track of hours via TimeKeeper?',
  `AdminTimeKeeper` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this Employee administer TimeKeeper?',
  `AdminTechSupport` enum('Y','N') NOT NULL DEFAULT 'N',
  `TechSupport` enum('Y','N') NOT NULL DEFAULT 'N',
  `CellPhone` varchar(40) DEFAULT NULL,
  `Snooze` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `EmployeeDepartmentID` int unsigned DEFAULT NULL,
  `Biography` varchar(2000) DEFAULT NULL,
  `PictureLocation` varchar(45) DEFAULT NULL,
  `Title` varchar(45) DEFAULT NULL,
  `Credentials` varchar(100) DEFAULT NULL,
  `Level` int unsigned DEFAULT '0',
  `Order` int unsigned DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Email_UNIQUE` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `EmployeeTimeEntries` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `EmployeeID` int unsigned NOT NULL COMMENT 'Foreign key into Employees table',
  `TimeIn` datetime NOT NULL COMMENT 'The date/time the user logged in',
  `TimeOut` datetime DEFAULT NULL COMMENT 'The date/time the user logged out',
  PRIMARY KEY (`ID`),
  KEY `EmployeeID` (`EmployeeID`),
  CONSTRAINT `EmployeeTimeEntries_ibfk_1` FOREIGN KEY (`EmployeeID`) REFERENCES `Employees` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2504 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `EncryptedKeyStores` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `EncryptedKey` varchar(255) NOT NULL,
  `LicensePeriodID` int unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_UNIQUE` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb3 COMMENT='Table to temporarily hold encrypted license keys for license periods';

CREATE TABLE `EncryptedLicenseKeys` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `EncryptedKey` varchar(255) NOT NULL,
  `LicenseKey` varchar(255) NOT NULL,
  `Iteration` int unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `EncryptedKey_UNIQUE` (`EncryptedKey`)
) ENGINE=InnoDB AUTO_INCREMENT=409 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `EncryptedLicenseSeats` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `EncryptedKey` varchar(255) NOT NULL,
  `LicenseSeatID` int unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `EncryptedKey_UNIQUE` (`EncryptedKey`)
) ENGINE=InnoDB AUTO_INCREMENT=260 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `EngineLogs` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Users table',
  `CourseID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Coursestable',
  `PageID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Pagestable',
  `Message` varchar(1024) NOT NULL COMMENT 'Text of log event',
  `TimeLogged` datetime NOT NULL COMMENT 'Date/time this event was created',
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `CourseID` (`CourseID`),
  KEY `PageID` (`PageID`),
  CONSTRAINT `EngineLog_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`),
  CONSTRAINT `EngineLog_ibfk_2` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=33359212 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `EssayAdmins` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `OrganizationID` int unsigned NOT NULL,
  `UserID` int unsigned NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `EssayAnswers` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `TestResultID` int unsigned NOT NULL,
  `EssayQuestionID` int unsigned NOT NULL,
  `Answer` text,
  `Score` int unsigned DEFAULT NULL,
  `Reviewed` enum('Y','N') DEFAULT 'N',
  `ReviewedBy` int unsigned DEFAULT NULL,
  `ReviewedDate` datetime DEFAULT NULL,
  `Note` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=513 DEFAULT CHARSET=utf8mb3 COMMENT='Answers for essay questions';

CREATE TABLE `EssayAssignments` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned DEFAULT NULL,
  `TestResultID` int unsigned NOT NULL,
  `AssignmentDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `EssayConfigurations` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `EssayQuestionID` int unsigned NOT NULL,
  `LicenseConfigurationID` int unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `LicenseID` (`LicenseConfigurationID`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb3 COMMENT='Link essay questions to license configurations';

CREATE TABLE `EssayDocuments` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `EssayQuestionID` int unsigned NOT NULL,
  `TestResultID` int unsigned NOT NULL,
  `UUID` varchar(36) DEFAULT NULL,
  `Size` int unsigned DEFAULT NULL,
  `Type` varchar(25) DEFAULT NULL,
  `Name` varchar(260) DEFAULT 'Untitled',
  `URL` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=635 DEFAULT CHARSET=utf8mb3 COMMENT='Stores location of documents for each essay.';

CREATE TABLE `EssayQuestions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Question` varchar(1024) NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `Weight` int NOT NULL DEFAULT '10',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COMMENT='Essay questions for tests';

CREATE TABLE `EvaluationMultiselectResponses` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `evaluationResponse` varchar(100) DEFAULT NULL,
  `EvaluationQuestionTypeID` int unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `id EvaluationMultiselectResponses_UNIQUE` (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3 COMMENT='Tables used to translate the comma seperated values in the E';

CREATE TABLE `EvaluationQuestions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'The primary key',
  `CourseID` int unsigned DEFAULT NULL COMMENT 'Foreign key into the Courses table',
  `OrganizationID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Organizations table',
  `EvaluationID` int DEFAULT NULL,
  `SectionID` int unsigned DEFAULT NULL,
  `ListOrder` int unsigned DEFAULT NULL COMMENT 'Order in which this question shows up on the list',
  `Question` varchar(512) NOT NULL COMMENT 'The question to be asked',
  `Abbreviation` varchar(20) NOT NULL COMMENT 'The abbreviated version of the question, appears in the e-mail',
  `Type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '1 - Excelent(5),Good(4),Average(3),Fair(2),Poor(1),NotAtAll(0); 2-freeform text (100 chars max); 3 - Yes(1),No(0)',
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is this question still active?',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `CourseIDAbbreviation` (`CourseID`,`Abbreviation`),
  KEY `ActiveAndType` (`Type`,`Active`) /*!80000 INVISIBLE */,
  FULLTEXT KEY `Question` (`Question`),
  CONSTRAINT `EvaluationQuestions_ibfk_1` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=868 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Evaluations` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(256) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COMMENT='All types of evaluations';

CREATE TABLE `EvaluationSections` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COMMENT='Holds section information for each field in evaluation ';

CREATE TABLE `EventTypes` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Description` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb3 COMMENT='Events for admin backpanel';

CREATE TABLE `failed_jobs` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `connection` varchar(25) DEFAULT NULL,
  `queue` varchar(255) DEFAULT NULL,
  `payload` varchar(1000) DEFAULT NULL,
  `failed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ForcedPatientLicenses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `LicenseID` int unsigned NOT NULL,
  `Patient` varchar(45) NOT NULL DEFAULT '"A"',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `LicenseID_UNIQUE` (`LicenseID`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3 COMMENT='Table joining together Licenses and forcing of patients for NIHSS';

CREATE TABLE `ForcedPrePostOutcomes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `CourseID` int unsigned NOT NULL,
  `TestQuestionID` int unsigned NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb3 COMMENT='Table for storing which courses are forced with which questions for PPO.';

CREATE TABLE `FrequentlyAskedQuestions` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Title` text NOT NULL,
  `Content` text NOT NULL,
  `ProductID` int DEFAULT NULL,
  `Type` varchar(255) NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb3 COMMENT='FAQs for website';

CREATE TABLE `GroupedCourseObjectives` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `CourseObjectiveID` int unsigned NOT NULL,
  `Group` varchar(45) NOT NULL,
  `Required` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3 COMMENT='Allows for objectives to be grouped and them pulled from to generate test questions. Put in place because of the requested changes for level 3 of impulse 4.0';

CREATE TABLE `HealthstreamAICCSessions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `SessionStart` datetime NOT NULL COMMENT 'Time this AICC session was started',
  `AICC_URL` varchar(1024) NOT NULL COMMENT 'URL passed in by Healthstream',
  `AICC_SID` varchar(1024) NOT NULL COMMENT 'SessionID passed in by Healthstream',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UserID` (`UserID`),
  CONSTRAINT `HealthstreamAICCSessions_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2796988 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `HTMLLicenseProducts` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `ProductID` int unsigned NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ProductID_UNIQUE` (`ProductID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COMMENT='Allows to turn on HTML licenses for a given products';

CREATE TABLE `HTMLLicenses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `LicenseID` int unsigned NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  KEY `Active` (`LicenseID`,`Active`)
) ENGINE=InnoDB AUTO_INCREMENT=1118 DEFAULT CHARSET=utf8mb3 COMMENT='Table to store licenses that will launch as original FLASH courses but will instead open HTML courses';

CREATE TABLE `Hyperlinks` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Link` text NOT NULL,
  `Description` text,
  `OrganizationID` int unsigned DEFAULT NULL,
  `DepartmentID` int unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=227 DEFAULT CHARSET=utf8mb3 COMMENT='Table containing hyperlink urls and their corresponding Organization or Department ID';

CREATE TABLE `InteractiveRemediations` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `TestResultID` int unsigned NOT NULL COMMENT 'Foreign key into TestResults table',
  `TestQuestionID` int unsigned NOT NULL COMMENT 'Foreign key into TestQuestions table',
  `Remediation` varchar(512) NOT NULL COMMENT 'The text of the remediation',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`TestResultID`,`TestQuestionID`),
  KEY `TestResultID` (`TestResultID`),
  KEY `TestQuestionID` (`TestQuestionID`),
  CONSTRAINT `InteractiveRemediations_ibfk_2` FOREIGN KEY (`TestQuestionID`) REFERENCES `TestQuestions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1042748 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `IssueAttachments` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `IssueID` int unsigned NOT NULL COMMENT 'Foreign key into Issues table',
  `FileLocator` varchar(128) NOT NULL COMMENT 'Filespec to local file (e.g. L:\\MyDoc.doc)',
  PRIMARY KEY (`ID`),
  KEY `IssueID` (`IssueID`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `IssueCommunications` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `IssueID` int unsigned NOT NULL COMMENT 'Foreign key into Issues table',
  `Date` datetime NOT NULL COMMENT 'Date of the communication',
  `EmployeeID` int unsigned NOT NULL COMMENT 'Foreign key into Employees table',
  `Message` varchar(2048) NOT NULL COMMENT 'Body of message (or gist of phone conversation) ',
  `Outgoing` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Did Apex send the e-mail or make the call?',
  PRIMARY KEY (`ID`),
  KEY `IssueID` (`IssueID`),
  KEY `EmployeeID` (`EmployeeID`),
  CONSTRAINT `IssueCommunications_ibfk_2` FOREIGN KEY (`EmployeeID`) REFERENCES `Employees` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=80345 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `IssueNotes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `IssueID` int unsigned NOT NULL COMMENT 'Foreign key into Issues table',
  `Date` datetime NOT NULL COMMENT 'Date this activity ocurred',
  `EmployeeID` int unsigned NOT NULL COMMENT 'Foreign key into Employees table',
  `Comment` varchar(4000) NOT NULL COMMENT 'A description of the activity releated to the issue',
  `Response` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `IssueID` (`IssueID`),
  KEY `EmployeeID` (`EmployeeID`),
  CONSTRAINT `IssueNotes_ibfk_2` FOREIGN KEY (`EmployeeID`) REFERENCES `Employees` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=847581 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `IssueResolveURLs` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `IssueID` int unsigned NOT NULL,
  `URL` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb3 COMMENT='Location of resolution URLS for trax issues.';

CREATE TABLE `Issues` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Users table (or NULL)',
  `CourseID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Courses table (or NULL)',
  `Submitted` datetime NOT NULL COMMENT 'Date this issue first recorded',
  `Closed` datetime DEFAULT NULL COMMENT 'Date this issue was closed',
  `EmployeeID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Employees table',
  `IssueTypeID` int unsigned NOT NULL COMMENT 'Foreign key into IssueTypes table',
  `IssueStageID` int unsigned NOT NULL DEFAULT '1' COMMENT 'Foreign key into IssueStages table',
  `ItemCode` varchar(20) DEFAULT NULL COMMENT 'Unique ID embedded in e-mail used to create this issue (prevents dups)',
  `Comment` varchar(2048) NOT NULL COMMENT 'Any comment(s) user made',
  `PoorEvals` varchar(1024) DEFAULT NULL COMMENT 'Any poor evaluations the user made',
  `TestResultID` int unsigned DEFAULT NULL COMMENT 'Foreign key into either TestResults or NIHSSResults table',
  `PageName` varchar(50) DEFAULT NULL COMMENT 'The name of the courseware page the user made comments on (if any)',
  `PageURL` varchar(100) DEFAULT NULL COMMENT 'The URL of the courseware page the user made comments on (if any)',
  `Browser` varchar(256) DEFAULT NULL COMMENT 'The URL of the courseware page the user made comments on (if any)',
  `CheckedTestQs` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Checked to see if Test Questions related to this issue needed modifications',
  `ChangedTestQs` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Had to update Test Questions?',
  `CheckedBiblio` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Checked to see if Bibliographies related to this issue needed modifications',
  `ChangedBiblio` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Had to update Bibliographies? ',
  `CheckedContent` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Checked to see if Content related to this issue needed modifications',
  `ChangedContent` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Had to update Content?',
  `CheckedAudio` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Checked to see if Audio related to this issue needed modifications',
  `ChangedAudio` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Had to update Audio?',
  `CheckedTranscripts` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Checked to see if Transcripts related to this issue needed modifications',
  `ChangedTranscripts` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Had to update Transcripts?',
  `FeatureRequest-Removed` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this issue a feature request?',
  `NotifyUsers` enum('Y','N') DEFAULT 'N' COMMENT 'Do we need to notify the general user population that this issue is resolved?',
  `NotifySales` enum('Y','N') DEFAULT 'N' COMMENT 'Do we need to notify the Apex Sales Department that this issue is resolved?',
  `PositiveComment` enum('Y','N') NOT NULL DEFAULT 'N',
  `Technical` enum('Y','N') NOT NULL DEFAULT 'N',
  `ClinicalImprovements` enum('Y','N') NOT NULL DEFAULT 'N',
  `Audio` enum('Y','N') NOT NULL DEFAULT 'N',
  `Testing` enum('Y','N') NOT NULL DEFAULT 'N',
  `FeatureRequests` enum('Y','N') NOT NULL DEFAULT 'N',
  `Improvements` enum('Y','N') NOT NULL DEFAULT 'N',
  `ScopeOfPractice` enum('Y','N') NOT NULL DEFAULT 'N',
  `Multiple` enum('Y','N') NOT NULL DEFAULT 'N',
  `Miscellaneous` enum('Y','N') NOT NULL DEFAULT 'N',
  `Advertising` enum('Y','N') NOT NULL DEFAULT 'N',
  `LeadPlacement` enum('Y','N') NOT NULL DEFAULT 'N',
  `Length` enum('Y','N') NOT NULL DEFAULT 'N',
  `EducationalNeeds` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ItemCode` (`ItemCode`),
  KEY `UserID` (`UserID`),
  KEY `CourseID` (`CourseID`),
  KEY `EmployeeID` (`EmployeeID`),
  CONSTRAINT `Issues_ibfk_17` FOREIGN KEY (`EmployeeID`) REFERENCES `Employees` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=881777 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `IssueStages` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Name` varchar(25) NOT NULL COMMENT 'Name of Stage',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `IssueTypes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Name` varchar(25) NOT NULL COMMENT 'Source of Issue',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb3_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_reserved_at_index` (`queue`,`reserved`,`reserved_at`)
) ENGINE=InnoDB AUTO_INCREMENT=54051 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `journey_answers` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `AnswerText` text NOT NULL,
  `NodeID` int NOT NULL,
  `CreationDate` datetime DEFAULT NULL,
  `NextNodeID` int DEFAULT '-1',
  `Weight` int DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_UNIQUE` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=658 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `journey_content` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NodeID` int NOT NULL,
  `Content` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `journey_followups` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `FollowupText` mediumtext,
  `AnswerID` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=533 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `journey_forests` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `CreationDate` datetime DEFAULT NULL,
  `ProductID` int unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_UNIQUE` (`ID`),
  UNIQUE KEY `ProductID_UNIQUE` (`ProductID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `journey_node_types` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_UNIQUE` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `journey_nodes` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `NodeText` text NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `CreationDate` datetime NOT NULL,
  `Weight` int NOT NULL DEFAULT '1',
  `TreeID` int NOT NULL,
  `PositionX` int NOT NULL DEFAULT '200',
  `PositionY` int NOT NULL DEFAULT '200',
  `TypeID` int unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_UNIQUE` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=209 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `journey_paths` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `TreeID` int NOT NULL,
  `CorrectResult` int DEFAULT NULL,
  `IncorrectResult` int DEFAULT NULL,
  `QuestionID` int NOT NULL,
  `Master` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `journey_results` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `JourneyTreeID` int NOT NULL,
  `UserID` int NOT NULL,
  `Score` int DEFAULT NULL,
  `NodesHit` varchar(1024) DEFAULT NULL,
  `AnswersGiven` varchar(1024) DEFAULT NULL,
  `JourneyStarted` datetime NOT NULL,
  `JourneyCompleted` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_UNIQUE` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `journey_trees` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL DEFAULT 'Default',
  `ForestID` int DEFAULT NULL,
  `MasterNodeID` int NOT NULL,
  `TreeOrder` int NOT NULL DEFAULT '1',
  `CourseID` int unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `CourseID_UNIQUE` (`CourseID`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `LeadPlacements` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `TestResultID` int NOT NULL,
  `QuestionID` int NOT NULL,
  `Placements` text NOT NULL,
  `Passed` enum('Y','N') NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `testAndQuestionIndex` (`TestResultID`,`QuestionID`)
) ENGINE=InnoDB AUTO_INCREMENT=628278 DEFAULT CHARSET=utf8mb3 COMMENT='Store the lead placements for flash questions.';

CREATE TABLE `LicenseAdmins` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `LicenseID` int unsigned NOT NULL COMMENT 'Foreign key into Licenses table',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `SeeEvaluations` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Does this admin want to see course evaluations? 0 - No; 1 - Bad only; 2 - All',
  `SeeNewUsers` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Does this admin want to see new users?',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`LicenseID`,`UserID`),
  KEY `Users` (`UserID`),
  CONSTRAINT `LicenseAdmins_ibfk_3` FOREIGN KEY (`LicenseID`) REFERENCES `Licenses` (`ID`),
  CONSTRAINT `LicenseAdmins_ibfk_4` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `LicenseConfigNames` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `LicenseConfigurations` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `LicenseID` int unsigned NOT NULL COMMENT 'Foreign key into Licenses table',
  `CourseID` int unsigned NOT NULL COMMENT 'Foreign key into Courses table',
  `Level` tinyint unsigned NOT NULL DEFAULT '1' COMMENT 'This places the Course specified at the menu button location in the user interface of the Product',
  `AllowCourse` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Does this license allow this Course to be taken?',
  `AllowTest` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Does this license allow the test for this Course to be taken?',
  `AllowCertificate` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Does this License allow the Certificates for this Course to be created?',
  `RandomizeAnswers` enum('Y','N') NOT NULL DEFAULT 'Y',
  `RandomizeQuestions` enum('Y','N') NOT NULL DEFAULT 'Y',
  `ForceTimeInCourse` enum('Y','N') NOT NULL DEFAULT 'N',
  `CustomExam` enum('Y','N') NOT NULL DEFAULT 'N',
  `ForceTime` tinyint unsigned NOT NULL DEFAULT '0',
  `PassingScore` tinyint unsigned NOT NULL DEFAULT '80' COMMENT 'The passing score (as a percentage) for this Course on this License',
  `XMLFile` varchar(30) NOT NULL DEFAULT 'menu.xml' COMMENT 'The name of the XML file to load in the courseware for this Course',
  `MaxFailsPerPeriod` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'The maximum number of tests a user can fail in a period before they are flagged to their admin',
  `MaxAttempts` tinyint unsigned NOT NULL DEFAULT '3',
  `Clears` tinyint unsigned NOT NULL DEFAULT '2',
  `MinutesBetweenTests` int unsigned NOT NULL DEFAULT '0' COMMENT 'Minutes required between tests on same course - 0 means none',
  `IPRestrictions` varchar(1024) DEFAULT NULL,
  `EnforceIP` enum('Y','N') NOT NULL DEFAULT 'N',
  `ShowQuestionID` enum('Y','N') NOT NULL DEFAULT 'N',
  `CustomExamID` int unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`LicenseID`,`CourseID`),
  KEY `Course` (`CourseID`),
  CONSTRAINT `LicenseConfigurations_ibfk_5` FOREIGN KEY (`LicenseID`) REFERENCES `Licenses` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `LicenseConfigurations_ibfk_6` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=119484 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `LicensedEditors` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `LicenseID` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=891 DEFAULT CHARSET=utf8mb3 COMMENT='Table to house users which allow editors';

CREATE TABLE `LicenseEvents` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `LicenseID` int unsigned NOT NULL,
  `EventID` int NOT NULL,
  `Notes` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=34597 DEFAULT CHARSET=utf8mb3 COMMENT='If an admin event involves a license';

CREATE TABLE `LicenseKeys` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `LicensePeriodID` int unsigned NOT NULL COMMENT 'Foreign key into LicensePeriods table',
  `Key` varchar(10) NOT NULL COMMENT 'The key that users use to assign themselves to a license period',
  `DepartmentID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Departments table',
  `Iteration` int unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Key` (`Key`),
  UNIQUE KEY `Unique2` (`LicensePeriodID`,`DepartmentID`),
  KEY `LicensePeriodID` (`LicensePeriodID`),
  CONSTRAINT `LicenseKeys_ibfk_1` FOREIGN KEY (`LicensePeriodID`) REFERENCES `LicensePeriods` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=20279 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `LicensePeriods` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `LicenseID` int unsigned NOT NULL COMMENT 'Foreign key into Licenses table',
  `StartDate` datetime NOT NULL COMMENT 'The starting date of this license period',
  `EndDate` datetime NOT NULL COMMENT 'The ending date of this license period',
  `Notes` varchar(1024) DEFAULT NULL COMMENT 'Notes about this license period',
  `HardStop` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `LicenseID` (`LicenseID`) /*!80000 INVISIBLE */,
  KEY `EndDate` (`EndDate`),
  KEY `licenseStartEnd` (`LicenseID`,`StartDate`,`EndDate` DESC),
  CONSTRAINT `LicensePeriods_ibfk_1` FOREIGN KEY (`LicenseID`) REFERENCES `Licenses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=19300 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Licenses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `ProductID` int unsigned NOT NULL COMMENT 'Foreign key into Products table - the product this license is for',
  `OrganizationID` int unsigned NOT NULL COMMENT 'Foreign key into Organizations table - the organization this license is for',
  `SalesRepID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Employees table',
  `NumberSeats` int unsigned NOT NULL DEFAULT '0' COMMENT 'The number of paid users able to use this license',
  `NumSeats` int unsigned NOT NULL DEFAULT '0' COMMENT 'The number of paid seats assigned to this license',
  `NumAdminSeats` int unsigned NOT NULL DEFAULT '0' COMMENT 'The number of free seats assigned to this license',
  `CreationDate` datetime NOT NULL COMMENT 'The date/time this license was created',
  `ExpirationDate` datetime DEFAULT NULL COMMENT 'The date/time this license expires',
  `Enterprise` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this an Enterprise (unlimited seat) license?',
  `ForceIPRangeAccess` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Must the user only access this course via the Organizations IPs?',
  `Notes` varchar(1024) DEFAULT NULL COMMENT 'A description of this license for administrators',
  `Beta` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this license allow users to preview new courseware features (e.g. new engine)?',
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is this an active license?',
  `TermInMonths` int unsigned NOT NULL DEFAULT '12' COMMENT 'The term of this license, in months',
  `NumTerms` tinyint unsigned NOT NULL DEFAULT '1' COMMENT 'The number of terms of this license',
  `Pretest` enum('Y','N') NOT NULL DEFAULT 'N',
  `PatientChoice` enum('Y','N') NOT NULL DEFAULT 'N',
  `Price` double DEFAULT '0',
  `Shareable` enum('Y','N') NOT NULL DEFAULT 'N',
  `LMSVerificationRequired` enum('Y','N') NOT NULL DEFAULT 'N',
  `InactiveByPass` enum('Y','N') NOT NULL DEFAULT 'N',
  `RequirementsByPass` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `ProductID` (`ProductID`),
  KEY `ExpirationDate` (`ExpirationDate`),
  KEY `SalesRepID` (`SalesRepID`),
  CONSTRAINT `Licenses_ibfk_5` FOREIGN KEY (`ProductID`) REFERENCES `Products` (`ID`),
  CONSTRAINT `Licenses_ibfk_7` FOREIGN KEY (`SalesRepID`) REFERENCES `Employees` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=19371 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `LicenseSeats` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `LicensePeriodID` int unsigned NOT NULL COMMENT 'Foreign key into LicensePeriods table',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into CourseEvaluationQuestions table',
  `CreationDate` datetime NOT NULL COMMENT 'Date/time this seat was created',
  `ExpirationDate` datetime NOT NULL COMMENT 'Date/time this seat expires',
  `DueDates` varchar(100) DEFAULT NULL COMMENT 'Comma separated list of dates the user should have passed the exams for this seat (e.g. "1=2010-04-01,2=2010-05-03,...")',
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is this an active seat (counts against usage/can take courseware)',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `LicensePeriodID` (`LicensePeriodID`,`UserID`),
  KEY `Active` (`Active`),
  KEY `UserID` (`UserID`),
  KEY `ExpirationDate` (`ExpirationDate` DESC) /*!80000 INVISIBLE */,
  KEY `LicensePeriodID-indx` (`LicensePeriodID` DESC)
) ENGINE=InnoDB AUTO_INCREMENT=6965960 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `LMSComments` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `LMSID` int NOT NULL,
  `Text` text NOT NULL,
  `EmployeeID` int NOT NULL,
  `CreatedAt` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `LMSCompletions` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `TestResultID` int unsigned DEFAULT NULL,
  `NIHSSResultID` int unsigned DEFAULT NULL,
  `Message` varchar(500) DEFAULT NULL,
  `Successful` enum('Y','N') NOT NULL DEFAULT 'N',
  `CreationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `LMSFailure_ibfk_3_idx` (`UserID`),
  CONSTRAINT `LMSCompletions_ibfk_3` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=111179 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `LMSConfigurations` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DepartmentID` int unsigned NOT NULL,
  `CanChangeName` enum('Y','N') NOT NULL DEFAULT 'N',
  `ShowLoginOnCertificate` enum('Y','N') NOT NULL DEFAULT 'N',
  `ShowLoginOnReport` enum('Y','N') NOT NULL DEFAULT 'N',
  `LMSVendorID` int unsigned NOT NULL DEFAULT '0',
  `ExpirationReminder` enum('Y','N') NOT NULL DEFAULT 'Y',
  `RelatedOrganizationID` int unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=58726 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `LMSInfo` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `InfoType` tinyint unsigned NOT NULL,
  `Info` text,
  `CreationDate` datetime DEFAULT NULL,
  `AdminID` int unsigned NOT NULL,
  `LMSID` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=507 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `LMSLicenses` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `DepartmentID` int NOT NULL,
  `LicenseID` int NOT NULL,
  UNIQUE KEY `ID_UNIQUE` (`ID`),
  KEY `lms` (`DepartmentID`),
  KEY `LIC` (`LicenseID`,`DepartmentID`)
) ENGINE=InnoDB AUTO_INCREMENT=4055 DEFAULT CHARSET=utf8mb3 COMMENT='Connecting table between LMSes, which are ''deparments'' and t';

CREATE TABLE `LMSRestrictions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `StartDate` datetime NOT NULL COMMENT 'The date this restriction was started',
  `MaxUsers` int unsigned NOT NULL COMMENT 'The maximum number of users this LMS is allowed',
  `UserList` varchar(1024) DEFAULT NULL COMMENT 'Comma separated list of User IDs',
  `FirstLoginIP` varchar(1024) DEFAULT NULL,
  `UserCanEnterName` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `LMSVendors` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Note` mediumtext,
  `FileSupport` enum('Y','N') NOT NULL DEFAULT 'N',
  `URLSupport` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name_UNIQUE` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `lti2_consumer` (
  `consumer_pk` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `consumer_key256` varchar(256) NOT NULL,
  `consumer_key` text,
  `secret` varchar(1024) NOT NULL,
  `lti_version` varchar(10) DEFAULT NULL,
  `consumer_name` varchar(255) DEFAULT NULL,
  `consumer_version` varchar(255) DEFAULT NULL,
  `consumer_guid` varchar(1024) DEFAULT NULL,
  `profile` text,
  `tool_proxy` text,
  `settings` text,
  `protected` tinyint(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `enable_from` datetime DEFAULT NULL,
  `enable_until` datetime DEFAULT NULL,
  `last_access` date DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`consumer_pk`),
  UNIQUE KEY `lti2_consumer_consumer_key_UNIQUE` (`consumer_key256`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

CREATE TABLE `lti2_context` (
  `context_pk` int NOT NULL AUTO_INCREMENT,
  `consumer_pk` int NOT NULL,
  `lti_context_id` varchar(255) NOT NULL,
  `settings` text,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`context_pk`),
  KEY `lti2_context_consumer_id_IDX` (`consumer_pk`),
  CONSTRAINT `lti2_context_lti2_consumer_FK1` FOREIGN KEY (`consumer_pk`) REFERENCES `lti2_consumer` (`consumer_pk`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=latin1;

CREATE TABLE `lti2_logs` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `LoggedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Data` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=5519 DEFAULT CHARSET=utf8mb3 COMMENT='Logging of LTI data	';

CREATE TABLE `lti2_nonce` (
  `consumer_pk` int NOT NULL,
  `value` varchar(255) NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`consumer_pk`,`value`),
  CONSTRAINT `lti2_nonce_lti2_consumer_FK1` FOREIGN KEY (`consumer_pk`) REFERENCES `lti2_consumer` (`consumer_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `lti2_resource_link` (
  `resource_link_pk` int NOT NULL AUTO_INCREMENT,
  `context_pk` int DEFAULT NULL,
  `consumer_pk` int DEFAULT NULL,
  `lti_resource_link_id` varchar(255) NOT NULL,
  `settings` text,
  `primary_resource_link_pk` int DEFAULT NULL,
  `share_approved` tinyint(1) DEFAULT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`resource_link_pk`),
  KEY `lti2_resource_link_lti2_resource_link_FK1` (`primary_resource_link_pk`),
  KEY `lti2_resource_link_consumer_pk_IDX` (`consumer_pk`),
  KEY `lti2_resource_link_context_pk_IDX` (`context_pk`),
  CONSTRAINT `lti2_resource_link_lti2_context_FK1` FOREIGN KEY (`context_pk`) REFERENCES `lti2_context` (`context_pk`),
  CONSTRAINT `lti2_resource_link_lti2_resource_link_FK1` FOREIGN KEY (`primary_resource_link_pk`) REFERENCES `lti2_resource_link` (`resource_link_pk`)
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=latin1;

CREATE TABLE `lti2_sessions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `ltiUserId` varchar(255) NOT NULL,
  `version` int unsigned NOT NULL DEFAULT '5',
  `resource_link_pk` varchar(255) NOT NULL,
  `UserID` int unsigned NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=671 DEFAULT CHARSET=utf8mb3 COMMENT='Session information for lti connection';

CREATE TABLE `lti2_share_key` (
  `share_key_id` varchar(32) NOT NULL,
  `resource_link_pk` int NOT NULL,
  `auto_approve` tinyint(1) NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`share_key_id`),
  KEY `lti2_share_key_resource_link_pk_IDX` (`resource_link_pk`),
  CONSTRAINT `lti2_share_key_lti2_resource_link_FK1` FOREIGN KEY (`resource_link_pk`) REFERENCES `lti2_resource_link` (`resource_link_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `lti2_tool_proxy` (
  `tool_proxy_pk` int NOT NULL AUTO_INCREMENT,
  `tool_proxy_id` varchar(32) NOT NULL,
  `consumer_pk` int NOT NULL,
  `tool_proxy` text NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`tool_proxy_pk`),
  UNIQUE KEY `lti2_tool_proxy_tool_proxy_id_UNIQUE` (`tool_proxy_id`),
  KEY `lti2_tool_proxy_consumer_id_IDX` (`consumer_pk`),
  CONSTRAINT `lti2_tool_proxy_lti2_consumer_FK1` FOREIGN KEY (`consumer_pk`) REFERENCES `lti2_consumer` (`consumer_pk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `lti2_user_result` (
  `user_pk` int NOT NULL AUTO_INCREMENT,
  `resource_link_pk` int NOT NULL,
  `lti_user_id` varchar(255) NOT NULL,
  `lti_result_sourcedid` varchar(1024) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`user_pk`),
  KEY `lti2_user_result_resource_link_pk_IDX` (`resource_link_pk`),
  CONSTRAINT `lti2_user_result_lti2_resource_link_FK1` FOREIGN KEY (`resource_link_pk`) REFERENCES `lti2_resource_link` (`resource_link_pk`)
) ENGINE=InnoDB AUTO_INCREMENT=2841 DEFAULT CHARSET=latin1;

CREATE TABLE `MailLogs` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `To` varchar(255) DEFAULT NULL,
  `Subject` varchar(255) DEFAULT NULL,
  `Date` datetime DEFAULT NULL,
  `Success` enum('Y','N') NOT NULL DEFAULT 'N',
  `Message` text,
  `MessageID` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=5663985 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `MassEmails` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Safety` varchar(45) NOT NULL,
  `Count` int NOT NULL,
  `CreationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `AdminID` int NOT NULL,
  `BatchName` varchar(255) NOT NULL DEFAULT 'unNamed',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=355 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `MCSUserData` (
  `UserID` int unsigned NOT NULL,
  `FirstName` varchar(45) NOT NULL,
  `LastName` varchar(45) NOT NULL,
  `DepartmentName` varchar(45) NOT NULL,
  `CourseID` int unsigned NOT NULL,
  `TimeIn` datetime DEFAULT NULL,
  `TestCompleted` datetime DEFAULT NULL,
  `TestResultID` int unsigned DEFAULT NULL,
  PRIMARY KEY (`UserID`,`CourseID`),
  KEY `DepartmentName` (`DepartmentName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Table stores all MCS user data for monthly data report';

CREATE TABLE `migrations` (
  `migration` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

CREATE TABLE `ModalPages` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `PageName` varchar(45) NOT NULL,
  `Delay` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb3 COMMENT='Stores delay amounts for given pages.';

CREATE TABLE `Modals` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `PageID` int NOT NULL,
  `Element` varchar(45) NOT NULL,
  `Description` varchar(500) NOT NULL,
  `Position` varchar(45) DEFAULT NULL,
  `Date` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Element` (`Element`),
  KEY `PageName` (`PageID`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb3 COMMENT='Individual modal events';

CREATE TABLE `ModalViews` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `ModalID` int NOT NULL,
  `ViewDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=109388 DEFAULT CHARSET=utf8mb3 COMMENT='Who has viewed what modal.';

CREATE TABLE `Nations` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Abbreviation` varchar(2) NOT NULL COMMENT 'The abbreviation associated with this Nation (e.g. US, UK)',
  `Name` varchar(25) NOT NULL COMMENT 'The name of this Nation',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Abbreviation` (`Abbreviation`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=245 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Navigation` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `ProductID` int DEFAULT NULL COMMENT 'which product is this navigation element for?',
  `ProductLevel` int DEFAULT NULL,
  `NavOrder` int DEFAULT NULL COMMENT 'the sort order for this element',
  `NavLabel` varchar(64) DEFAULT NULL COMMENT 'the label as it appears in the product',
  `NavType` varchar(16) DEFAULT NULL COMMENT 'top or side',
  `PageInfo` varchar(32) DEFAULT NULL COMMENT 'if NavType is side, the page related to this link',
  `NavActive` varchar(16) DEFAULT NULL COMMENT 'a=active, i=inactive, d=development',
  `NavSub` varchar(3) DEFAULT NULL COMMENT 'y=has sub-menu, n or '' has no sub-menu',
  `SubLevel` tinyint DEFAULT '0',
  `FullTitle` varchar(100) DEFAULT NULL,
  `Demo` enum('Y','N') NOT NULL DEFAULT 'N',
  `LandingPage` enum('Y','N') NOT NULL DEFAULT 'N',
  `Snapshot` varchar(255) DEFAULT NULL,
  `PageTypeID` int unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `pageID` (`PageInfo`),
  KEY `type` (`NavActive`),
  KEY `active` (`NavType`),
  KEY `fc_nav_page_idx` (`PageTypeID`),
  KEY `product` (`ProductID`,`PageInfo`) /*!80000 INVISIBLE */,
  KEY `JustProduct` (`ProductID` DESC),
  CONSTRAINT `fc_nav_page` FOREIGN KEY (`PageTypeID`) REFERENCES `PageTypes` (`ID`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=15081 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `NavigationDeleted` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `ProductID` int DEFAULT NULL COMMENT 'which product is this navigation element for?',
  `ProductLevel` int DEFAULT NULL,
  `NavOrder` int DEFAULT NULL COMMENT 'the sort order for this element',
  `NavLabel` varchar(64) DEFAULT NULL COMMENT 'the label as it appears in the product',
  `NavType` varchar(16) DEFAULT NULL COMMENT 'top or side',
  `PageInfo` varchar(64) DEFAULT NULL COMMENT 'if NavType is side, the page related to this link',
  `NavActive` varchar(16) DEFAULT NULL COMMENT 'a=active, i=inactive, d=development',
  `NavSub` varchar(3) DEFAULT NULL COMMENT 'y=has sub-menu, n or '' has no sub-menu',
  `SubLevel` tinyint DEFAULT '0',
  `FullTitle` varchar(100) DEFAULT NULL,
  `Demo` enum('Y','N') NOT NULL DEFAULT 'N',
  `OldNavigationID` int NOT NULL,
  `DeletedBy` int NOT NULL,
  `DeletionDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `product` (`ProductID`)
) ENGINE=MyISAM AUTO_INCREMENT=1890 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `NavigationSub` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `ProductID` int DEFAULT NULL COMMENT 'which product is this navigation element for?',
  `NavigationID` int DEFAULT NULL COMMENT 'the ID of the parent of this sub in the Navigation table',
  `NavOrder` int DEFAULT NULL COMMENT 'the sort order for this element',
  `NavLabel` varchar(64) DEFAULT NULL COMMENT 'the label as it appears in the product',
  `PageInfo` varchar(64) DEFAULT NULL COMMENT 'the page related to this link',
  `NavActive` varchar(16) DEFAULT NULL COMMENT 'a=active, i=inactive, d=development',
  `SubLevel` varchar(45) DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `product` (`ProductID`)
) ENGINE=MyISAM AUTO_INCREMENT=159 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `NIHAnswers` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `NIHQuestionID` int unsigned NOT NULL COMMENT 'Foreign key into NIHQuestions table',
  `Answer` varchar(512) NOT NULL COMMENT 'The description of the answer as specified by NIH',
  `Value` tinyint unsigned DEFAULT NULL COMMENT 'The value of this answer as specified by NIH',
  `Correct` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this the correct Answer to the question?',
  `Explanation` varchar(2048) DEFAULT NULL COMMENT 'An explanation of why this is the (in)correct answer',
  PRIMARY KEY (`ID`),
  KEY `NIHQuestionID` (`NIHQuestionID`),
  KEY `value_index` (`Value`),
  CONSTRAINT `NIHAnswers_ibfk_1` FOREIGN KEY (`NIHQuestionID`) REFERENCES `NIHQuestions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1143 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `NIHPatients` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Name` varchar(2) NOT NULL COMMENT 'Name of the Patient',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `NIHQuestions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `NIHPatientID` int unsigned NOT NULL COMMENT 'Foreign key into NIHPatients table',
  `Label` varchar(2) NOT NULL COMMENT 'The "number" of this question as specified by NIH',
  `Name` varchar(50) NOT NULL COMMENT 'The "name" of this question as specified by NIH',
  `Description` varchar(1024) NOT NULL COMMENT 'The description of this question as specified by NIH',
  `VideoLink` varchar(50) DEFAULT NULL,
  `HasVideo` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Does this question have its own video? ''N'' implies that the previous question had the video related to this question',
  `VimeoLink` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `NIHPatientID` (`NIHPatientID`),
  CONSTRAINT `NIHQuestions_ibfk_1` FOREIGN KEY (`NIHPatientID`) REFERENCES `NIHPatients` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `NIHResponses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `NIHResultID` int unsigned NOT NULL COMMENT 'Foreign key into NIHResults table',
  `NIHQuestionID` int unsigned NOT NULL COMMENT 'Foreign key into NIHQuestions table',
  `NIHAnswerID` int unsigned DEFAULT NULL COMMENT 'Foreign key into NIHAnswers table',
  PRIMARY KEY (`ID`),
  KEY `NIHResultID` (`NIHResultID`),
  KEY `NIHAnswerID` (`NIHAnswerID`),
  KEY `NIHQuestionID` (`NIHQuestionID`),
  CONSTRAINT `NIHResponses_ibfk_2` FOREIGN KEY (`NIHAnswerID`) REFERENCES `NIHAnswers` (`ID`),
  CONSTRAINT `NIHResponses_ibfk_3` FOREIGN KEY (`NIHQuestionID`) REFERENCES `NIHQuestions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=510302798 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `NIHSSAnswers` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `QuestionID` int unsigned NOT NULL COMMENT 'Foreign key into NIHSSQuestions table',
  `Answer` varchar(512) NOT NULL COMMENT 'The description of the answer as specified by NIH',
  `Value` tinyint unsigned DEFAULT NULL COMMENT 'The value of this answer as specified by NIH',
  `CorrectFor` varchar(60) DEFAULT NULL COMMENT 'Comma separated list of patients for whom this answer is correct (e.g. ''A1,B5,C3'')',
  PRIMARY KEY (`ID`),
  KEY `QuestionID` (`QuestionID`),
  CONSTRAINT `NIHSSAnswers_ibfk_1` FOREIGN KEY (`QuestionID`) REFERENCES `NIHSSQuestions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `NIHSSCertificates` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `TestResultID` int unsigned NOT NULL COMMENT 'Foreign key into NIHSSResults table',
  `CertificateTypeID` int unsigned NOT NULL COMMENT 'Foreign key into CertificateTypes table',
  `TimePrinted` datetime NOT NULL COMMENT 'The date/time this certificate was printed',
  PRIMARY KEY (`ID`),
  KEY `TestResultID` (`TestResultID`),
  KEY `CertificateTypeID` (`CertificateTypeID`),
  CONSTRAINT `NIHSSCertificates_ibfk_2` FOREIGN KEY (`CertificateTypeID`) REFERENCES `TestCertificates` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4077456 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `NIHSSEvaluationDates` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `TestResultID` int unsigned NOT NULL,
  `EvaluationCompleted` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ResultID` (`TestResultID`)
) ENGINE=InnoDB AUTO_INCREMENT=2145748 DEFAULT CHARSET=utf8mb3 COMMENT='Table for tracking when an evaluation is completed.';

CREATE TABLE `NIHSSEvaluationResponses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `TestResultID` int unsigned NOT NULL COMMENT 'Foreign key into NIHSSResults table',
  `EvaluationQuestionID` int unsigned NOT NULL COMMENT 'Foreign key into EvaluationQuestions table',
  `Score` tinyint(1) DEFAULT NULL COMMENT 'The score recorded for this question',
  `multichoice` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `TestResultID` (`TestResultID`),
  KEY `EvaluationQuestionID` (`EvaluationQuestionID`),
  CONSTRAINT `NIHSSEvaluationResponses_ibfk_2` FOREIGN KEY (`EvaluationQuestionID`) REFERENCES `EvaluationQuestions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=47846141 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `NIHSSQuestions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary k',
  `Label` varchar(2) NOT NULL COMMENT 'The "number" of this question as specified by NIH',
  `Name` varchar(50) NOT NULL COMMENT 'The "name" of this question as specified by NIH',
  `Description` varchar(1024) NOT NULL COMMENT 'The description of this question as specified by NIH',
  `HasVideo` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Does this question have its own video? ''N'' implies that the previous question had the video related to this question',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `NIHSSResults` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `CourseID` int unsigned NOT NULL DEFAULT '15' COMMENT 'Foreign key into Courses table',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into CourseObjectives table',
  `TestStarted` datetime NOT NULL COMMENT 'The date/time this test was started',
  `Patient` varchar(3) NOT NULL,
  `PatientList` varchar(20) NOT NULL COMMENT 'The randomized list of patients used in this test, in comma separated format (e.g. ''A3,A6,A1,A4,A2,A4'')',
  `Patient1Answers` varchar(50) DEFAULT NULL COMMENT 'The list of answerIDs given for the first patient listed in PatientList  in comma separated format (e.g. ''3,6,8,13,...'')',
  `Patient2Answers` varchar(50) DEFAULT NULL COMMENT 'The list of answerIDs given for the second patient listed in PatientList  in comma separated format (e.g. ''3,6,8,13,...'')',
  `Patient3Answers` varchar(50) DEFAULT NULL COMMENT 'The list of answerIDs given for the third patient listed in PatientList  in comma separated format (e.g. ''3,6,8,13,...'')',
  `Patient4Answers` varchar(50) DEFAULT NULL COMMENT 'The list of answerIDs given for the fourth patient listed in PatientList  in comma separated format (e.g. ''3,6,8,13,...'')',
  `Patient5Answers` varchar(50) DEFAULT NULL COMMENT 'The list of answerIDs given for the fifth patient listed in PatientList  in comma separated format (e.g. ''3,6,8,13,...'')',
  `Patient6Answers` varchar(50) DEFAULT NULL COMMENT 'The list of answerIDs given for the sixth patient listed in PatientList  in comma separated format (e.g. ''3,6,8,13,...'')',
  `QuestionsAsked` varchar(512) DEFAULT NULL COMMENT 'The comma-separated list of NIHQuestions asked. New for re-do of NIH exam ~1/2012',
  `AnswersGiven` varchar(512) DEFAULT NULL COMMENT 'The comma-separated list of NIHAnswers given. nswers, sequentially. New for re-do of NIH exam ~1/2012',
  `TestCompleted` datetime DEFAULT NULL COMMENT 'The date/time this test was completed',
  `Score` int unsigned DEFAULT NULL COMMENT 'Actual percentage score. 93% or better is required to pass',
  `EvaluationCompleted` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Has this user already taken the course evaluation for this course?',
  `CEHoursClaimed` decimal(4,2) unsigned DEFAULT NULL COMMENT 'The number of CE hours claimed by the test taker',
  `ClearStatus` enum('Y','N') NOT NULL DEFAULT 'N',
  `LicenseID` varchar(10) DEFAULT NULL,
  `CredentialID` varchar(10) DEFAULT NULL,
  `SyncExtendedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `CourseID` (`CourseID`),
  KEY `id_index` (`ID`,`UserID`),
  KEY `EvaluationCompleted` (`EvaluationCompleted`) /*!80000 INVISIBLE */,
  KEY `TestCompleted` (`TestCompleted`,`TestStarted`),
  KEY `Patient` (`Patient`),
  CONSTRAINT `NIHSSResults_ibfk_2` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=6024933 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `NIHSSSpecialLicenses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `LicenseID` int NOT NULL,
  `OneYear` enum('Y','N') NOT NULL DEFAULT 'N',
  `TwoYear` enum('Y','N') NOT NULL DEFAULT 'N',
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb3 COMMENT='If the NIHSS needs 2 year across the board, the license that is used for the NIHSS will be housed here';

CREATE TABLE `NIHTempResults` (
  `ID` int unsigned NOT NULL DEFAULT '0' COMMENT 'Primary key',
  `CourseID` int unsigned NOT NULL DEFAULT '15' COMMENT 'Foreign key into Courses table',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into CourseObjectives table',
  `TestStarted` datetime NOT NULL COMMENT 'The date/time this test was started',
  `PatientList` varchar(20) NOT NULL COMMENT 'The randomized list of patients used in this test, in comma separated format (e.g. ''A3,A6,A1,A4,A2,A4'')',
  `Patient1Answers` varchar(50) DEFAULT NULL COMMENT 'The list of answerIDs given for the first patient listed in PatientList  in comma separated format (e.g. ''3,6,8,13,...'')',
  `Patient2Answers` varchar(50) DEFAULT NULL COMMENT 'The list of answerIDs given for the second patient listed in PatientList  in comma separated format (e.g. ''3,6,8,13,...'')',
  `Patient3Answers` varchar(50) DEFAULT NULL COMMENT 'The list of answerIDs given for the third patient listed in PatientList  in comma separated format (e.g. ''3,6,8,13,...'')',
  `Patient4Answers` varchar(50) DEFAULT NULL COMMENT 'The list of answerIDs given for the fourth patient listed in PatientList  in comma separated format (e.g. ''3,6,8,13,...'')',
  `Patient5Answers` varchar(50) DEFAULT NULL COMMENT 'The list of answerIDs given for the fifth patient listed in PatientList  in comma separated format (e.g. ''3,6,8,13,...'')',
  `Patient6Answers` varchar(50) DEFAULT NULL COMMENT 'The list of answerIDs given for the sixth patient listed in PatientList  in comma separated format (e.g. ''3,6,8,13,...'')',
  `QuestionsAsked` varchar(512) DEFAULT NULL COMMENT 'The comma-separated list of NIHQuestions asked. New for re-do of NIH exam ~1/2012',
  `AnswersGiven` varchar(512) DEFAULT NULL COMMENT 'The comma-separated list of NIHAnswers given. nswers, sequentially. New for re-do of NIH exam ~1/2012',
  `TestCompleted` datetime DEFAULT NULL COMMENT 'The date/time this test was completed',
  `Score` int unsigned DEFAULT NULL COMMENT 'Actual percentage score. 93% or better is required to pass',
  `EvaluationCompleted` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Has this user already taken the course evaluation for this course?',
  `CEHoursClaimed` decimal(4,2) unsigned DEFAULT NULL COMMENT 'The number of CE hours claimed by the test taker',
  KEY `idx_Temp` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Notices` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Message` text NOT NULL,
  `Date` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `OrganizationAdmins` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `OrganizationID` int unsigned NOT NULL COMMENT 'Foreign key into Organizations table',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `SeeEvaluations` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Does this admin want to see course evaluations? 0 - No; 1 - Bad only; 2 - All',
  `SeeNewUsers` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Does this admin want to see new users?',
  `CanMoveSeats` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Can this Admin use the Move Seats feature?',
  `AllowedProductIDs` varchar(50) DEFAULT NULL COMMENT 'Comma separated list of product IDs the user is allowed to administer',
  `examFailEmail` enum('Y','N') NOT NULL DEFAULT 'N',
  `MaxAttempts` enum('Y','N') NOT NULL DEFAULT 'Y',
  `LicenseInfo` enum('Y','N') NOT NULL DEFAULT 'Y',
  `EssayEmail` enum('Y','N') NOT NULL DEFAULT 'N',
  `TestCompleted` enum('Y','N') NOT NULL DEFAULT 'N',
  `MainContact` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unique_user` (`OrganizationID`,`UserID`),
  KEY `Users` (`UserID`),
  CONSTRAINT `OrganizationAdmins_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=5484 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `OrganizationEvents` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `OrganizationID` int unsigned NOT NULL,
  `EventID` int NOT NULL,
  `Notes` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=30169 DEFAULT CHARSET=utf8mb3 COMMENT='If an admin event involves an organization';

CREATE TABLE `Organizations` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Name` varchar(60) NOT NULL COMMENT 'The name of this Organization',
  `Address` varchar(100) NOT NULL COMMENT 'The mailing address for this Organization',
  `Address2` varchar(100) DEFAULT NULL COMMENT 'Second line of address',
  `City` varchar(50) NOT NULL COMMENT 'The mailing city for this Organization',
  `StateID` int unsigned DEFAULT NULL COMMENT 'Foreign key into States table - the mailing state/province for this Organization',
  `PostalCode` varchar(10) NOT NULL COMMENT 'The postal code for this Organization',
  `CountryID` int unsigned NOT NULL DEFAULT '231' COMMENT 'Foreign key into Countries table - the mailing country for this Organization',
  `Phone` varchar(25) NOT NULL COMMENT 'The phone number for this Organization',
  `CreationDate` date NOT NULL COMMENT 'The date this Organization was created in the database',
  `Comments` varchar(1024) DEFAULT NULL COMMENT 'Any comments Apex needs to add about this Organization',
  `CurriculumNotice` varchar(600) DEFAULT NULL COMMENT 'The notice to put up on all users'' MyCurriculum page',
  `CurriculumDate` datetime DEFAULT NULL,
  `CommunityNotice` varchar(600) DEFAULT NULL COMMENT 'The notice to put up on all users'' MyCommunity page',
  `CommunityDate` datetime DEFAULT NULL,
  `Demo` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this a Demonstration Organization? (all users expire after 48 hours)',
  `RequireEmployeeNum` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this Organization require new accounts to supply an Employee ID?',
  `CoursewareTimeout` int unsigned NOT NULL DEFAULT '15' COMMENT 'Minutes until Users in this Organization will timeout of the courseware if idle (0 means never)',
  `PasswordExpirationDays` smallint unsigned NOT NULL DEFAULT '0' COMMENT 'Number of days users can use a password before it expires (0 means never)',
  `PasswordMinLength` tinyint unsigned NOT NULL DEFAULT '6' COMMENT 'Minimum length allowed for a password',
  `PasswordHistoryLength` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Number of unique new passwords that have to be associated with a user before an old password can be reused',
  `PasswordLockoutAttempts` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Number of times user is allowed to fail login before account is locked out (0 means never lockout)',
  `PasswordLockoutDuration` smallint unsigned NOT NULL DEFAULT '0' COMMENT 'If account locked out, number of minutes until user can re-login (0 means immediately)',
  `PasswordComplexityNumeric` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Must password contain at least one numeric character?',
  `PasswordComplexitySpecial` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Must password contain at least one special character?',
  `PasswordComplexityUppercase` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Must password contain at least one uppercase character?',
  `PasswordComplexityLowercase` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Must password contain at least one lowercase character?',
  `PasswordComplexityNoUserInfo` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Must password not contain any User info (name, login)?',
  `IPRange` varchar(1024) DEFAULT NULL COMMENT 'Comma separated list of IPv4 addresses, ranges, or CIDR representations with submask (e.g. 1.2.3.4 and/or 1.2.0.0-1.2.0.32 and/or 1.0.0.0/8) this organization uses ',
  `ForceIPRangeLogin` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'If Y, only allow users to login from IPRange specified',
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is this an Active Organization?',
  `TestQuestionExclusion` enum('Y','N') NOT NULL DEFAULT 'N',
  `AllowFullDepartmentSeats` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Can seats be issued for users if their department is already at capacity when they register keys?',
  `AllowEHAC` enum('Y','N') NOT NULL DEFAULT 'Y',
  `AllowNIHSS` enum('Y','N') NOT NULL DEFAULT 'Y',
  `Latitude` float DEFAULT NULL COMMENT 'Approximate latitude (0 to not show)',
  `Longitude` float DEFAULT NULL COMMENT 'Approximate longitude (0 to not show)',
  `ApexCommunity` enum('Y','N') DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`),
  KEY `StateID` (`StateID`),
  KEY `CountryID` (`CountryID`),
  KEY `Demo` (`Demo`),
  CONSTRAINT `Organizations_ibfk_36` FOREIGN KEY (`StateID`) REFERENCES `States` (`ID`),
  CONSTRAINT `Organizations_ibfk_37` FOREIGN KEY (`CountryID`) REFERENCES `Countries` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1081 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `organizationStatistics` (
  `OrganizationID` int unsigned NOT NULL,
  `EndDate` datetime DEFAULT NULL,
  `ExpirationDate` datetime DEFAULT NULL,
  `HasActiveSeats` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`OrganizationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Table used for compiled data';

CREATE TABLE `PageBookmarks` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `CourseID` int unsigned NOT NULL COMMENT 'Foreign key into Courses table',
  `PageID` int unsigned NOT NULL COMMENT 'Foreign key into Pages table - eventually',
  `Created` datetime NOT NULL COMMENT 'The date/time this record was created',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`UserID`,`CourseID`,`PageID`),
  KEY `UserID` (`UserID`),
  KEY `PageID` (`PageID`),
  KEY `CourseID` (`CourseID`),
  CONSTRAINT `PageBookmarks_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`),
  CONSTRAINT `PageBookmarks_ibfk_2` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=27404 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Pages` (
  `ID` int unsigned NOT NULL,
  `Name` varchar(256) DEFAULT NULL COMMENT 'The name of the Page',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `PageSearches` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `CourseID` int unsigned NOT NULL COMMENT 'Foreign key into Courses table',
  `PageID` int unsigned NOT NULL COMMENT 'Foreign key into Pages table',
  `SearchText` varchar(256) NOT NULL COMMENT 'Actual search text',
  `Created` datetime NOT NULL COMMENT 'The date/time this record was created',
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `PageID` (`PageID`),
  KEY `CourseID` (`CourseID`),
  CONSTRAINT `PageSearches_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`),
  CONSTRAINT `PageSearches_ibfk_2` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=213218 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `PageTypes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Type` varchar(45) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3 COMMENT='Types of pages used in HTML5 engine	';

CREATE TABLE `PageVisits` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `CourseID` int unsigned NOT NULL COMMENT 'Foreign key into Courses table',
  `PageID` int unsigned NOT NULL COMMENT 'Foreign key into Pages table - eventually',
  `TimeIn` datetime NOT NULL COMMENT 'Time the page was opened',
  `SecondsIn` int unsigned NOT NULL DEFAULT '0' COMMENT 'Number of seconds page was opened',
  PRIMARY KEY (`ID`,`TimeIn`),
  KEY `UserID` (`UserID`),
  KEY `PageID` (`PageID`),
  KEY `CourseID` (`CourseID`),
  KEY `TimeIn` (`TimeIn` DESC),
  KEY `key1` (`TimeIn` DESC,`UserID`,`CourseID`)
) ENGINE=InnoDB AUTO_INCREMENT=277210766 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `PageVisits_Archive` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `CourseID` int unsigned NOT NULL COMMENT 'Foreign key into Courses table',
  `PageID` int unsigned NOT NULL COMMENT 'Foreign key into Pages table - eventually',
  `TimeIn` datetime NOT NULL COMMENT 'Time the page was opened',
  `SecondsIn` int unsigned NOT NULL DEFAULT '0' COMMENT 'Number of seconds page was opened',
  PRIMARY KEY (`ID`,`TimeIn`),
  KEY `UserID` (`UserID`),
  KEY `PageID` (`PageID`),
  KEY `CourseID` (`CourseID`),
  KEY `TimeIn` (`TimeIn` DESC),
  KEY `key1` (`TimeIn` DESC,`UserID`,`CourseID`)
) ENGINE=InnoDB AUTO_INCREMENT=245031497 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ParameterTypes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COMMENT='The parameters for reports';

CREATE TABLE `password_resets` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Login` varchar(60) NOT NULL,
  `token` varchar(60) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Secondary` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=461002 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `PreEvalLicenses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `LicenseID` int unsigned NOT NULL,
  `EvalBefore` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COMMENT='Table to store licenses which need to be given a pre-evaluation before testing can begin or evaluation can be completed.';

CREATE TABLE `PreEvalQuestions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `LicenseID` int unsigned NOT NULL,
  `Question` varchar(256) NOT NULL,
  `Order` int NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb3 COMMENT='Table for storing PreEvalQuestions so we dont mix data with EvalQuestions';

CREATE TABLE `PreEvalResponses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `LicenseID` int unsigned NOT NULL,
  `CourseID` int unsigned NOT NULL,
  `TestResultID` int unsigned NOT NULL,
  `Response` varchar(256) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb3 COMMENT='Table for storing our PreEvaluation Responses.';

CREATE TABLE `PrePostOutcomes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `LicenseConfigurationID` int unsigned NOT NULL COMMENT 'Foreign key into LicenseConfigurations table',
  `TestQuestionID` int unsigned NOT NULL COMMENT 'Foreign key into TestQuestions table - this test question cannot be missed and the user still pass the test',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`LicenseConfigurationID`,`TestQuestionID`) /*!80000 INVISIBLE */,
  KEY `TestQuestion` (`TestQuestionID`),
  KEY `License` (`LicenseConfigurationID`),
  CONSTRAINT `PrePostOutcomes_ibfk_3` FOREIGN KEY (`LicenseConfigurationID`) REFERENCES `LicenseConfigurations` (`ID`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `PrePostOutcomes_ibfk_4` FOREIGN KEY (`TestQuestionID`) REFERENCES `TestQuestions` (`ID`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=13011 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ProductCourseTerms` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ProductID` int NOT NULL,
  `Term` varchar(500) NOT NULL,
  `Definition` text NOT NULL,
  `SortOrder` int NOT NULL DEFAULT '0',
  `CreatedBy` int DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `idx_product` (`ProductID`)
) ENGINE=InnoDB AUTO_INCREMENT=4397 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ProductDemos` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `ProductID` int unsigned NOT NULL COMMENT 'Foreign key into Products table',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `DemoStartDate` datetime NOT NULL COMMENT 'Date/Time when demo starts',
  `DemoHours` int unsigned NOT NULL DEFAULT '336',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`ProductID`,`UserID`),
  KEY `UserID` (`UserID`),
  CONSTRAINT `ProductDemos_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `Products` (`ID`),
  CONSTRAINT `ProductDemos_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7501 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ProductPreferences` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `ProductID` int unsigned NOT NULL,
  `UserID` int unsigned NOT NULL,
  `Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `PropertiesJSON` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=868 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Products` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Name` varchar(45) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'The name of this Apex Innovation product',
  `Registered` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Has this product received a registered trademark?',
  `TagLine` varchar(150) DEFAULT NULL COMMENT 'The tagline (slogan) for this Product',
  `Description` text COMMENT 'An HTML paragraph describing the product',
  `MaxCourses` smallint unsigned NOT NULL DEFAULT '8' COMMENT 'The maximum number of courses that appear on the User Interface of this product',
  `PathToUserGuide` varchar(256) DEFAULT NULL COMMENT 'The relative path to the PDF file that is the User Guide for the product',
  `DemoKey` varchar(6) DEFAULT NULL COMMENT 'Demo key to allow users temporary access to product',
  `CME` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this product offer CME credits?',
  `CNE` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this product offer CNE credits?',
  `CEH` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this product offer CEH credits?',
  `CAPT` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Physical Therapy Board of California',
  `CPE` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Physical Therapy Board of California',
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is this an Active Product?',
  `Beta` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this a beta product? Set inactive as well...',
  `Version` decimal(3,1) NOT NULL DEFAULT '3.0' COMMENT 'The version of the courseware engine this course currently runs on',
  `DefaultButtons` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Does this product have default custom buttons imported?',
  `PathToLogo` varchar(256) DEFAULT NULL,
  `PathToLogoLarge` varchar(256) DEFAULT NULL,
  `UnitPrice` decimal(6,2) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`),
  UNIQUE KEY `DemoKey` (`DemoKey`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ProductTranslations` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `FromProductID` int unsigned NOT NULL,
  `ToProductID` int unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ToProductID_index` (`ToProductID`),
  KEY `FromProductID_index` (`FromProductID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COMMENT='Product to product translations';

CREATE TABLE `ProfessionalCredentialFilters` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `ProfessionalRoleID` int NOT NULL,
  `CredentialID` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb3 COMMENT='Intersection between Professional Roles and Credentials';

CREATE TABLE `ProfessionalRoles` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(35) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name_UNIQUE` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Providers` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(60) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ProxyLogonTokens` (
  `Token` char(64) NOT NULL,
  `AdminID` int NOT NULL,
  `TargetLogin` varchar(255) NOT NULL,
  `ExpiresAt` datetime NOT NULL,
  `UsedAt` datetime DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ReportCredentials` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Name` varchar(35) NOT NULL COMMENT 'The name of this Credential (e.g. Ph.D., M.D.)',
  `CEH` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this Credential receive CEH credit?',
  `CNE` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this Credential receive CNE credit?',
  `CME` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this Credential receive CME credit?',
  `CAPT` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Does this Credential receive Physical Therapy Board of California credit?',
  `OHPT` enum('Y','N') NOT NULL DEFAULT 'N',
  `PNPT` enum('Y','N') NOT NULL DEFAULT 'N',
  `CPE` enum('Y','N') NOT NULL DEFAULT 'N',
  `AccreditorID` int unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `ReportFormats` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `ReportID` int unsigned NOT NULL,
  `Format` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ReportIDColumn_idx` (`ReportID`),
  CONSTRAINT `ReportIDColumn` FOREIGN KEY (`ReportID`) REFERENCES `Reports` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3 COMMENT='Listing of formats for each report';

CREATE TABLE `ReportLogs` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `ReportID` int NOT NULL,
  `UserID` int NOT NULL,
  `Version` int NOT NULL,
  `CreationDate` datetime DEFAULT NULL,
  `Parameters` varchar(255) DEFAULT NULL,
  `DataType` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1003609 DEFAULT CHARSET=utf8mb3 COMMENT='Record all report creations.';

CREATE TABLE `ReportOccurrences` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Type` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Type_UNIQUE` (`Type`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COMMENT='A listing of possible occurences for a scheduled report';

CREATE TABLE `ReportParams` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `ReportID` int unsigned NOT NULL,
  `ParamID` int unsigned NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb3 COMMENT='Params for reports';

CREATE TABLE `Reports` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Controller` varchar(255) DEFAULT NULL,
  `EmployeeAdmin` enum('Y','N') NOT NULL DEFAULT 'N',
  `SystemAdmin` enum('Y','N') NOT NULL DEFAULT 'N',
  `OrganizationAdmin` enum('Y','N') NOT NULL DEFAULT 'N',
  `DepartmentAdmin` enum('Y','N') NOT NULL DEFAULT 'N',
  `Custom` enum('Y','N') NOT NULL DEFAULT 'N',
  `Active` enum('Y','N') NOT NULL DEFAULT 'N',
  `Downloads` enum('Y','N') NOT NULL DEFAULT 'Y',
  `Legend` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb3 COMMENT='A listing of all reports';

CREATE TABLE `ReportSchedules` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `ReportID` int unsigned NOT NULL,
  `ReportOccurrenceID` int unsigned NOT NULL,
  `UserID` int unsigned NOT NULL,
  `ScheduleDate` datetime NOT NULL,
  `Parameters` text NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=588 DEFAULT CHARSET=utf8mb3 COMMENT='A listing of all reports that are to be ran on a schedule';

CREATE TABLE `SecurityQuestions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Question` varchar(128) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'The text of the security question - used to reissue a user a new password',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Question` (`Question`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `SeeUsSoon` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Link` varchar(255) DEFAULT NULL,
  `Title` varchar(45) NOT NULL,
  `Location` varchar(45) DEFAULT NULL,
  `BeginDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `BoothInfo` varchar(45) DEFAULT NULL,
  `Host` varchar(45) DEFAULT NULL,
  `UUID` varchar(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`,`UUID`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `session_handler_table` (
  `id` varchar(255) NOT NULL,
  `data` mediumtext NOT NULL,
  `timestamp` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `SocialLogins` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `UserID` int DEFAULT NULL,
  `Provider` int NOT NULL,
  `Email` varchar(60) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2993 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `States` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Abbreviation` varchar(2) NOT NULL COMMENT 'The abbreviation associated with this State/province (e.g. NY, LA, CA)',
  `Name` varchar(25) NOT NULL COMMENT 'The name of this State/province',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Abbreviation` (`Abbreviation`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `SummaryQAndAStats` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Month` date NOT NULL COMMENT 'Snapshot date',
  `TestQuestionID` int unsigned NOT NULL COMMENT 'Foreign key into TestQuestions table',
  `NumAnswered` int unsigned NOT NULL COMMENT 'Number of times this question was asked in the month',
  `NumCorrect` int unsigned NOT NULL COMMENT 'Number of time this question was answered correctly in the month',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Month` (`Month`,`TestQuestionID`),
  KEY `TestQuestionID` (`TestQuestionID`),
  CONSTRAINT `SummaryQAndAStats_ibfk_1` FOREIGN KEY (`TestQuestionID`) REFERENCES `TestQuestions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=19222 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `SummaryTestResponses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `DepartmentID` int unsigned NOT NULL,
  `TestQuestionID` int unsigned NOT NULL,
  `TotalCorrect` int NOT NULL,
  `TotalGraded` int NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Unique` (`DepartmentID`,`TestQuestionID`),
  KEY `DepartmentID` (`DepartmentID`),
  KEY `TestQuestionID` (`TestQuestionID`)
) ENGINE=InnoDB AUTO_INCREMENT=96582230 DEFAULT CHARSET=utf8mb3 COMMENT='Remade SummaryTestResponses table since the old one is givin';

CREATE TABLE `SummaryTestResponsesOverTime` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Month` date NOT NULL COMMENT 'Snapshot date',
  `TestQuestionID` int unsigned NOT NULL COMMENT 'Foreign key into TestQuestions table',
  `CredentialID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Credentials table. The credentials of this User (e.g. M.D., Ph.D., R.N.)',
  `NumAnswered` int unsigned NOT NULL COMMENT 'Number of times this question was asked in the month',
  `NumCorrect` int unsigned NOT NULL COMMENT 'Number of time this question was answered correctly in the month',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`Month`,`TestQuestionID`,`CredentialID`),
  KEY `TestQuestionID` (`TestQuestionID`),
  CONSTRAINT `SummaryTestResponsesOverTime_ibfk_1` FOREIGN KEY (`TestQuestionID`) REFERENCES `TestQuestions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=41874921 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `SummaryUsageStats` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Date` datetime NOT NULL COMMENT 'The date this occurred',
  `NumUsers` int unsigned NOT NULL COMMENT 'Number of Users logging in on this date',
  `NumLMSUsers` int unsigned DEFAULT NULL COMMENT 'Number of LMS Users logging in on this date',
  `NumTests` int unsigned NOT NULL COMMENT 'The number of tests taken on the date specified',
  `NumNIHTests` int unsigned NOT NULL DEFAULT '0' COMMENT 'The number of NIHSS tests completed on the date in question',
  `HoursIn` int unsigned NOT NULL COMMENT 'The number of hours spent in courses on the date specified',
  `MonthlyUsers` int unsigned DEFAULT NULL COMMENT 'Number of Users logging in this month',
  `MonthlyLMSUsers` int unsigned DEFAULT NULL COMMENT 'Number of LMS Users logging in this month',
  `YearlyUsers` int unsigned DEFAULT NULL COMMENT 'Number of Users logging in this year',
  `YearlyLMSUsers` int unsigned DEFAULT NULL COMMENT 'Number of LMS Users logging in this year',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UNIQUE` (`Date`)
) ENGINE=InnoDB AUTO_INCREMENT=7276 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `SupportAttachments` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `SupportTicketID` int unsigned NOT NULL,
  `Name` text NOT NULL,
  `Size` int NOT NULL,
  `Type` int NOT NULL,
  `MimeType` text NOT NULL,
  `Deleted` enum('Y','N') NOT NULL DEFAULT 'N',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1986 DEFAULT CHARSET=utf8mb3 COMMENT='Store attachment URL and type';

CREATE TABLE `SupportAttachmentTypes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Type` varchar(45) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COMMENT='Type of attachments';

CREATE TABLE `SupportNotices` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) NOT NULL,
  `Notice` text NOT NULL,
  `EmployeeID` int DEFAULT NULL,
  `StartDate` datetime DEFAULT NULL,
  `EndDate` datetime DEFAULT NULL,
  `Priority` int NOT NULL DEFAULT '1',
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `Visible` enum('Y','N') NOT NULL DEFAULT 'Y',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb3 COMMENT='Houses notices used for contact us system.';

CREATE TABLE `SupportReplyEmails` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `SupportTicketID` int NOT NULL,
  `ReplyEmail` text NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=20469 DEFAULT CHARSET=utf8mb3 COMMENT='Keeps a record of replys sent to Contact Us emails';

CREATE TABLE `SupportReplyTemplates` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `EmployeeID` int NOT NULL,
  `Public` enum('Y','N') CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `Title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `Template` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `Active` enum('Y','N') CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT 'Y',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='All reply templates set for use by tech support staff';

CREATE TABLE `SupportTicketPayouts` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `EmployeeTriggerID` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=496 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `SupportTickets` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `CodeName` varchar(100) DEFAULT NULL,
  `EmailMessage` longtext,
  `PhoneNumber` varchar(45) DEFAULT NULL,
  `UserID` int DEFAULT NULL,
  `EmployeeID` int DEFAULT NULL,
  `Key` varchar(45) DEFAULT NULL,
  `Notes` varchar(400) DEFAULT NULL,
  `From` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `Started` datetime DEFAULT NULL,
  `Completed` datetime DEFAULT NULL,
  `BountyClaimed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Solved` enum('Y','N') NOT NULL DEFAULT 'N',
  `ResolutionNotes` varchar(400) DEFAULT NULL,
  `SupportTypeID` int DEFAULT NULL,
  `LengthOfCall` int DEFAULT NULL,
  `Archived` enum('Y','N') NOT NULL DEFAULT 'N',
  `VoicemailFileName` varchar(100) DEFAULT NULL,
  `EmailAddress` text,
  `Validated` enum('Y','N') NOT NULL DEFAULT 'Y',
  `Paid` enum('Y','N') NOT NULL DEFAULT 'N',
  `SupportTicketPayoutID` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=43930 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `SupportTicketTransfers` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `SupportTicketID` int NOT NULL,
  `TransferFromEmployeeID` int DEFAULT NULL,
  `TransferToEmployeeID` int DEFAULT NULL,
  `TransferReason` mediumtext,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=760 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `SupportTypes` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(45) NOT NULL,
  `BaseRate` float NOT NULL,
  `TimeMultiplier` float NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `SystemAdmins` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `SystemID` int unsigned NOT NULL COMMENT 'Foreign key into Systems table',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  PRIMARY KEY (`ID`),
  KEY `Users` (`UserID`),
  CONSTRAINT `SystemAdmins_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=226 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `SystemEvents` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `SystemID` int unsigned NOT NULL,
  `EventID` int NOT NULL,
  `Notes` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=932 DEFAULT CHARSET=utf8mb3 COMMENT='If an admin event involves a system';

CREATE TABLE `SystemOrganizations` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `SystemID` int unsigned NOT NULL COMMENT 'Foreign key into Systems table',
  `OrganizationID` int unsigned NOT NULL COMMENT 'Foreign key into Organizations table',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2083 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Systems` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Name` varchar(50) NOT NULL COMMENT 'The name of this System of Organizations',
  `CreationDate` date NOT NULL COMMENT 'The date this System was created in the database',
  `Comments` varchar(1024) DEFAULT NULL COMMENT 'Any comments Apex needs to add about this System',
  `CurriculumNotice` varchar(600) DEFAULT NULL COMMENT 'The notice to put up on all users'' MyCurriculum page',
  `CurriculumDate` datetime DEFAULT NULL,
  `CommunityNotice` varchar(600) DEFAULT NULL COMMENT 'The notice to put up on all users'' MyCommunity page',
  `CommunityDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `TaskrunnerChecks` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `CheckDate` datetime NOT NULL,
  `Response` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1307972 DEFAULT CHARSET=utf8mb3 COMMENT='List of when taskrunner was checked';

CREATE TABLE `TestAnswers` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `QuestionID` int unsigned NOT NULL COMMENT 'Foreign key into TestQuestions table',
  `Answer` varchar(1024) NOT NULL COMMENT 'The text of the Answer',
  `Correct` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this the correct Answer to the question?',
  `Explanation` text COMMENT 'An explanation of why this is the (in)correct answer',
  PRIMARY KEY (`ID`),
  KEY `QuestionID` (`QuestionID`),
  CONSTRAINT `TestAnswers_ibfk_1` FOREIGN KEY (`QuestionID`) REFERENCES `TestQuestions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=89064 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `TestAttemptEmails` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `LicenseSeatID` int unsigned NOT NULL,
  `CourseID` int unsigned NOT NULL,
  `EmailSent` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=251707 DEFAULT CHARSET=utf8mb3 COMMENT='Store when an email has already been sent for a given user f';

CREATE TABLE `TestCertificates` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `TestResultID` int unsigned NOT NULL COMMENT 'Foreign key into TestResults table',
  `CertificateTypeID` int unsigned NOT NULL COMMENT 'Foreign key into CertificateTypes table',
  `TimePrinted` datetime NOT NULL COMMENT 'The date/time this certificate was printed',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2956796 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `TestClearEvents` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `TestResultID` int unsigned NOT NULL,
  `UserID` int unsigned NOT NULL,
  `EventDate` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `TestResultID` (`TestResultID`),
  KEY `UserID` (`UserID`),
  CONSTRAINT `TestClearEvents_ibfk_1` FOREIGN KEY (`TestResultID`) REFERENCES `TestResults` (`ID`),
  CONSTRAINT `TestClearEvents_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=17047 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `TestEvaluationDates` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `TestResultID` int unsigned NOT NULL,
  `EvaluationCompleted` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ResultID` (`TestResultID`)
) ENGINE=InnoDB AUTO_INCREMENT=1551692 DEFAULT CHARSET=utf8mb3 COMMENT='Table for tracking when an evaluation is completed.';

CREATE TABLE `TestEvaluationResponses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `TestResultID` int unsigned NOT NULL COMMENT 'Foreign key into TestResults table',
  `EvaluationQuestionID` int unsigned NOT NULL COMMENT 'Foreign key into EvaluationQuestions table',
  `Score` tinyint unsigned DEFAULT NULL COMMENT 'The score recorded for this question',
  `multichoice` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Unique` (`TestResultID`,`EvaluationQuestionID`),
  KEY `EvaluationQuestionID` (`EvaluationQuestionID`),
  KEY `TestResultID` (`TestResultID`),
  CONSTRAINT `TestEvaluationResponses_ibfk_2` FOREIGN KEY (`EvaluationQuestionID`) REFERENCES `EvaluationQuestions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=44960647 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `TestQuestionAssets` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `TestQuestionID` int NOT NULL,
  `URL` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `TestQuestionIndex` (`TestQuestionID`)
) ENGINE=InnoDB AUTO_INCREMENT=927 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `TestQuestionExclusions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `OrganizationID` int NOT NULL,
  `CredentialID` int NOT NULL,
  `CourseID` int NOT NULL,
  `TestQuestionIDs` varchar(256) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb3 COMMENT='Determine which questions should be excluded from certain cr';

CREATE TABLE `TestQuestionLinks` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `TestQuestionID` int unsigned NOT NULL,
  `PageID` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2221 DEFAULT CHARSET=utf8mb3 COMMENT='Holds connection between test questions and navigation pages.';

CREATE TABLE `TestQuestions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `CourseID` int unsigned NOT NULL COMMENT 'Foreign key into Courses table',
  `Question` varchar(1024) NOT NULL COMMENT 'The text of the Question',
  `PathToDiagram` varchar(256) DEFAULT NULL COMMENT 'The relative path to a diagram (.gif, .jpg, .png, .swf) associated with this Question',
  `WidthOfDiagram` smallint unsigned DEFAULT NULL COMMENT 'The width of the diagram in pixels',
  `HeightOfDiagram` smallint unsigned DEFAULT NULL COMMENT 'The height of the diagram in pixels',
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is this an active question?',
  `Referral` text COMMENT 'Where in the course material this material is covered',
  `CourseObjectiveID` int unsigned DEFAULT NULL COMMENT 'Foreign key into CourseObjectives table',
  `Float` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is the diagram for this question supposed to CSS ''float''?',
  `FlashDescriptor` varchar(256) DEFAULT NULL,
  `Notes` varchar(512) DEFAULT NULL COMMENT 'Note any reasons for activation/deactivation',
  `OutcomeMeasure` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is the question specifically outcome based?',
  PRIMARY KEY (`ID`),
  KEY `CourseID` (`CourseID`),
  KEY `Active` (`Active`),
  CONSTRAINT `TestQuestions_ibfk_1` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=17997 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `TestQuestionSiblings` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `QuestionID` int unsigned NOT NULL,
  `Group` varchar(128) NOT NULL,
  `Order` smallint NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1759 DEFAULT CHARSET=utf8mb3 COMMENT='This table holds questions that need to be asked back to back while the test itself is still randomized.';

CREATE TABLE `TestResponses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `TestResultID` int unsigned NOT NULL COMMENT 'Foreign key into TestResults table',
  `TestQuestionID` int unsigned NOT NULL COMMENT 'Foreign key into TestQuestions table',
  `TestAnswerID` int unsigned DEFAULT NULL COMMENT 'Foreign key into TestAnswers table',
  PRIMARY KEY (`ID`),
  KEY `TestResultID` (`TestResultID`),
  KEY `TestAnswerID` (`TestAnswerID`),
  KEY `TestQuestionID` (`TestQuestionID`),
  CONSTRAINT `TestResponses_ibfk_2` FOREIGN KEY (`TestAnswerID`) REFERENCES `TestAnswers` (`ID`),
  CONSTRAINT `TestResponses_ibfk_3` FOREIGN KEY (`TestQuestionID`) REFERENCES `TestQuestions` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=165045153 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `TestResults` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `CourseID` int unsigned NOT NULL COMMENT 'Foreign key into Courses table',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `TestStarted` datetime NOT NULL COMMENT 'Date/time this test was started',
  `TestCompleted` datetime DEFAULT NULL COMMENT 'Date/time this test was completed',
  `Score` tinyint unsigned DEFAULT NULL COMMENT 'The User''s score on this test (percentage)',
  `EvaluationCompleted` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Has this user already taken the course evaluation for this course?',
  `QuestionsAsked` varchar(2048) DEFAULT NULL COMMENT 'The QuestionIDs asked when the user took this test, comma separated',
  `AnswersGiven` varchar(2048) DEFAULT NULL COMMENT 'The AnswerIDs given when the user took this test, comma separated',
  `CEHoursClaimed` decimal(4,2) unsigned DEFAULT NULL COMMENT 'The number of CE hours claimed by the test taker',
  `ClearStatus` enum('Y','N') NOT NULL DEFAULT 'N',
  `LicenseID` int unsigned DEFAULT NULL,
  `CredentialID` int unsigned DEFAULT NULL,
  `SyncExtendedDate` datetime DEFAULT NULL,
  `Essay` enum('Y','N') NOT NULL DEFAULT 'N',
  `GradePending` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `CourseID` (`CourseID`),
  KEY `UserID` (`UserID`),
  KEY `EvaluationCompleted` (`EvaluationCompleted`),
  KEY `TestCompleted` (`TestCompleted`,`TestStarted`),
  KEY `id_index` (`ID`,`UserID`),
  KEY `LicenseID` (`LicenseID`,`Essay`),
  CONSTRAINT `TestResults_ibfk_3` FOREIGN KEY (`CourseID`) REFERENCES `Courses` (`ID`),
  CONSTRAINT `TestResults_ibfk_4` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=11265698 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `thoughts_and_prayers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `ip_address` text NOT NULL,
  `event_type` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `TraxAutoCloses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Text` varchar(255) NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb3 COMMENT='Listing of comments that will cause a ticket to autoclose';

CREATE TABLE `TraxNotes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Mnemonic` varchar(75) NOT NULL,
  `Note` varchar(2048) NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb3 COMMENT='Predetermined notes which can be attached to trax issues';

CREATE TABLE `UpgradedToHemi2` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UpgradeDate` datetime DEFAULT NULL,
  `UserID` int unsigned NOT NULL,
  `OrganizationID` int unsigned NOT NULL,
  `Store` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb3 COMMENT='This table logs all orgs and users which upgrade to hemi 2.0';

CREATE TABLE `Uploads` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Uploaded` datetime NOT NULL COMMENT 'Date/time this file was uploaded',
  `UserID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Users table. The person who uploaded the file.',
  `OrganizationID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Organizations table. The organization this file is for.',
  `UploadTypeID` int unsigned DEFAULT NULL COMMENT 'Foreign key into UploadTypes table. Specifies what kind of file this is (NOT it''s extension)',
  `ProductID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Products table. Which product does this apply to?',
  `OriginalName` varchar(128) NOT NULL COMMENT 'The original name of this file',
  `Extension` varchar(4) NOT NULL COMMENT 'The file extension (without dot)',
  `System` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this file available to just the Organization, or to users from the System?',
  `Public` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this file available to everyone, or just to users from the Organization?',
  `Size` int unsigned NOT NULL COMMENT 'The size of the file, in bytes (pre-SQL encoding)',
  `Height` int unsigned NOT NULL DEFAULT '0' COMMENT 'If upload is an image, its height in pixels',
  `Width` int unsigned NOT NULL DEFAULT '0' COMMENT 'If upload is an image, its width in pixels',
  `ContentType` varchar(128) NOT NULL COMMENT 'The HTML content type for header (e.g. image/gif)',
  `Descriptor` varchar(50) DEFAULT NULL COMMENT 'Brief descriptor of file',
  `Description` varchar(512) DEFAULT NULL COMMENT 'A description of this file, provided by the original uploader.',
  `Locator` varchar(256) NOT NULL COMMENT 'The path to this file on the server',
  `AnyoneCanDownload` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `UploadTypeID` (`UploadTypeID`),
  CONSTRAINT `Uploads_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`),
  CONSTRAINT `Uploads_ibfk_3` FOREIGN KEY (`UploadTypeID`) REFERENCES `UploadTypes` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=451 DEFAULT CHARSET=utf8mb3 COMMENT='Data capture of uploaded files';

CREATE TABLE `UploadTypes` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  `Name` varchar(30) NOT NULL COMMENT 'Name of this upload type',
  `Description` varchar(256) NOT NULL COMMENT 'Description of this upload type',
  `AllowedTypes` varchar(256) NOT NULL DEFAULT 'Documents' COMMENT 'Comma separated list of types allowed (e.g. Documents, Images, Presentations, Spreadsheets)',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3 COMMENT='Types of file uploads - e.g. Case Studies';

CREATE TABLE `UserAccountEvents` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `EventID` int NOT NULL,
  `Notes` text,
  PRIMARY KEY (`ID`),
  KEY `UserAccountEvents_dbfk_1_idx` (`UserID`) /*!80000 INVISIBLE */,
  KEY `UsersAndEvents` (`EventID`,`UserID`)
) ENGINE=InnoDB AUTO_INCREMENT=220752 DEFAULT CHARSET=utf8mb3 COMMENT='If an admin event involves a user';

CREATE TABLE `UserConsents` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `LicenseID` int unsigned NOT NULL,
  `Consent` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_UNIQUE` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3133 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `UserCoursewarePreferences` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `autoCloseMenu` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Automatically close open folders when new folders are opened',
  `audioVolume` tinyint NOT NULL DEFAULT '50' COMMENT 'Default audio volume to start (0-100)',
  `animatePageTransitions` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Animate transitions from page to page with random effects',
  `closedCaptioning` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Show closed captioning on slides',
  `printSlideOnly` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Only print the slide portion of the screen',
  `videoSelection` int unsigned NOT NULL DEFAULT '0',
  `pageNumberInSearch` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UserID` (`UserID`),
  CONSTRAINT `UserCoursewarePreferences_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `Users` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=5891688 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `UserEvents` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Description` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `UserLogs` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `UserID` int NOT NULL,
  `EventID` int NOT NULL,
  `Info` varchar(9001) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=522021 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `UserNotes` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Note` varchar(255) DEFAULT NULL,
  `UserID` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=648 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Users` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Login` varchar(254) NOT NULL COMMENT 'Login ID for this User - for all new records, this should be the User''s e-mail address',
  `Password` varchar(60) NOT NULL DEFAULT '$2y$10$EfcI1qp9XMzyS4usKka7rOanBbyT0fy9ldKK5HqwWoJndPODPyx2W' COMMENT 'Hashed password for this User',
  `PasswordHistory` varchar(1024) DEFAULT NULL COMMENT 'Comma separated list of old hashed passwords',
  `PasswordLastChanged` datetime DEFAULT NULL COMMENT 'Last date/time the password was changed',
  `PasswordLockoutExpires` datetime DEFAULT NULL COMMENT 'Date/time the account lockout (from bad login attempts) ends',
  `FirstName` varchar(25) NOT NULL COMMENT 'First name of this User',
  `LastName` varchar(30) NOT NULL COMMENT 'Last name of this User',
  `Address` varchar(100) NOT NULL COMMENT 'The mailing address for this User',
  `Address2` varchar(100) DEFAULT NULL COMMENT 'Second line of address',
  `City` varchar(50) NOT NULL COMMENT 'The mailing city for this User',
  `StateID` int unsigned DEFAULT NULL COMMENT 'Foreign key into States table - the mailing state/province for this User',
  `PostalCode` varchar(10) NOT NULL COMMENT 'The postal code for this User',
  `CountryID` int unsigned NOT NULL DEFAULT '231' COMMENT 'Foreign key into Countries table - the mailing country for this User',
  `Phone` varchar(25) NOT NULL COMMENT 'The phone number for this User',
  `Title` varchar(30) DEFAULT NULL COMMENT 'The User''s job title',
  `EmployeeID` varchar(25) DEFAULT NULL COMMENT 'The User''s Employee ID as specified by the Organization',
  `CredentialID` int unsigned DEFAULT NULL COMMENT 'Foreign key into Credentials table. The credentials of this User (e.g. M.D., Ph.D., R.N.)',
  `StateOfLicensureID` int unsigned DEFAULT NULL COMMENT 'In which state is the user  licensed?',
  `StateLicenseNumber` varchar(20) DEFAULT NULL COMMENT 'What is the user''s state license ID?',
  `StateLicenseExpirationDate` datetime DEFAULT NULL COMMENT 'The date the user''s state license expires.',
  `NREMTCertificationNumber` varchar(20) DEFAULT NULL COMMENT 'The user''s NREMT certification number, if applicable.',
  `NREMTReregistrationDate` datetime DEFAULT NULL COMMENT 'The date when the user must reregister with the NREMT, if applicable.',
  `NEMSID` varchar(20) DEFAULT NULL,
  `CredentialLicenseTypeID` int unsigned DEFAULT NULL COMMENT 'Foreign key into CredentialLicenseTypes table - the type of certificate license',
  `DepartmentID` int unsigned NOT NULL COMMENT 'Foreign key into Departments table - the department this user belongs to',
  `CreationDate` datetime NOT NULL COMMENT 'The date this User record was created',
  `LastLoginDate` datetime DEFAULT NULL COMMENT 'The date this User last logged into the database',
  `PreviousLastLoginDate` datetime DEFAULT NULL,
  `SecurityQuestionID` int unsigned NOT NULL DEFAULT '1' COMMENT 'Foreign key into the SecurityQuestions table - the question to ask this user if he forgets his password',
  `SecurityAnswer` varchar(30) NOT NULL COMMENT 'The answer to the User''s security question',
  `LMS` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this an LMS User?',
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Is this an Active User (able to login)?',
  `Disabled` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this user disabled for breaking contract, malicious intent, or copyright infringment',
  `Beta` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Is this a beta user?',
  `ShowDemoReporting` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Show this User the Demo Reporting menu option',
  `PasswordChangedByAdmin` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'If true, password must be reset by user on next login',
  `Locale` varchar(5) NOT NULL DEFAULT 'en-us' COMMENT 'The language/region of the user',
  `oldUser` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Was this User ported from the old (pre-Hemispheres) database?',
  `oldPassword` varchar(33) DEFAULT NULL COMMENT 'The user''s old (MD5 hashed) password - set NULL if we''ve moved it to the new Password field',
  `oldEMail` varchar(254) DEFAULT NULL COMMENT 'The User''s e-mail address from the old (pre-Hemispheres) database - Depricated. Use the Login field for all new User''s e-mail addresses',
  `LMSEmail` varchar(254) DEFAULT NULL COMMENT 'Stores the e-mail address for LMS users, since Login can''t be e-mail address',
  `LastSessionID` varchar(40) DEFAULT NULL,
  `CommunityID` varchar(11) DEFAULT NULL,
  `provider_id` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Login_UNIQUE` (`Login`),
  KEY `Active` (`Active`),
  KEY `StateID` (`StateID`),
  KEY `CountryID` (`CountryID`),
  KEY `SecurityQuestionID` (`SecurityQuestionID`),
  KEY `DepartmentID` (`DepartmentID`),
  KEY `StateOfLicensureID` (`StateOfLicensureID`),
  KEY `CredentialLicenseTypeID` (`CredentialLicenseTypeID`),
  KEY `Locale` (`Locale`),
  KEY `CredentialID` (`CredentialID`),
  KEY `name` (`FirstName`,`LastName`,`Login`,`ID`),
  KEY `LastLoginDate` (`LastLoginDate` DESC),
  FULLTEXT KEY `searchText` (`Login`,`FirstName`,`LastName`),
  FULLTEXT KEY `Login_2` (`Login`)
) ENGINE=InnoDB AUTO_INCREMENT=3977284 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `UserTimeBypass` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `Bypass` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_UNIQUE` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=539 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `UserWebsitePreferences` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `UserID` int unsigned NOT NULL COMMENT 'Foreign key into Users table',
  `SendNewsletter` enum('Y','N') NOT NULL DEFAULT 'Y',
  `LastSelectedProductID` int DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UserID` (`UserID`)
) ENGINE=InnoDB AUTO_INCREMENT=153278 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `VerificationLogs` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `VerificationUserID` int unsigned NOT NULL,
  `Event` text NOT NULL,
  `EventDate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3 COMMENT='Log of verifications api';

CREATE TABLE `VerificationUsers` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(60) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COMMENT='Accounts used for Verification API';

CREATE TABLE `WebinarAdmins` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `RestrictedToOrg` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `WebinarEvaluationQuestions` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `WebinarID` int unsigned DEFAULT NULL,
  `ListOrder` int unsigned DEFAULT NULL,
  `Question` varchar(256) NOT NULL,
  `Type` tinyint unsigned NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `Section` int unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `WebinarEvaluationResponses` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `WebinarResultID` int unsigned NOT NULL,
  `WebinarEvaluationQuestionID` int unsigned NOT NULL,
  `Score` tinyint unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=262 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `WebinarLogs` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Data` text,
  `Source` varchar(45) DEFAULT NULL,
  `Date` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=989 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `WebinarResults` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  `SecondsIn` int unsigned NOT NULL DEFAULT '0',
  `WebinarID` int unsigned NOT NULL,
  `CEAwarded` enum('Y','N') NOT NULL DEFAULT 'N',
  `EvaluationCompleted` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ID`),
  KEY `Webinar_idx` (`WebinarID`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `Webinars` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Topic` varchar(255) NOT NULL,
  `ZoomWebinarID` varchar(10) DEFAULT NULL,
  `Duration` varchar(45) DEFAULT NULL,
  `CEHours` varchar(45) DEFAULT NULL,
  `WebinarStarted` datetime DEFAULT NULL,
  `WebinarCompleted` datetime DEFAULT NULL,
  `Public` enum('Y','N') NOT NULL DEFAULT 'N',
  `StartTime` datetime DEFAULT NULL,
  `Timezone` varchar(45) DEFAULT NULL,
  `AutoRecord` enum('Y','N') DEFAULT 'N',
  `Agenda` varchar(2000) DEFAULT NULL,
  `YoutubeLink` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=632 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `WebLogs` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `Site` varchar(256) DEFAULT NULL COMMENT 'The website logging the issue',
  `File` varchar(256) DEFAULT NULL COMMENT 'The filename logging the issue',
  `Line` int unsigned DEFAULT NULL COMMENT 'The line of the file logging the issue',
  `Message` varchar(1024) NOT NULL COMMENT 'Text of log event',
  `TimeLogged` datetime NOT NULL COMMENT 'Date/time this event was created',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1871487 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `WebsiteNews` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) DEFAULT NULL,
  `Content` text,
  `ReleaseDate` datetime NOT NULL,
  `Active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `WebsiteNIHSSUsers` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int unsigned NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=127493 DEFAULT CHARSET=utf8mb3 COMMENT='Track any users made through the NIHSS webpage.';

CREATE TABLE `WebsiteTracking` (
  `ID` int unsigned NOT NULL AUTO_INCREMENT,
  `Date` datetime NOT NULL,
  `Ip_address` text NOT NULL,
  `Event_type` text NOT NULL,
  `UserID` int DEFAULT NULL,
  `ButtonID` int NOT NULL,
  `Newsletter` text NOT NULL,
  `Event` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1711 DEFAULT CHARSET=utf8mb3;
