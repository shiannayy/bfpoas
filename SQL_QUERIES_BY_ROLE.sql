-- =====================================================
-- INSPECTION SCHEDULE QUERIES BY ROLE
-- Pending items sorted to top of list
-- =====================================================

-- =====================================================
-- 1. CLIENT ROLE - Views own inspection schedules
-- =====================================================
-- Status sorting: Pending (0) → In Progress (1) → Completed (2)
SELECT 
    ins.schedule_id,
    ins.order_number,
    ins.scheduled_date,
    ins.schedule_time,
    ins.inspection_sched_status AS status,
    g.building_name,
    g.owner_name,
    c.title AS checklist_title,
    ins.HasClientAck,
    u.full_name AS owner_full_name,
    inspector.full_name AS inspector_name,
    ins.proceed_instructions,
    ins.fsic_purpose,
    noi.noi_text AS noi_desc
FROM inspection_schedule ins
INNER JOIN checklists c ON ins.checklist_id = c.checklist_id
INNER JOIN general_info g ON ins.gen_info_id = g.gen_info_id
LEFT JOIN users u ON g.owner_id = u.user_id
LEFT JOIN users inspector ON ins.inspector_id = inspector.user_id
LEFT JOIN nature_of_inspection noi ON ins.noi_id = noi.noi_id
WHERE g.owner_id = [CLIENT_USER_ID]
ORDER BY 
    CASE 
        WHEN ins.inspection_sched_status = 'Pending' THEN 0
        WHEN ins.inspection_sched_status = 'In Progress' THEN 1
        WHEN ins.inspection_sched_status = 'Completed' THEN 2
        ELSE 3
    END ASC,
    ins.scheduled_date DESC
LIMIT 1000;

-- =====================================================
-- 2. INSPECTOR ROLE - Views assigned inspections
-- =====================================================
-- Status sorting: Pending Ack (0) → Acknowledged (1)
SELECT 
    ins.schedule_id,
    ins.order_number,
    ins.scheduled_date,
    ins.schedule_time,
    ins.inspection_sched_status AS status,
    g.building_name,
    g.owner_name,
    c.title AS checklist_title,
    ins.hasInspectorAck,
    ins.dateInspectorAck,
    u.full_name AS owner_full_name,
    inspector.full_name AS inspector_name,
    ins.proceed_instructions,
    noi.noi_text AS noi_desc,
    i.has_defects,
    i.status AS inspection_status,
    i.completed_at
FROM inspection_schedule ins
INNER JOIN checklists c ON ins.checklist_id = c.checklist_id
INNER JOIN general_info g ON ins.gen_info_id = g.gen_info_id
LEFT JOIN users u ON g.owner_id = u.user_id
LEFT JOIN users inspector ON ins.inspector_id = inspector.user_id
LEFT JOIN inspections i ON ins.schedule_id = i.schedule_id
LEFT JOIN nature_of_inspection noi ON ins.noi_id = noi.noi_id
WHERE inspector.user_id = [INSPECTOR_USER_ID]
ORDER BY 
    CASE 
        WHEN ins.hasInspectorAck = 0 THEN 0  -- Pending Acknowledgment
        WHEN ins.hasInspectorAck = 1 THEN 1  -- Acknowledged
        ELSE 2
    END ASC,
    ins.scheduled_date DESC
LIMIT 1000;

-- =====================================================
-- 3. RECOMMENDING APPROVER (CHIEF FSES) ROLE
-- =====================================================
-- Status sorting: Pending Recommendation (0) → Recommended (1)
SELECT 
    ins.schedule_id,
    ins.order_number,
    ins.scheduled_date,
    ins.schedule_time,
    ins.inspection_sched_status AS status,
    g.building_name,
    g.owner_name,
    c.title AS checklist_title,
    ins.hasInspectorAck,
    ins.hasRecommendingApproval,
    ins.dateRecommendedForApproval,
    u.full_name AS owner_full_name,
    inspector.full_name AS inspector_name,
    recommender.full_name AS recommending_approver,
    ins.proceed_instructions,
    noi.noi_text AS noi_desc,
    i.has_defects,
    i.status AS inspection_status
FROM inspection_schedule ins
INNER JOIN checklists c ON ins.checklist_id = c.checklist_id
INNER JOIN general_info g ON ins.gen_info_id = g.gen_info_id
LEFT JOIN users u ON g.owner_id = u.user_id
LEFT JOIN users inspector ON ins.inspector_id = inspector.user_id
LEFT JOIN users recommender ON ins.RecommendingApprover = recommender.user_id
LEFT JOIN inspections i ON ins.schedule_id = i.schedule_id
LEFT JOIN nature_of_inspection noi ON ins.noi_id = noi.noi_id
WHERE 
    ins.hasInspectorAck = 1
    AND ins.scheduled_date >= CURDATE()
