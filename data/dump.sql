SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `schedule_emails`;
CREATE TABLE `schedule_emails`
(
    `id`           int(11)      NOT NULL AUTO_INCREMENT,
    `email_from`   text         NOT NULL,
    `addresses`    text         NOT NULL,
    `charset`      varchar(255) NOT NULL,
    `subject`      varchar(255) NOT NULL,
    `altText`      varchar(255) DEFAULT NULL,
    `content`      longtext     NOT NULL,
    `send_at`      datetime     DEFAULT NULL,
    `retried`      int(11)      DEFAULT NULL,
    `created_at`   datetime     DEFAULT CURRENT_TIMESTAMP,
    `sending_date` datetime     DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `sending_date_send_at_retried` (`sending_date`, `send_at`, `retried`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

# SQL SERVER
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `schedule_emails`;
CREATE TABLE `schedule_emails`
(
    `id`         int(11)      NOT NULL AUTO_INCREMENT,
    `email_from` text         NOT NULL,
    `addresses`  text         NOT NULL,
    `charset`    varchar(255) NOT NULL,
    `subject`    varchar(255) NOT NULL,
    `altText`    varchar(255) DEFAULT NULL,
    `content`    longtext     NOT NULL,
    `send_at` datetime2 DEFAULT NULL,
    `retried`    int(11)      DEFAULT NULL,
    `created_at` datetime2 DEFAULT CURRENT_TIMESTAMP,
    `sending_date` datetime2 DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `sending_date_send_at_retried` (`sending_date`, `send_at`, `retried`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;
