-- Rabtora Madhyam Management — lead/enquiry capture
-- Run once to create the database table.
--
--   mysql -u <user> -p < schema.sql
--
-- (Or paste into phpMyAdmin → SQL tab.)

CREATE DATABASE IF NOT EXISTS rabtora
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE rabtora;

CREATE TABLE IF NOT EXISTS enquiries (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name         VARCHAR(120)  NOT NULL,
  email        VARCHAR(190)  NOT NULL,
  mobile       VARCHAR(40)   NOT NULL,
  service      VARCHAR(60)   NOT NULL,
  message      TEXT          NULL,
  ip_address   VARCHAR(45)   NULL,          -- supports IPv4 and IPv6
  user_agent   VARCHAR(255)  NULL,
  status       ENUM('new','contacted','closed') NOT NULL DEFAULT 'new',
  created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_created_at (created_at),
  KEY idx_email (email),
  KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
