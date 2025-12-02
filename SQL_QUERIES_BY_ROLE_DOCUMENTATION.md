# Inspection Schedule Queries by Role - Documentation

## Overview
Each role has a specialized SQL query that fetches inspection schedules relevant to their workflow. **All queries prioritize PENDING items at the top of the list.**

---

## Sorting Strategy

### Priority Order (All Roles):
```
PENDING (top) → ACKNOWLEDGED/RECOMMENDED → COMPLETED (bottom)
```

Each role uses a CASE statement to assign numeric priorities:
- **0** = Pending (highest priority, shows first)
- **1** = In Progress / Acknowledged / Recommended
- **2** = Completed
- **3** = Unknown status

Then sorted by `scheduled_date DESC` as secondary sort.

---

## Role Breakdown

### 1. **CLIENT ROLE**
**Purpose:** View their own inspection schedules and acknowledgments

**Key Fields:**
- `HasClientAck` - Whether client has acknowledged
- `building_name`, `owner_name` - Property details
- `inspection_sched_status` - Pending/In Progress/Completed

**Sorting:**
```sql
ORDER BY 
    CASE 
        WHEN ins.inspection_sched_status = 'Pending' THEN 0
        WHEN ins.inspection_sched_status = 'In Progress' THEN 1
        WHEN ins.inspection_sched_status = 'Completed' THEN 2
        ELSE 3
    END ASC,
    ins.scheduled_date DESC
```

**Filter:** `g.owner_id = [USER_ID]`

---

### 2. **INSPECTOR ROLE**
**Purpose:** View assigned inspections to conduct

**Key Fields:**
- `hasInspectorAck` - Inspector acknowledgment status (0 = Pending, 1 = Acknowledged)
- `dateInspectorAck` - When inspector acknowledged
- `has_defects`, `inspection_status` - Inspection results

**Sorting:**
```sql
ORDER BY 
    CASE 
        WHEN ins.hasInspectorAck = 0 THEN 0      -- Pending Ack (top)
        WHEN ins.hasInspectorAck = 1 THEN 1      -- Acknowledged
        ELSE 2
    END ASC,
    ins.scheduled_date DESC
```

**Filter:** `inspector.user_id = [USER_ID]`

---

### 3. **RECOMMENDING APPROVER (Chief FSES)**
**Purpose:** Review and recommend inspections

**Key Fields:**
- `hasRecommendingApproval` - Recommendation status (null/0 = Pending, 1 = Recommended)
- `dateRecommendedForApproval` - When recommended
- `has_defects` - Whether defects found

**Sorting:**
```sql
ORDER BY 
    CASE 
        WHEN ins.hasRecommendingApproval IS NULL OR ins.hasRecommendingApproval = 0 THEN 0  -- Pending
        WHEN ins.hasRecommendingApproval = 1 THEN 1  -- Recommended
        ELSE 2
    END ASC,
    ins.scheduled_date DESC
```

**Filters:**
- Inspector must have acknowledged: `ins.hasInspectorAck = 1`
- Scheduled date >= today: `ins.scheduled_date >= CURDATE()`

---

### 4. **APPROVER (Fire Marshal)**
**Purpose:** Final approval of inspections

**Key Fields:**
- `hasFinalApproval` - Final approval status (null/0 = Pending, 1 = Approved)
- `dateFinalApproval` - When approved
- `compliance_rate` - Compliance percentage

**Sorting:**
```sql
ORDER BY 
    CASE 
        WHEN ins.hasFinalApproval IS NULL OR ins.hasFinalApproval = 0 THEN 0  -- Pending
        WHEN ins.hasFinalApproval = 1 THEN 1  -- Approved
        ELSE 2
    END ASC,
    ins.scheduled_date DESC
```

**Filters:**
- Recommending approval must exist: `ins.hasRecommendingApproval = 1`
- Scheduled date >= today: `ins.scheduled_date >= CURDATE()`

---

### 5. **ADMIN ASSISTANT ROLE**
**Purpose:** Comprehensive view of all inspections for administrative management

**Key Fields:** All fields from all roles (complete data)

**Sorting:**
```sql
ORDER BY 
    CASE 
        WHEN ins.inspection_sched_status = 'Pending' THEN 0         -- Pending (top)
        WHEN ins.inspection_sched_status = 'In Progress' THEN 1    -- In Progress
        WHEN ins.inspection_sched_status = 'Completed' THEN 2      -- Completed
        ELSE 3
    END ASC,
    ins.scheduled_date DESC
```

**Filter:** None (sees all records)

---

## Status Field Values

| Field | Values | Meaning |
|-------|--------|---------|
| `inspection_sched_status` | 'Pending', 'In Progress', 'Completed' | Overall schedule status |
| `HasClientAck` | 'Y', 'N' | Client acknowledgment |
| `hasInspectorAck` | 0, 1, NULL | Inspector acknowledgment |
| `hasRecommendingApproval` | 0, 1, NULL | Recommender approval |
| `hasFinalApproval` | 0, 1, NULL | Final approval |
| `has_defects` | 0, 1 | Inspection result |

---

## Query Optimization Tips

1. **Indexes:** Ensure these columns have indexes for performance:
   ```sql
   CREATE INDEX idx_owner_id ON general_info(owner_id);
   CREATE INDEX idx_inspector_id ON inspection_schedule(inspector_id);
   CREATE INDEX idx_schedule_status ON inspection_schedule(inspection_sched_status);
   CREATE INDEX idx_scheduled_date ON inspection_schedule(scheduled_date);
   CREATE INDEX idx_hasInspectorAck ON inspection_schedule(hasInspectorAck);
   CREATE INDEX idx_hasRecommendingApproval ON inspection_schedule(hasRecommendingApproval);
   CREATE INDEX idx_hasFinalApproval ON inspection_schedule(hasFinalApproval);
   ```

2. **Pagination:** Add `OFFSET` for large datasets:
   ```sql
   LIMIT 50 OFFSET 0  -- First 50 records
   LIMIT 50 OFFSET 50 -- Next 50 records
   ```

3. **Search:** Add to WHERE clause:
   ```sql
   AND (
       ins.order_number LIKE '%search%'
       OR g.building_name LIKE '%search%'
       OR g.owner_name LIKE '%search%'
   )
   ```

---

## Implementation in PHP

Replace placeholders in queries:
```php
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$sql = match($role) {
    'Client' => "... WHERE g.owner_id = $user_id ...",
    'Inspector' => "... WHERE inspector.user_id = $user_id ...",
    'Administrator' => "... WHERE ... (role-specific)" ,
    default => "No access"
};

$result = mysqli_query($CONN, $sql);
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);
```

---

## Testing

To test each query:
1. Replace `[USER_ID]` placeholders with actual user IDs
2. Run in MySQL client or phpMyAdmin
3. Verify "Pending" items appear at top
4. Check row counts match expected data

Example users:
- Client: user_id = 26 (owner_id)
- Inspector: user_id = 25
- Admin: user_id = 6

---

**Last Updated:** December 3, 2025
