/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
DROP TABLE IF EXISTS `account_receivable_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_receivable_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `code` longtext DEFAULT NULL,
  `transaction_header_id` bigint(20) DEFAULT NULL,
  `transaction_code` longtext DEFAULT NULL,
  `currency` varchar(255) DEFAULT NULL,
  `exchange_rate` decimal(18,4) DEFAULT NULL,
  `debit` decimal(18,4) DEFAULT NULL,
  `credit` decimal(18,4) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `account_receivables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_receivables` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `is_approve` int(11) DEFAULT NULL,
  `code` longtext DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `locker_id` bigint(20) DEFAULT NULL,
  `acc_code` varchar(255) DEFAULT NULL,
  `ref_no` longtext DEFAULT NULL,
  `currency` varchar(255) DEFAULT NULL,
  `exchange_rate` decimal(18,4) DEFAULT NULL,
  `deposit_status` int(11) DEFAULT NULL,
  `total` decimal(18,4) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `is_delete` int(11) DEFAULT NULL,
  `deleted_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_account_receivables_branch_id_idx` (`branch_id`),
  KEY `FK_account_receivables_created_by_idx` (`created_by`),
  KEY `FK_account_receivables_updated_by_idx` (`updated_by`),
  KEY `FK_account_receivables_deleted_by_idx` (`deleted_by`),
  CONSTRAINT `FK_account_receivables_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `FK_account_receivables_created_by` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`),
  CONSTRAINT `FK_account_receivables_deleted_by` FOREIGN KEY (`deleted_by`) REFERENCES `admin_users` (`id`),
  CONSTRAINT `FK_account_receivables_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `admin_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `is_super` tinyint(1) DEFAULT 0,
  `is_developer` tinyint(1) DEFAULT 0,
  `is_bod` tinyint(1) DEFAULT 0,
  `is_finance` tinyint(1) DEFAULT 0,
  `status_id` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_admin_users_status_id_idx` (`status_id`),
  KEY `FK_admin_users_branch_id_idx` (`branch_id`),
  CONSTRAINT `FK_admin_users_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `FK_admin_users_status_id` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ap_detail1s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ap_detail1s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `supplier_invoice_id` bigint(20) DEFAULT NULL,
  `supplier_invoice_code` varchar(255) DEFAULT NULL,
  `debit` double DEFAULT NULL,
  `kredit` double DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_ap_detail1s_header_id_idx` (`header_id`),
  KEY `FK_ap_detail1s_supplier_invoice_id_idx` (`supplier_invoice_id`),
  CONSTRAINT `FK_ap_detail1s_header_id` FOREIGN KEY (`header_id`) REFERENCES `ap_headers` (`id`),
  CONSTRAINT `FK_ap_detail1s_supplier_invoice_id` FOREIGN KEY (`supplier_invoice_id`) REFERENCES `supplier_invoice_headers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ap_detail2s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ap_detail2s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(255) DEFAULT NULL,
  `debit` double DEFAULT NULL,
  `kredit` double DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_ap_detail2s_header_id_idx` (`header_id`),
  KEY `FK_ap_detail2s_coa_id_idx` (`coa_id`),
  CONSTRAINT `FK_ap_detail2s_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `coas` (`id`),
  CONSTRAINT `FK_ap_detail2s_header_id` FOREIGN KEY (`header_id`) REFERENCES `ap_headers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ap_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ap_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `is_approve` smallint(6) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `reference_code` varchar(255) DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(100) DEFAULT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `grandtotal` double DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app_birthday_coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_birthday_coupons` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `user_phone` varchar(30) DEFAULT NULL,
  `user_name` varchar(200) DEFAULT NULL,
  `coupon_list_id` bigint(20) DEFAULT NULL,
  `coupon_code` varchar(100) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `membership` varchar(50) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_user_id_idx` (`user_id`),
  KEY `FK_app_birthday_coupons_coupon_list_id_idx` (`coupon_list_id`),
  CONSTRAINT `FK_app_birthday_coupons_coupon_list_id` FOREIGN KEY (`coupon_list_id`) REFERENCES `coupon_lists` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app_birthday_promo_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_birthday_promo_usages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `user_phone` varchar(30) DEFAULT NULL,
  `user_name` varchar(200) DEFAULT NULL,
  `booking_id` bigint(20) DEFAULT NULL,
  `transaction_id` bigint(20) DEFAULT NULL,
  `membership` varchar(50) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `birthday_discount_percentage` float DEFAULT 0,
  `status_id` int(11) DEFAULT 2,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_abpu_booking_id_idx` (`booking_id`),
  KEY `FK_abpu_transaction_id_idx` (`transaction_id`),
  CONSTRAINT `FK_abpu_booking_id` FOREIGN KEY (`booking_id`) REFERENCES `booking_headers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_abpu_transaction_id` FOREIGN KEY (`transaction_id`) REFERENCES `transaction_headers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app_membership_advanceds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_membership_advanceds` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `membership_id` bigint(20) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'PACKAGE, ADDON, ITEM',
  `reference_id` bigint(20) DEFAULT NULL,
  `discount_percentage` float DEFAULT 0,
  `max_discount_amount` float DEFAULT 0,
  `max_discount_usage` int(11) DEFAULT 0,
  `bonus_point_percentage` float DEFAULT 0,
  `max_bonus_point` float DEFAULT 0,
  `status_id` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_app_membership_discounts_membership_id_idx` (`membership_id`),
  CONSTRAINT `FK_app_membership_discounts_membership_id` FOREIGN KEY (`membership_id`) REFERENCES `app_memberships` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app_membership_benefits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_membership_benefits` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `membership_id` bigint(20) DEFAULT NULL,
  `index` int(11) DEFAULT NULL,
  `text` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_membership_id_idx` (`membership_id`),
  CONSTRAINT `FK_membership_id` FOREIGN KEY (`membership_id`) REFERENCES `app_memberships` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app_membership_bonus_coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_membership_bonus_coupons` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `user_phone` varchar(30) DEFAULT NULL,
  `user_name` varchar(200) DEFAULT NULL,
  `coupon_list_id` bigint(20) DEFAULT NULL,
  `coupon_code` varchar(100) DEFAULT NULL,
  `membership_before` varchar(50) DEFAULT NULL,
  `membership_after` varchar(50) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_ambc_coupon_list_id_idx` (`coupon_list_id`),
  CONSTRAINT `FK_ambc_coupon_list_id` FOREIGN KEY (`coupon_list_id`) REFERENCES `coupon_lists` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app_membership_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_membership_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `membership_id` bigint(20) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `discount_percentage` float DEFAULT 0,
  `max_discount_amount` float DEFAULT 0,
  `max_discount_usage` int(11) DEFAULT 0,
  `bonus_point_percentage` float DEFAULT 0,
  `max_bonus_point` float DEFAULT 0,
  `bonus_point_value` float DEFAULT 0,
  `bonus_point_order_amount` float DEFAULT 0,
  `birthday_discount_percentage` float DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_app_membership_details_membership_id_idx` (`membership_id`),
  CONSTRAINT `FK_app_membership_details_membership_id` FOREIGN KEY (`membership_id`) REFERENCES `app_memberships` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app_membership_directs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_membership_directs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `membership_id` bigint(20) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_amd_user_id_idx` (`user_id`),
  KEY `FK_amd_membership_id_idx` (`membership_id`),
  CONSTRAINT `FK_amd_membership_id` FOREIGN KEY (`membership_id`) REFERENCES `app_memberships` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_amd_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app_memberships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_memberships` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(100) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `min_spending` float DEFAULT 0,
  `discount_percentage` float DEFAULT 0,
  `max_discount_amount` float DEFAULT 0,
  `max_discount_usage` int(11) DEFAULT 0,
  `bonus_point_percentage` float DEFAULT 0,
  `max_bonus_point` float DEFAULT 0,
  `description` longtext DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `point_conversion` decimal(20,4) DEFAULT NULL,
  `img_path` longtext DEFAULT NULL,
  `package_durations` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app_payment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_payment_types` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `img_url` longtext DEFAULT NULL,
  `doku_code` varchar(255) DEFAULT NULL,
  `extra_fee_percentage` decimal(20,6) DEFAULT NULL,
  `extra_fee_value` decimal(20,6) DEFAULT NULL,
  `status_id` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ar_detail1s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ar_detail1s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `invoice_id` bigint(20) DEFAULT NULL,
  `invoice_code` varchar(255) DEFAULT NULL,
  `nominal_invoice` double DEFAULT NULL,
  `debit` double DEFAULT NULL,
  `kredit` double DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_ar_detail1s_header_id_idx` (`header_id`),
  KEY `FK_ar_detail1s_invoice_id_idx` (`invoice_id`),
  CONSTRAINT `FK_ar_detail1s_header_id` FOREIGN KEY (`header_id`) REFERENCES `ar_headers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_ar_detail1s_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `invoice_manual_custom_headers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ar_detail2s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ar_detail2s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(255) DEFAULT NULL,
  `debit` double DEFAULT NULL,
  `kredit` double DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_ar_detail2s_header_id_idx` (`header_id`),
  KEY `FK_ar_detail2s_coa_id_idx` (`coa_id`),
  CONSTRAINT `FK_ar_detail2s_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `coas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_ar_detail2s_header_id` FOREIGN KEY (`header_id`) REFERENCES `ar_headers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ar_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ar_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `is_approve` smallint(6) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `reference_code` varchar(255) DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(100) DEFAULT NULL,
  `partner_id` bigint(20) DEFAULT NULL,
  `partner_number` varchar(200) DEFAULT NULL,
  `partner_alias` varchar(200) DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `employee_name` varchar(200) DEFAULT NULL,
  `subtotal` double DEFAULT NULL,
  `grandtotal` double DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_ar_headers_status_id_idx` (`status_id`),
  KEY `FK_ar_headers_branch_id_idx` (`branch_id`),
  KEY `FK_ar_headers_coa_id_idx` (`coa_id`),
  CONSTRAINT `FK_ar_headers_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_ar_headers_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `coas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_ar_headers_status_id` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendance_configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_configurations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `late_penalty_amount` decimal(18,4) DEFAULT 0.0000,
  `alpha_penalty_amount` decimal(18,4) DEFAULT 0.0000,
  `late_chance` int(11) DEFAULT 0,
  `alpha_chance` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_attendance_configurations_branch_id_idx` (`branch_id`),
  KEY `FK_attendance_configurations_department_id_idx` (`department_id`),
  CONSTRAINT `FK_attendance_configurations_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_attendance_configurations_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendance_datas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_datas` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `machine_id` int(11) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `employee_id` varchar(45) DEFAULT NULL,
  `employee_name` varchar(45) DEFAULT NULL,
  `partner_id` varchar(45) DEFAULT NULL,
  `partner_name` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendance_shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_shifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL,
  `shift_index` int(11) DEFAULT 1,
  `shift_name` varchar(100) DEFAULT NULL,
  `time_start` varchar(10) DEFAULT NULL,
  `time_end` varchar(10) DEFAULT NULL,
  `is_end_at_closing` tinyint(1) DEFAULT 0,
  `is_next_day` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_attendance_shifts_department_id_idx` (`department_id`),
  CONSTRAINT `FK_attendance_shifts_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `authorization_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `authorization_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `menu_level_1_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_level_2_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_level_3_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_authorization_modules_branch_id_idx` (`branch_id`),
  CONSTRAINT `FK_authorization_modules_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `authorization_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `authorization_positions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `auth_module_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_authorization_positions_position_id_idx` (`position_id`),
  KEY `FK_authorization_positions_auth_module_id_idx` (`auth_module_id`),
  KEY `FK_authorization_positions_branch_id_idx` (`branch_id`),
  CONSTRAINT `FK_authorization_positions_auth_module_id` FOREIGN KEY (`auth_module_id`) REFERENCES `authorization_modules` (`id`),
  CONSTRAINT `FK_authorization_positions_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `FK_authorization_positions_position_id` FOREIGN KEY (`position_id`) REFERENCES `employee_positions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `auto_email_receivers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `auto_email_receivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `document_type` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `autonumbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `autonumbers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `doc_code` varchar(50) DEFAULT NULL,
  `next_no` bigint(20) DEFAULT NULL,
  `current_month` int(11) DEFAULT NULL,
  `current_year` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bank_cashin_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_cashin_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) unsigned DEFAULT NULL,
  `header_code` varchar(100) DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(50) DEFAULT NULL,
  `coa_name` varchar(100) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `amount` decimal(20,0) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_bank_cashin_details_header_id_idx` (`header_id`),
  KEY `FK_bank_cashin_details_coa_id_idx` (`coa_id`),
  CONSTRAINT `FK_bank_cashin_details_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `coas` (`id`),
  CONSTRAINT `FK_bank_cashin_details_header_id` FOREIGN KEY (`header_id`) REFERENCES `bank_cashin_headers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bank_cashin_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_cashin_headers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `department_name` varchar(100) DEFAULT NULL,
  `receive_date` timestamp NULL DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `employee_name` varchar(100) DEFAULT NULL,
  `coa_bank_id` bigint(20) DEFAULT NULL,
  `coa_bank_code` varchar(50) DEFAULT NULL,
  `coa_bank_name` varchar(200) DEFAULT NULL,
  `currency_id` bigint(20) unsigned DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `total_price` decimal(20,0) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_bank_cashin_headers_employee_id_idx` (`employee_id`),
  KEY `FK_bank_cashin_headers_department_id_idx` (`department_id`),
  KEY `FK_bank_cashin_headers_coa_id_idx` (`coa_bank_id`),
  KEY `FK_bank_cashin_headers_currency_id_idx` (`currency_id`),
  KEY `FK_bank_cashin_headers_status_id_idx` (`status_id`),
  KEY `FK_bank_cashin_headers_branch_id_idx` (`branch_id`),
  CONSTRAINT `FK_bank_cashin_headers_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `FK_bank_cashin_headers_coa_id` FOREIGN KEY (`coa_bank_id`) REFERENCES `coas` (`id`),
  CONSTRAINT `FK_bank_cashin_headers_currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  CONSTRAINT `FK_bank_cashin_headers_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `FK_bank_cashin_headers_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `FK_bank_cashin_headers_status_id` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bank_cashout_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_cashout_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) unsigned DEFAULT NULL,
  `header_code` varchar(100) DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(50) DEFAULT NULL,
  `coa_name` varchar(100) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `amount` decimal(20,0) DEFAULT NULL,
  `is_tax` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_bank_cashout_details_header_id_idx` (`header_id`),
  KEY `FK_bank_cashout_details_coa_id_idx` (`coa_id`),
  CONSTRAINT `FK_bank_cashout_details_coa_id` FOREIGN KEY (`coa_id`) REFERENCES `coas` (`id`),
  CONSTRAINT `FK_bank_cashout_details_header_id` FOREIGN KEY (`header_id`) REFERENCES `bank_cashout_headers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bank_cashout_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_cashout_headers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `employee_name` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `department_name` varchar(100) DEFAULT NULL,
  `coa_bank_id` bigint(20) DEFAULT NULL,
  `coa_bank_code` varchar(50) DEFAULT NULL,
  `coa_bank_name` varchar(200) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `currency_id` bigint(20) unsigned DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `reference` longtext DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `total_price` decimal(20,0) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `delete_status` int(11) DEFAULT NULL,
  `delete_at` varchar(45) DEFAULT NULL,
  `delete_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_bank_cashout_headers_branch_id_idx` (`branch_id`),
  KEY `FK_bank_cashout_headers_coa_bank_id_idx` (`coa_bank_id`),
  KEY `FK_bank_cashout_headers_employee_id_idx` (`employee_id`),
  KEY `FK_bank_cashout_headers_status_id_idx` (`status_id`),
  KEY `FK_bank_cashout_headers_currency_id_idx` (`currency_id`),
  KEY `FK_bank_cashout_headers_department_id_idx` (`department_id`),
  CONSTRAINT `FK_bank_cashout_headers_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  CONSTRAINT `FK_bank_cashout_headers_coa_bank_id` FOREIGN KEY (`coa_bank_id`) REFERENCES `coas` (`id`),
  CONSTRAINT `FK_bank_cashout_headers_currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  CONSTRAINT `FK_bank_cashout_headers_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `FK_bank_cashout_headers_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `FK_bank_cashout_headers_status_id` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `banners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `index` int(11) DEFAULT 0,
  `type` varchar(255) DEFAULT NULL COMMENT '1 = BLOG,  2 = PRODUCT, 3 = SELLER',
  `url` varchar(200) DEFAULT NULL,
  `branch_id` int(10) unsigned DEFAULT NULL,
  `blog_id` int(10) unsigned DEFAULT NULL,
  `coupon_id` bigint(20) DEFAULT NULL,
  `image_path` longtext DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `booking_carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_carts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `booking_detail_partners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_detail_partners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `detail_id` bigint(20) DEFAULT NULL,
  `partner_id` bigint(20) DEFAULT NULL,
  `partner_name` varchar(100) DEFAULT NULL,
  `partner_code` varchar(100) DEFAULT NULL,
  `partner_number` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `partner_alias` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_booking_detail_partners_detail_id_idx` (`detail_id`),
  KEY `FK_booking_detail_partners_partner_id_idx` (`partner_id`),
  CONSTRAINT `FK_booking_detail_partners_detail_id` FOREIGN KEY (`detail_id`) REFERENCES `booking_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_booking_detail_partners_partner_id` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `booking_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `package_code` varchar(100) DEFAULT NULL,
  `package_name` varchar(200) DEFAULT NULL,
  `room_id` bigint(20) DEFAULT NULL,
  `room_name` varchar(200) DEFAULT NULL,
  `room_code` varchar(100) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `item_name` varchar(200) DEFAULT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `qty` int(11) DEFAULT 0,
  `price` decimal(18,4) DEFAULT 0.0000,
  `second_price` decimal(18,4) DEFAULT 0.0000,
  `discount_percentage` int(11) DEFAULT 0,
  `discount_amount` decimal(18,4) DEFAULT 0.0000,
  `subtotal` decimal(18,4) DEFAULT 0.0000,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `booking_time` varchar(40) DEFAULT NULL,
  `massaging_option` varchar(30) DEFAULT NULL,
  `therapist_gender` varchar(20) DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `ts_notes` varchar(200) DEFAULT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `udf_01` varchar(200) DEFAULT NULL,
  `udf_02` varchar(200) DEFAULT NULL,
  `udf_03` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_reviewed` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `FK_booking_details_header_id_idx` (`header_id`),
  KEY `FK_booking_details_package_id_idx` (`package_id`),
  KEY `FK_booking_details_item_id_idx` (`item_id`),
  KEY `FK_booking_details_room_id_idx` (`room_id`),
  CONSTRAINT `FK_booking_details_header_id` FOREIGN KEY (`header_id`) REFERENCES `booking_headers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_booking_details_item_id` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_booking_details_package_id` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_booking_details_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `booking_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `branch_code` varchar(30) DEFAULT NULL,
  `branch_name` varchar(200) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_phone` varchar(30) DEFAULT NULL,
  `user_name` varchar(200) DEFAULT NULL,
  `user_email` varchar(200) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `pph` decimal(18,4) DEFAULT NULL,
  `ppn` decimal(18,4) DEFAULT NULL,
  `total_discount` decimal(18,4) DEFAULT 0.0000,
  `grand_total` decimal(18,4) DEFAULT 0.0000,
  `voucher_code` varchar(255) DEFAULT NULL,
  `voucher_value` decimal(18,4) DEFAULT 0.0000,
  `payment_charge_ppn_percentage` decimal(8,4) DEFAULT 0.0000,
  `payment_charge_ppn_value` decimal(18,4) DEFAULT 0.0000,
  `payment_charge` decimal(18,4) DEFAULT 0.0000,
  `payment_extra_charge` decimal(18,4) DEFAULT 0.0000,
  `payment_extra_percentage` decimal(18,4) DEFAULT 0.0000,
  `total_payment_before` decimal(18,4) DEFAULT 0.0000,
  `total_payment` decimal(18,4) DEFAULT 0.0000,
  `payment_type` varchar(50) DEFAULT NULL,
  `pg_code` varchar(50) DEFAULT NULL,
  `card_owner` varchar(100) DEFAULT NULL,
  `card_number` varchar(30) DEFAULT NULL,
  `pg_url` longtext DEFAULT NULL,
  `pg_service` varchar(50) DEFAULT NULL,
  `pg_acquirer` varchar(50) DEFAULT NULL,
  `is_settled` tinyint(1) DEFAULT 1,
  `pg_request_id` varchar(255) DEFAULT NULL,
  `pg_settlement_date` datetime DEFAULT NULL,
  `pg_expired_date` datetime DEFAULT NULL,
  `pg_status` varchar(100) DEFAULT NULL,
  `qr_payment_data` longtext DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `payment_at` datetime DEFAULT NULL,
  `payment_due_at` datetime DEFAULT NULL,
  `payment_reminded_at` datetime DEFAULT NULL,
  `payment_proof` text DEFAULT NULL,
  `udf_01` varchar(200) DEFAULT NULL,
  `udf_02` varchar(200) DEFAULT NULL,
  `udf_03` varchar(200) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `transaction_id` bigint(20) DEFAULT NULL,
  `customer_country_id` int(11) DEFAULT 106,
  `customer_country_name` varchar(100) DEFAULT 'Indonesia',
  `customer_province_id` int(11) DEFAULT 1,
  `customer_province_name` varchar(45) DEFAULT 'Jawa Timur',
  `customer_city_id` int(10) unsigned DEFAULT NULL,
  `customer_city_name` varchar(100) DEFAULT NULL,
  `customer_district_id` int(11) DEFAULT NULL,
  `customer_district_name` varchar(100) DEFAULT NULL,
  `customer_mail` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `customer_tel_1` varchar(50) DEFAULT NULL,
  `customer_full_address` longtext DEFAULT NULL,
  `customer_postal_code` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `customer_latitude` decimal(8,6) DEFAULT NULL,
  `customer_longitude` decimal(9,6) DEFAULT NULL,
  `customer_distance` float DEFAULT NULL,
  `booked_gender` varchar(50) DEFAULT NULL,
  `booked_massage_style` varchar(50) DEFAULT NULL,
  `booking_time` varchar(40) DEFAULT NULL,
  `booking_date` datetime DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `booking_notes` text DEFAULT NULL,
  `point_used` int(11) DEFAULT 0,
  `point_bonus` int(11) DEFAULT 0,
  `is_from_application` int(11) DEFAULT NULL,
  `home_service_finished_at` datetime DEFAULT NULL,
  `app_coupon_code` varchar(50) DEFAULT NULL,
  `membership_id` bigint(20) DEFAULT NULL,
  `membership_name` varchar(100) DEFAULT NULL,
  `membership_discount_percentage` float DEFAULT 0,
  `is_membership_discount` tinyint(1) DEFAULT 0,
  `is_birthday_discount` tinyint(1) DEFAULT 0,
  `birthday_discount_percentage` float DEFAULT 0,
  `payment_approved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_booking_headers_user_id_idx` (`user_id`),
  KEY `FK_booking_headers_branch_id_idx` (`branch_id`),
  KEY `FK_booking_headers_status_id_idx` (`status_id`),
  KEY `FK_booking_headers_transaction_id_idx` (`transaction_id`),
  CONSTRAINT `FK_booking_headers_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_booking_headers_status_id` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_booking_headers_transaction_id` FOREIGN KEY (`transaction_id`) REFERENCES `transaction_headers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_booking_headers_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `booking_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `book_by` varchar(200) DEFAULT NULL,
  `partner_id` bigint(20) DEFAULT NULL,
  `book_date` datetime DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_booking_notes_branch_id_idx` (`branch_id`),
  KEY `FK_booking_notes_partner_id_idx` (`partner_id`),
  CONSTRAINT `FK_booking_notes_branch_id` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_booking_notes_partner_id` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `address` longtext DEFAULT NULL,
  `item_unit_mode` varchar(100) DEFAULT 'single',
  `image_filename` longtext DEFAULT NULL,
  `email_from` varchar(200) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `extra_text` longtext DEFAULT NULL,
  `voucher_code` varchar(200) DEFAULT NULL,
  `is_oura` int(11) DEFAULT 0,
  `latitude` decimal(8,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `home_service_fee` int(11) DEFAULT 0,
  `status_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `voucher_id` bigint(20) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `is_check` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_users_FK` (`user_id`),
  KEY `carts_coupons_FK` (`voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `category_coa_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `category_coa_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `coa_code` longtext DEFAULT NULL,
  `coa_name` longtext DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_coa_sales_category_id_idx` (`category_id`),
  KEY `FK_coa_sales_coa_id_idx` (`coa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coa_monthly_amounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `coa_monthly_amounts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(50) DEFAULT NULL,
  `coa_name` varchar(200) DEFAULT NULL,
  `debit_add` double DEFAULT 0,
  `credit_add` double DEFAULT 0,
  `debit_total` double DEFAULT 0,
  `credit_total` double DEFAULT 0,
  `balance_total` double DEFAULT 0,
  `aktiva` double DEFAULT 0,
  `pasiva` double DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coa_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `coa_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_coa_types_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `coas` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `type` varchar(200) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `level` varchar(45) DEFAULT NULL,
  `is_header` int(11) DEFAULT NULL,
  `parent_id` bigint(20) DEFAULT NULL,
  `parent_code` varchar(45) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `profit_loss` int(11) DEFAULT 0,
  `cashflow` int(11) DEFAULT 0,
  `balance_sheet` int(11) DEFAULT 0,
  `trial_balance` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_coas_branch_id_idx` (`branch_id`),
  KEY `FK_coas_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commisions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `percentage_amount` decimal(18,4) DEFAULT NULL,
  `amount` decimal(18,4) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_commisions_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_employee_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_employee_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `transaction_id` bigint(20) DEFAULT NULL,
  `transaction_code` varchar(100) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `employee_code` varchar(100) DEFAULT NULL,
  `employee_name` varchar(100) DEFAULT NULL,
  `employee_phone` varchar(30) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `item_qty` int(11) DEFAULT 0,
  `item_code` varchar(100) DEFAULT NULL,
  `item_name` varchar(200) DEFAULT NULL,
  `commission_percentage` float DEFAULT 0,
  `commission_add` decimal(18,4) DEFAULT 0.0000,
  `commission_sub` decimal(18,4) DEFAULT 0.0000,
  `commission_result` decimal(18,4) DEFAULT 0.0000,
  `udf_01` varchar(200) DEFAULT NULL,
  `udf_02` varchar(200) DEFAULT NULL,
  `udf_03` varchar(200) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_commission_employee_histories_branch_id_idx` (`branch_id`),
  KEY `IDX_commission_employee_histories_item_id_idx` (`item_id`),
  KEY `IDX_commission_employee_histories_item_code_idx` (`item_code`),
  KEY `IDX_commission_employee_histories_created_at_idx` (`created_at`),
  KEY `IDX_commission_employee_histories_employee_id_idx` (`employee_id`),
  KEY `IDX_commission_employee_histories_employee_code_idx` (`employee_code`),
  KEY `IDX_commission_employee_histories_transaction_code_idx` (`transaction_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_employee_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_employee_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `template_id` int(10) unsigned DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `item_unit_id` bigint(20) DEFAULT NULL,
  `commission_percentage` float DEFAULT 0,
  `commission_flat` decimal(18,6) DEFAULT 0.000000,
  `udf_01` varchar(255) DEFAULT NULL,
  `udf_02` varchar(255) DEFAULT NULL,
  `udf_03` varchar(255) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_commission_employee_items_branch_id_idx` (`branch_id`),
  KEY `FK_commission_employee_items_employee_id_idx` (`employee_id`),
  KEY `FK_commission_employee_items_template_id_idx` (`template_id`),
  KEY `FK_commission_employee_items_item_id_idx` (`item_id`),
  KEY `FK_commission_employee_items_item_units_id_idx` (`item_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_item_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_item_histories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `branch_code` varchar(100) DEFAULT NULL,
  `branch_name` varchar(200) DEFAULT NULL,
  `commission_type` varchar(100) DEFAULT NULL,
  `transaction_header_id` bigint(20) DEFAULT NULL,
  `transaction_code` varchar(100) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `item_name` varchar(200) DEFAULT NULL,
  `item_price` decimal(18,4) DEFAULT 0.0000,
  `item_qty` int(11) DEFAULT 0,
  `item_subtotal` decimal(18,4) DEFAULT 0.0000,
  `commission_amount` decimal(18,4) DEFAULT 0.0000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_item_monthly_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_item_monthly_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_id` int(11) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_comission_item_monthly_details_header_id_idx` (`header_id`),
  KEY `FK_comission_item_monthly_details_item_id_idx` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_item_monthly_employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_item_monthly_employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_id` int(11) DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_commission_item_monthly_employees_header_id_idx` (`header_id`),
  KEY `FK_commission_item_monthly_employees_employee_id_idx` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_item_monthly_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_item_monthly_headers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `type` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `display` varchar(200) DEFAULT NULL,
  `commission_percentage` float DEFAULT 0,
  `commission_flat` float DEFAULT 0,
  `status_id` int(11) DEFAULT 1,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_comission_item_monthly_headers_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_partner_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_partner_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `transaction_id` bigint(20) DEFAULT NULL,
  `transaction_code` varchar(100) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `partner_id` bigint(20) DEFAULT NULL,
  `partner_number` varchar(100) DEFAULT NULL,
  `partner_type` varchar(50) DEFAULT NULL,
  `partner_name` varchar(100) DEFAULT NULL,
  `partner_phone` varchar(30) DEFAULT NULL,
  `partner_alias` varchar(100) DEFAULT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `package_code` varchar(50) DEFAULT NULL,
  `package_name` varchar(200) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `item_name` varchar(200) DEFAULT NULL,
  `commission_percentage` float DEFAULT 0,
  `commission_flat` decimal(18,4) DEFAULT 0.0000,
  `commission_add` decimal(18,4) DEFAULT 0.0000,
  `commission_sub` decimal(18,4) DEFAULT 0.0000,
  `commission_result` decimal(18,4) DEFAULT 0.0000,
  `udf_01` varchar(200) DEFAULT NULL,
  `udf_02` varchar(200) DEFAULT NULL,
  `udf_03` varchar(200) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_commission_partner_histories_branch_id_idx` (`branch_id`),
  KEY `IDX_commission_partner_histories_partner_id_idx` (`partner_id`),
  KEY `IDX_commission_partner_histories_partner_number_idx` (`partner_number`),
  KEY `IDX_commission_partner_histories_created_at_idx` (`created_at`),
  KEY `IDX_commission_partner_histories_package_id_idx` (`package_id`),
  KEY `IDX_commission_partner_histories_package_code_idx` (`package_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_partner_level_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_partner_level_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `template_id` int(10) unsigned DEFAULT NULL,
  `partner_level` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `item_unit_id` bigint(20) DEFAULT NULL,
  `commission_percentage` float DEFAULT 0,
  `commission_flat` decimal(18,6) DEFAULT 0.000000,
  `udf_01` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `udf_02` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `udf_03` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_commission_partner_level_items_branch_id_idx` (`branch_id`),
  KEY `FK_commission_partner_level_items_item_id_idx` (`item_id`),
  KEY `FK_commission_partner_level_items_template_id_idx` (`template_id`),
  KEY `FK_commission_partner_level_items_item_unit_id_idx` (`item_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_partner_level_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_partner_level_packages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `template_id` int(10) unsigned DEFAULT NULL,
  `partner_level` varchar(100) DEFAULT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `commission_percentage` float DEFAULT 0,
  `commission_flat` decimal(18,6) DEFAULT 0.000000,
  `deduction_before` decimal(18,6) DEFAULT 0.000000,
  `divider_after` int(11) DEFAULT 0,
  `extend_percentage` float DEFAULT 0,
  `extend_flat` decimal(18,6) DEFAULT 0.000000,
  `udf_01` varchar(255) DEFAULT NULL,
  `udf_02` varchar(255) DEFAULT NULL,
  `udf_03` varchar(255) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commission_partner_level_packages_branch_id_idx` (`branch_id`),
  KEY `FK_commission_partner_level_packages_package_id_idx` (`package_id`),
  KEY `FK_commission_partner_level_packages_template_id_idx` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_partner_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_partner_packages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `template_id` int(10) unsigned DEFAULT NULL,
  `partner_id` bigint(20) DEFAULT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `commission_percentage` float DEFAULT 0,
  `commission_flat` decimal(18,6) DEFAULT 0.000000,
  `extend_percentage` float DEFAULT 0,
  `extend_float` decimal(18,6) DEFAULT 0.000000,
  `udf_01` varchar(255) DEFAULT NULL,
  `udf_02` varchar(255) DEFAULT NULL,
  `udf_03` varchar(255) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_commission_partner_packages_branch_id_idx` (`branch_id`),
  KEY `FK_commission_partner_packages_package_id_idx` (`package_id`),
  KEY `FK_commission_partner_packages_partner_id_idx` (`partner_id`),
  KEY `FK_commission_partner_packages_template_id_idx` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_position_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_position_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `template_id` int(10) unsigned DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `item_unit_id` bigint(20) DEFAULT NULL,
  `commission_percentage` float DEFAULT 0,
  `commission_flat` decimal(18,6) DEFAULT 0.000000,
  `udf_01` varchar(255) DEFAULT NULL,
  `udf_02` varchar(255) DEFAULT NULL,
  `udf_03` varchar(255) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_commission_position_items_branch_id_idx` (`branch_id`),
  KEY `FK_commission_position_items_position_id_idx` (`position_id`),
  KEY `FK_commission_position_items_template_id_idx` (`template_id`),
  KEY `FK_commission_position_items_item_id_idx` (`item_id`),
  KEY `FK_commission_position_items_item_unit_id_idx` (`item_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_template_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_template_headers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_commission_template_headers_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `commission_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `header_id` int(10) unsigned DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_commission_templates_branch_id_idx` (`branch_id`),
  KEY `FK_commission_templates_header_id_idx` (`header_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `configurations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `key` longtext DEFAULT NULL,
  `value` longtext DEFAULT NULL,
  `value_int` decimal(18,4) DEFAULT NULL,
  `branch_id` bigint(20) DEFAULT NULL,
  `is_editable` int(11) DEFAULT 0,
  `value_2` longtext DEFAULT NULL,
  `value_2_int` decimal(18,4) DEFAULT NULL,
  `sequence` int(11) DEFAULT 99999,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coupon_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupon_lists` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint(20) DEFAULT NULL,
  `member_id` bigint(20) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `is_used` int(11) DEFAULT 0,
  `exp_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_coupon_lists_coupon_id_idx` (`coupon_id`),
  KEY `FK_coupon_lists_created_by_idx` (`created_by`),
  KEY `FK_coupon_lists_updated_by_idx` (`updated_by`),
  KEY `FK_coupon_lists_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coupon_package_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupon_package_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint(20) DEFAULT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_coupon_package_links_coupon_id_idx` (`coupon_id`),
  KEY `FK_coupon_package_links_package_id_idx` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `discount_percentage` decimal(18,6) DEFAULT NULL,
  `discount_amount` decimal(18,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `list_code` varchar(200) DEFAULT NULL,
  `next_no` int(11) DEFAULT NULL,
  `is_foc` tinyint(1) DEFAULT 0,
  `is_unlimited` tinyint(1) DEFAULT 0,
  `is_free_treatment` tinyint(1) DEFAULT 0,
  `can_redeem` int(11) DEFAULT 0,
  `redeem_points` int(11) DEFAULT NULL,
  `is_for_sell` tinyint(4) DEFAULT 1,
  `duration` int(11) DEFAULT NULL,
  `img_path` longtext DEFAULT NULL,
  `price` decimal(20,4) DEFAULT NULL,
  `terms_condition` longtext DEFAULT NULL,
  `valid_date` datetime DEFAULT NULL,
  `expired_date` datetime DEFAULT NULL,
  `is_lifetime` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `FK_coupons_branch_id_idx` (`branch_id`),
  KEY `FK_coupons_status_id_idx` (`status_id`),
  KEY `FK_coupons_created_by_idx` (`created_by`),
  KEY `FK_coupons_updated_by_idx` (`updated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `currencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(100) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `rate` int(11) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` bigint(20) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `npwp` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `address1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `address2` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `province` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `district` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `zip_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `top` smallint(6) DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(50) DEFAULT NULL,
  `coa_name` varchar(200) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL,
  `delete_status` smallint(6) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_departments_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employee_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `marital_status` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gender` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `education` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone_number` varchar(45) DEFAULT NULL,
  `commission` double DEFAULT NULL,
  `salary` double DEFAULT NULL,
  `description` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `ktp_image_path` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pkwt_image_path` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_employeer_contracts_branch_id_idx` (`branch_id`),
  KEY `FK_employee_contracts_employee_id_idx` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employee_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_employee_positions_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `admin_user_id` bigint(20) unsigned DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `npwp` varchar(255) DEFAULT NULL,
  `id_card_no` varchar(255) DEFAULT NULL,
  `address` longtext DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `join_date` datetime DEFAULT NULL,
  `attend_machine_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `udf_01` varchar(200) DEFAULT NULL,
  `is_external` tinyint(1) DEFAULT 0,
  `basic_salary_amount` decimal(18,4) DEFAULT 0.0000,
  PRIMARY KEY (`id`),
  KEY `FK_employees_branch_id_idx` (`branch_id`),
  KEY `FK_employees_admin_user_id_idx` (`admin_user_id`),
  KEY `FK_employees_status_id_idx` (`status_id`),
  KEY `FK_employees_position_id_idx` (`position_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcm_token_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcm_token_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `token` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fcm_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fcm_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `token` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `food_booking_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `food_booking_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `locker_id` bigint(20) DEFAULT NULL,
  `transaction_details_id` bigint(20) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `good_receive_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `good_receive_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `header_id` bigint(20) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `unit_id` bigint(20) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `purchase_price` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `max_qty` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_good_receive_details_header_id_idx` (`header_id`),
  KEY `FK_good_receive_details_item_id_idx` (`item_id`),
  KEY `FK_good_receive_details_unit_id_idx` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `good_receive_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `good_receive_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `is_approve` smallint(6) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `po_id` bigint(20) DEFAULT NULL,
  `po_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `pr_id` bigint(20) DEFAULT NULL,
  `pr_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `warehouse_id` bigint(20) DEFAULT NULL,
  `receiver_id` bigint(20) DEFAULT NULL,
  `receiver_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL,
  `delete_status` smallint(6) DEFAULT NULL,
  `is_close` smallint(6) DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `beban_ongkir` double DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `FK_good_receive_headers_branch_id_idx` (`branch_id`),
  KEY `FK_good_receive_headers_po_id_idx` (`po_id`),
  KEY `FK_good_receive_headers_pr_id_idx` (`pr_id`),
  KEY `FK_good_receive_headers_warehouse_id_idx` (`warehouse_id`),
  KEY `FK_good_receive_headers_supplier_id_idx` (`supplier_id`),
  KEY `FK_good_receive_headers_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `google_cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `google_cities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `province_id` int(10) unsigned DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_google_cities_province_id_idx` (`province_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `google_districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `google_districts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `city_id` int(10) unsigned DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_google_districts_city_id_idx` (`city_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `google_provinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `google_provinces` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `internal_request_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `internal_request_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `header_id` bigint(20) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `unit_id` bigint(20) DEFAULT NULL,
  `qty_unit` double DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `qty_pr` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_internal_request_details_header_id_idx` (`header_id`),
  KEY `FK_internal_request_details_item_id_idx` (`item_id`),
  KEY `FK_internal_request_details_unit_id_idx` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `internal_request_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `internal_request_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `is_approve` smallint(6) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `warehouse_id` bigint(20) DEFAULT NULL,
  `required_date` timestamp NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `is_pr` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_internal_request_headers_branch_id_idx` (`branch_id`),
  KEY `FK_internal_request_headers_supplier_id_idx` (`supplier_id`),
  KEY `FK_internal_request_headers_warehouse_id_idx` (`warehouse_id`),
  KEY `FK_internal_request_headers_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `warehouse_id` bigint(20) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_inventories_item_id_idx` (`item_id`),
  KEY `FK_inventories_warehouse_id_idx` (`warehouse_id`),
  KEY `FK_inventories_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventory_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_histories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `warehouse_id` bigint(20) DEFAULT NULL,
  `warehouse_name` varchar(100) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `item_name` varchar(200) DEFAULT NULL,
  `qty_sub` double DEFAULT NULL,
  `qty_add` double DEFAULT NULL,
  `qty_result` double DEFAULT NULL,
  `qty_total` double DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `udf_1` text DEFAULT NULL,
  `udf_2` text DEFAULT NULL,
  `udf_3` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_inventory_histories_item_id_idx` (`item_id`),
  KEY `FK_inventory_histories_warehouse_id_idx` (`warehouse_id`),
  KEY `FK_inventory_histories_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventory_in_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_in_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `header_id` bigint(20) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `unit_id` bigint(20) DEFAULT NULL,
  `qty_unit` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_inventory_in_details_header_id_idx` (`header_id`),
  KEY `FK_inventory_in_details_item_id_idx` (`item_id`),
  KEY `FK_inventory_in_details_unit_id_idx` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventory_in_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_in_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `warehouse_id` bigint(20) DEFAULT NULL,
  `warehouse_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `total_qty` double DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_inventory_in_headers_warehouse_id_idx` (`warehouse_id`),
  KEY `FK_inventory_in_headers_branch_id_idx` (`branch_id`),
  KEY `FK_inventory_in_headers_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventory_out_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_out_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `header_id` bigint(20) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `unit_id` bigint(20) DEFAULT NULL,
  `qty_unit` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_inventory_out_details_header_id_idx` (`header_id`),
  KEY `FK_inventory_out_details_item_id_idx` (`item_id`),
  KEY `FK_inventory_out_details_unit_id_idx` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventory_out_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_out_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `warehouse_id` bigint(20) DEFAULT NULL,
  `warehouse_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `internal_request_id` bigint(20) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `total_qty` double DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_inventory_out_headers_warehouse_id_idx` (`warehouse_id`),
  KEY `FK_inventory_out_headers_branch_id_idx` (`branch_id`),
  KEY `FK_inventory_out_headers_status_id_idx` (`status_id`),
  KEY `FK_inventory_out_headers_internal_request_id_idx` (`internal_request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invoice_manual_custom_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_manual_custom_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `unit_id` bigint(20) DEFAULT NULL,
  `qty_unit` double DEFAULT NULL,
  `price` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `disc_percent` int(11) DEFAULT NULL,
  `disc_value` double DEFAULT NULL,
  `ppn_percent` int(11) DEFAULT NULL,
  `ppn_value` double DEFAULT NULL,
  `subtotal` double DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_invoice_manual_custom_details_header_id_idx` (`header_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invoice_manual_custom_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_manual_custom_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `is_approve` smallint(6) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT NULL,
  `warehouse_id` bigint(20) DEFAULT NULL,
  `warehouse_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `customer_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `customer_type` varchar(45) DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `employee_name` varchar(200) DEFAULT NULL,
  `partner_id` bigint(20) DEFAULT NULL,
  `partner_number` varchar(200) DEFAULT NULL,
  `partner_alias` varchar(200) DEFAULT NULL,
  `subtotal` double DEFAULT NULL,
  `disc_percent` int(11) DEFAULT NULL,
  `disc_value` double DEFAULT NULL,
  `ppn_percent` int(11) DEFAULT NULL,
  `ppn_value` double DEFAULT NULL,
  `grandtotal` double DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_invoice_manual_custom_headers_branch_id_idx` (`branch_id`),
  KEY `FK_invoice_manual_custom_headers_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invoice_manual_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_manual_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `unit_id` bigint(20) DEFAULT NULL,
  `qty_unit` double DEFAULT NULL,
  `price` double DEFAULT NULL,
  `disc_percent` int(11) DEFAULT NULL,
  `disc_value` double DEFAULT NULL,
  `ppn_percent` int(11) DEFAULT NULL,
  `ppn_value` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_invoice_manual_details_header_id_idx` (`header_id`),
  KEY `FK_invoice_manual_details_item_id_idx` (`item_id`),
  KEY `FK_invoice_manual_details_unit_id_idx` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invoice_manual_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_manual_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `is_approve` smallint(6) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT NULL,
  `warehouse_id` bigint(20) DEFAULT NULL,
  `warehouse_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `customer_id` bigint(20) DEFAULT NULL,
  `customer_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(50) DEFAULT NULL,
  `purchase_type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `subtotal` double DEFAULT NULL,
  `disc_percent` int(11) DEFAULT NULL,
  `disc_value` double DEFAULT NULL,
  `ppn_percent` int(11) DEFAULT NULL,
  `ppn_value` double DEFAULT NULL,
  `grandtotal` double DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_invoice_manual_headers_branch_id_idx` (`branch_id`),
  KEY `FK_invoice_manual_headers_warehouse_id_idx` (`warehouse_id`),
  KEY `FK_invoice_manual_headers_customer_id_idx` (`customer_id`),
  KEY `FK_invoice_manual_headers_coa_id_idx` (`coa_id`),
  KEY `FK_invoice_manual_headers_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `item_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_addons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `item_boms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_boms` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `material_id` bigint(20) DEFAULT NULL,
  `material_name` varchar(255) DEFAULT NULL,
  `uom_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_item_boms_branch_id_idx` (`branch_id`),
  KEY `FK_item_boms_item_id_idx` (`item_id`),
  KEY `FK_item_boms_material_id_idx` (`material_id`),
  KEY `FK_item_boms_uom_id_idx` (`uom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `item_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `sequence` int(11) DEFAULT 1,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `desc` varchar(150) DEFAULT NULL,
  `coa_stock` bigint(20) DEFAULT NULL,
  `coa_cost` bigint(20) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_item_categories_coa_stock_idx` (`coa_stock`),
  KEY `FK_item_categories_coa_cost_idx` (`coa_cost`),
  KEY `FK_item_categories_status_id_idx` (`status_id`),
  KEY `FK_item_categories_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `item_transfer_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_transfer_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `header_id` bigint(20) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `unit_id` bigint(20) DEFAULT NULL,
  `qty_transfer` double DEFAULT NULL,
  `cost_price` double DEFAULT NULL,
  `sell_price` double DEFAULT NULL,
  `total_cost` double DEFAULT NULL,
  `total_sell` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_item_transfer_details_item_id_idx` (`item_id`),
  KEY `FK_item_transfer_details_header_id_idx` (`header_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `item_transfer_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_transfer_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `transfer_date` timestamp NULL DEFAULT NULL,
  `warehouse_origin_id` bigint(20) DEFAULT NULL,
  `warehouse_origin_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `warehouse_destination_id` bigint(20) DEFAULT NULL,
  `warehouse_destination_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `period_time` timestamp NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `total_qty` double DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `reject_reason` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_item_transfer_headers_branch_id_idx` (`branch_id`),
  KEY `FK_item_transfer_headers_warehouse_origin_id_idx` (`warehouse_origin_id`),
  KEY `FK_item_transfer_headers_warehouse_destination_id_idx` (`warehouse_destination_id`),
  KEY `FK_item_transfer_headers_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `item_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_units` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `item_id` bigint(20) DEFAULT NULL,
  `unit_id` bigint(20) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `purchase_price` double DEFAULT NULL,
  `selling_price` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_item_units_item_id_idx` (`item_id`),
  KEY `FK_item_units_unit_id_idx` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `image_path` longtext DEFAULT NULL,
  `price` decimal(20,4) DEFAULT NULL,
  `second_price` decimal(20,4) DEFAULT NULL,
  `commission_percent` decimal(18,2) DEFAULT 0.00,
  `have_bom` int(11) DEFAULT 0,
  `duration` int(11) DEFAULT 0,
  `category_id` int(11) DEFAULT 1,
  `category_2_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `img_path` longtext DEFAULT NULL,
  `is_unlimited_stock` int(11) DEFAULT 0,
  `cost_price` decimal(20,4) DEFAULT 0.0000,
  `manual_commission_amount` decimal(20,4) DEFAULT 0.0000,
  `default_uom_id` bigint(20) DEFAULT NULL,
  `manual_partner_commission_amount` float DEFAULT 0,
  `is_addon` tinyint(1) DEFAULT 0,
  `is_addon_partner_commission` tinyint(1) DEFAULT 0,
  `is_buy_with_point` tinyint(1) DEFAULT 0,
  `badge_status` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_items_branch_id_idx` (`branch_id`),
  KEY `FK_items_statuses_id_idx` (`status_id`),
  KEY `FK_items_category_id_idx` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` text NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `journal_coa_monthly_amounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_coa_monthly_amounts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(50) DEFAULT NULL,
  `coa_name` varchar(200) DEFAULT NULL,
  `debit_total_amount` double DEFAULT 0,
  `credit_total_amount` double DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `journal_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `header_id` bigint(20) DEFAULT NULL,
  `transaction_code` varchar(200) NOT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `reference_code` varchar(200) DEFAULT NULL,
  `account_id` bigint(20) DEFAULT NULL,
  `account_code` varchar(200) DEFAULT NULL,
  `account_name` varchar(200) DEFAULT NULL,
  `account_type_id` int(11) DEFAULT NULL,
  `account_type_name` varchar(200) DEFAULT NULL,
  `debit` decimal(20,5) DEFAULT NULL,
  `credit` decimal(20,5) DEFAULT NULL,
  `rate` decimal(20,5) DEFAULT NULL,
  `transaction_type` varchar(50) DEFAULT NULL,
  `original_amount` decimal(20,5) DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_journal_details_header_id_idx` (`header_id`),
  KEY `FK_journal_details_ref_code_idx` (`reference_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `journal_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `transaction_code` varchar(50) NOT NULL DEFAULT '-',
  `transaction_date` datetime DEFAULT NULL,
  `transaction_type` varchar(50) DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `is_approve` int(11) DEFAULT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `reference_code` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `journal_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_maps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_name` varchar(255) DEFAULT NULL,
  `coa_code` varchar(255) DEFAULT NULL,
  `journal_type` varchar(255) DEFAULT NULL,
  `transaction_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `room_type_id` varchar(255) DEFAULT NULL,
  `category_2_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `journal_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lockers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lockers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_lockers_status_id_idx` (`status_id`),
  KEY `FK_lockers_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `master_booking_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `master_booking_slots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `time_start` varchar(10) DEFAULT NULL,
  `hour_start` int(11) DEFAULT NULL,
  `minutes_start` int(11) DEFAULT NULL,
  `time_end` varchar(10) DEFAULT NULL,
  `hour_end` int(11) DEFAULT NULL,
  `minutes_end` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `FK_master_booking_slots_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `member_benefits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `member_benefits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` bigint(20) DEFAULT NULL,
  `membership_id` int(11) DEFAULT NULL,
  `membership_name` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL COMMENT 'treatment, items, facility',
  `discount_percentage` float DEFAULT 0,
  `discount_flat` decimal(18,4) DEFAULT 0.0000,
  `free_amount` int(11) DEFAULT 0,
  `usage_count` int(11) DEFAULT 0,
  `item_category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_member_benefits_member_id_idx` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `members` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` longtext DEFAULT NULL,
  `name` longtext DEFAULT NULL,
  `phone` longtext DEFAULT NULL,
  `address` longtext DEFAULT NULL,
  `email` longtext DEFAULT NULL,
  `join_date` timestamp NULL DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `is_infinite` int(11) DEFAULT NULL,
  `membership_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `is_active` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_members_created_by_idx` (`created_by`),
  KEY `FK_members_updated_by_idx` (`updated_by`),
  KEY `FK_members_membership_id_idx` (`membership_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `membership_benefits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `membership_benefits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `membership_id` bigint(20) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL COMMENT 'treatment, items, facility',
  `discount_percentage` float DEFAULT 0,
  `discount_flat` decimal(18,4) DEFAULT 0.0000,
  `free_amount` int(11) DEFAULT 0,
  `usage_count` int(11) DEFAULT 0,
  `item_category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_membership_benefits_membership_id_idx` (`membership_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `memberships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `memberships` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `price` decimal(18,4) DEFAULT NULL,
  `is_infinite` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `package_discount` decimal(18,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_memberships_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sequence` int(11) DEFAULT NULL,
  `category` varchar(45) DEFAULT NULL,
  `code` varchar(45) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `actions` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitoring_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitoring_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `admin_user_id` bigint(20) unsigned DEFAULT NULL,
  `partner_id` bigint(20) DEFAULT NULL,
  `room_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `scopes` text DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_auth_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `scopes` text DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_auth_codes_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `secret` varchar(100) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `redirect` text NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_personal_access_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `oauth_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) NOT NULL,
  `access_token_id` varchar(100) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_voucher_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_voucher_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `voucher_id` bigint(20) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `price` decimal(20,4) DEFAULT NULL,
  `subtotal` decimal(20,4) DEFAULT NULL,
  `discount` decimal(20,4) DEFAULT NULL,
  `total` decimal(20,4) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_voucher_details_order_voucher_headers_FK` (`header_id`),
  KEY `order_voucher_details_coupons_FK` (`voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_voucher_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_voucher_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` longtext DEFAULT NULL,
  `grand_total` decimal(20,4) DEFAULT NULL,
  `payment_charge_ppn_percentage` decimal(8,2) NOT NULL DEFAULT 0.00,
  `payment_charge_ppn_value` decimal(18,2) NOT NULL DEFAULT 0.00,
  `total_payment` decimal(18,2) NOT NULL DEFAULT 0.00,
  `payment_proof` varchar(255) DEFAULT NULL,
  `payment_due_at` datetime DEFAULT NULL,
  `qr_payment_data` varchar(255) DEFAULT NULL,
  `discount` decimal(20,4) DEFAULT NULL,
  `promo_code` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `pg_cost` decimal(20,4) DEFAULT NULL,
  `total_qty_voucher` int(11) DEFAULT NULL,
  `promo_id` bigint(20) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `subtotal` decimal(20,4) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `pg_status` varchar(255) DEFAULT NULL,
  `pg_service` varchar(255) DEFAULT NULL,
  `pg_acquirer` varchar(255) DEFAULT NULL,
  `pg_settlement_date` datetime DEFAULT NULL,
  `point_bonus` int(11) DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `type` varchar(255) DEFAULT 'normal',
  `payment_at` datetime DEFAULT NULL,
  `payment_approved_at` varchar(100) DEFAULT NULL,
  `payment_reminded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_voucher_headers_users_FK` (`created_by`),
  KEY `order_voucher_headers_users_FK_1` (`updated_by`),
  KEY `order_voucher_headers_branches_FK` (`branch_id`),
  KEY `order_voucher_headers_statuses_FK` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_voucher_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_voucher_payments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `payment_id` bigint(20) DEFAULT NULL,
  `payment_code` varchar(255) DEFAULT NULL,
  `amount` decimal(20,4) DEFAULT NULL,
  `va_number` varchar(255) DEFAULT NULL,
  `file` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `external_request_id` varchar(255) DEFAULT NULL,
  `external_expired_date` datetime DEFAULT NULL,
  `pg_url` longtext DEFAULT NULL,
  `pg_status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_voucher_payments_order_voucher_headers_FK` (`header_id`),
  KEY `order_voucher_payments_order_voucher_payment_FK_idx` (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `packages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `price` decimal(18,4) DEFAULT NULL,
  `second_price` decimal(18,4) DEFAULT NULL,
  `image_path` longtext DEFAULT NULL,
  `partner_count` int(11) DEFAULT NULL,
  `room_type_id` int(11) DEFAULT NULL,
  `extend_duration` int(11) DEFAULT NULL,
  `extend_price` decimal(18,4) DEFAULT NULL,
  `additional_person_price` decimal(18,4) DEFAULT NULL,
  `additional_partner_price` decimal(18,4) DEFAULT NULL,
  `is_price_cut` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `oil_count_override` int(11) DEFAULT 0,
  `commission_price` decimal(18,4) DEFAULT 0.0000,
  `commission_percent` decimal(18,2) DEFAULT 0.00,
  `is_home_service` tinyint(1) DEFAULT 0,
  `is_happy_hour` tinyint(1) DEFAULT 0,
  `is_birthday_promo` tinyint(1) DEFAULT 0,
  `is_favorite` tinyint(1) DEFAULT 0,
  `is_buy_with_point` tinyint(1) DEFAULT 0,
  `badge_status` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_packages_admin_user_id_idx` (`created_by`),
  KEY `FK_packages_admin_user_id_updated_by_idx` (`updated_by`),
  KEY `FK_packages_room_branch_id_idx` (`branch_id`),
  KEY `FK_packages_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `partner_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `partner_contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `partner_id` bigint(20) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `marital_status` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gender` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `education` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone_number` varchar(45) DEFAULT NULL,
  `salary` double DEFAULT NULL,
  `commission` double DEFAULT NULL,
  `description` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `ktp_image_path` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pkwt_image_path` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_partner_contracts_branch_id_idx` (`branch_id`),
  KEY `FK_partner_contracts_partner_id_idx` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `partner_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `partner_levels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_partner_levels_branch_id_idx` (`branch_id`),
  KEY `FK_partner_levels_item_id_idx` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `partners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `partners` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `level_id` int(10) unsigned DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `gender` varchar(45) DEFAULT 'P',
  `attend_machine_id` int(11) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `ktp` varchar(255) DEFAULT NULL,
  `npwp` varchar(255) DEFAULT NULL,
  `id_card_no` varchar(255) DEFAULT NULL,
  `address` longtext DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `join_date` datetime DEFAULT NULL,
  `is_present` tinyint(4) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  `is_can_use_hotstone` tinyint(1) DEFAULT 0,
  `book_name` varchar(100) DEFAULT NULL,
  `basic_salary_amount` decimal(18,4) DEFAULT 0.0000,
  `commission_percentage` float DEFAULT 0,
  `photo_filename` text DEFAULT NULL,
  `specialty` text DEFAULT NULL,
  `emergency_name` varchar(255) DEFAULT NULL,
  `emergency_phone` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `account_bank_name` varchar(255) DEFAULT NULL,
  `insentif` double DEFAULT NULL,
  `employement_status` varchar(45) DEFAULT 'Aktif',
  PRIMARY KEY (`id`),
  KEY `FK_partners_branch_id_idx` (`branch_id`),
  KEY `FK_partners_level_id_idx` (`level_id`),
  KEY `FK_partners_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `img_url` longtext DEFAULT NULL,
  `doku_code` varchar(255) DEFAULT NULL,
  `extra_fee_percentage` decimal(20,6) DEFAULT NULL,
  `extra_fee_value` decimal(20,6) DEFAULT NULL,
  `status_id` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pkwt_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pkwt_histories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(45) DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `filename` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pos_payment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_payment_types` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `charge` double DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prewedding_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `prewedding_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `transaction_detail_id` bigint(20) DEFAULT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `partner_id` bigint(20) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `room_id` bigint(20) DEFAULT NULL,
  `receipt_id` bigint(20) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `price` decimal(18,4) DEFAULT NULL,
  `subtotal` decimal(18,4) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prewedding_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `prewedding_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(45) DEFAULT NULL,
  `transaction_id` bigint(20) DEFAULT NULL,
  `is_from_application` int(11) DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `member_id` bigint(20) DEFAULT NULL,
  `member_name` varchar(255) DEFAULT NULL,
  `member_phone` varchar(30) DEFAULT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `qr_payment_data` longtext DEFAULT NULL,
  `payment_at` datetime DEFAULT NULL,
  `payment_proof` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prewedding_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `prewedding_packages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(45) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `packages` text DEFAULT NULL,
  `package_prices` text DEFAULT NULL,
  `price` double DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prewedding_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `prewedding_receipts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_order_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `header_id` bigint(20) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `unit_id` bigint(20) DEFAULT NULL,
  `qty_pr` double DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `price` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `disc1_percent` int(11) DEFAULT NULL,
  `disc1_value` double DEFAULT NULL,
  `disc2_percent` int(11) DEFAULT NULL,
  `disc2_value` double DEFAULT NULL,
  `ppn_percent` int(11) DEFAULT NULL,
  `ppn_value` double DEFAULT NULL,
  `subtotal` double DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `qty_gr` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_purchase_order_details_header_id_idx` (`header_id`),
  KEY `FK_purchase_order_details_item_id_idx` (`item_id`),
  KEY `FK_purchase_order_details_unit_id_idx` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_order_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `is_approve` smallint(6) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `reference_code` varchar(255) DEFAULT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `warehouse_id` bigint(20) DEFAULT NULL,
  `transaction_date` timestamp NULL DEFAULT NULL,
  `top` bigint(20) DEFAULT NULL,
  `subtotal` double DEFAULT NULL,
  `disc1_percent` int(11) DEFAULT NULL,
  `disc1_value` double DEFAULT NULL,
  `disc2_percent` int(11) DEFAULT NULL,
  `disc2_value` double DEFAULT NULL,
  `ppn_type` varchar(45) DEFAULT NULL,
  `ppn_percent` int(11) DEFAULT NULL,
  `ppn_value` double DEFAULT NULL,
  `grandtotal` double DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `is_gr` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_purchase_order_headers_branch_id_idx` (`branch_id`),
  KEY `FK_purchase_order_headers_supplier_id_idx` (`supplier_id`),
  KEY `Fk_purchase_order_headers_warehouse_id_idx` (`warehouse_id`),
  KEY `FK_purchase_order_headers_status_id_idx` (`status_id`),
  KEY `Fk_purchase_order_headers_reference_id_idx` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_request_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_request_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `header_id` bigint(20) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `unit_id` bigint(20) DEFAULT NULL,
  `qty_unit` double DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `qty_po` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_purchase_request_details_header_id_idx` (`header_id`),
  KEY `FK_purchase_request_details_item_id_idx` (`item_id`),
  KEY `FK_purchase_request_details_unit_id_idx` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `purchase_request_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_request_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `is_approve` smallint(6) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `reference_code` varchar(255) DEFAULT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `warehouse_id` bigint(20) DEFAULT NULL,
  `required_date` timestamp NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `is_po` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_purchase_request_headers_supplier_id_idx` (`supplier_id`),
  KEY `FK_purchase_request_headers_branch_id_idx` (`branch_id`),
  KEY `FK_purchase_request_headers_warehouse_id_idx` (`warehouse_id`),
  KEY `FK_purchase_request_headers_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `report_generator_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_generator_attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `header_id` int(10) unsigned DEFAULT NULL,
  `file_name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_report_generator_attachments_header_id_idx` (`header_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `report_generators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_generators` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `type` varchar(200) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `file_name` varchar(200) DEFAULT NULL,
  `filesystem_name` varchar(100) DEFAULT NULL,
  `emailed_to` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_report_generators_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `revision_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `revision_logs` (
  `code` varchar(45) DEFAULT NULL,
  `caused_by` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `display` varchar(255) DEFAULT NULL,
  `sequence` int(11) DEFAULT 1,
  `branch_id` int(11) DEFAULT NULL,
  `type_name` varchar(255) DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(45) DEFAULT NULL,
  `coa_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` longtext DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_rooms_status_id_idx` (`status_id`),
  KEY `FK_rooms_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  `display` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_ins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_ins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `warehouse_id` bigint(20) DEFAULT NULL,
  `requested_by` varchar(100) DEFAULT NULL,
  `purpose` varchar(200) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `udf_01` varchar(255) DEFAULT NULL,
  `udf_02` varchar(255) DEFAULT NULL,
  `udf_03` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_stock_ins_branch_id_idx` (`branch_id`),
  KEY `FK_stock_ins_warehouse_id_idx` (`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supplier_invoice_detail1s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_invoice_detail1s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `header_code` varchar(45) DEFAULT NULL,
  `gr_id` bigint(20) DEFAULT NULL,
  `gr_code` varchar(45) DEFAULT NULL,
  `total` double DEFAULT NULL,
  `no_invoice` varchar(200) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_purchase_invoice_detail1s_gr_id_idx` (`gr_id`),
  KEY `FK_supplier_invoice_detail1s_header_id_idx` (`header_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supplier_invoice_detail2s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_invoice_detail2s` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `header_code` varchar(45) DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(45) DEFAULT NULL,
  `total` double DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_purchase_invoice_detail2s_coa_id_idx` (`coa_id`),
  KEY `FK_supplier_invoice_detail2s_header_id_idx` (`header_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supplier_invoice_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_invoice_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `is_approve` smallint(6) DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `reference_code` varchar(255) DEFAULT NULL,
  `coa_id` bigint(20) DEFAULT NULL,
  `coa_code` varchar(100) DEFAULT NULL,
  `image_path` varchar(200) DEFAULT NULL,
  `supplier_id` bigint(20) DEFAULT NULL,
  `supplier_name` varchar(200) DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `top` bigint(20) DEFAULT NULL,
  `grandtotal` double DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_purchase_invoice_headers_branch_id_idx` (`branch_id`),
  KEY `FK_purchase_invoice_headers_supplier_id_idx` (`supplier_id`),
  KEY `FK_purchase_invoice_headers_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `address1` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `address2` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `zip_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `npwp` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `pic_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `pic_phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `term` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_suppliers_branch_id_idx` (`branch_id`),
  KEY `FK_suppliers_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `temp_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `temp_bookings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `partner_id` bigint(20) DEFAULT NULL,
  `room_id` bigint(20) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transaction_booking_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_booking_slots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `booking_detail_id` bigint(20) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `time_start` varchar(10) DEFAULT NULL,
  `hour_start` int(11) DEFAULT NULL,
  `minute_start` int(11) DEFAULT NULL,
  `time_end` varchar(10) DEFAULT NULL,
  `hour_end` int(11) DEFAULT NULL,
  `minute_end` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `canceled_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_transaction_booking_slots_branch_id_idx` (`branch_id`),
  KEY `FK_transaction_booking_slots_user_id_idx` (`user_id`),
  KEY `FK_transaction_booking_slots_booking_detail_id_idx` (`booking_detail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transaction_detail_partners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_detail_partners` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `partner_id` bigint(20) DEFAULT NULL,
  `transaction_detail_id` bigint(20) DEFAULT NULL,
  `is_main` int(11) DEFAULT 1,
  `is_extend` int(11) DEFAULT 0,
  `is_checkout` int(11) DEFAULT 0,
  `book_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_detail_partners_detail_id_idx` (`transaction_detail_id`),
  KEY `transaction_detail_partners_partner_id_idx` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transaction_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `room_id` bigint(20) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `price` decimal(18,4) DEFAULT NULL,
  `subtotal` decimal(18,4) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `started_from_cron` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `coupon_id` bigint(20) DEFAULT NULL,
  `is_fnb` int(11) DEFAULT 0,
  `is_extended` int(11) DEFAULT 0,
  `is_called` int(11) DEFAULT 0,
  `voucher_id` bigint(20) DEFAULT NULL,
  `voucher_code` varchar(255) DEFAULT NULL,
  `voucher_name` varchar(255) DEFAULT NULL,
  `extension_amount` decimal(18,4) DEFAULT 0.0000,
  `extension_times` int(11) DEFAULT 0,
  `additional_person_amount` decimal(18,4) DEFAULT 0.0000,
  `additional_person_qty` int(11) DEFAULT 0,
  `additional_partner_amount` decimal(18,4) DEFAULT 0.0000,
  `additional_partner_qty` int(11) DEFAULT 0,
  `discount_percentage` int(11) DEFAULT NULL,
  `discount_amount` decimal(18,4) DEFAULT NULL,
  `membership_id` bigint(20) DEFAULT NULL,
  `is_facility_only` int(11) DEFAULT 0,
  `is_cut` int(11) DEFAULT 0,
  `subtotal_after_commission` decimal(18,4) DEFAULT 0.0000,
  `member_id` bigint(20) DEFAULT NULL,
  `new_order_qty` int(11) DEFAULT 1,
  `is_new_order` int(11) DEFAULT 0,
  `customer_name` varchar(200) DEFAULT NULL,
  `is_ignore_monitor` tinyint(1) DEFAULT 0,
  `partner_id` bigint(20) DEFAULT NULL,
  `partner_level` varchar(30) DEFAULT NULL,
  `free_partners` int(11) DEFAULT 0,
  `voided_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_transaction_details_header_id_idx` (`header_id`),
  KEY `FK_transaction_details_item_id_idx` (`item_id`),
  KEY `FK_transaction_details_package_id_idx` (`package_id`),
  KEY `FK_transaction_details_membership_id_idx` (`membership_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transaction_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_headers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `is_prewed` int(11) DEFAULT 0,
  `branch_id` int(11) DEFAULT NULL,
  `room_id` bigint(20) DEFAULT NULL,
  `member_id` bigint(20) DEFAULT NULL,
  `member_name` varchar(255) DEFAULT NULL,
  `member_phone` varchar(30) DEFAULT NULL,
  `member_membership_id` bigint(20) DEFAULT NULL,
  `member_membership_code` varchar(100) DEFAULT NULL,
  `grand_total` decimal(18,4) DEFAULT NULL,
  `total_qty` int(11) DEFAULT NULL,
  `pph` decimal(18,4) DEFAULT NULL,
  `ppn` decimal(18,4) DEFAULT NULL,
  `voucher_code` varchar(255) DEFAULT NULL,
  `voucher_value` decimal(18,4) DEFAULT NULL,
  `discount` decimal(18,4) DEFAULT NULL,
  `amount` decimal(18,4) DEFAULT NULL,
  `rounding_amount` decimal(18,4) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `return_value` decimal(18,4) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `discount_total` decimal(18,4) DEFAULT NULL,
  `foc_type` varchar(255) DEFAULT 'no',
  `foc_number` varchar(255) DEFAULT NULL,
  `is_happy_hour` int(11) DEFAULT 0,
  `total_after_commission` decimal(18,4) DEFAULT 0.0000,
  `is_showing` int(11) DEFAULT 0,
  `customer_name` varchar(255) DEFAULT NULL,
  `app_coupon_code` varchar(50) DEFAULT NULL,
  `extra_voucher_code` varchar(255) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `approved_at` datetime DEFAULT NULL,
  `approved_by` bigint(20) DEFAULT NULL,
  `voided_at` datetime DEFAULT NULL,
  `is_app_transaction` tinyint(1) DEFAULT 0,
  `is_app_user_phone_input` tinyint(1) DEFAULT 0,
  `app_user_id` bigint(20) unsigned DEFAULT NULL,
  `app_user_phone` varchar(30) DEFAULT NULL,
  `app_user_name` varchar(100) DEFAULT NULL,
  `app_bonus_point` int(11) DEFAULT 0,
  `is_paid_with_points` tinyint(1) DEFAULT 0,
  `app_point_used` int(11) DEFAULT 0,
  `google_review_amount` decimal(18,4) DEFAULT 0.0000,
  `total_cust_male` int(11) DEFAULT NULL,
  `total_cust_female` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_transaction_headers_created_by_idx` (`created_by`),
  KEY `FK_transaction_headers_updated_by_idx` (`updated_by`),
  KEY `FK_transaction_headers_status_id_idx` (`status_id`),
  KEY `FK_transaction_headers_branch_id_idx` (`branch_id`),
  KEY `FK_transaction_headers_employee_id_idx` (`employee_id`),
  KEY `IDX_transaction_headers_created_at_idx` (`created_at`),
  KEY `FK_transaction_headers_locker_id_idx` (`member_id`),
  KEY `FK_transaction_headers_room_id_idx` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transaction_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_invoices` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `header_ids` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `subtotal` decimal(18,4) DEFAULT NULL,
  `rounding_amount` decimal(18,4) DEFAULT 0.0000,
  `pph` decimal(18,4) DEFAULT NULL,
  `ppn` decimal(18,4) DEFAULT NULL,
  `pb2` decimal(18,4) DEFAULT 0.0000,
  `is_use_voucher_code` tinyint(4) DEFAULT NULL,
  `coupon_id` varchar(255) DEFAULT NULL,
  `voucher_code` varchar(255) DEFAULT NULL,
  `voucher_discount_value` decimal(18,4) DEFAULT NULL,
  `grandtotal` decimal(18,4) DEFAULT NULL,
  `return_value` decimal(18,4) DEFAULT NULL,
  `amount` decimal(18,4) DEFAULT NULL,
  `member_id` bigint(20) DEFAULT NULL,
  `member_name` varchar(255) DEFAULT NULL,
  `member_phone` varchar(30) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `voided_at` datetime DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `reprint` bigint(20) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `FK_transaction_invoices_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transaction_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_payments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `invoice_id` bigint(20) DEFAULT NULL,
  `payment_type_id` bigint(20) DEFAULT NULL,
  `payment_type_code` varchar(255) DEFAULT NULL,
  `amount` decimal(18,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_payments_payment_type_id_idx` (`payment_type_id`),
  KEY `transaction_payments_created_by_idx` (`created_by`),
  KEY `transaction_payments_header_id_idx` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unit_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `unit_materials` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_unit_materials_status_id_idx` (`status_id`),
  KEY `FK_unit_materials_branch_id_idx` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `receiver_name` varchar(200) DEFAULT NULL,
  `receiver_phone` longtext DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `country_id` int(10) unsigned DEFAULT NULL,
  `country_name` varchar(200) DEFAULT NULL,
  `province_id` int(10) unsigned DEFAULT NULL,
  `province_name` varchar(200) DEFAULT NULL,
  `city_id` int(10) unsigned DEFAULT NULL,
  `city_name` varchar(200) DEFAULT NULL,
  `district_id` int(10) unsigned DEFAULT NULL,
  `district_name` varchar(200) DEFAULT NULL,
  `full_address` longtext DEFAULT NULL,
  `google_street_address` longtext DEFAULT NULL,
  `latitude` decimal(8,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `udf_01` varchar(200) DEFAULT NULL,
  `udf_02` varchar(200) DEFAULT NULL,
  `udf_03` varchar(200) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_addresses_user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_app_spending_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_app_spending_messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `percentage_start` int(11) DEFAULT NULL,
  `percentage_end` int(11) DEFAULT NULL,
  `text` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_membership_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_membership_histories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_name` varchar(200) DEFAULT NULL,
  `user_phone` varchar(30) DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `membership_id` bigint(20) DEFAULT NULL,
  `membership_name` varchar(100) DEFAULT NULL,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `message_id` varchar(150) DEFAULT NULL,
  `notif_type` varchar(145) DEFAULT NULL,
  `model` longtext DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_notifications_user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_point_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_point_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `user_name` varchar(200) DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `user_phone` varchar(30) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `amount_add` int(11) DEFAULT 0,
  `amount_sub` int(11) DEFAULT 0,
  `amount_result` int(11) DEFAULT 0,
  `reference_1` varchar(200) DEFAULT NULL,
  `reference_2` varchar(200) DEFAULT NULL,
  `reference_3` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_point_histories_user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_redeems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_redeems` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `voucher_id` bigint(20) DEFAULT NULL,
  `voucher_code` varchar(255) DEFAULT NULL,
  `point_used` int(11) DEFAULT NULL,
  `cretated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_redeems_users_FK` (`user_id`),
  KEY `user_redeems_coupons_FK` (`voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_review_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_review_images` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `review_id` bigint(20) DEFAULT NULL,
  `image_filename` varchar(100) DEFAULT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_review_images_review_id_idx` (`review_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_reviews` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `user_phone` varchar(30) DEFAULT NULL,
  `user_name` varchar(200) DEFAULT NULL,
  `booking_id` bigint(20) DEFAULT NULL,
  `booking_detail_id` bigint(20) DEFAULT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `package_name` varchar(100) DEFAULT NULL,
  `item_id` bigint(20) DEFAULT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `pranikah_id` bigint(20) DEFAULT NULL,
  `pranikah_header_id` bigint(20) DEFAULT NULL,
  `pranikah_name` varchar(100) DEFAULT NULL,
  `partner_id` bigint(20) DEFAULT NULL,
  `partner_number` varchar(10) DEFAULT NULL,
  `partner_alias` varchar(100) DEFAULT NULL,
  `partner_name` varchar(200) DEFAULT NULL,
  `star` float DEFAULT NULL,
  `text` longtext DEFAULT NULL,
  `is_image_uploaded` tinyint(1) DEFAULT 0,
  `reviewed_at` datetime DEFAULT NULL,
  `status_id` int(11) DEFAULT 3,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_reviews_branch_id_idx` (`branch_id`),
  KEY `FK_user_reviews_booking_id_idx` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_voucher_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_voucher_codes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `expired_date` datetime DEFAULT NULL,
  `is_used` int(11) DEFAULT 0,
  `void` int(11) DEFAULT 0,
  `is_lifetime` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_voucher_codes_user_vouchers_FK` (`header_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_vouchers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_voucher_header_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `voucher_id` bigint(20) DEFAULT NULL,
  `voucher_code` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status_id` bigint(20) DEFAULT 1,
  `redeemed_at` datetime DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `terms_condition` longtext DEFAULT NULL,
  `notes` longtext DEFAULT NULL,
  `is_lifetime` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_vouchers_users_FK` (`user_id`),
  KEY `user_vouchers_coupons_FK` (`voucher_id`),
  KEY `user_vouchers_order_voucher_header_id_index` (`order_voucher_header_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `country` varchar(45) DEFAULT 'ID',
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `is_password_set` int(11) DEFAULT 0,
  `status_id` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `otp_code` varchar(20) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `program_name` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `frequency` int(11) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `city` longtext DEFAULT NULL,
  `address` longtext DEFAULT NULL,
  `wallet_name` varchar(255) DEFAULT NULL,
  `wallet_type` varchar(255) DEFAULT NULL,
  `point_expired_date` datetime DEFAULT NULL,
  `converted_amount` int(11) DEFAULT NULL,
  `point` int(11) DEFAULT 0,
  `balance_amount` bigint(20) DEFAULT NULL,
  `average_spending` bigint(20) DEFAULT NULL,
  `store_activation` varchar(255) DEFAULT NULL,
  `activation_date` datetime DEFAULT NULL,
  `last_transaction` datetime DEFAULT NULL,
  `total_transaction_amount` bigint(20) DEFAULT NULL,
  `total_spending_amount` bigint(20) DEFAULT NULL,
  `total_reward_earning_balance` bigint(20) DEFAULT NULL,
  `total_reward_earning_wallet` bigint(20) DEFAULT NULL,
  `total_redeem_balance` bigint(20) DEFAULT NULL,
  `registration_date` datetime DEFAULT NULL,
  `customer_status` varchar(255) DEFAULT NULL,
  `card_status` varchar(255) DEFAULT NULL,
  `revenue_generated_from_referral` bigint(20) DEFAULT NULL,
  `referred_by` varchar(255) DEFAULT NULL,
  `total_referred` int(11) DEFAULT NULL,
  `rfm_segmentation` varchar(255) DEFAULT NULL,
  `active_at` datetime DEFAULT NULL,
  `forgot_password_otp_code` varchar(20) DEFAULT NULL,
  `forgot_password_otp_expired_at` datetime DEFAULT NULL,
  `forgot_password_unique_code` varchar(20) DEFAULT NULL,
  `birthday_date` datetime DEFAULT NULL,
  `membership_id` bigint(20) DEFAULT 1,
  `membership_status` varchar(50) DEFAULT NULL,
  `membership_start_at` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `FK_users_branch_id_idx` (`branch_id`),
  KEY `FK_users_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `voucher_package_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `voucher_package_details` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `header_id` bigint(20) DEFAULT NULL,
  `package_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `voucher_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `voucher_packages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(45) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` double DEFAULT NULL,
  `expired` int(11) DEFAULT NULL,
  `is_shown` int(11) DEFAULT 0,
  `img` text DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `voucher_promo_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `voucher_promo_codes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `discount_percentage` int(11) DEFAULT NULL,
  `discount_amount` decimal(20,5) DEFAULT NULL,
  `employee_id` bigint(20) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `voucher_promo_codes_admin_users_FK` (`created_by`),
  KEY `voucher_promo_codes_admin_users_FK_1` (`updated_by`),
  KEY `voucher_promo_codes_branches_FK` (`branch_id`),
  KEY `voucher_promo_codes_employees_FK` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `warehouses` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_warehouses_branch_id_idx` (`branch_id`),
  KEY `FK_statuses_status_id_idx` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

