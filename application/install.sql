CREATE TABLE `users` (
	`id` int NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(200) NOT NULL,
	`password` VARCHAR(200) NOT NULL,
	`email` VARCHAR(200) NOT NULL,
	`time` timestamp NOT NULL,
	`lastlogin` varchar(200) DEFAULT '',
	PRIMARY KEY(`id`)
);

CREATE TABLE `chats` (
	`id` int NOT NULL AUTO_INCREMENT,
	`text` VARCHAR(200) NOT NULL,
	`userID` int NOT NULL,
	`time` varchar(100) NOT NULL,
	PRIMARY KEY(`id`)	
);

