$(document).ready(function() {
    // Initialize count display with specified selector
    initializeRoleBasedCounts('#counts');
});

function initializeRoleBasedCounts(containerSelector) {
    console.log('🔍 Initializing role-based counts...');
    
    // Check session using callback
    checkSession(
        function(user) {
            console.log('📊 User data:', user);
            
            if (user && user.role) {
                // Get the role label using role and subrole
                const userRole = getRoleLabel(user.role, user.subrole);
                console.log('👤 User role:', userRole);
                
                // Fetch data based on role
                fetchRoleBasedData(containerSelector, userRole);
            } else {
                console.warn('⚠️ User data incomplete');
            }
        },
        function() {
            console.warn('⚠️ User not logged in');
        }
    );
}

async function fetchRoleBasedData(containerSelector, userRole) {
    try {
        console.log('📥 Fetching data for role:', userRole);
        
        const response = await fetchData('../includes/_inspection_schedule.php', 'POST', {});
        console.log('✅ API Response:', response);
        
        if (response.success) {
            console.log('📦 Data received, count:', response.data.length);
            buildRoleBasedDisplay(containerSelector, response.data, userRole, "col-6 col-sm-4 col-md-4 col-lg-2 mb-3 border-0");
        } else {
            console.error('❌ API returned success=false:', response.message);
        }
    } catch (error) {
        console.error('❌ Error fetching data:', error);
    }
}

function buildRoleBasedDisplay(containerSelector, data, userRole, classes = "col-6") {
    console.log('🎨 Building display for role:', userRole);
    console.log('📍 Container selector:', containerSelector);
    
    let displayHTML = '';
    
    // Define role-specific count logic
    const roleCounts = getRoleCounts(data, userRole);
    console.log('📊 Role counts:', roleCounts);
    
    // Build display based on role
    switch (userRole) {
        case 'Inspector':
            displayHTML = buildInspectorDisplay(roleCounts, classes);
            break;
        case 'Recommending Approver':
            displayHTML = buildRecommendingApproverDisplay(roleCounts, classes);
            break;
        case 'Approver':
            displayHTML = buildApproverDisplay(roleCounts, classes);
            break;
        case 'Admin_Assistant':
            displayHTML = buildAdminDisplay(roleCounts, classes);
            break;
        default:
            displayHTML = '<p>Role not recognized</p>';
            console.warn('⚠️ Role not recognized:', userRole);
    }
    
    console.log('✏️ HTML built, inserting into DOM...');
    // Insert into specified container
    $(containerSelector).html(displayHTML);
    console.log('✅ Display rendered successfully');
}

