#!/bin/bash

set -e
echo "Drop database, create again and initialize it."
mysql << EOF
DROP DATABASE IF EXISTS DEMO_DB;
CREATE DATABASE DEMO_DB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- CREATE USER 'demo'@'localhost' IDENTIFIED BY 'demopassword';
GRANT ALL PRIVILEGES ON DEMO_DB.* TO 'demo'@'localhost';

USE DEMO_DB;
CREATE TABLE User (
  id char(36),
  createUserId char(36),
  createTime datetime,
  updateUserId char(36),
  updateTime datetime,
  name varchar(50) NOT NULL,
  password varchar(100),
  remark varchar(100),
  UNIQUE KEY (name),
  PRIMARY KEY(id)
);
INSERT INTO User 
  (id, createUserId, createTime, updateUserId, updateTime, name, password, remark)
VALUES
  ("f3dc0a90-3466-4ff4-856f-117b753b85bf", "system", NOW(), "system", NOW(), "admin", 
   "\$2y\$10\$G6zcWPiHuwaayPu8Eb.1suhQXgLPGW.Ilx7xtHYFs4ZVGvDEmrDSa", "Default administrator");


CREATE TABLE Master (
  id char(36),
  createUserId char(36),
  createTime datetime,
  updateUserId char(36),
  updateTime datetime,
  masterNo varchar(20),
  name varchar(20),
  description varchar(255),
  UNIQUE KEY (masterNo),
  PRIMARY KEY(id)
);

CREATE TABLE Detail (
  id char(36),
  createUserId char(36),
  createTime datetime,
  updateUserId char(36),
  updateTime datetime,
  masterId char(36),
  item varchar(20),
  PRIMARY KEY(id)
);
EOF
