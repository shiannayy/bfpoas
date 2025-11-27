
-- ======================================================
-- 1. General Information
-- ======================================================
CREATE TABLE general_info (
    gen_info_id INT AUTO_INCREMENT PRIMARY KEY,
    building_name VARCHAR(255),
    business_name VARCHAR(255),
    address TEXT,
    nature_of_business VARCHAR(255),
    owner_name VARCHAR(255),
    owner_contact VARCHAR(50),
    representative_name VARCHAR(255),
    representative_contact VARCHAR(50),
    storeys INT,
    building_height DECIMAL(6,2),
    portion_occupied VARCHAR(255),
    area_per_floor DECIMAL(10,2),
    total_floor_area DECIMAL(10,2),
    building_permit_no VARCHAR(100),
    building_permit_date DATE,
    occupancy_permit_no VARCHAR(100),
    occupancy_permit_date DATE,
    latest_fsic_no VARCHAR(100),
    latest_fsic_date DATE,
    fire_drill_cert_no VARCHAR(100),
    fire_drill_date DATE,
    ntcv_no VARCHAR(100),
    ntcv_date DATE,
    insurance_company VARCHAR(255),
    insurance_policy_no VARCHAR(100),
    insurance_date DATE,
    mayor_permit_no VARCHAR(100),
    mayor_permit_date DATE,
    electrical_cert_no VARCHAR(100),
    electrical_cert_date DATE,
    other_info TEXT
);

-- ======================================================
-- 2. Building Construction
-- ======================================================
CREATE TABLE building_construction (
    building_cons_id INT AUTO_INCREMENT PRIMARY KEY,
    general_info_id INT,
    beams VARCHAR(255),
    columns1 VARCHAR(255),
    flooring VARCHAR(255),
    exterior_walls VARCHAR(255),
    corridor_walls VARCHAR(255),
    partitions1 VARCHAR(255),
    main_stair VARCHAR(255),
    windows VARCHAR(255),
    ceiling VARCHAR(255),
    main_door VARCHAR(255),
    trusses VARCHAR(255),
    roof VARCHAR(255),
    FOREIGN KEY (general_info_id) REFERENCES general_info(gen_info_id) ON DELETE CASCADE
);

-- ======================================================
-- 3. Sectional Occupancy
-- ======================================================
CREATE TABLE sectional_occupancy (
    sectional_occupancy_id INT AUTO_INCREMENT PRIMARY KEY,
    general_info_id INT,
    floor_no INT,
    section_name VARCHAR(255),
    usage1 TEXT,
    FOREIGN KEY (general_info_id) REFERENCES general_info(gen_info_id) ON DELETE CASCADE
);

-- ======================================================
-- 4. Classification
-- ======================================================
CREATE TABLE classification (
    classification_id INT AUTO_INCREMENT PRIMARY KEY,
    general_info_id INT,
    occupancy_class VARCHAR(50),
    occupant_load INT,
    renovations BOOLEAN,
    renovation_details TEXT,
    underground BOOLEAN,
    windowless BOOLEAN,
    FOREIGN KEY (general_info_id) REFERENCES general_info(gen_info_id) ON DELETE CASCADE
);

-- ======================================================
-- 5. Exits / Means of Egress
-- ======================================================
CREATE TABLE exits (
    exits_id INT AUTO_INCREMENT PRIMARY KEY,
    general_info_id INT,
    exit_type ENUM('Vertical','Horizontal','Ramp','Refuge'),
    location TEXT,
    width DECIMAL(5,2),
    construction VARCHAR(255),
    rating VARCHAR(100),
    has_fire_door BOOLEAN,
    has_self_closing BOOLEAN,
    has_vision_panel BOOLEAN,
    swing_direction_correct BOOLEAN,
    pressurized BOOLEAN,
    last_tested DATE,
    FOREIGN KEY (general_info_id) REFERENCES general_info(gen_info_id) ON DELETE CASCADE
);