function getRoleCounts(data, userRole) {
    let counts = {};
    //userRole = Role Label

    if (!Array.isArray(data) || data.length === 0) {
        return initializeCountsForRole(userRole);
    }
    
    // Get current year
    const currentYear = new Date().getFullYear();
    
    // Initialize counts based on role
    counts = initializeCountsForRole(userRole);
    
    $.each(data, function(index, item) {
        // Check if item is from current year
        let itemYear = null;
        
        if (item.scheduled_date) {
            itemYear = new Date(item.scheduled_date).getFullYear();
        } else if (item.created_at) {
            itemYear = new Date(item.created_at).getFullYear();
        }
        
        // Skip if not from current year
        if (itemYear !== currentYear) {
            return; // continue to next item
        }
        
         const scheduledDate = new Date(item.scheduled_date);
                const today = new Date();
                const oneDayAgo = new Date(today);
                oneDayAgo.setDate(oneDayAgo.getDate() - 1);
                
        if (userRole === 'Inspector') {
            // Inspector counts
            if (item.inspection_status === 'Completed') {
                counts.completed_inspection++;
            } else if ((item.sched_status === 'Scheduled' || item.sched_status === 'Pending') && item.sched_hasFinalApproval === 1) {
                // Check if inspection is overdue (past 1 day)
               
                if (scheduledDate < oneDayAgo) {
                    counts.overdue_inspection++;
                } 
                
                if (item.sched_HasClientAck === "Y" && item.sched_hasInspectorAck === 0) {
                    counts.pending_inspection++;
                }
            }
        } 
        else if (userRole === 'Chief FSES' || userRole === 'Recommending Approver') {
            // Chief FSES / Recommending Approver counts
            // Pending Schedule Acknowledgement
            if (item.sched_hasRecommendingApproval === 0 && item.has_defects === 0 && (item.sched_status !== 'Archived' && item.sched_status !== 'Cancelled' && item.sched_status !== 'In Progress')) {
                counts.pending_ack++;
            }
            if (item.sched_hasRecommendingApproval === 1) {
                counts.complete_ack++;
            }
            
            // Pending/Completed FSIC Recommendation
            if (item.fsic_hasRecoApproval === 0 && item.inspection_status === 'Completed') {
                counts.pending_fsic_rec++;
            } 
            if (item.fsic_hasRecoApproval === 1 && item.inspection_status === 'Completed') {
                counts.completed_fsic_rec++;
            }
        } 
        else if (userRole === 'Fire Marshall' || userRole === 'Approver') {
            // Fire Marshal / Approver counts
            // Pending Schedule Acknowledgement
            if (item.sched_hasFinalApproval === 0 && (item.sched_status !== 'Archived' && item.sched_status !== 'Cancelled')) {
                counts.pending_ack++;
            } 
            if (item.sched_hasFinalApproval === 1){
                counts.complete_ack++;
            }
            
            // Pending/Completed FSIC Final Approval
            if ((item.fsic_hasFinalApproval === 0 || item.fsic_hasFinalApproval === null) && item.inspection_status === 'Completed') {
                counts.pending_fsic_approval++;
            } 
            if (item.fsic_hasFinalApproval === 1 && item.inspection_status === 'Completed'){
                counts.completed_fsic_approval++;
            }
        }
        else if (userRole === 'Admin_Assistant') {
            // Admin Assistant dashboard counts
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const scheduledDate = new Date(item.scheduled_date);
            scheduledDate.setHours(0, 0, 0, 0);
            
            // Total inspections due for current date
            if (scheduledDate.getTime() === today.getTime()) {
                counts.total_inspections_due++;
            }
            
            // Overdue inspections (past 1 day that need approval)
            const oneDayAgo = new Date(today);
            oneDayAgo.setDate(oneDayAgo.getDate() - 1);
            if (scheduledDate < oneDayAgo && item.sched_hasFinalApproval === 1) {
                counts.overdue_inspections++;
            }
            
            // Pending inspection schedule for Recommendation (sched_hasRecommendingApproval == 0 and not overdue)
            if (item.sched_hasRecommendingApproval === 0 && scheduledDate >= oneDayAgo && item.sched_hasInspectorAck == 1) {
                counts.pending_recommendation++;
            }
            
            // Pending inspection schedule for Approval (sched_hasFinalApproval == 0 and sched_hasRecommendingApproval == 1)
            if (item.sched_hasFinalApproval === 0 && item.sched_hasRecommendingApproval === 1) {
                counts.pending_approval++;
            }
            
            // Pending FSIC Recommendations (fsic_hasRecoApproval == 0 and completed_at is not null and has_defects == 0)
            if (item.fsic_hasRecoApproval === 0 && item.completed_at !== null && item.has_defects === 0) {
                counts.pending_fsic_rec++;
            }
            
            // Pending FSIC Approvals (fsic_hasFinalApproval == 0 and fsic_hasRecoApproval == 1 and has_defects == 0)
            if (item.fsic_hasFinalApproval === 0 && item.fsic_hasRecoApproval === 1 && item.has_defects === 0 && item.inspection_status === 'Completed') {
                counts.pending_fsic_approval++;
            }
        }
    });
    
    return counts;
}

