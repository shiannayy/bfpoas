-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 27, 2025 at 05:12 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u444252215_fsedoas`
--

-- --------------------------------------------------------

--
-- Table structure for table `bfp_standards`
--

CREATE TABLE `bfp_standards` (
  `id` int(11) NOT NULL,
  `standard_code` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `occupancy_type` varchar(100) DEFAULT NULL,
  `parameter` varchar(100) DEFAULT NULL,
  `min_value` decimal(10,2) DEFAULT NULL,
  `max_value` decimal(10,2) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `source_reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `bfp_standards`
--

INSERT INTO `bfp_standards` (`id`, `standard_code`, `description`, `occupancy_type`, `parameter`, `min_value`, `max_value`, `unit`, `source_reference`, `created_at`, `updated_at`) VALUES
(1, 'EXIT_DOOR_WIDTH_MIN', 'Minimum exit door width', 'All', 'Exit Door Width', 0.71, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(2, 'HEADROOM_MIN', 'Minimum floor-to-ceiling headroom in stairs', 'All', 'Headroom', 2.30, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(3, 'STAIR_RISE_MAX', 'Maximum stair rise height (Class A)', 'All', 'Stair Rise Height', 0.19, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(4, 'STAIR_LANDING_HEIGHT_MAX', 'Maximum height between landings (Class A)', 'All', 'Height Between Landings', 2.75, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(5, 'UNIT_AREA_PER_PERSON', 'Minimum area per person (waiting/standing)', 'All', 'Area per Person', 0.28, NULL, 'sqm/person', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(6, 'TRAVEL_DIST_NO_SPRINKLER', 'Travel distance to exit (no sprinkler)', 'All', 'Travel Distance', 46.00, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(7, 'TRAVEL_DIST_WITH_SPRINKLER', 'Travel distance to exit (with sprinkler)', 'All', 'Travel Distance', 61.00, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(8, 'AISLE_WIDTH_MIN', 'Minimum aisle width for occupancy of 60+', 'Public Assembly', 'Aisle Width', 0.91, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(9, 'SEAT_WIDTH_STANDARD', 'Standard seat width without dividing arms', 'Public Assembly', 'Seat Width', 0.60, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(10, 'FIRE_ESCAPE_TREAD_MIN', 'Minimum tread for fire escape stair', 'All', 'Stair Tread', 0.15, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(11, 'RAMP_WIDTH_CLASS_A_MIN', 'Minimum class A ramp width', 'All', 'Ramp Width', 1.12, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(12, 'RAMP_WIDTH_CLASS_B_MIN', 'Minimum class B ramp width', 'All', 'Ramp Width', 0.76, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(13, 'AISLE_WIDTH_HOSPITAL', 'Corridor/aisle width for hospitals', 'Hospital', 'Corridor Width', 2.44, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(14, 'AISLE_WIDTH_CUSTODIAL', 'Corridor/aisle width for residential-custodial care', 'Residential-Custodial', 'Corridor Width', 1.83, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(15, 'TRAVEL_DIST_MERCANTILE', 'Travel distance to exit for mercantile occupancy', 'Mercantile', 'Travel Distance', 30.50, NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38');

-- --------------------------------------------------------

--
-- Table structure for table `checklists`
--

CREATE TABLE `checklists` (
  `checklist_id` int(11) NOT NULL,
  `fsed_code` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `version` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `checklist_status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `checklists`
--

INSERT INTO `checklists` (`checklist_id`, `fsed_code`, `title`, `description`, `version`, `created_at`, `checklist_status`) VALUES
(1, 'FSED-6F', 'Fire Safety Checklist on Building Plans', 'Checklist for reviewing fire safety compliance of building plans', 'Rev02', '2025-08-30 21:45:01', 0),
(2, 'FSED-14F', 'Industrial Occupancy Checklist', 'Inspection checklist for industrial occupancy compliance', 'Rev01', '2025-08-30 21:45:01', 1),
(3, 'FSED-18F', 'Business Occupancy Checklist', 'Inspection checklist for business occupancy compliance', 'Rev01', '2025-08-30 21:45:01', 0),
(4, 'FSED-15F', 'Educational Occupancy Checklist', 'Inspection checklist for educational occupancy compliance', 'Rev01', '2025-08-30 21:45:50', 1),
(5, 'FSED-16F', 'Healthcare Occupancy Checklist', 'Inspection checklist for healthcare occupancy compliance', 'Rev01', '2025-08-30 22:13:49', 0),
(6, 'FSED-17F', 'Mercantile Occupancy Checklist', 'Inspection checklist for mercantile occupancy compliance', 'Rev01', '2025-08-30 23:06:03', 0),
(8, 'FSED-19F', 'Health Care Occupancy Checklist', 'Inspection checklist for healthcare occupancy compliance', 'Rev01', '2025-08-30 23:27:12', 0),
(9, 'FSED-20F', 'Storage Occupancy Checklist', 'Inspection checklist for storage occupancy compliance', 'Rev01', '2025-08-30 23:44:34', 0),
(10, 'FSED-21F', 'Single and Two-Family Dwellings Checklist', 'Checklist for fire safety inspection of single and two-family dwellings (BFP-QSF-FSED-021 Rev. 01)', 'Rev. 01', '2025-08-31 00:03:05', 0),
(11, 'FSED-6F', 'Fire Safety Checklist on Building Plans', 'Checklist for evaluating fire safety compliance on building plans as per RA 9514 and its RIRR 2019', 'Rev01', '2025-09-01 02:58:46', 0),
(12, 'FSED-24F', 'Small/General Business Establishment Checklist', 'Inspection checklist for compliance of small/general business establishments', 'Rev01', '2025-09-01 03:02:52', 1),
(13, 'FSED-26F', 'Gasoline Service Station Checklist', 'Inspection checklist for compliance of gasoline service stations', 'Rev01', '2025-09-01 03:08:20', 1),
(14, 'FSED-27F', 'Places of Assembly Occupancy Checklist', 'Checklist for inspection and fire safety evaluation of places of assembly occupancy, covering general information, construction, exits, fire protection features, hazardous materials, and operating features in compliance with RA 9514 and its IRR.', '1.0', '2025-09-01 03:17:07', 1);

-- --------------------------------------------------------

--
-- Table structure for table `checklist_items`
--

CREATE TABLE `checklist_items` (
  `item_id` int(11) NOT NULL,
  `checklist_id` int(11) NOT NULL,
  `section` int(6) DEFAULT NULL,
  `item_no` int(11) DEFAULT NULL,
  `item_text` text NOT NULL,
  `input_type` enum('checkbox','text','number','date','select','textarea') NOT NULL DEFAULT 'checkbox',
  `unit_label` varchar(40) DEFAULT NULL,
  `checklist_criteria` varchar(25) DEFAULT NULL COMMENT 'range, min_val, bool, min_elapse_days,\r\nmanual_pass',
  `threshold_range_min` varchar(10) DEFAULT NULL,
  `threshold_range_max` varchar(10) DEFAULT NULL,
  `threshold_min_val` varchar(10) DEFAULT NULL COMMENT 'minimum_value',
  `threshold_max_val` varchar(10) DEFAULT NULL,
  `threshold_yes_no` char(1) DEFAULT NULL COMMENT 'either 1 = yes , 0 = no',
  `threshold_elapse_day` varchar(10) DEFAULT NULL,
  `threshold_text_value` text DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `chk_item_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Active , 0 = Archived'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checklist_item_select_options`
--

CREATE TABLE `checklist_item_select_options` (
  `option_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `option_value` varchar(255) NOT NULL,
  `option_label` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checklist_sections`
--

CREATE TABLE `checklist_sections` (
  `checklist_section_id` int(11) NOT NULL,
  `section` varchar(100) NOT NULL,
  `checklist_id` int(11) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `general_info`
--

CREATE TABLE `general_info` (
  `gen_info_id` int(11) NOT NULL,
  `gen_info_control_no` varchar(55) DEFAULT NULL,
  `form_code` int(11) DEFAULT NULL COMMENT 'points to checklist_id',
  `building_name` varchar(255) DEFAULT NULL,
  `location_of_construction` text DEFAULT NULL,
  `postal_address` varchar(100) DEFAULT NULL,
  `loc_id` int(11) DEFAULT NULL,
  `project_title` varchar(255) DEFAULT NULL,
  `height_of_building` decimal(8,2) DEFAULT 0.00,
  `no_of_storeys` int(11) DEFAULT NULL,
  `area_per_floor` decimal(10,2) DEFAULT NULL,
  `total_floor_area` decimal(10,2) DEFAULT NULL,
  `portion_occupied` varchar(255) DEFAULT NULL,
  `bed_capacity` int(11) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL COMMENT 'comes from user_id',
  `owner_name` varchar(255) DEFAULT NULL COMMENT 'comes from users.full_name',
  `occupant_name` varchar(255) DEFAULT NULL,
  `representative_name` varchar(255) DEFAULT NULL,
  `administrator_name` varchar(255) DEFAULT NULL,
  `owner_contact_no` varchar(50) DEFAULT NULL,
  `representative_contact_no` varchar(50) DEFAULT NULL,
  `telephone_email` varchar(255) DEFAULT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `establishment_name` varchar(255) DEFAULT NULL,
  `nature_of_business` varchar(255) DEFAULT NULL,
  `classification_of_occupancy` varchar(255) DEFAULT NULL COMMENT 'classification: commercial or residential',
  `healthcare_facility_name` varchar(255) DEFAULT NULL,
  `healthcare_facility_type` varchar(100) DEFAULT NULL,
  `building_permit_no` varchar(100) DEFAULT NULL,
  `building_permit_date` date DEFAULT NULL,
  `occupancy_permit_no` varchar(100) DEFAULT NULL,
  `occupancy_permit_date` date DEFAULT NULL,
  `mayors_permit_no` varchar(100) DEFAULT NULL,
  `mayors_permit_date` date DEFAULT NULL,
  `municipal_license_no` varchar(100) DEFAULT NULL,
  `municipal_license_date` date DEFAULT NULL,
  `electrical_cert_no` varchar(100) DEFAULT NULL,
  `electrical_cert_date` date DEFAULT NULL,
  `fsic_control_no` varchar(100) DEFAULT NULL,
  `fsic_date` date DEFAULT NULL,
  `fsic_fire_code_fee` decimal(10,2) DEFAULT NULL,
  `fire_drill_cert_no` varchar(100) DEFAULT NULL,
  `fire_drill_cert_date` date DEFAULT NULL,
  `fire_drill_fee` decimal(10,2) DEFAULT NULL,
  `ntcv_control_no` varchar(100) DEFAULT NULL,
  `ntcv_date` date DEFAULT NULL,
  `insurance_company` varchar(255) DEFAULT NULL,
  `insurance_coinsurer` varchar(255) DEFAULT NULL,
  `insurance_policy_no` varchar(100) DEFAULT NULL,
  `insurance_date` date DEFAULT NULL,
  `policy_date` date DEFAULT NULL,
  `fire_code_fee` decimal(10,2) DEFAULT NULL,
  `building_plan_checklist_no` varchar(100) DEFAULT NULL,
  `other_info` text DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `district_office` varchar(255) DEFAULT NULL,
  `station` varchar(255) DEFAULT NULL,
  `station_address` varchar(255) DEFAULT NULL,
  `date_received` date DEFAULT NULL,
  `date_released` date DEFAULT NULL,
  `gen_info_status` varchar(32) DEFAULT 'New',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspections`
--

CREATE TABLE `inspections` (
  `inspection_id` int(11) NOT NULL,
  `fsic_no` varchar(20) DEFAULT NULL,
  `gen_info_id` int(11) DEFAULT NULL,
  `schedule_id` int(11) NOT NULL,
  `checklist_id` int(11) NOT NULL,
  `inspector_id` int(11) NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `status` enum('In Progress','Completed','Cancelled','Reschedule','Non Compliant','Has Issuance') NOT NULL DEFAULT 'In Progress',
  `hasRecoApproval` int(11) DEFAULT 0,
  `dateRecommended` timestamp NULL DEFAULT NULL,
  `recommended_by` int(11) DEFAULT NULL,
  `hasFinalApproval` int(11) DEFAULT 0,
  `dateApproved` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `hasBeenReceived` int(11) DEFAULT 0,
  `dateReceived` timestamp NULL DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `inspection_score` float DEFAULT NULL,
  `total_items` int(11) DEFAULT 0,
  `passed_items` int(11) DEFAULT 0,
  `failed_items` int(11) DEFAULT 0,
  `not_applicable_items` int(11) DEFAULT 0,
  `required_items` int(11) DEFAULT 0,
  `required_passed` int(11) DEFAULT 0,
  `required_failed` int(11) DEFAULT 0,
  `compliance_rate` float DEFAULT 0,
  `cancellation_reason` text DEFAULT NULL,
  `has_Defects` int(11) DEFAULT NULL,
  `defects_details` text DEFAULT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspection_responses`
--

CREATE TABLE `inspection_responses` (
  `response_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `response_value` text DEFAULT NULL,
  `remarks` text DEFAULT NULL COMMENT '1 = pass\r\n0 = failed\r\n9 = no criteria\r\n8 = not applicable',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `response_proof_img` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspection_schedule`
--

CREATE TABLE `inspection_schedule` (
  `schedule_id` int(11) NOT NULL,
  `checklist_id` int(11) NOT NULL,
  `HasClientAck` char(1) NOT NULL DEFAULT 'N',
  `DateAckbyClient` timestamp NULL DEFAULT NULL,
  `AckByClient_id` int(11) DEFAULT NULL,
  `hasRecommendingApproval` int(1) DEFAULT 0 COMMENT '1 or 0 = has recommending approval',
  `dateRecommendedForApproval` timestamp NULL DEFAULT NULL COMMENT 'date Recommended for Approval',
  `RecommendingApprover` int(11) DEFAULT NULL COMMENT 'user_id recommending approver',
  `hasFinalApproval` int(1) DEFAULT 0 COMMENT '1 or 0 = has approval',
  `dateFinalApproval` timestamp NULL DEFAULT NULL COMMENT 'date of Approval',
  `FinalApprover` int(11) DEFAULT NULL COMMENT 'user_id final approver',
  `hasInspectorAck` int(1) DEFAULT 0 COMMENT '1 or 0',
  `dateInspectorAck` timestamp NULL DEFAULT NULL COMMENT 'date inspector acknowledged',
  `inspector_id` int(11) DEFAULT NULL COMMENT 'user_id of the inspector',
  `order_number` varchar(50) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `scheduled_date` datetime NOT NULL,
  `schedule_time` time DEFAULT NULL,
  `preferredSchedule` varchar(25) DEFAULT NULL,
  `rescheduleCount` int(11) NOT NULL DEFAULT 0,
  `rescheduleReason` text DEFAULT NULL,
  `to_officer` varchar(150) NOT NULL,
  `assigned_to_officer_id` int(11) DEFAULT NULL,
  `proceed_instructions` text DEFAULT NULL,
  `noi_id` varchar(99) DEFAULT NULL COMMENT 'Nature_of_inspection',
  `noi_text` varchar(99) DEFAULT NULL,
  `fsic_purpose` varchar(99) DEFAULT NULL,
  `gen_info_id` int(11) DEFAULT NULL COMMENT 'establishment_id',
  `purpose` text DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `ins_sched_logs` text DEFAULT NULL,
  `inspection_sched_status` varchar(50) DEFAULT 'Scheduled' COMMENT 'scheduled\r\ncompleted\r\ncancelled\r\nrescheduled',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ClientHasSeen` int(1) DEFAULT 1,
  `InspectorHasSeen` int(1) DEFAULT 1,
  `RecoApproverHasSeen` int(1) DEFAULT 1,
  `ApproverHasSeen` int(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `log_id` int(11) NOT NULL,
  `log_action` varchar(55) DEFAULT NULL,
  `log_message` text DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL COMMENT 'if log is for schedules',
  `inspection_id` int(11) DEFAULT NULL COMMENT 'if log is for inspections',
  `user_id` int(11) DEFAULT NULL COMMENT 'if log is for user login',
  `checklist_id` int(11) DEFAULT NULL COMMENT 'if log is for checklists',
  `gen_info_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `map_saved_location`
--

CREATE TABLE `map_saved_location` (
  `loc_id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `lat` decimal(20,15) DEFAULT NULL,
  `lng` decimal(20,15) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nature_of_inspection`
--

CREATE TABLE `nature_of_inspection` (
  `noi_id` int(11) NOT NULL,
  `noi_text` varchar(100) DEFAULT NULL,
  `noi_status` varchar(1) DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `nature_of_inspection`
--

INSERT INTO `nature_of_inspection` (`noi_id`, `noi_text`, `noi_status`, `created_at`) VALUES
(1, 'Building Under Construction', 'A', '2025-10-29 17:00:22'),
(2, 'Periodic Inspection of Occupancy', 'A', '2025-10-29 17:00:22'),
(3, 'Application for Occupancy Permit', 'A', '2025-10-29 17:00:22'),
(4, 'Verification Inspection of Compliance to NTCV', 'A', '2025-10-29 17:00:22'),
(5, 'Application for Business Permit', 'A', '2025-10-29 17:00:22'),
(6, 'Verification Inspection of Complaint Received', 'A', '2025-10-29 17:00:22');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `OR_number` varchar(20) DEFAULT NULL COMMENT 'Auto generated',
  `schedule_id` int(11) DEFAULT NULL COMMENT 'linked to inspection_schedule.schedule_id',
  `amount_paid` float DEFAULT NULL,
  `date_paid` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_config`
--

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) DEFAULT NULL,
  `config_value` text NOT NULL,
  `iv_value` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `system_config`
--

INSERT INTO `system_config` (`id`, `config_key`, `config_value`, `iv_value`, `created_at`) VALUES
(1, 'API_KEY', 'KvD+F5BhhHPGhouhCeNgS7o6ZeYUIfVqt1eXT8XseJsJi3/xL1Da', 'D9iNv36ta+rqwI5HtWdQTw==', '2025-10-07 12:06:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contact_no` varchar(11) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Administrator','Inspector','Client') NOT NULL,
  `sub_role` varchar(55) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `signature` varchar(55) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `comments` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `contact_no`, `password_hash`, `role`, `sub_role`, `is_active`, `signature`, `created_at`, `comments`, `updated_at`) VALUES
(6, 'Admin', 'fsed@dmin.gov', NULL, '$2y$10$WqEMqOM50RJeSxq4vOSCLOaQnzwyoAFY5SqNWLudrnUL/pdNH3rpW', 'Administrator', 'Admin_Assistant', 1, 'SIGN-6-Administrator.png', '2025-09-01 07:25:26', NULL, '2025-11-23 01:35:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bfp_standards`
--
ALTER TABLE `bfp_standards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `checklists`
--
ALTER TABLE `checklists`
  ADD PRIMARY KEY (`checklist_id`),
  ADD UNIQUE KEY `uk_checklists_code_version` (`fsed_code`,`version`);

--
-- Indexes for table `checklist_items`
--
ALTER TABLE `checklist_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_items_checklist` (`checklist_id`),
  ADD KEY `idx_items_section` (`section`);

--
-- Indexes for table `checklist_item_select_options`
--
ALTER TABLE `checklist_item_select_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `checklist_sections`
--
ALTER TABLE `checklist_sections`
  ADD PRIMARY KEY (`checklist_section_id`);

--
-- Indexes for table `general_info`
--
ALTER TABLE `general_info`
  ADD PRIMARY KEY (`gen_info_id`),
  ADD UNIQUE KEY `gen_info_control_no` (`gen_info_control_no`);

--
-- Indexes for table `inspections`
--
ALTER TABLE `inspections`
  ADD PRIMARY KEY (`inspection_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `checklist_id` (`checklist_id`),
  ADD KEY `inspector_id` (`inspector_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_inspections_general_info` (`gen_info_id`);

--
-- Indexes for table `inspection_responses`
--
ALTER TABLE `inspection_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD UNIQUE KEY `uk_insp_item` (`schedule_id`,`item_id`),
  ADD KEY `fk_resp_item` (`item_id`);

--
-- Indexes for table `inspection_schedule`
--
ALTER TABLE `inspection_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `checklist_id` (`checklist_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `map_saved_location`
--
ALTER TABLE `map_saved_location`
  ADD PRIMARY KEY (`loc_id`);

--
-- Indexes for table `nature_of_inspection`
--
ALTER TABLE `nature_of_inspection`
  ADD PRIMARY KEY (`noi_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bfp_standards`
--
ALTER TABLE `bfp_standards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `checklists`
--
ALTER TABLE `checklists`
  MODIFY `checklist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `checklist_items`
--
ALTER TABLE `checklist_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checklist_item_select_options`
--
ALTER TABLE `checklist_item_select_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checklist_sections`
--
ALTER TABLE `checklist_sections`
  MODIFY `checklist_section_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_info`
--
ALTER TABLE `general_info`
  MODIFY `gen_info_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspections`
--
ALTER TABLE `inspections`
  MODIFY `inspection_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspection_responses`
--
ALTER TABLE `inspection_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspection_schedule`
--
ALTER TABLE `inspection_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `map_saved_location`
--
ALTER TABLE `map_saved_location`
  MODIFY `loc_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nature_of_inspection`
--
ALTER TABLE `nature_of_inspection`
  MODIFY `noi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_config`
--
ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