-- ======================================================
-- 6. Lighting & Signs
-- ======================================================
CREATE TABLE lighting_signs (
    lighting_signs_id INT AUTO_INCREMENT PRIMARY KEY,
    general_info_id INT,
    emergency_lights BOOLEAN,
    emergency_light_source VARCHAR(50),
    emergency_light_units INT,
    exit_signs BOOLEAN,
    exit_sign_location TEXT,
    exit_sign_power VARCHAR(50),
    directional_signs BOOLEAN,
    safety_signs TEXT,
    FOREIGN KEY (general_info_id) REFERENCES general_info(gen_info_id) ON DELETE CASCADE
);

-- ======================================================
-- 7. Fire Protection Features
-- ======================================================
CREATE TABLE fire_protection (
    fire_protection_id INT AUTO_INCREMENT PRIMARY KEY,
    general_info_id INT,
    vertical_openings_protected BOOLEAN,
    alarm_system_type VARCHAR(100),
    alarm_location TEXT,
    standpipe_type VARCHAR(50),
    extinguisher_type VARCHAR(100),
    extinguisher_units INT,
    sprinkler_system BOOLEAN,
    sprinkler_type VARCHAR(100),
    firewall_required BOOLEAN,
    firewall_provided BOOLEAN,
    FOREIGN KEY (general_info_id) REFERENCES general_info(gen_info_id) ON DELETE CASCADE
);

-- ======================================================
-- 8. Building Service Equipment
-- ======================================================
CREATE TABLE service_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    general_info_id INT,
    boiler BOOLEAN,
    boiler_fuel VARCHAR(50),
    boiler_capacity VARCHAR(100),
    generator BOOLEAN,
    generator_capacity VARCHAR(100),
    refuse_facility BOOLEAN,
    electrical_hazard BOOLEAN,
    mechanical_hazard BOOLEAN,
    elevators INT,
    has_firemans_elevator BOOLEAN,
    FOREIGN KEY (general_info_id) REFERENCES general_info(gen_info_id) ON DELETE CASCADE
);

-- ======================================================
-- 9. Hazardous Areas
-- ======================================================
CREATE TABLE hazardous_areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    general_info_id INT,
    area_type VARCHAR(100),
    separation_rated BOOLEAN,
    fire_protection_type VARCHAR(100),
    hazardous_materials TEXT,
    storage_permit BOOLEAN,
    clearance_from_ceiling DECIMAL(4,2),
    FOREIGN KEY (general_info_id) REFERENCES general_info(gen_info_id) ON DELETE CASCADE
);

-- ======================================================
-- 10. Operating Features
-- ======================================================
CREATE TABLE operating_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    general_info_id INT,
    fire_brigade BOOLEAN,
    fire_safety_seminar BOOLEAN,
    employees_trained BOOLEAN,
    fire_drill BOOLEAN,
    fire_drill_date DATE,
    FOREIGN KEY (general_info_id) REFERENCES general_info(gen_info_id) ON DELETE CASCADE
);

-- ======================================================
-- 11. Defects & Deficiencies
-- ======================================================
CREATE TABLE defects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    general_info_id INT,
    defect_description TEXT,
    photo_url VARCHAR(255),
    sketch_url VARCHAR(255),
    FOREIGN KEY (general_info_id) REFERENCES general_info(gen_info_id) ON DELETE CASCADE
);

-- ======================================================
-- 12. Recommendations
-- ======================================================
CREATE TABLE recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    general_info_id INT,
    recommendation_text TEXT,
    FOREIGN KEY (general_info_id) REFERENCES general_info(gen_info_id) ON DELETE CASCADE
);

-- ======================================================
-- 13. Approvals
-- ======================================================
CREATE TABLE approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    general_info_id INT,
    owner_signature VARCHAR(255),
    inspector_signature VARCHAR(255),
    team_leader_signature VARCHAR(255),
    chief_signature VARCHAR(255),
    fire_marshal_signature VARCHAR(255),
    approved BOOLEAN,
    approval_date DATE,
    FOREIGN KEY (general_info_id) REFERENCES general_info(gen_info_id) ON DELETE CASCADE
);