function initializeCountsForRole(userRole) {
    let counts = {};
    
    if (userRole === 'Inspector') {
        counts = {
            pending_inspection: 0,
            completed_inspection: 0,
            overdue_inspection: 0
        };
    } else if (userRole === 'Chief FSES' || userRole === 'Recommending Approver') {
        counts = {
            pending_ack: 0,
            complete_ack: 0,
            pending_fsic_rec: 0,
            completed_fsic_rec: 0
        };
    } else if (userRole === 'Fire Marshal' || userRole === 'Approver') {
        counts = {
            pending_ack: 0,
            complete_ack: 0,
            pending_fsic_approval: 0,
            completed_fsic_approval: 0
        };
    } else if (userRole === 'Admin_Assistant') {
        counts = {
            total_inspections_due: 0,
            overdue_inspections: 0,
            pending_recommendation: 0,
            pending_approval: 0,
            pending_fsic_rec: 0,
            pending_fsic_approval: 0
        };
    }
    
    return counts;
}

function buildInspectorDisplay(counts, classes) {
    return `
        <div class="row">
            <div class="${classes} pending-card">
                <div class="card border-0 shadow text-center"> 
                    <div class="card-header bg-navy text-gold">
                        <h6 class="card-title text-uppercase">Pending Inspection</h6>
                    </div>
                    <div class="card-body text-center content-align-center">
                        <h3 class="count-number">${counts.pending_inspection}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} overdue-card">
                <div class="card border-0 shadow text-center"> 
                    <div class="card-header bg-danger text-white">
                        <h6 class="card-title text-uppercase">Overdue For Inspection </h6>
                    </div>
                    <div class="card-body text-center content-align-center">
                        <h3 class="count-number">${counts.overdue_inspection}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} completed-card">
                <div class="card border-0 shadow text-center"> 
                    <div class="card-header bg-navy text-gold">
                        <h6 class="card-title text-uppercase">Completed Inspection</h6>
                    </div>
                    <div class="card-body text-center content-align-center">
                        <h3 class="count-number">${counts.completed_inspection}</h3>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function buildRecommendingApproverDisplay(counts, classes) {
    return `
        <div class="row">
            <div class="${classes} ack-pending">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-info text-white">
                        <h6 class="card-title text-uppercase">Pending I.O. Recomendations</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.pending_ack}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} ack-complete">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title text-uppercase">Completed I.O. Recommendations</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.complete_ack}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} fsic-pending">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-warning text-dark">
                        <h6 class="card-title text-uppercase">Pending FSIC Recommendations</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.pending_fsic_rec}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} fsic-completed">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title text-uppercase">Completed FSIC Recommendations</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.completed_fsic_rec}</h3>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function buildApproverDisplay(counts, classes) {
    return `
        <div class="row">
            <div class="${classes} ack-pending">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-info text-white">
                        <h6 class="card-title text-uppercase">Pending I.O. Approval</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.pending_ack}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} ack-complete">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title text-uppercase">Completed I.O. Approval</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.complete_ack}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} fsic-pending">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-warning text-dark">
                        <h6 class="card-title text-uppercase">Pending FSIC Approval</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.pending_fsic_approval}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} fsic-completed">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title text-uppercase">Completed FSIC Approval</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.completed_fsic_approval}</h3>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function buildAdminDisplay(counts, classes) {
    return `
        <div class="row">
            <div class="${classes} total-due">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-navy-dark text-white">
                        <h6 class="card-title text-uppercase">Total Scheduled<br>Due Today</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.total_inspections_due}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} overdue">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-danger text-white">
                        <h6 class="card-title text-uppercase">Overdue <br> Inspections</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.overdue_inspections}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} pending-rec">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-warning text-dark">
                        <h6 class="card-title text-uppercase">
                               Pending I.O. Recommendation
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.pending_recommendation}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} pending-app">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-warning text-dark">
                        <h6 class="card-title text-uppercase">
                        Pending I.O. <br> Approval
                        
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.pending_approval}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} pending-fsic-rec">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-navy text-white">
                        <h6 class="card-title text-uppercase">Pending FSIC <br> Recommendation</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.pending_fsic_rec}</h3>
                    </div>
                </div>
            </div>
            <div class="${classes} pending-fsic-app">
                <div class="card  border-0 shadow text-center"> 
                    <div class="card-header bg-navy text-white">
                        <h6 class="card-title text-uppercase">Pending FSIC <br>Approval</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="count-number">${counts.pending_fsic_approval}</h3>
                    </div>
                </div>
            </div>
        </div>
    `;
}