ORDER BY 
    CASE 
        WHEN ins.hasRecommendingApproval IS NULL OR ins.hasRecommendingApproval = 0 THEN 0  -- Pending
        WHEN ins.hasRecommendingApproval = 1 THEN 1  -- Recommended
        ELSE 2
    END ASC,
    ins.scheduled_date DESC
LIMIT 1000;

-- =====================================================
-- 4. APPROVER (FIRE MARSHAL) ROLE
-- =====================================================
-- Status sorting: Pending Final Approval (0) → Approved (1)
SELECT 
    ins.schedule_id,
    ins.order_number,
    ins.scheduled_date,
    ins.schedule_time,
    ins.inspection_sched_status AS status,
    g.building_name,
    g.owner_name,
    c.title AS checklist_title,
    ins.hasRecommendingApproval,
    ins.hasFinalApproval,
    ins.dateFinalApproval,
    u.full_name AS owner_full_name,
    inspector.full_name AS inspector_name,
    approver.full_name AS final_approver,
    ins.proceed_instructions,
    noi.noi_text AS noi_desc,
    i.has_defects,
    i.compliance_rate
FROM inspection_schedule ins
INNER JOIN checklists c ON ins.checklist_id = c.checklist_id
INNER JOIN general_info g ON ins.gen_info_id = g.gen_info_id
LEFT JOIN users u ON g.owner_id = u.user_id
LEFT JOIN users inspector ON ins.inspector_id = inspector.user_id
LEFT JOIN users approver ON ins.FinalApprover = approver.user_id
LEFT JOIN inspections i ON ins.schedule_id = i.schedule_id
LEFT JOIN nature_of_inspection noi ON ins.noi_id = noi.noi_id
WHERE 
    ins.hasRecommendingApproval = 1
    AND ins.scheduled_date >= CURDATE()
ORDER BY 
    CASE 
        WHEN ins.hasFinalApproval IS NULL OR ins.hasFinalApproval = 0 THEN 0  -- Pending
        WHEN ins.hasFinalApproval = 1 THEN 1  -- Approved
        ELSE 2
    END ASC,
    ins.scheduled_date DESC
LIMIT 1000;

-- =====================================================
-- 5. ADMIN ASSISTANT ROLE - Views all schedules
-- =====================================================
-- Status sorting: All Pending → In Progress → Completed
SELECT 
    ins.schedule_id,
    ins.order_number,
    ins.scheduled_date,
    ins.schedule_time,
    ins.inspection_sched_status AS status,
    g.building_name,
    g.owner_name,
    c.title AS checklist_title,
    ins.HasClientAck,
    ins.hasInspectorAck,
    ins.hasRecommendingApproval,
    ins.hasFinalApproval,
    u.full_name AS owner_full_name,
    inspector.full_name AS inspector_name,
    recommender.full_name AS recommending_approver,
    approver.full_name AS final_approver,
    ins.proceed_instructions,
    noi.noi_text AS noi_desc,
    i.has_defects,
    i.status AS inspection_status,
    i.compliance_rate
FROM inspection_schedule ins
INNER JOIN checklists c ON ins.checklist_id = c.checklist_id
INNER JOIN general_info g ON ins.gen_info_id = g.gen_info_id
LEFT JOIN users u ON g.owner_id = u.user_id
LEFT JOIN users inspector ON ins.inspector_id = inspector.user_id
LEFT JOIN users recommender ON ins.RecommendingApprover = recommender.user_id
LEFT JOIN users approver ON ins.FinalApprover = approver.user_id
LEFT JOIN inspections i ON ins.schedule_id = i.schedule_id
LEFT JOIN nature_of_inspection noi ON ins.noi_id = noi.noi_id
ORDER BY 
    CASE 
        WHEN ins.inspection_sched_status = 'Pending' THEN 0
        WHEN ins.inspection_sched_status = 'In Progress' THEN 1
        WHEN ins.inspection_sched_status = 'Completed' THEN 2
        ELSE 3
    END ASC,
    ins.scheduled_date DESC
LIMIT 1000;

-- =====================================================
-- NOTES ON STATUS VALUES
-- =====================================================
-- inspection_sched_status: 'Pending', 'In Progress', 'Completed'
-- HasClientAck: 'Y'/'N'
-- hasInspectorAck: 0/1 (null = pending)
-- hasRecommendingApproval: 0/1 (null = pending)
-- hasFinalApproval: 0/1 (null = pending)
-- 
-- REPLACE [CLIENT_USER_ID], [INSPECTOR_USER_ID] with actual user_id
-- =====================================================
