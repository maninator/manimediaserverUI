
CREATE USER IF NOT EXISTS 'mani'@'%' IDENTIFIED BY '{!PASSWORD}';
CREATE DATABASE IF NOT EXISTS `mani`;
GRANT ALL PRIVILEGES ON `mani`.* TO 'mani'@'%';
GRANT ALL PRIVILEGES ON `mani\_%`.* TO 'mani'@'%';