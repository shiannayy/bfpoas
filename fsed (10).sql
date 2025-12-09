-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 02, 2025 at 10:16 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fsed`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bfp_standards`
--

INSERT INTO `bfp_standards` (`id`, `standard_code`, `description`, `occupancy_type`, `parameter`, `min_value`, `max_value`, `unit`, `source_reference`, `created_at`, `updated_at`) VALUES
(1, 'EXIT_DOOR_WIDTH_MIN', 'Minimum exit door width', 'All', 'Exit Door Width', '0.71', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(2, 'HEADROOM_MIN', 'Minimum floor-to-ceiling headroom in stairs', 'All', 'Headroom', '2.30', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(3, 'STAIR_RISE_MAX', 'Maximum stair rise height (Class A)', 'All', 'Stair Rise Height', '0.19', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(4, 'STAIR_LANDING_HEIGHT_MAX', 'Maximum height between landings (Class A)', 'All', 'Height Between Landings', '2.75', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(5, 'UNIT_AREA_PER_PERSON', 'Minimum area per person (waiting/standing)', 'All', 'Area per Person', '0.28', NULL, 'sqm/person', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(6, 'TRAVEL_DIST_NO_SPRINKLER', 'Travel distance to exit (no sprinkler)', 'All', 'Travel Distance', '46.00', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(7, 'TRAVEL_DIST_WITH_SPRINKLER', 'Travel distance to exit (with sprinkler)', 'All', 'Travel Distance', '61.00', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(8, 'AISLE_WIDTH_MIN', 'Minimum aisle width for occupancy of 60+', 'Public Assembly', 'Aisle Width', '0.91', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(9, 'SEAT_WIDTH_STANDARD', 'Standard seat width without dividing arms', 'Public Assembly', 'Seat Width', '0.60', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(10, 'FIRE_ESCAPE_TREAD_MIN', 'Minimum tread for fire escape stair', 'All', 'Stair Tread', '0.15', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(11, 'RAMP_WIDTH_CLASS_A_MIN', 'Minimum class A ramp width', 'All', 'Ramp Width', '1.12', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(12, 'RAMP_WIDTH_CLASS_B_MIN', 'Minimum class B ramp width', 'All', 'Ramp Width', '0.76', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(13, 'AISLE_WIDTH_HOSPITAL', 'Corridor/aisle width for hospitals', 'Hospital', 'Corridor Width', '2.44', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(14, 'AISLE_WIDTH_CUSTODIAL', 'Corridor/aisle width for residential-custodial care', 'Residential-Custodial', 'Corridor Width', '1.83', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38'),
(15, 'TRAVEL_DIST_MERCANTILE', 'Travel distance to exit for mercantile occupancy', 'Mercantile', 'Travel Distance', '30.50', NULL, 'meters', 'RA 9514', '2025-09-04 16:36:38', '2025-09-04 16:36:38');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `checklists`
--

INSERT INTO `checklists` (`checklist_id`, `fsed_code`, `title`, `description`, `version`, `created_at`, `checklist_status`) VALUES
(1, 'FSED-6F', 'Fire Safety Checklist on Building Plans', 'Checklist for reviewing fire safety compliance of building plans', 'Rev02', '2025-08-31 05:45:01', 0),
(2, 'FSED-14F', 'Industrial Occupancy Checklist', 'Inspection checklist for industrial occupancy compliance', 'Rev01', '2025-08-31 05:45:01', 1),
(3, 'FSED-18F', 'Business Occupancy Checklist', 'Inspection checklist for business occupancy compliance', 'Rev01', '2025-08-31 05:45:01', 0),
(4, 'FSED-15F', 'Educational Occupancy Checklist', 'Inspection checklist for educational occupancy compliance', 'Rev01', '2025-08-31 05:45:50', 1),
(5, 'FSED-16F', 'Healthcare Occupancy Checklist', 'Inspection checklist for healthcare occupancy compliance', 'Rev01', '2025-08-31 06:13:49', 0),
(6, 'FSED-17F', 'Mercantile Occupancy Checklist', 'Inspection checklist for mercantile occupancy compliance', 'Rev01', '2025-08-31 07:06:03', 0),
(8, 'FSED-19F', 'Health Care Occupancy Checklist', 'Inspection checklist for healthcare occupancy compliance', 'Rev01', '2025-08-31 07:27:12', 0),
(9, 'FSED-20F', 'Storage Occupancy Checklist', 'Inspection checklist for storage occupancy compliance', 'Rev01', '2025-08-31 07:44:34', 0),
(10, 'FSED-21F', 'Single and Two-Family Dwellings Checklist', 'Checklist for fire safety inspection of single and two-family dwellings (BFP-QSF-FSED-021 Rev. 01)', 'Rev. 01', '2025-08-31 08:03:05', 0),
(11, 'FSED-6F', 'Fire Safety Checklist on Building Plans', 'Checklist for evaluating fire safety compliance on building plans as per RA 9514 and its RIRR 2019', 'Rev01', '2025-09-01 10:58:46', 0),
(12, 'FSED-24F', 'Small/General Business Establishment Checklist', 'Inspection checklist for compliance of small/general business establishments', 'Rev01', '2025-09-01 11:02:52', 1),
(13, 'FSED-26F', 'Gasoline Service Station Checklist', 'Inspection checklist for compliance of gasoline service stations', 'Rev01', '2025-09-01 11:08:20', 1),
(14, 'FSED-27F', 'Places of Assembly Occupancy Checklist', 'Checklist for inspection and fire safety evaluation of places of assembly occupancy, covering general information, construction, exits, fire protection features, hazardous materials, and operating features in compliance with RA 9514 and its IRR.', '1.0', '2025-09-01 11:17:07', 1);

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
  `checklist_criteria` varchar(25) DEFAULT NULL COMMENT 'range, min_val, bool, min_elapse_days',
  `threshold_range_min` varchar(10) DEFAULT NULL,
  `threshold_range_max` varchar(10) DEFAULT NULL,
  `threshold_min_val` varchar(10) DEFAULT NULL COMMENT 'minimum_value',
  `threshold_max_val` varchar(10) DEFAULT NULL,
  `threshold_yes_no` char(1) DEFAULT NULL COMMENT 'either 1 = yes , 0 = no',
  `threshold_elapse_day` varchar(10) DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `checklist_items`
--

INSERT INTO `checklist_items` (`item_id`, `checklist_id`, `section`, `item_no`, `item_text`, `input_type`, `unit_label`, `checklist_criteria`, `threshold_range_min`, `threshold_range_max`, `threshold_min_val`, `threshold_max_val`, `threshold_yes_no`, `threshold_elapse_day`, `required`) VALUES
(6, 12, 2, NULL, 'Doors', 'number', 'm', 'min_val', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(7, 12, 2, NULL, 'Corridors / Hallways', 'number', 'm', 'range', '0.9', '2', '0', '0', '0', '0', 0),
(8, 12, 2, NULL, 'Exit Doors', 'number', 'm', 'min_val', '0', '0', '12', '0', '0', '0', 0),
(9, 12, 2, NULL, 'Horizontal Exits', 'number', 'm', 'range', '0.9', '2', '0', '0', '0', '0', 0),
(10, 12, 2, NULL, 'Stairs', 'number', 'm', 'range', '0.6', '0.915', '0', '0', '0', '0', 0),
(11, 12, 3, NULL, 'Minimum Letter Height  - 150 mm; ', 'number', 'mm', 'min_val', '', '', '150', '', NULL, '', 0),
(12, 12, 3, NULL, 'Width of Stroke - 19mm', 'number', 'mm', 'min_val', '', '', '19', '', NULL, '', 0),
(13, 12, 3, NULL, 'Exit Signs are posted along Exit Access, Exits and Exit Discharge', 'checkbox', '', 'yes_no', '', '', '', '', '1', '', 0),
(15, 12, 4, NULL, 'Expiration', 'date', 'days', 'days', '', '', '', '', NULL, '300', 0),
(16, 12, 4, NULL, 'Number of Fire Extinguisher', 'number', 'pcs', 'min_val', '', '', '4', '', NULL, '', 0),
(17, 12, 4, NULL, 'Chemical Fire Extinguisher', 'checkbox', '', 'yes_no', '', '', '', '', '1', '', 0),
(18, 12, 4, NULL, 'Regular Fire Extinguisher', 'checkbox', '', 'yes_no', '', '', '', '', '1', '', 0),
(19, 12, 1, NULL, 'Doors', 'number', '', 'range', '0.9', '2.1', '0', '0', '0', '0', 0),
(20, 12, 1, NULL, 'Doors', 'number', 'm', 'range', '1', '3', '', '', NULL, '', 0),
(21, 1, 5, NULL, 'Test if No Criteria', 'text', 'm', '', '', '', '', '', NULL, '', 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `checklist_sections`
--

CREATE TABLE `checklist_sections` (
  `checklist_section_id` int(11) NOT NULL,
  `section` varchar(100) NOT NULL,
  `checklist_id` int(11) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `checklist_sections`
--

INSERT INTO `checklist_sections` (`checklist_section_id`, `section`, `checklist_id`, `date_added`) VALUES
(1, 'EXIT ACCESS', 12, '2025-10-11 06:13:56'),
(2, 'EXITS', 12, '2025-10-11 07:06:53'),
(3, 'SIGNS, LIGHTING, AND EXIT SIGNAGE - MARKING OF MEANS OF EGRESS (EXIT)', 12, '2025-10-11 08:09:15'),
(4, 'FIRE EXTINGUISHER', 12, '2025-10-17 07:34:16'),
(5, 'Other', 12, '2025-10-17 14:35:58');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `doc_id` int(11) NOT NULL,
  `inspection_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `general_info`
--

CREATE TABLE `general_info` (
  `gen_info_id` int(11) NOT NULL,
  `gen_info_control_no` varchar(55) DEFAULT NULL,
  `form_code` varchar(20) DEFAULT NULL,
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
  `classification_of_occupancy` varchar(255) DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `general_info`
--

INSERT INTO `general_info` (`gen_info_id`, `gen_info_control_no`, `form_code`, `building_name`, `location_of_construction`, `postal_address`, `loc_id`, `project_title`, `height_of_building`, `no_of_storeys`, `area_per_floor`, `total_floor_area`, `portion_occupied`, `bed_capacity`, `owner_id`, `owner_name`, `occupant_name`, `representative_name`, `administrator_name`, `owner_contact_no`, `representative_contact_no`, `telephone_email`, `business_name`, `establishment_name`, `nature_of_business`, `classification_of_occupancy`, `healthcare_facility_name`, `healthcare_facility_type`, `building_permit_no`, `building_permit_date`, `occupancy_permit_no`, `occupancy_permit_date`, `mayors_permit_no`, `mayors_permit_date`, `municipal_license_no`, `municipal_license_date`, `electrical_cert_no`, `electrical_cert_date`, `fsic_control_no`, `fsic_date`, `fsic_fire_code_fee`, `fire_drill_cert_no`, `fire_drill_cert_date`, `fire_drill_fee`, `ntcv_control_no`, `ntcv_date`, `insurance_company`, `insurance_coinsurer`, `insurance_policy_no`, `insurance_date`, `policy_date`, `fire_code_fee`, `building_plan_checklist_no`, `other_info`, `region`, `district_office`, `station`, `station_address`, `date_received`, `date_released`, `gen_info_status`, `created_at`, `created_by`, `updated_at`) VALUES
(12, NULL, 'FSED-24F', 'Cafe Locran', 'Café Locran by Lovecakes, Ragos Street, Oas, Albay, Philippines', NULL, 30, NULL, '0.00', 0, '0.00', '0.00', '', 0, 13, 'Cafe Locran', 'Cafe Locran', 'Cafe Locran', 'Cafe Locran', '09091234567', '09091234567', NULL, 'Cafe Locran', 'Cafe Locran', 'Coffee Shop', NULL, '', '', '', NULL, '', NULL, '', NULL, '', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, 'V', '3D', 'Oas', 'Oas, Albay', NULL, NULL, 'Completed', '2025-09-30 10:15:04', NULL, '2025-10-07 08:10:13'),
(13, NULL, 'FSED-27F', 'Coca Cola Company', 'Oas Albay', NULL, NULL, NULL, '0.00', 0, '0.00', '0.00', '', 0, 9, 'Coca Cola Owner', 'La Coca', 'La Coca', 'La Coca', '09091234567', '09091234567', 'coca@fsic.gov', 'Coca Cola Company', 'Coca Cola Company', 'Cola', NULL, '', '', '', NULL, '', NULL, '', NULL, '', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, 'V', 'Albay', 'Oas', 'Centro, Oas, Albay', NULL, NULL, 'Completed', '2025-09-30 10:19:19', NULL, '2025-09-30 13:05:15'),
(14, NULL, 'FSED-14F', 'OPS', 'Oas Polytechnic School, 1, Oas, Albay, Philippines', NULL, 28, NULL, '0.00', 0, '0.00', '0.00', '', 0, 12, 'Oas Poly', 'Students', 'None', 'None', 'None', 'None', 'ops@g.c', 'None', 'None', 'Educational Facility', NULL, '', '', '', NULL, '', NULL, '', NULL, '', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, 'V', 'Oas', 'Oas Fire Station', 'Oas, Albay', NULL, NULL, 'Completed', '2025-10-04 02:12:06', NULL, '2025-10-07 08:06:28'),
(15, NULL, 'FSED-14F', 'Oas Community College', 'Oas Community College, 1, Oas, Albay, Philippines', NULL, 32, NULL, '0.00', 0, '0.00', '0.00', '', 0, 11, 'Oas Com', 'Students', 'Principal', 'None', '09091234567', 'none', 'oas@coo.com', 'Oas Community College', 'Oas Community College', 'School Ground', NULL, '', '', '', NULL, '', NULL, '', NULL, '', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, 'V', '3rd District', 'Oas Fire Station', 'Oas, Albay', NULL, NULL, 'Completed', '2025-10-06 14:20:18', NULL, '2025-10-07 10:09:14'),
(21, NULL, 'FSED-24F', 'Gabbis', 'Gabbi\'s Bistro - Oas Albay, Oas, Albay, Philippines', NULL, 38, NULL, '0.00', 0, '0.00', '0.00', '', 0, 20, 'Gabbi Bistro', 'Gabbi', 'Gabbi', 'Gabbi', '090912345678', '090912345678', 'test@email.com', 'Gabbi\'s Bistro', 'Gabbi\'s Bistro', 'Restaurant', NULL, '', '', '', NULL, '', NULL, '', NULL, '', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, 'V', 'Albay', 'Oas', 'Ilaor Sur', NULL, NULL, 'Completed', '2025-10-28 11:26:43', NULL, '2025-10-30 22:37:34'),
(78, NULL, 'FSED-24F', 'Pande', '7G42+F82, J. Rayala St, Oas, Albay, Philippines', 'J. Rayala St., OAS, ALBAY, 4504', 43, NULL, '200.00', 1, '200.00', '300.00', 'All Floor', 0, 23, 'Shan@fsic.gov', 'Shan', 'Shan', 'Shan', '09118081677', '09118081677', NULL, 'Shan Pharmaceutical Dealer and Drugstore', 'Shan Pharmaceutical Dealer and Drugstore', 'Pharmacy', NULL, '', '', '', NULL, '', NULL, '', NULL, '', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, 'V', 'Albay', 'Oas', 'Iraya Oas Albay', NULL, NULL, 'Completed', '2025-11-02 13:07:34', NULL, '2025-11-02 13:14:03'),
(79, 'GI8586640', 'FSED-24F', 'Gabbi\'s Bistro 2', '7F6R+8W3, Provincial Rd, Oas, Albay, Philippines', 'Provicial Road, OAS, ALBAY, 4504', 44, NULL, '5.00', 1, '200.00', '200.00', 'All', 0, 20, 'Gabbi Bistro', 'Gabbi', 'Gabbi', 'Gabbi', '09118081677', '09118081677', NULL, 'Gabbi\'s Bistro 2', 'Gabbi\'s Bistro 2', 'Bistro Cafe', NULL, '', '', '', NULL, '', NULL, '', NULL, '', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, 'V', 'Albay', 'Oas', 'Iraya Oas Albay', NULL, NULL, 'Completed', '2025-11-02 13:39:17', NULL, '2025-11-02 13:41:47'),
(80, 'GI1392582', 'FSED-24F', 'Paler Realty', 'Nuestra Sra. de Salvacion Funeral Parlor, Oas, Albay, Philippines', ', OAS, ALBAY, 4504', 45, NULL, '300.00', 2, '200.00', '400.00', 'Ground Floor', 2, 22, 'Pande Marino', 'Pande Marino Family', 'Pande Marino Family', 'Pande Marino Family', '09099999999', '09999999999', '', 'Paler Realty', 'Paler Realty', 'Bakery', NULL, '', '', '', NULL, '', NULL, '', NULL, '', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, 'V', 'Albay', 'Oas', 'Iraya Oas Albay', NULL, NULL, 'Completed', '2025-11-02 13:42:48', NULL, '2025-11-02 14:08:16'),
(82, 'GI3923528', NULL, NULL, NULL, NULL, NULL, NULL, '0.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Draft', '2025-11-02 14:07:49', NULL, '2025-11-02 21:07:49');

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
  `cancellation_reason` text DEFAULT NULL,
  `has_Defects` int(11) DEFAULT NULL,
  `defects_details` text DEFAULT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inspections`
--

INSERT INTO `inspections` (`inspection_id`, `fsic_no`, `gen_info_id`, `schedule_id`, `checklist_id`, `inspector_id`, `started_at`, `completed_at`, `status`, `hasRecoApproval`, `dateRecommended`, `recommended_by`, `hasFinalApproval`, `dateApproved`, `approved_by`, `hasBeenReceived`, `dateReceived`, `received_by`, `inspection_score`, `cancellation_reason`, `has_Defects`, `defects_details`, `reference_no`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'R050108-25-0003', 13, 3, 12, 16, '2025-10-25 03:02:20', '2025-10-25 03:03:44', 'Completed', 1, '2025-11-01 07:20:53', 18, 1, '2025-11-01 10:32:59', 15, 0, NULL, NULL, 100, NULL, 0, NULL, NULL, 16, '2025-10-25 01:02:20', '2025-10-29 10:49:37'),
(2, 'R050108-25-0002', 12, 2, 12, 16, '2025-10-26 12:01:10', '2025-10-26 19:36:16', 'Completed', 1, '2025-11-01 01:04:06', 18, 1, '2025-11-01 10:33:02', 15, 0, NULL, NULL, 100, NULL, 0, NULL, NULL, 16, '2025-10-26 11:01:10', '2025-10-29 10:49:37'),
(3, 'R050108-25-0001', 13, 1, 12, 16, '2025-10-26 12:23:44', '2025-10-26 12:23:47', 'Completed', 1, '2025-11-01 07:17:01', 18, 1, '2025-11-01 10:33:04', 15, 0, NULL, NULL, 100, NULL, 0, NULL, NULL, 16, '2025-10-26 11:23:44', '2025-10-29 10:49:37'),
(4, 'R050108-25-0004', 11, 4, 12, 16, '2025-10-27 03:33:30', '2025-10-29 09:32:21', 'Completed', 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL, 100, NULL, 0, '[{\"item_id\":17,\"item_text\":\"Chemical Fire Extinguisher\",\"criteria\":\"Must be YES\",\"response_value\":\"NO\"},{\"item_id\":18,\"item_text\":\"Regular Fire Extinguisher\",\"criteria\":\"Must be YES\",\"response_value\":\"NO\"}]', NULL, 16, '2025-10-27 02:33:30', '2025-10-29 10:49:37'),
(5, 'R050108-25-0005', 12, 5, 12, 16, '2025-10-29 09:30:47', '2025-10-29 09:31:51', 'Completed', 1, '2025-11-01 00:46:52', 18, 1, '2025-11-01 10:31:01', 15, 0, NULL, NULL, 100, NULL, 0, NULL, NULL, 16, '2025-10-29 08:30:47', '2025-10-29 10:49:37'),
(6, 'R050108-25-0006', 12, 6, 12, 16, '2025-10-29 12:58:31', '2025-10-29 12:59:56', 'Completed', 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL, 7.14, NULL, 1, '[{\"item_id\":7,\"item_text\":\"Corridors \\/ Hallways\",\"criteria\":\"0.9-2\",\"response_value\":\"0\"},{\"item_id\":8,\"item_text\":\"Exit Doors\",\"criteria\":\"Must be Greater than or Eq 12\",\"response_value\":\"0\"},{\"item_id\":9,\"item_text\":\"Horizontal Exits\",\"criteria\":\"0.9-2\",\"response_value\":\"0\"},{\"item_id\":10,\"item_text\":\"Stairs\",\"criteria\":\"0.6-0.915\",\"response_value\":\"0\"},{\"item_id\":11,\"item_text\":\"Minimum Letter Height  - 150 mm; \",\"criteria\":\"Must be Greater than or Eq 150\",\"response_value\":\"0\"},{\"item_id\":12,\"item_text\":\"Width of Stroke - 19mm\",\"criteria\":\"Must be Greater than or Eq 19\",\"response_value\":\"0\"},{\"item_id\":13,\"item_text\":\"Exit Signs are posted along Exit Access, Exits and Exit Discharge\",\"criteria\":\"Must be YES\",\"response_value\":\"NO\"},{\"item_id\":15,\"item_text\":\"Expiration\",\"criteria\":\"Must be More than or equal to 300 days\",\"response_value\":null},{\"item_id\":16,\"item_text\":\"Number of Fire Extinguisher\",\"criteria\":\"Must be Greater than or Eq 4\",\"response_value\":\"0\"},{\"item_id\":17,\"item_text\":\"Chemical Fire Extinguisher\",\"criteria\":\"Must be YES\",\"response_value\":\"NO\"},{\"item_id\":18,\"item_text\":\"Regular Fire Extinguisher\",\"criteria\":\"Must be YES\",\"response_value\":\"NO\"},{\"item_id\":19,\"item_text\":\"Doors\",\"criteria\":\"0.9-2.1\",\"response_value\":\"0\"},{\"item_id\":20,\"item_text\":\"Doors\",\"criteria\":\"1-3\",\"response_value\":\"0\"}]', NULL, 16, '2025-10-29 11:58:31', '2025-10-29 04:59:56');

-- --------------------------------------------------------

--
-- Table structure for table `inspection_recommendations`
--

CREATE TABLE `inspection_recommendations` (
  `rec_id` int(11) NOT NULL,
  `inspection_id` int(11) NOT NULL,
  `recommendation` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `inspection_responses`
--

CREATE TABLE `inspection_responses` (
  `response_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `response_value` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `response_proof_img` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inspection_responses`
--

INSERT INTO `inspection_responses` (`response_id`, `schedule_id`, `item_id`, `response_value`, `remarks`, `updated_at`, `response_proof_img`) VALUES
(1, 3, 19, '2', '1', '2025-10-24 19:02:25', NULL),
(2, 3, 20, '2', '1', '2025-10-24 19:02:25', NULL),
(3, 3, 6, '2', '1', '2025-10-24 19:03:32', NULL),
(4, 3, 7, '2', '1', '2025-10-24 19:03:32', NULL),
(5, 3, 8, '12', '1', '2025-10-24 19:03:32', NULL),
(6, 3, 9, '2', '1', '2025-10-24 19:03:32', NULL),
(7, 3, 10, '0.7', '1', '2025-10-24 19:03:32', NULL),
(8, 3, 11, '150', '1', '2025-10-24 19:02:55', NULL),
(9, 3, 12, '19', '1', '2025-10-24 19:02:55', NULL),
(10, 3, 13, '1', '1', '2025-10-24 19:02:55', NULL),
(11, 3, 15, '2027-10-10', '1', '2025-10-24 19:03:42', NULL),
(12, 3, 16, '4', '1', '2025-10-24 19:03:42', NULL),
(13, 3, 17, '1', '1', '2025-10-24 19:03:43', NULL),
(14, 3, 18, '1', '1', '2025-10-24 19:03:43', NULL),
(15, 2, 19, '2', '1', '2025-10-26 10:48:10', 'Proof_2_1_19.jpg'),
(16, 2, 20, '3', '1', '2025-10-26 10:48:10', 'Proof_2_1_20.jpg'),
(17, 2, 6, '4', '1', '2025-10-26 11:33:56', 'Proof_2_2_6.jpg'),
(18, 2, 7, '2', '1', '2025-10-26 11:33:56', 'Proof_2_2_7.jpg'),
(19, 2, 8, '12', '1', '2025-10-26 11:33:56', 'Proof_2_2_8.jpg'),
(20, 2, 9, '2', '1', '2025-10-26 11:33:56', NULL),
(21, 2, 10, '0.6', '1', '2025-10-26 11:33:56', NULL),
(22, 2, 11, '150', '1', '2025-10-26 04:01:38', NULL),
(23, 2, 12, '20', '1', '2025-10-26 04:01:38', NULL),
(24, 2, 13, '1', '1', '2025-10-26 04:01:38', NULL),
(25, 2, 15, '2029-10-10', '1', '2025-10-26 11:36:04', NULL),
(26, 2, 16, '4', '1', '2025-10-26 11:36:04', NULL),
(27, 2, 17, '1', '1', '2025-10-26 11:36:04', NULL),
(28, 2, 18, '1', '1', '2025-10-26 11:36:04', NULL),
(29, 4, 19, '2', '1', '2025-10-29 01:32:18', NULL),
(30, 4, 20, '2', '1', '2025-10-29 01:32:18', NULL),
(31, 4, 6, '2', '1', '2025-10-29 01:32:18', NULL),
(32, 4, 7, '2', '1', '2025-10-29 01:32:18', NULL),
(33, 4, 8, '12', '1', '2025-10-29 01:32:18', NULL),
(34, 4, 9, '2', '1', '2025-10-29 01:32:18', NULL),
(35, 4, 10, '0.8', '1', '2025-10-29 01:32:18', NULL),
(36, 4, 11, '151', '1', '2025-10-29 01:32:18', NULL),
(37, 4, 12, '19', '1', '2025-10-29 01:32:18', NULL),
(38, 4, 13, '1', '1', '2025-10-29 01:32:18', NULL),
(39, 4, 15, '2100-10-10', '1', '2025-10-29 01:32:20', NULL),
(40, 4, 16, '4', '1', '2025-10-29 01:32:20', NULL),
(41, 4, 17, '1', '1', '2025-10-29 01:32:20', NULL),
(42, 4, 18, '1', '1', '2025-10-29 01:32:20', NULL),
(43, 5, 19, '2', '1', '2025-10-29 01:30:54', NULL),
(44, 5, 20, '3', '1', '2025-10-29 01:30:54', NULL),
(45, 5, 6, '2', '1', '2025-10-29 01:31:03', NULL),
(46, 5, 7, '2', '1', '2025-10-29 01:31:03', NULL),
(47, 5, 8, '12', '1', '2025-10-29 01:31:03', NULL),
(48, 5, 9, '2', '1', '2025-10-29 01:31:03', NULL),
(49, 5, 10, '0.8', '1', '2025-10-29 01:31:03', NULL),
(50, 5, 11, '150', '1', '2025-10-29 01:31:10', NULL),
(51, 5, 12, '19', '1', '2025-10-29 01:31:10', NULL),
(52, 5, 13, '1', '1', '2025-10-29 01:31:10', NULL),
(53, 5, 15, '2026-10-10', '1', '2025-10-29 01:31:48', NULL),
(54, 5, 16, '4', '1', '2025-10-29 01:31:48', NULL),
(55, 5, 17, '1', '1', '2025-10-29 01:31:48', NULL),
(56, 5, 18, '1', '1', '2025-10-29 01:31:48', NULL),
(57, 6, 11, '0', '0', '2025-10-29 04:59:49', NULL),
(58, 6, 12, '0', '0', '2025-10-29 04:59:49', NULL),
(59, 6, 13, '0', '0', '2025-10-29 04:59:49', NULL),
(60, 6, 6, '0', '1', '2025-10-29 04:59:49', NULL),
(61, 6, 7, '0', '0', '2025-10-29 04:59:49', NULL),
(62, 6, 8, '0', '0', '2025-10-29 04:59:49', NULL),
(63, 6, 9, '0', '0', '2025-10-29 04:59:49', NULL),
(64, 6, 10, '0', '0', '2025-10-29 04:59:49', NULL),
(65, 6, 19, '0', '0', '2025-10-29 04:59:49', NULL),
(66, 6, 20, '0', '0', '2025-10-29 04:59:49', NULL),
(67, 6, 15, '0', '0', '2025-10-29 04:59:49', NULL),
(68, 6, 16, '0', '0', '2025-10-29 04:59:49', NULL),
(69, 6, 17, '0', '0', '2025-10-29 04:59:49', NULL),
(70, 6, 18, '0', '0', '2025-10-29 04:59:49', NULL);

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
  `scheduled_date` date NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `inspection_schedule`
--

INSERT INTO `inspection_schedule` (`schedule_id`, `checklist_id`, `HasClientAck`, `DateAckbyClient`, `AckByClient_id`, `hasRecommendingApproval`, `dateRecommendedForApproval`, `RecommendingApprover`, `hasFinalApproval`, `dateFinalApproval`, `FinalApprover`, `hasInspectorAck`, `dateInspectorAck`, `inspector_id`, `order_number`, `payment_id`, `scheduled_date`, `preferredSchedule`, `rescheduleCount`, `rescheduleReason`, `to_officer`, `assigned_to_officer_id`, `proceed_instructions`, `noi_id`, `noi_text`, `fsic_purpose`, `gen_info_id`, `purpose`, `duration`, `remarks`, `ins_sched_logs`, `inspection_sched_status`, `created_by`, `created_at`, `updated_at`, `ClientHasSeen`, `InspectorHasSeen`, `RecoApproverHasSeen`, `ApproverHasSeen`) VALUES
(1, 12, 'Y', '2025-10-24 11:34:32', 9, 1, '2025-10-21 10:34:15', 18, 1, '2025-10-24 12:00:10', 15, 1, '2025-10-21 10:18:27', 16, 'R05-ADV-0001', NULL, '2025-11-01', '2025-11-01', 0, 'eto na po', 'Sr Fire Officer Inspecsyon Taka', 16, 'Coca Cola Company - Oas Albay', '1', NULL, 'FOR CERTIFICATE OF OCCUPANCY', 13, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '', NULL, 'Completed', 18, '2025-10-21 16:17:37', '2025-11-02 18:14:25', 1, 1, 1, 1),
(2, 12, 'Y', '2025-10-21 22:06:30', 13, 1, '2025-10-21 22:06:55', 18, 1, '2025-10-21 22:08:45', 15, 1, '2025-10-21 22:07:59', 16, 'R05-ADV-0002', NULL, '2025-11-08', NULL, 0, NULL, 'Sr Fire Officer Inspecsyon Taka', 16, 'Cafe Locran - Café Locran by Lovecakes, Ragos Street, Oas, Albay, Philippines', '1', NULL, 'FOR CERTIFICATE OF OCCUPANCY', 12, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '', NULL, 'Completed', 18, '2025-10-22 04:03:34', '2025-11-02 18:14:25', 1, 1, 1, 1),
(3, 12, 'Y', '2025-10-24 11:37:14', 9, 1, '2025-10-24 11:37:43', 18, 1, '2025-10-24 11:38:54', 15, 1, '2025-10-24 11:36:24', 16, 'R05-ADV-0003', NULL, '2025-12-04', NULL, 0, NULL, 'Sr Fire Officer Inspecsyon Taka', 16, 'Coca Cola Company - Oas Albay', '1', NULL, 'FOR CERTIFICATE OF OCCUPANCY', 13, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '', NULL, 'Completed', 14, '2025-10-24 17:31:13', '2025-11-02 18:14:25', 1, 1, 1, 1),
(4, 12, 'Y', '2025-10-26 19:29:53', 8, 1, '2025-10-26 19:32:02', 18, 1, '2025-10-26 19:32:31', 15, 1, '2025-10-27 09:16:55', 16, 'R05-ADV-0004', NULL, '2025-11-21', '2025-11-21', 0, 'it will be emie', 'Sr Fire Officer Inspecsyon Taka', 16, 'Ayala Homes - Oas', '1', NULL, 'FOR CERTIFICATE OF OCCUPANCY', 11, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '', NULL, 'Completed', 6, '2025-10-27 02:27:27', '2025-11-02 18:14:25', 1, 1, 1, 1),
(5, 12, 'Y', '2025-10-29 01:22:55', 13, 1, '2025-10-29 01:26:34', 18, 1, '2025-10-29 01:27:32', 15, 1, '2025-10-29 01:28:27', 16, 'R05-ADV-0005', NULL, '2025-10-30', NULL, 0, NULL, 'Sr Fire Officer Inspecsyon Taka', 16, 'Cafe Locran - Café Locran by Lovecakes, Ragos Street, Oas, Albay, Philippines', '1', NULL, 'OTHERS', 12, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '', NULL, 'Completed', 6, '2025-10-29 06:53:12', '2025-11-02 18:25:52', 1, 1, 1, 1),
(6, 12, 'Y', '2025-10-29 03:39:29', 13, 1, '2025-10-29 04:10:36', 18, 1, '2025-10-29 04:13:49', 15, 1, '2025-10-29 04:58:29', 16, 'R05-ADV-0006', NULL, '2025-10-31', '2025-10-31', 1, 'Got Expired', 'Sr Fire Officer Inspecsyon Taka', 16, 'Cafe Locran - Café Locran by Lovecakes, Ragos Street, Oas, Albay, Philippines', '1', NULL, 'FOR CERTIFICATE OF OCCUPANCY', 12, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '[Approved] Reschedule to 2025-10-31 due to: unspecified reason.', NULL, 'Archived', 6, '2025-10-29 08:33:50', '2025-11-02 18:14:25', 0, 0, 0, 0),
(7, 12, 'N', NULL, NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, 16, 'R05-ADV-0007', 1, '2025-10-31', NULL, 0, NULL, 'Sr Fire Officer Inspecsyon Taka', 16, 'OPS - Oas Polytechnic School, 1, Oas, Albay, Philippines', '1', NULL, 'FOR CERTIFICATE OF OCCUPANCY', 14, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '', NULL, 'Archived', 6, '2025-10-29 14:53:24', '2025-11-02 18:14:25', 0, 0, 0, 0),
(8, 12, 'N', NULL, NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, 16, 'R05-ADV-0008', 2, '2025-11-05', NULL, 0, NULL, 'Sr Fire Officer Inspecsyon Taka', 16, 'OPS - Oas Polytechnic School, 1, Oas, Albay, Philippines', '1', NULL, 'FOR CERTIFICATE OF OCCUPANCY', 14, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '', NULL, 'Scheduled', 6, '2025-10-29 15:15:12', '2025-11-02 18:14:25', 0, 0, 0, 0),
(9, 12, 'Y', '2025-10-30 00:58:18', 9, 1, '2025-11-01 07:37:46', 18, 1, '2025-11-01 10:19:41', 15, 0, NULL, 16, 'R05-ADV-0009', 3, '2025-11-09', NULL, 0, NULL, 'Sr Fire Officer Inspecsyon Taka', 16, 'Coca Cola Company - Oas Albay', '1', NULL, 'FOR CERTIFICATE OF OCCUPANCY', 13, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '[Client] Has Acknowledged. <br>[Recommending Approver] Has Acknowledged. <br>[Final Approver] Has Acknowledged. <br>', NULL, 'Scheduled', 6, '2025-10-29 10:45:27', '2025-11-01 10:19:41', 0, 0, 0, 0),
(10, 12, 'Y', '2025-10-30 00:58:15', 9, 1, '2025-11-01 07:37:40', 18, 1, '2025-11-01 10:19:38', 15, 0, NULL, 16, 'R05-ADV-0010', 4, '2025-11-25', NULL, 0, NULL, 'Sr Fire Officer Inspecsyon Taka', 16, 'Coca Cola Company - Oas Albay', '2', NULL, 'FOR CERTIFICATE OF OCCUPANCY', 13, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '[Client] Has Acknowledged. <br>[Recommending Approver] Has Acknowledged. <br>[Final Approver] Has Acknowledged. <br>', NULL, 'Scheduled', 6, '2025-10-29 10:53:41', '2025-11-01 10:19:38', 0, 0, 0, 0),
(11, 12, 'N', NULL, NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, 16, 'R05-ADV-0011', 5, '2025-11-04', '2025-11-05', 0, 'Meeting on that day.', 'Sr Fire Officer Inspecsyon Taka', 16, 'Oas Community College - Oas Community College, 1, Oas, Albay, Philippines', '3', NULL, 'FOR CERTIFICATE OF OCCUPANCY', 15, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '[Cancelled] Fire Officer is not available by the requested Date', NULL, 'Archived', 6, '2025-10-30 00:35:30', '2025-11-01 09:17:57', 0, 0, 0, 0),
(12, 12, 'N', NULL, NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, 16, 'R05-ADV-0012', 6, '2025-11-19', NULL, 0, NULL, 'Sr Fire Officer Inspecsyon Taka', 16, 'Coca Cola Company - Oas Albay', '3', NULL, 'FOR CERTIFICATE OF OCCUPANCY', 13, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '', NULL, 'Scheduled', 9, '2025-10-30 07:42:27', '2025-10-30 14:42:28', 1, 1, 1, 1),
(13, 12, 'N', NULL, NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, 10, 'R05-ADV-0013', 7, '2025-11-01', NULL, 0, NULL, 'Inspector Gadget', 10, 'Ayala Homes - Oas', '5', NULL, 'FOR CERTIFICATE OF OCCUPANCY', 11, 'Conduct inspection of the said Establishment as required by RA 9514 RIRR Fire Code of the Philippines 2008 RIRR', 'Until the end of Inspection', '', NULL, 'Scheduled', 9, '2025-10-30 07:44:03', '2025-10-30 14:44:03', 1, 1, 1, 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_ts`, `log_id`, `log_action`, `log_message`, `schedule_id`, `inspection_id`, `user_id`, `checklist_id`, `gen_info_id`) VALUES
('2025-10-24 18:00:10', 1, 'sch', 'sr Fire Officer Approvado Taka(Final Approver)  has acknowledged 1 on 2025-10-24 20:00:10', 1, NULL, NULL, NULL, NULL),
('2025-10-25 00:52:33', 2, 'login', 'Sr Fire Officer Inspecsyon Taka(Inspector)  has logged-in', NULL, NULL, 16, NULL, NULL),
('2025-10-26 18:37:00', 3, 'sch', '(Recommending Approver)  has signed R05-ADV-0002 on 2025-10-26 19:37:00', 2, NULL, NULL, NULL, NULL),
('2025-10-26 18:37:00', 4, 'sch', '(Final Approver)  has signed R05-ADV-0002 on 2025-10-26 19:37:00', 2, NULL, NULL, NULL, NULL),
('2025-10-26 18:37:00', 5, 'sch', '(Client)  has signed R05-ADV-0002 on 2025-10-26 19:37:00', 2, NULL, NULL, NULL, NULL),
('2025-10-27 02:25:39', 6, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-27 02:27:13', 7, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-27 02:29:44', 8, 'login', 'Juan Discaya(Client)  has logged-in', NULL, NULL, 8, NULL, NULL),
('2025-10-27 02:29:53', 9, 'sch', 'Juan Discaya(Client)  has acknowledged R05-ADV-0004 on 2025-10-27 03:29:53', 4, NULL, NULL, NULL, NULL),
('2025-10-27 02:31:16', 10, 'sch', 'Sr Fire Officer Inspecsyon Taka(Inspector)  has acknowledged R05-ADV-0004 on 2025-10-27 03:31:16', 4, NULL, NULL, NULL, NULL),
('2025-10-27 02:31:47', 11, 'login', 'Fire Marshal(Administrator)  has logged-in', NULL, NULL, 18, NULL, NULL),
('2025-10-27 02:32:02', 12, 'sch', 'Fire Marshal(Recommending Approver)  has acknowledged R05-ADV-0004 on 2025-10-27 03:32:02', 4, NULL, NULL, NULL, NULL),
('2025-10-27 02:32:16', 13, 'login', 'sr Fire Officer Approvado Taka(Administrator)  has logged-in', NULL, NULL, 15, NULL, NULL),
('2025-10-27 02:32:31', 14, 'sch', 'sr Fire Officer Approvado Taka(Final Approver)  has acknowledged R05-ADV-0004 on 2025-10-27 03:32:31', 4, NULL, NULL, NULL, NULL),
('2025-10-27 14:00:20', 15, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-27 16:16:55', 16, 'sch', 'Sr Fire Officer Inspecsyon Taka(Inspector)  has acknowledged R05-ADV-0004 on 2025-10-27 17:16:55', 4, NULL, NULL, NULL, NULL),
('2025-10-27 16:23:26', 17, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-27 16:40:25', 18, 'login', 'Juan Discaya(Client)  has logged-in', NULL, NULL, 8, NULL, NULL),
('2025-10-28 17:01:08', 19, 'login', 'Cafe Locran(Client)  has logged-in', NULL, NULL, 13, NULL, NULL),
('2025-10-29 05:08:16', 20, 'login', 'Juan Discaya(Client)  has logged-in', NULL, NULL, 8, NULL, NULL),
('2025-10-29 06:36:51', 21, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-29 08:14:52', 22, 'login', 'Cafe Locran(Client)  has logged-in', NULL, NULL, 13, NULL, NULL),
('2025-10-29 08:22:55', 23, 'sch', 'Cafe Locran(Client)  has acknowledged R05-ADV-0005 on 2025-10-29 09:22:55', 5, NULL, NULL, NULL, NULL),
('2025-10-29 08:26:23', 24, 'login', 'Fire Marshal(Administrator)  has logged-in', NULL, NULL, 18, NULL, NULL),
('2025-10-29 08:26:34', 25, 'sch', 'Fire Marshal(Recommending Approver)  has acknowledged R05-ADV-0005 on 2025-10-29 09:26:34', 5, NULL, NULL, NULL, NULL),
('2025-10-29 08:26:50', 26, 'login', 'sr Fire Officer Approvado Taka(Administrator)  has logged-in', NULL, NULL, 15, NULL, NULL),
('2025-10-29 08:27:32', 27, 'sch', 'sr Fire Officer Approvado Taka(Final Approver)  has acknowledged R05-ADV-0005 on 2025-10-29 09:27:32', 5, NULL, NULL, NULL, NULL),
('2025-10-29 08:27:42', 28, 'login', 'Sr Fire Officer Inspecsyon Taka(Inspector)  has logged-in', NULL, NULL, 16, NULL, NULL),
('2025-10-29 08:28:27', 29, 'sch', 'Sr Fire Officer Inspecsyon Taka(Inspector)  has acknowledged R05-ADV-0005 on 2025-10-29 09:28:27', 5, NULL, NULL, NULL, NULL),
('2025-10-29 08:33:10', 30, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-29 10:39:29', 31, 'sch', 'Cafe Locran(Client)  has acknowledged R05-ADV-0006 on 2025-10-29 11:39:29', 6, NULL, NULL, NULL, NULL),
('2025-10-29 11:10:25', 32, 'login', 'Fire Marshal(Administrator)  has logged-in', NULL, NULL, 18, NULL, NULL),
('2025-10-29 11:10:36', 33, 'sch', 'Fire Marshal(Recommending Approver)  has acknowledged R05-ADV-0006 on 2025-10-29 12:10:36', 6, NULL, NULL, NULL, NULL),
('2025-10-29 11:13:03', 34, 'login', 'sr Fire Officer Approvado Taka(Administrator)  has logged-in', NULL, NULL, 15, NULL, NULL),
('2025-10-29 11:13:49', 35, 'sch', 'sr Fire Officer Approvado Taka(Final Approver)  has acknowledged R05-ADV-0006 on 2025-10-29 12:13:49', 6, NULL, NULL, NULL, NULL),
('2025-10-29 11:58:13', 36, 'login', 'Sr Fire Officer Inspecsyon Taka(Inspector)  has logged-in', NULL, NULL, 16, NULL, NULL),
('2025-10-29 11:58:29', 37, 'sch', 'Sr Fire Officer Inspecsyon Taka(Inspector)  has acknowledged R05-ADV-0006 on 2025-10-29 12:58:29', 6, NULL, NULL, NULL, NULL),
('2025-10-29 13:02:55', 38, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-29 17:50:04', 39, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 07:35:59', 40, 'login', 'Oas Com(Client)  has logged-in', NULL, NULL, 11, NULL, NULL),
('2025-10-30 07:47:30', 41, 'login', 'Coca Cola Owner(Client)  has logged-in', NULL, NULL, 9, NULL, NULL),
('2025-10-30 07:58:15', 42, 'sch', 'Coca Cola Owner(Client)  has acknowledged R05-ADV-0010 on 2025-10-30 08:58:15', 10, NULL, NULL, NULL, NULL),
('2025-10-30 07:58:18', 43, 'sch', 'Coca Cola Owner(Client)  has acknowledged R05-ADV-0009 on 2025-10-30 08:58:18', 9, NULL, NULL, NULL, NULL),
('2025-10-30 09:19:31', 44, 'login', 'Coca Cola Owner(Client)  has logged-in', NULL, NULL, 9, NULL, NULL),
('2025-10-30 14:45:34', 45, 'login', 'Coca Cola Owner(Client)  has logged-in', NULL, NULL, 9, NULL, NULL),
('2025-10-30 14:45:54', 46, 'login', 'Coca Cola Owner(Client)  has logged-in', NULL, NULL, 9, NULL, NULL),
('2025-10-30 15:02:31', 47, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 15:05:54', 48, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 15:09:00', 49, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 15:09:12', 50, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 15:44:02', 51, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 15:44:11', 52, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 15:45:16', 53, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 18:08:23', 54, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 18:09:27', 55, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 18:09:56', 56, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 18:10:31', 57, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 18:11:02', 58, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 20:04:53', 59, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 20:05:20', 60, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-30 20:05:37', 61, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-31 07:20:08', 62, 'auto cleanup general_info', 'Removed expired draft (created 2025-10-22 06:45:01)', NULL, NULL, NULL, NULL, 20),
('2025-10-31 11:19:11', 63, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-31 18:45:59', 64, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-10-31 19:42:28', 65, 'login', 'Fire Marshal(Administrator)  has logged-in', NULL, NULL, 18, NULL, NULL),
('2025-11-01 14:26:48', 66, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-11-01 14:37:40', 67, 'sch', 'Fire Marshal(Recommending Approver)  has acknowledged R05-ADV-0010 on 2025-11-01 15:37:40', 10, NULL, NULL, NULL, NULL),
('2025-11-01 14:37:46', 68, 'sch', 'Fire Marshal(Recommending Approver)  has acknowledged R05-ADV-0009 on 2025-11-01 15:37:46', 9, NULL, NULL, NULL, NULL),
('2025-11-01 17:14:07', 69, 'login', 'Sr Fire Officer Inspecsyon Taka(Inspector)  has logged-in', NULL, NULL, 16, NULL, NULL),
('2025-11-01 17:17:35', 70, 'login', 'Sr Fire Officer Inspecsyon Taka(Inspector)  has logged-in', NULL, NULL, 16, NULL, NULL),
('2025-11-01 17:18:42', 71, 'login', 'sr Fire Officer Approvado Taka(Administrator)  has logged-in', NULL, NULL, 15, NULL, NULL),
('2025-11-01 17:19:38', 72, 'sch', 'sr Fire Officer Approvado Taka(Final Approver)  has acknowledged R05-ADV-0010 on 2025-11-01 18:19:38', 10, NULL, NULL, NULL, NULL),
('2025-11-01 17:19:41', 73, 'sch', 'sr Fire Officer Approvado Taka(Final Approver)  has acknowledged R05-ADV-0009 on 2025-11-01 18:19:41', 9, NULL, NULL, NULL, NULL),
('2025-11-01 17:37:47', 74, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-11-01 18:00:21', 75, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-11-01 18:02:07', 76, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-11-01 18:06:19', 77, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-11-01 18:15:47', 78, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-11-01 18:20:50', 79, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-11-02 19:14:15', 80, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:35:41)', NULL, NULL, NULL, NULL, 22),
('2025-11-02 19:14:15', 81, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:38:08)', NULL, NULL, NULL, NULL, 23),
('2025-11-02 19:14:15', 82, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:38:17)', NULL, NULL, NULL, NULL, 24),
('2025-11-02 19:14:15', 83, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:44:54)', NULL, NULL, NULL, NULL, 25),
('2025-11-02 19:14:15', 84, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:46:06)', NULL, NULL, NULL, NULL, 26),
('2025-11-02 19:14:15', 85, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:46:31)', NULL, NULL, NULL, NULL, 27),
('2025-11-02 19:14:15', 86, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:46:32)', NULL, NULL, NULL, NULL, 28),
('2025-11-02 19:14:15', 87, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:46:41)', NULL, NULL, NULL, NULL, 29),
('2025-11-02 19:14:15', 88, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:46:50)', NULL, NULL, NULL, NULL, 30),
('2025-11-02 19:14:15', 89, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:47:03)', NULL, NULL, NULL, NULL, 31),
('2025-11-02 19:14:15', 90, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:48:09)', NULL, NULL, NULL, NULL, 32),
('2025-11-02 19:14:15', 91, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:48:35)', NULL, NULL, NULL, NULL, 33),
('2025-11-02 19:14:15', 92, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:52:19)', NULL, NULL, NULL, NULL, 34),
('2025-11-02 19:14:15', 93, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:54:15)', NULL, NULL, NULL, NULL, 35),
('2025-11-02 19:14:15', 94, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:55:20)', NULL, NULL, NULL, NULL, 36),
('2025-11-02 19:14:15', 95, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:55:30)', NULL, NULL, NULL, NULL, 37),
('2025-11-02 19:14:15', 96, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:56:04)', NULL, NULL, NULL, NULL, 38),
('2025-11-02 19:14:15', 97, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:56:51)', NULL, NULL, NULL, NULL, 39),
('2025-11-02 19:14:15', 98, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 19:56:59)', NULL, NULL, NULL, NULL, 40),
('2025-11-02 19:14:15', 99, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:05:48)', NULL, NULL, NULL, NULL, 41),
('2025-11-02 19:14:15', 100, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:09:05)', NULL, NULL, NULL, NULL, 42),
('2025-11-02 19:14:15', 101, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:10:50)', NULL, NULL, NULL, NULL, 43),
('2025-11-02 19:23:34', 102, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-11-02 19:45:15', 103, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:15:38)', NULL, NULL, NULL, NULL, 44),
('2025-11-02 19:45:15', 104, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:18:35)', NULL, NULL, NULL, NULL, 45),
('2025-11-02 19:45:15', 105, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:23:38)', NULL, NULL, NULL, NULL, 46),
('2025-11-02 19:45:15', 106, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:24:31)', NULL, NULL, NULL, NULL, 47),
('2025-11-02 19:45:15', 107, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:26:08)', NULL, NULL, NULL, NULL, 48),
('2025-11-02 19:45:15', 108, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:26:57)', NULL, NULL, NULL, NULL, 49),
('2025-11-02 19:45:15', 109, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:27:06)', NULL, NULL, NULL, NULL, 50),
('2025-11-02 19:45:15', 110, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:27:07)', NULL, NULL, NULL, NULL, 51),
('2025-11-02 19:45:15', 111, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:27:07)', NULL, NULL, NULL, NULL, 52),
('2025-11-02 19:45:15', 112, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:28:25)', NULL, NULL, NULL, NULL, 53),
('2025-11-02 19:45:15', 113, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:28:26)', NULL, NULL, NULL, NULL, 54),
('2025-11-02 19:45:15', 114, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:28:26)', NULL, NULL, NULL, NULL, 55),
('2025-11-02 19:45:15', 115, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:28:28)', NULL, NULL, NULL, NULL, 56),
('2025-11-02 19:45:15', 116, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:44:32)', NULL, NULL, NULL, NULL, 57),
('2025-11-02 19:45:15', 117, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:44:33)', NULL, NULL, NULL, NULL, 58),
('2025-11-02 19:45:15', 118, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:44:34)', NULL, NULL, NULL, NULL, 59),
('2025-11-02 19:45:15', 119, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:44:34)', NULL, NULL, NULL, NULL, 60),
('2025-11-02 19:45:15', 120, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:44:35)', NULL, NULL, NULL, NULL, 61),
('2025-11-02 19:45:15', 121, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:44:35)', NULL, NULL, NULL, NULL, 62),
('2025-11-02 19:45:15', 122, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:44:36)', NULL, NULL, NULL, NULL, 63),
('2025-11-02 19:45:15', 123, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:44:36)', NULL, NULL, NULL, NULL, 64),
('2025-11-02 19:45:15', 124, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:44:36)', NULL, NULL, NULL, NULL, 65),
('2025-11-02 19:45:15', 125, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:44:37)', NULL, NULL, NULL, NULL, 66),
('2025-11-02 19:45:15', 126, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:44:37)', NULL, NULL, NULL, NULL, 67),
('2025-11-02 19:45:30', 127, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:45:30)', NULL, NULL, NULL, NULL, 68),
('2025-11-02 19:47:30', 128, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:47:30)', NULL, NULL, NULL, NULL, 69),
('2025-11-02 19:47:42', 129, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:47:41)', NULL, NULL, NULL, NULL, 70),
('2025-11-02 19:49:35', 130, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:49:34)', NULL, NULL, NULL, NULL, 71),
('2025-11-02 19:52:47', 131, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:52:42)', NULL, NULL, NULL, NULL, 72),
('2025-11-02 19:55:54', 132, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:55:49)', NULL, NULL, NULL, NULL, 73),
('2025-11-02 19:58:42', 133, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:58:42)', NULL, NULL, NULL, NULL, 74),
('2025-11-02 19:58:49', 134, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 20:58:49)', NULL, NULL, NULL, NULL, 75),
('2025-11-02 20:04:08', 135, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 21:04:07)', NULL, NULL, NULL, NULL, 76),
('2025-11-02 20:04:25', 136, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 21:04:13)', NULL, NULL, NULL, NULL, 76),
('2025-11-02 20:04:25', 137, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 21:04:20)', NULL, NULL, NULL, NULL, 77),
('2025-11-02 20:04:52', 138, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 21:04:26)', NULL, NULL, NULL, NULL, 77),
('2025-11-02 20:05:06', 139, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 21:05:01)', NULL, NULL, NULL, NULL, 78),
('2025-11-02 20:07:34', 140, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 21:05:23)', NULL, NULL, NULL, NULL, 78),
('2025-11-02 20:39:13', 141, 'login', 'Admin(Administrator)  has logged-in', NULL, NULL, 6, NULL, NULL),
('2025-11-02 21:13:28', 142, 'auto cleanup general_info', 'Removed expired draft (created 2025-11-02 22:00:38)', NULL, NULL, NULL, NULL, 81);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `map_saved_location`
--

INSERT INTO `map_saved_location` (`loc_id`, `address`, `lat`, `lng`, `date_added`) VALUES
(5, '7G34+JFG, National Rd, Oas, Albay, Philippines', '13.254080000000000', '123.506510000000000', '2025-10-06 13:26:47'),
(6, '48 Diversion Rd', '13.256910000000000', '123.505640000000000', '2025-10-06 13:44:36'),
(7, '48 Diversion Rd', '13.256910000000000', '123.505640000000000', '2025-10-06 13:49:53'),
(8, '7G43+FX6, National Rd, Oas, Albay, Philippines', '13.256230000000000', '123.504490000000000', '2025-10-06 13:56:04'),
(9, '3 Iraya St', '13.255380000000000', '123.504240000000000', '2025-10-06 13:56:09'),
(10, '3 Iraya St', '13.255210000000000', '123.504660000000000', '2025-10-06 13:56:14'),
(11, '7G43+4MX, Oas, Albay, Philippines', '13.254990000000000', '123.503990000000000', '2025-10-06 13:56:18'),
(12, '8 Romano St', '13.258360000000000', '123.496150000000000', '2025-10-06 14:18:20'),
(13, 'Oas', '13.249720000000000', '123.515430000000000', '2025-10-07 07:12:20'),
(28, 'Oas Polytechnic School, 1, Oas, Albay, Philippines', '13.263665100000000', '123.495658800000000', '2025-10-07 14:02:11'),
(29, 'Cafe Lo', '13.256445919417000', '123.504030704500000', '2025-10-07 14:08:14'),
(30, 'Café Locran by Lovecakes, Ragos Street, Oas, Albay, Philippines', '13.256438500000000', '123.504051900000000', '2025-10-07 14:08:18'),
(31, '7F5X+VXP', '13.259724983538000', '123.499953746800000', '2025-10-07 09:34:19'),
(32, 'Oas Community College, 1, Oas, Albay, Philippines', '13.249629600000000', '123.515471000000000', '2025-10-07 16:09:10'),
(33, '6GQ8+W7', '13.241616478809000', '123.514351844790000', '2025-10-11 02:42:53'),
(34, '6GX5+F65', '13.248300282214000', '123.507957458500000', '2025-10-11 02:56:09'),
(35, '7F6Q+7CV Mayao', '13.260727491131000', '123.488527536390000', '2025-10-16 02:38:17'),
(36, '7G67+5P Oas, Albay, Philippines', '13.260377331885000', '123.514250793460000', '2025-10-30 20:09:18'),
(37, 'Gabbi', '13.256706993021000', '123.499953746800000', '2025-10-30 20:09:37'),
(38, 'Gabbi\'s Bistro - Oas Albay, Oas, Albay, Philippines', '13.256647100000000', '123.499955600000000', '2025-10-30 20:09:41'),
(39, 'Pan de-', '13.255607871254000', '123.500806689260000', '2025-11-02 20:07:16'),
(40, 'Pan de-Marino Oas, Revatoris St, Oas, Albay, Philippines', '13.255606600000000', '123.500807100000000', '2025-11-02 20:07:19'),
(41, 'Shan P', '13.256132630722000', '123.500755727290000', '2025-11-02 20:10:50'),
(42, 'Shan Pharmaceutical Dealer and Drugstore, J. Rayala Street, Oas, Albay, Philippines', '13.256132300000000', '123.500756300000000', '2025-11-02 20:10:54'),
(43, '7G42+F82, J. Rayala St, Oas, Albay, Philippines', '13.256132630722000', '123.500755727290000', '2025-11-02 20:13:01'),
(44, '7F6R+8W3, Provincial Rd, Oas, Albay, Philippines', '13.260794069592000', '123.492091426400000', '2025-11-02 20:40:53'),
(45, 'Nuestra Sra. de Salvacion Funeral Parlor, Oas, Albay, Philippines', '13.256679580306000', '123.500087857250000', '2025-11-02 20:57:47'),
(46, '7F5X+VXP, Oas, Albay, Philippines', '13.259724983538000', '123.499953746800000', '2025-11-02 21:09:21'),
(47, 'Twin Palace Hotel And Convention Center, Oas, Albay, Philippines', '13.259724983538000', '123.499953746800000', '2025-11-02 21:09:25'),
(48, 'Twin Palace Hotel And Convention Center, Oas, Albay, Philippines', '13.259718600000000', '123.499957400000000', '2025-11-02 21:09:30');

-- --------------------------------------------------------

--
-- Table structure for table `nature_of_inspection`
--

CREATE TABLE `nature_of_inspection` (
  `noi_id` int(11) NOT NULL,
  `noi_text` varchar(100) DEFAULT NULL,
  `noi_status` varchar(1) DEFAULT 'A',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `OR_number`, `schedule_id`, `amount_paid`, `date_paid`) VALUES
(1, 'R0528863', 7, 300, '2025-10-29 14:53:24'),
(2, 'R0526967', 8, 300, '2025-10-29 15:15:12'),
(3, 'R0557755', 9, 300, '2025-10-29 17:45:27'),
(4, 'R0544555', 10, 300, '2025-10-29 17:53:41'),
(5, 'R0575021', 11, 300, '2025-10-30 07:35:30'),
(6, 'R0585271', 12, 300, '2025-10-30 14:42:28'),
(7, 'R0529272', 13, 300, '2025-10-30 14:44:03');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `contact_no`, `password_hash`, `role`, `sub_role`, `is_active`, `signature`, `created_at`, `comments`, `updated_at`) VALUES
(6, 'Admin', 'fsed@dmin.gov', NULL, '$2y$10$WqEMqOM50RJeSxq4vOSCLOaQnzwyoAFY5SqNWLudrnUL/pdNH3rpW', 'Administrator', 'Admin_Assistant', 1, NULL, '2025-09-01 15:25:26', NULL, NULL),
(7, 'Inspector I', 'fsed@ins.gov', NULL, '$2y$10$hQeVh1Jl/fdyenb46J0vSe20J.2Wl82s8G6fGlBZKQiw1csivGP4a', 'Inspector', 'Inspector', 1, 'SIGN-7-Inspector.png', '2025-09-01 16:36:25', NULL, NULL),
(8, 'Juan Discaya', 'jd@gmail.com', NULL, '$2y$10$Pjooiw5GNSL8fv9pgRAfPuCb3X6swOl/g9E4dO0m4mCGAc7CMgbKe', 'Client', 'Client', 1, 'SIGN-8-Client.png', '2025-09-14 17:19:44', NULL, '2025-10-26 19:30:46'),
(9, 'Coca Cola Owner', 'coca@fsic.gov', NULL, '$2y$10$mvO23QdwMgvsf9V/HQOn9O8JmUm4EjaU.Q7E5rlmtU/N/sSFDYDpi', 'Client', 'Client', 1, 'SIGN-9-Client.png', '2025-09-30 16:21:51', NULL, '2025-10-30 00:58:28'),
(10, 'Inspector Gadget', 'insg@fsic.gov', NULL, '$2y$10$KLW59IUCuH9D7SAFBQQSA.zKoI.uV0G3I46TT0C9ZhqBuybkGkahe', 'Inspector', 'Inspector', 1, NULL, '2025-09-30 19:06:32', NULL, NULL),
(11, 'Oas Com', 'oas@gmail.com', NULL, '$2y$10$bmZ3aW9RrtTUc8YBAXvsY.dRllNZCB8UzWHrCsMrf0Zmp9GfW0BOi', 'Client', 'Client', 1, 'SIGN-11-Client.png', '2025-10-07 12:52:07', NULL, NULL),
(12, 'Oas Poly', 'ops@g.c', NULL, '$2y$10$gkB2v6Ea181cEM0r.PEgD.pM99R.24OdjAsK8bEl8399G5fehE3V6', 'Client', 'Client', 1, 'SIGN-12-Client.png', '2025-10-07 13:45:12', NULL, '2025-10-20 00:38:26'),
(13, 'Cafe Locran', 'CafeLoc@fsic.gov', NULL, '$2y$10$dIBPaLTSiYXEEIuljz7gTuC/uhG2vuxF/aLy4QWn4flHlEYZlvySq', 'Client', 'Client', 1, 'SIGN-13-Client.png', '2025-10-07 14:09:22', NULL, '2025-10-16 22:08:34'),
(14, 'Sr Fire Officer Recommend Taka', 'sfo@fsic.gov', NULL, '$2y$10$bMwshjR.44b8ay6cIe3GbOE3.arGITWyvu2O5FYcqyn0gqfXAE4Gy', 'Administrator', 'Fire Marshall', 1, 'SIGN-14-Administrator.png', '2025-10-07 15:49:22', NULL, '2025-10-24 11:33:45'),
(15, 'sr Fire Officer Approvado Taka', 'approver@fsic.gov', NULL, '$2y$10$uRZqZ4OEJipjfbH9V.56TuSoHFIllVXIFXZPCUwShvjlzRoZ4chAC', 'Administrator', 'Approver', 1, 'SIGN-15-Administrator.png', '2025-10-07 15:50:06', NULL, '2025-11-01 10:20:33'),
(16, 'Sr Fire Officer Inspecsyon Taka', 'inspector@fsic.gov', NULL, '$2y$10$ksXs6z/.4rN.dGMFwQvUseJ8dlwu2A4z.YDo9QLIEvjeNVROHUQK6', 'Inspector', 'Inspector', 1, 'SIGN-16-Inspector.png', '2025-10-07 15:51:15', NULL, '2025-11-01 10:15:16'),
(17, 'Sr. Fire Office Juan Dela Cruz', 'sfojdc@fsic.gov', NULL, '$2y$10$gsav8484tQ2DMGT3Cud7geeaIQuQ.lWW2MBtS6sKLqFxo.AHlHs.i', 'Inspector', 'Inspector', 1, NULL, '2025-10-12 04:10:16', NULL, NULL),
(18, 'Fire Marshal', 'fm@fsic.gov', NULL, '$2y$10$Ge7niJfrXhkt4PZIsUla/uRmcA7GT4Hsp1YiGulK5vtWBLMxq5Njm', 'Administrator', 'Fire Marshall', 1, 'SIGN-18-Administrator.png', '2025-10-21 12:47:07', NULL, '2025-11-01 10:13:21'),
(19, 'admin assist', 'aa@fsic.gov', NULL, '$2y$10$Zklfsa0wjhBadMX1Zbsam.efSXc6v0QEUka02X73sX/22JISg1nz.', 'Administrator', 'Admin_Assistant', 1, NULL, '2025-10-21 12:57:20', NULL, NULL),
(20, 'Gabbi Bistro', 'gb@fsic.gov', NULL, '$2y$10$M9CeLFHZmwTxblB6.QQNLulzO7iNxz8PdglyJVQegR7vXrhvWN0k.', 'Client', 'Client', 1, NULL, '2025-10-30 20:11:48', NULL, NULL),
(21, 'Admin Assistant I', 'testadmin@gmail.com', NULL, '$2y$10$E7/SewvjuSaVAXKloDTIg.jM.hWLE50nAXnPfuD.ycVm9NrFC8VIC', 'Client', 'Client', 1, NULL, '2025-11-02 13:50:34', NULL, NULL),
(22, 'Pande Marino', 'pm@fsic.gov', NULL, '$2y$10$ZrwRUmAx8Pi37K3ee3Ou5.E0rJzLyHOZOtwzjHTuhUuvjaE7U7Iae', 'Client', 'Client', 1, NULL, '2025-11-02 20:08:09', NULL, NULL),
(23, 'Shan@fsic.gov', 's@fsic.gov', NULL, '$2y$10$Z2TDpCFYfp2rDzXKtR6jQef6JJE0ZGuAXe.YIuk/izGTybUsSJi7.', 'Client', 'Client', 1, NULL, '2025-11-02 20:11:20', NULL, NULL);

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
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `idx_doc_insp` (`inspection_id`);

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
-- Indexes for table `inspection_recommendations`
--
ALTER TABLE `inspection_recommendations`
  ADD PRIMARY KEY (`rec_id`),
  ADD KEY `idx_rec_insp` (`inspection_id`);

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
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `checklist_item_select_options`
--
ALTER TABLE `checklist_item_select_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `checklist_sections`
--
ALTER TABLE `checklist_sections`
  MODIFY `checklist_section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_info`
--
ALTER TABLE `general_info`
  MODIFY `gen_info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `inspections`
--
ALTER TABLE `inspections`
  MODIFY `inspection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inspection_recommendations`
--
ALTER TABLE `inspection_recommendations`
  MODIFY `rec_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspection_responses`
--
ALTER TABLE `inspection_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `inspection_schedule`
--
ALTER TABLE `inspection_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `map_saved_location`
--
ALTER TABLE `map_saved_location`
  MODIFY `loc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `nature_of_inspection`
--
ALTER TABLE `nature_of_inspection`
  MODIFY `noi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `system_config`
--
ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
