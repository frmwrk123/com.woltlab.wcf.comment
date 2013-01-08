-- comments
DROP TABLE IF EXISTS wcf1_comment;
CREATE TABLE wcf1_comment (
	commentID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectTypeID INT(10) NOT NULL,
	objectID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT '0',
	userID INT(10) NOT NULL,
	username VARCHAR(255) NOT NULL,
	message TEXT NOT NULL,
	responses MEDIUMINT(7) NOT NULL DEFAULT '0',
	lastResponseIDs VARCHAR(255) NOT NULL DEFAULT '',
	
	KEY (objectTypeID, objectID, time)
);

-- comment responses
DROP TABLE IF EXISTS wcf1_comment_response;
CREATE TABLE wcf1_comment_response (
	responseID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	commentID INT(10) NOT NULL,
	time INT(10) NOT NULL DEFAULT '0',
	userID INT(10) NOT NULL,
	username VARCHAR(255) NOT NULL,
	message TEXT NOT NULL,
	
	KEY (commentID, time)
);

ALTER TABLE wcf1_comment ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
ALTER TABLE wcf1_comment ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;

ALTER TABLE wcf1_comment_response ADD FOREIGN KEY (commentID) REFERENCES wcf1_comment (commentID) ON DELETE CASCADE;
ALTER TABLE wcf1_comment_response ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
