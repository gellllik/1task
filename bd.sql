CREATE DATABASE IF NOT EXISTS `bd-task`
  CHARACTER SET utf8 COLLATE utf8_general_ci;

USE `bd-task`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(30) NOT NULL DEFAULT '',
  `position` ENUM('programmer','lawyer','economist') NOT NULL DEFAULT 'programmer',
  `login` VARCHAR(60) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(120) NOT NULL DEFAULT '',
  `birthdate` DATE NULL,
  `telegram` VARCHAR(60) NOT NULL DEFAULT '',
  `city` VARCHAR(60) NOT NULL DEFAULT '',
  `bio` TEXT,
  `photo` LONGTEXT,
  `registered_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `date` DATE NOT NULL,
  `status` ENUM('actual','done') NOT NULL DEFAULT 'actual',
  `priority` ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  INDEX (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `archive` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `date` DATE NOT NULL,
  `status` ENUM('actual','done') NOT NULL DEFAULT 'actual',
  `priority` ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  `archived_at` DATETIME NOT NULL,
  INDEX (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;