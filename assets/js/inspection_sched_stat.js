$(document).ready(function(){
    loadInspectionSchedules(window.currentUser);
});

function loadInspectionSchedules(user = {}, search = null, rpp = 25, sortBy = null) {
    const $listContainer = $("#inspectionListContainer"); // Change this selector
    const $pagination = $(".pagination");
    const recordsPerPage = rpp;
    let currentPage = 1;
    let allData = [];

    // -------------------------------------------
    // RENDER LIST GROUP (REPLACES TABLE)
    // -------------------------------------------
    function renderListGroup(page) {
        $listContainer.html("");
        $pagination.html("");

        const start = (page - 1) * recordsPerPage;
        const end = start + recordsPerPage;
        const pageData = allData.slice(start, end);

        if (pageData.length === 0) {
            $listContainer.html(`
                <div class="list-group-item text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 text-muted mb-2"></i>
                    <p class="mb-0">No inspection schedules found.</p>
                </div>
            `);
            return;
        }

        let listHtml = '';
        
        pageData.forEach(item => {
            const status = item.sched_status || "";
            const remarks = item.sched_remarks || "";
            const hasdefects = remarks.toLowerCase().includes("has defects");
            
            // Determine status badge color
            let statusBadgeClass = "bg-secondary";
            let statusText = status;
            
            if (status === "Completed") {
                statusBadgeClass = hasdefects ? "bg-warning" : "bg-success";
                statusText = hasdefects ? "Completed (Has Defects)" : "Completed";
            } else if (status === "Rescheduled") {
                statusBadgeClass = "bg-info";
            } else if (status === "Cancelled") {
                statusBadgeClass = "bg-danger";
            }
            
            // Format date
            const scheduledDate = formatScheduleDateOnly(item.scheduled_date);
            const scheduledTime = item.schedule_time || "";
            const scheduledFull = `${scheduledDate} at ${scheduledTime}`;
            
            // Generate buttons
            const roleLabel = getRoleLabel(user.role || "", user.subrole || "");
            let buttons = generateScheduleButtons(item, roleLabel, status);
            
            // Create list item
            listHtml += `
                <div class="list-group-item list-group-item-action p-3 mb-2 border-start-0 border-end-0 ${statusBadgeClass} bg-opacity-10">
                    <!-- Header row -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge ${statusBadgeClass} mb-2">${statusText}</span>
                            <h6 class="mb-1">${item.order_number || "No Order Number"}</h6>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                ${buttons}
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Main content -->
                    <div class="row">
                        <!-- Left column - Primary info -->
                        <div class="col-md-7">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-building me-2 text-muted"></i>
                                <strong class="me-2">Establishment:</strong>
                                <span>${item.proceed_instructions || "Not specified"}</span>
                            </div>
                            
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-person me-2 text-muted"></i>
                                <strong class="me-2">Owner:</strong>
                                <span>${item.owner_full_name || "Not specified"}</span>
                            </div>
                            
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-calendar-event me-2 text-muted"></i>
                                <strong class="me-2">Scheduled:</strong>
                                <span class="${getScheduleClass(item.scheduled_date)} px-2 rounded">
                                    ${scheduledFull}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Right column - Status indicators -->
                        <div class="col-md-5">
                            <div class="row g-2">
                                <!-- Client Acknowledgement -->
                                <div class="col-6">
                                    <div class="text-center p-1 rounded ${item.HasClientAck === 'Y' ? 'bg-success bg-opacity-25' : 'bg-danger bg-opacity-25'}">
                                        <small class="d-block fw-bold">Client</small>
                                        <small class="d-block">${item.HasClientAck === 'Y' ? '✓ Acknowledged' : 'Pending'}</small>
                                    </div>
                                </div>
                                
                                <!-- Inspector Acknowledgement -->
                                <div class="col-6">
                                    <div class="text-center p-1 rounded ${item.hasInspectorAck == 1 ? 'bg-success bg-opacity-25' : 'bg-danger bg-opacity-25'}">
                                        <small class="d-block fw-bold">Inspector</small>
                                        <small class="d-block">${item.hasInspectorAck == 1 ? '✓ Acknowledged' : 'Pending'}</small>
                                    </div>
                                </div>
                                
                                <!-- Chief FSES Acknowledgement -->
                                <div class="col-6">
                                    <div class="text-center p-1 rounded ${item.hasRecommendingApproval == 1 ? 'bg-success bg-opacity-25' : 'bg-danger bg-opacity-25'}">
                                        <small class="d-block fw-bold">Chief FSES</small>
                                        <small class="d-block">${item.hasRecommendingApproval == 1 ? '✓ Recommended' : 'Pending'}</small>
                                    </div>
                                </div>
                                
                                <!-- Fire Marshal Acknowledgement -->
                                <div class="col-6">
                                    <div class="text-center p-1 rounded ${item.hasFinalApproval == 1 ? 'bg-success bg-opacity-25' : 'bg-danger bg-opacity-25'}">
                                        <small class="d-block fw-bold">Fire Marshal</small>
                                        <small class="d-block">${item.hasFinalApproval == 1 ? '✓ Approved' : 'Pending'}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer with quick actions -->
                    <div class="mt-3 pt-2 border-top">
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-outline-primary more-details-btn" 
                                    data-sched-id="${item.schedule_id}">
                                <i class="bi bi-info-circle me-1"></i>Details
                            </button>
                            
                            ${item.Inspection_status === "Completed" ? `
                                <button class="btn btn-sm btn-outline-success checkInspectionReport" 
                                        data-sched-id="${item.schedule_id}">
                                    <i class="bi bi-file-earmark-text me-1"></i>Report
                                </button>
                            ` : ''}
                            
                            ${status === "Scheduled" && roleLabel === "Inspector" && 
                              item.hasInspectorAck == 1 && 
                              item.hasRecommendingApproval == 1 && 
                              item.hasFinalApproval == 1 && 
                              item.HasClientAck == "Y" ? `
                                <button class="btn btn-sm btn-primary startInspectionBtn" 
                                        data-sched-id="${item.schedule_id}">
                                    <i class="bi bi-play-circle me-1"></i>Start Inspection
                                </button>
                            ` : ''}
                            
                            <a href="../pages/print_inspection_order.php?id=${item.schedule_id}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-file-pdf me-1"></i>FSED-9F
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $listContainer.html(listHtml);
        
        // PAGINATION (same as before, but for list)
        const totalPages = Math.ceil(allData.length / recordsPerPage);
        if (totalPages > 1) {
            $pagination.html(`
                <div class="d-flex justify-content-center align-items-center mt-3">
                    <button class="btn btn-outline-secondary btn-sm prev-page" ${currentPage === 1 ? "disabled" : ""}>
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span class="text-muted mx-3">Page ${currentPage} of ${totalPages}</span>
                    <button class="btn btn-outline-secondary btn-sm next-page" ${currentPage === totalPages ? "disabled" : ""}>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            `);

            $pagination.off("click").on("click", ".prev-page", () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderListGroup(currentPage);
                }
            });

            $pagination.on("click", ".next-page", () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderListGroup(currentPage);
                }
            });
        }
    }

    // -------------------------------------------
    // AJAX LOAD
    // -------------------------------------------
    $.ajax({
        url: "../includes/fetch_inspection_schedule.php",
        method: "POST",
        dataType: "json",
        data: { search },
        beforeSend: function () {
            $listContainer.html(`
                <div class="list-group-item text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <div class="mt-2">Loading inspection schedules...</div>
                </div>
            `);
            $pagination.html("");
        },
        success: function (res) {
            if (!res || !res.success || !Array.isArray(res.data)) {
                $listContainer.html(`
                    <div class="list-group-item text-center text-muted py-4">
                        <i class="bi bi-exclamation-triangle fs-1 text-muted mb-2"></i>
                        <p class="mb-0">No inspection schedules found.</p>
                    </div>
                `);
                return;
            }

            allData = res.data;
            renderListGroup(currentPage);
        }
    });




}



function formatScheduleDateOnly(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });
}

function generateScheduleButtons(item, roleLabel, status) {
    let buttons = '';

    // ---------- Client: Set Schedule ----------
    if (roleLabel === "Client" && status !== "Cancelled") {
        const hasClientAck = String(item.HasClientAck ?? "").toUpperCase();
        if (hasClientAck === "N") {
            buttons += createButton({
                classList: ['ack', 'page-button', 'resched-btn', 'btn-light', 'ms-0'],
                icon: safeGetIcon('repeat'),
                otherAttr: {
                    'data-sched-id': item.schedule_id,
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#reschedCanvas',
                    'type': 'button'
                },
                label: 'Set Schedule'
            });
        }
    }

    // ---------- Admin_Assistant: Cancel ----------
    if (roleLabel === "Admin_Assistant" && status !== "Cancelled" && status !== "Completed") {
        buttons += createButton({
            classList: ['page-button', 'bg-danger', 'text-light', 'px-1', 'ms-0', 'cancel-schedule-btn'],
            icon: safeGetIcon('ban'),
            otherAttr: { 'data-sched-id': item.schedule_id },
            label: 'Cancel Schedule'
        });
    }

    // ---------- Admin_Assistant: Approve Reschedule ----------
    if (roleLabel === "Admin_Assistant" && status === "Rescheduled") {
        const remarksText = `Reschedule to ${item.preferredSchedule || ''} due to: ${item.rescheduleReason || 'unspecified reason.'}`;
        buttons += createButton({
            classList: ['page-button'],
            otherAttr: {
                'href': `?page=ins_sched&action=reschedule&sched_id=${item.schedule_id}&remarks=${encodeURIComponent(remarksText)}`,
                'data-sched-id': item.schedule_id
            },
            label: `<small class="fine-print small text-small">${item.preferredSchedule}</small><hr class="my-0">Approve Requested Schedule?`
        });
    }

    // ---------- Cancelled ----------
    if (status === "Cancelled") {
        buttons = createButton({
            classList: ['page-button', 'bg-danger', 'btn-disabled'],
            icon: safeGetIcon('dash-circle'),
            label: 'Cancelled'
        });

        buttons += createButton({
            classList: ['page-button', 'bg-danger', 'archive-schedule-btn'],
            icon: safeGetIcon('trash'),
            otherAttr: { 'data-sched-id': item.schedule_id },
            label: 'Archive'
        });
    }

    // ---------- ACKNOWLEDGEMENT FLOW ----------
    if (status !== "Cancelled" && status !== "Rescheduled" && status !== "Completed") {
        const ackMap = {
            "Client": "HasClientAck",
            "Recommending Approver": "hasRecommendingApproval",
            "Approver": "hasFinalApproval",
            "Inspector": "hasInspectorAck"
        };

        const ackField = ackMap[roleLabel];
        const val = String(item[ackField] ?? "").toLowerCase().trim();

        const clientAck = String(item.HasClientAck ?? "").toLowerCase().trim();
        const recAck = String(item.hasRecommendingApproval ?? "").toLowerCase().trim();
        const fmAck = String(item.hasFinalApproval ?? "").toLowerCase().trim();
        const inspAck = String(item.hasInspectorAck ?? "").toLowerCase().trim();

        const isAcked = v => v === "y" || v === "1" || v === 1;

        let canAcknowledge = false;
        let reason = ""; let label = "";

        switch (roleLabel) {
            case "Client":
                canAcknowledge = !isAcked(clientAck);
                label = "Acknowledge as";
                break;
            
            case "Inspector":
                if (!isAcked(clientAck)) reason = "Waiting for Client's Acknowledgement";
                else canAcknowledge = !isAcked(inspAck);
                
                label = "Acknowledge as";
                break;

            case "Recommending Approver":
                if (!isAcked(inspAck)) reason = "Waiting for Inspector's Acknowledgement";
                else canAcknowledge = !isAcked(recAck);
                
                label = "Recommend Schedule as";
                break;
            
            case "Approver":
                if (!isAcked(recAck)) reason = "Waiting for Recommendation";
                else canAcknowledge = !isAcked(fmAck);
                
                label = "Approve Schedule as";
                break;
            
        }

        if (canAcknowledge) {
            buttons += createButton({
                classList: ['ack-btn', 'page-button', 'btn-light', 'ms-0'],
                icon: safeGetIcon('circle-check'),
                otherAttr: {
                    'data-sched-id': item.schedule_id,
                    'data-role': roleLabel,
                    'data-btnid': item.schedule_id
                },
                label: `${label} ${roleLabel}`
            });
        } else if (reason) {
            buttons += createButton({
                classList: ['page-button', 'bg-secondary', 'text-light', 'd-flex', 'align-items-center', 'gap-2'],
                icon: safeGetIcon('hourglass'),
                label: reason
            });
        }
    }

    // ---------- Inspector Controls ----------
    if (
        roleLabel === "Inspector" &&
        item.hasInspectorAck == 1 &&
        item.hasRecommendingApproval == 1 &&
        item.hasFinalApproval == 1 &&
        item.HasClientAck == "Y" &&
        status !== "Cancelled"
    ) {
        const address = item.gi_location || "";

        buttons += createButton({
            classList: ['page-button', 'ms-2'],
            icon: safeGetIcon("geo"),
            otherAttr: {
                'href': `../pages/map.php?address=${encodeURIComponent(address)}`
            },
            label: 'View Location'
        });

        if (status === "Scheduled") {
            buttons += createButton({
                classList: ['btn-primary', 'page-button', 'startInspectionBtn'],
                icon: safeGetIcon('checklist'),
                otherAttr: { 'data-sched-id': item.schedule_id },
                label: 'Start Inspection'
            });
        }

        if (status === "Completed" && item.Inspection_status === "Completed" && item.has_Defects === 1) {
            buttons += createButton({
                classList: ['page-button', 'startInspectionBtn'],
                icon: safeGetIcon('checklist'),
                otherAttr: { 'data-sched-id': item.schedule_id },
                label: 'Re-Inspect for defects'
            });
        }
    }

    // ---------- Inspection Report ----------
    if ((status === "Completed" || status === "Pending" || status === "Rescheduled") &&
        item.Inspection_status === "Completed") {

        let classDefect = item.has_defects === 0 ? "success" : "outline-danger bg-warning bg-opacity-75 shadow";
        let iconBtn = item.has_defects === 0 ? safeGetIcon('patchcheck') : safeGetIcon('patchcaution');

        buttons += createButton({
            classList: [`btn-${classDefect}`, 'page-button', 'checkInspectionReport'],
            icon: iconBtn,
            otherAttr: { 'data-sched-id': item.schedule_id },
            label: 'Inspection Report'
        });
    }

    // ---------- FSED-9F ----------
    if (status !== "Cancelled") {
        buttons += createButton({
            classList: ['page-button', 'text-dark', 'ps-2', 'ms-0'],
            icon: safeGetIcon('pdf'),
            otherAttr: {
                'href': `../pages/print_inspection_order.php?id=${item.schedule_id}`,
                'target': '_blank'
            },
            label: 'View FSED-9F'
        });
    }

    // ---------- Expired ----------
    const scheduleDate = new Date(item.scheduled_date);
    const now = new Date();

    if (scheduleDate < now && status !== "Completed" && status !== "Rescheduled") {
        buttons = createButton({
            classList: ['page-button', 'bg-secondary', 'text-light'],
            icon: safeGetIcon('dash-circle'),
            label: 'Expired'
        });

        buttons += createButton({
            classList: ['page-button', 'bg-danger', 'text-light', 'archive-schedule-btn'],
            icon: safeGetIcon('trash'),
            otherAttr: { 'data-sched-id': item.schedule_id },
            label: 'Archive'
        });

        if (roleLabel === "Client" || roleLabel === "Admin_Assistant") {
            buttons += createButton({
                classList: ['ack', 'page-button', 'resched-btn', 'btn-light'],
                icon: safeGetIcon('repeat'),
                otherAttr: {
                    'data-sched-id': item.schedule_id,
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#reschedCanvas'
                },
                label: 'Reset Schedule'
            });
        }
    }

    // ---------- Archived ----------
    if (status === 'Archived') {
        buttons = createButton({
            classList: ['page-button', 'btn-secondary', 'btn-disabled'],
            icon: safeGetIcon('trash'),
            label: 'Archived'
        });
    }

    // ---------- More Details ----------
    buttons += createButton({
        classList: ['page-button', 'bg-secondary', 'text-light', 'more-details-btn'],
        icon: safeGetIcon('more'),
        otherAttr: { 'data-sched-id': item.schedule_id },
        label: 'More Details'
    });

    return buttons;
}
