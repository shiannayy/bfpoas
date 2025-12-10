

// ======================================================================
// HELPERS
// ======================================================================



function openPrintWindow(printWindow, scheduleId, res) {
    const htmlContent = buildInspectionReportHTML(scheduleId, res.inspection_data[0], res.data, true);

    const html = `
    <html>
    <head>
        <title>Inspection Report - Schedule ${scheduleId}</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            body { padding: 20px; font-size: 0.9rem; }
            @media print {
                button { display: none !important; }
                body { margin: 0; padding: 0; }
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            ${htmlContent}
        </div>
    </body>
    </html>`;

    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();

    // Print once content fully loaded
    printWindow.onload = () => {
        printWindow.focus();
        setTimeout(() => printWindow.print(), 600);
    };
}

// Utility: safely get icon function (assumes getIcon() is defined elsewhere)
function safeGetIcon(name) {
    try {
        return typeof getIcon === "function" ? getIcon(name) : '';
    } catch (e) {
        return '';
    }
}

// ======================================================================
// AJAX: Load Inspection Schedules
// ======================================================================
function loadInspectionSchedules(user = {}, search = null) {
    $("#inspectionTableBody").html("");
    $.ajax({
        url: "../includes/fetch_inspection_schedule.php",
        method: "POST",
        dataType: "json",
        data: {
            search: search
        },
        beforeSend: function () {
            $("#inspectionTableBody").html(`
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                       <div class="spinner-border spinner-border-sm text-gold" role="status"></div>
                       <div>Loading inspection schedules...</div>
                    </td>
                </tr>
            `);
        },
        success: function (res) {
            if (!res || !res.success || !Array.isArray(res.data) || res.data.length === 0) {
                $("#inspectionTableBody").html(`
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            No inspection schedules found.
                        </td>
                    </tr>
                `);
                return;
            }

            const role = user.role || res.role || "";
            const subrole = user.subrole || res.subrole || "";
            let rows = "";

            res.data.forEach((item) => {
                // derive state & role label
                const status = item.sched_status || "";
                const remarks = item.sched_remarks || "";
                const hasdefects = remarks.toLowerCase().includes("has defects");
                let statusClass = "";

                if (status === "Completed") {
                    statusClass = hasdefects ? "bg-success bg-opacity-50 text-gold" : "bg-success bg-opacity-75 text-light";
                } else if (status === "Rescheduled") {
                    statusClass = "bg-gold text-dark";
                } else if (status === "Cancelled") {
                    statusClass = "bg-danger text-light";
                }

                let roleLabel = null;
                if (role === "Client" || subrole === "Client") roleLabel = "Client";
                else if (role === "Administrator" && ["Recommending Approver", "Fire Marshall"].includes(subrole)) roleLabel = "Recommending Approver";
                else if (role === "Administrator" && subrole === "Admin_Assistant") roleLabel = "Admin_Assistant";
                else if (role === "Administrator" && subrole === "Approver") roleLabel = "Final Approver";
                else if (role === "Inspector" || subrole === "Inspector") roleLabel = "Inspector";
                else {
                    roleLabel = "Unauthorized";
                }

                let buttons = "";

                // Client reschedule rules
                if (roleLabel === "Client" && status !== "Cancelled") {
                    const hasClientAck = String(item.HasClientAck ?? "").toUpperCase();
                    // if not acknowledged (empty string) => allow set schedule
                    if (hasClientAck === "N") {
                        buttons += `
                            <a href="#"
                               class="ack page-button btn btn-navy resched-btn flex-fill d-flex align-items-center justify-content-start gap-2"
                               data-sched-id="${item.schedule_id}"
                               data-bs-toggle="offcanvas"
                               data-bs-target="#reschedCanvas">
                               ${safeGetIcon('repeat')}
                               <span class="d-none d-lg-inline">Set Schedule</span>
                            </a>`;
                    }
                }

                // Admin: Cancel (Admin_Assistant)
                if (roleLabel === "Admin_Assistant" && status !== "Cancelled" && status !== "Completed") {
                    buttons += `
                        <button type="button"
                                class="page-button btn btn-danger cancel-schedule-btn"
                                data-sched-id="${item.schedule_id}">
                            ${safeGetIcon('ban')}
                            <span class="d-none d-lg-inline">Cancel Schedule</span>
                        </button>`;
                }

                // Admin: Approve Reschedule
                if (roleLabel === "Admin_Assistant" && status === "Rescheduled") {
                    const remarksText = `Reschedule to ${item.preferredSchedule || 'N/A'} due to: ${item.rescheduleReason || 'unspecified reason.'}`;
                    buttons += `
                        <a href="?page=ins_sched&action=reschedule&sched_id=${item.schedule_id}&remarks=${encodeURIComponent(remarksText)}"
                           class="btn btn-navy page-button"
                           data-sched-id="${item.schedule_id}">
                            <small class="fine-print small text-small">${item.preferredSchedule}</small>
                            <hr class="my-0">
                            <small>Approve Rescheduling?</small>
                        </a>`;
                }

                // Cancelled badge for non-clients
                if (status === "Cancelled") {
                    buttons = `
                        <button disabled type="button" class="page-button btn btn-danger btn-disabled">
                            ${safeGetIcon('dash-circle')}
                            <span class="d-none d-lg-inline">Cancelled</span>
                        </button>`;
                    
                   buttons += `<button type="button"
                                class="page-button btn btn-danger archive-schedule-btn"
                                data-sched-id="${item.schedule_id}">
                            ${safeGetIcon('trash')}
                            <span class="d-none d-lg-inline">Archive</span>
                        </button>`;
                        
                }

                // Acknowledgment flow (Client -> Recommending -> Final -> Inspector)
                if (status !== "Cancelled" && status !== "Rescheduled" && status !== "Completed") {
                    const ackMap = {
                        "Client": "HasClientAck",
                        "Recommending Approver": "hasRecommendingApproval",
                        "Fire Marshall": "hasRecommendingApproval",
                        "Final Approver": "hasFinalApproval",
                        "Inspector": "hasInspectorAck"
                    };

                    const ackField = ackMap[roleLabel];
                    const val = String(item[ackField] ?? "").toLowerCase().trim();

                    const clientAck = String(item["HasClientAck"] ?? "").toLowerCase().trim();
                    const recAck = String(item["hasRecommendingApproval"] ?? "").toLowerCase().trim();
                    const finalAck = String(item["hasFinalApproval"] ?? "").toLowerCase().trim();

                    const isAcked = (v) => v === "y" || v === "1" || v === 1;

                    let canAcknowledge = false;
                    let reason = "";

                    switch (roleLabel) {
                        case "Client":
                            canAcknowledge = !isAcked(clientAck);
                            break;

                        case "Recommending Approver":
                        case "Fire Marshall":
                            if (!isAcked(clientAck)) {
                                reason = "Waiting for Client acknowledgment";
                            } else {
                                canAcknowledge = !isAcked(recAck);
                            }
                            break;

                        case "Final Approver":
                            if (!isAcked(recAck)) {
                                reason = "Waiting for Recommending Approver acknowledgment";
                            } else {
                                canAcknowledge = !isAcked(finalAck);
                            }
                            break;

                        case "Inspector":
                            if (!isAcked(finalAck)) {
                                reason = "Waiting for Final Approver acknowledgment";
                            } else {
                                canAcknowledge = !isAcked(item["hasInspectorAck"]);
                            }
                            break;
                    }

                    if (canAcknowledge) {
                        buttons += `
                            <button type="button"
                                    class="ack-btn page-button btn btn-outline-danger flex-fill d-flex align-items-center justify-content-start gap-2"
                                    data-sched-id="${item.schedule_id}"
                                    data-role="${roleLabel}"
                                    data-btnid="${item.schedule_id}">
                                <i>${safeGetIcon('circle-check')}</i>
                                <b class="d-none d-lg-inline">Acknowledge as ${roleLabel}</b>
                            </button>`;
                    } else if (reason) {
                        buttons += `
                            <button type="button"
                                    class="page-button btn btn-outline-secondary flex-fill d-flex align-items-center justify-content-start gap-2"
                                    disabled>
                                <i>${safeGetIcon('clock')}</i>
                                <b class="d-none d-lg-inline">${reason}</b>
                            </button>`;
                    }
                }

                // Inspector controls (requires approvals and client ack)
                if ( role === "Inspector" &&
                    item.hasInspectorAck == 1 &&
                    item.hasRecommendingApproval == 1 &&
                    item.hasFinalApproval == 1 &&
                    item.HasClientAck == "Y" &&
                    status !== "Cancelled") 
                {
                    const address = item.gi_location || "";
                    buttons += `
                        <a href="../pages/map.php?address=${encodeURIComponent(address)}"
                           class="btn btn-navy-dark page-button flex-fill d-flex align-items-center justify-content-start gap-2">
                           ${safeGetIcon("geo")}
                           <small class="d-none d-lg-inline">View Location</small>
                        </a>`;

                    if (status === "Scheduled") {
                        let disabledAttr = "";
                        const schedDate = new Date(item.scheduled_date);
                        const today = new Date();
                        schedDate.setHours(0, 0, 0, 0);
                        today.setHours(0, 0, 0, 0);
                        const isFuture = schedDate > today;
                        disabledAttr = isFuture ? "disabled" : "";

                        // if you want to override during development, you can temporarily set disabledAttr = "";
                        disabledAttr = "";

                        buttons += `
                            <button class="btn btn-primary page-button startInspectionBtn flex-fill d-flex align-items-center justify-content-start gap-2"
                                    data-sched-id="${item.schedule_id}" ${disabledAttr}>
                                <i>${safeGetIcon('checklist')}</i>
                                <small class="d-none d-lg-inline">Start Inspection</small>
                            </button>
                        `;
                    }

                    if (status === "Completed" && item.Inspection_status === "Completed" && item.has_Defects === 1) {
                        buttons += `<button class="btn btn-navy page-button startInspectionBtn flex-fill d-flex align-items-center justify-content-start gap-2"
                                data-sched-id="${item.schedule_id}">
                           <i>${safeGetIcon('checklist')}</i>
                           <small class="d-none d-lg-inline">Re-Inspect for defects</small>
                        </button>`;
                    }

                    if ((status === "Completed" || status === "Pending" || status === "Rescheduled") && item.Inspection_status === "Completed") {
                        let classDefect = item.has_defects === 0 ? "success" : "outline-danger bg-warning bg-opacity-75 shadow shadow-sm";
                        let iconBtn = item.has_defects === 0 ? safeGetIcon('patchcheck') : safeGetIcon('patchcaution');
                        buttons += `<button class="btn btn-${classDefect} page-button checkInspectionReport flex-fill d-flex align-items-center justify-content-start gap-2"
                                data-sched-id="${item.schedule_id}">
                                        <b>${iconBtn}</b> 
                                        <small class="d-none d-lg-inline">Completed</small>
                                        <br>
                                       <i>${safeGetIcon('card-checklist')}</i>
                                       <small class="d-none d-lg-inline">Inspection Report</small>
                                    </button>`;
                    }
                }

                // Print / View FSED-9F
                if (status !== "Cancelled") {
                    buttons += `
                        <a href="../pages/print_inspection_order.php?id=${item.schedule_id}"
                           target="_blank"
                           class="page-button btn btn-gold flex-fill d-flex align-items-center justify-content-start gap-2">
                           <i>${safeGetIcon('pdf')}</i>
                           <small class="d-none d-lg-inline">View FSED-9F</small>
                        </a>`;
                }
                // Handle Expired Schedules
                const scheduleDate = new Date(item.scheduled_date);
                const now = new Date();

                // Only flag as expired if the scheduled date is in the past AND not completed
                if (scheduleDate < now && status !== "Completed" && status !== "Rescheduled") {
                    buttons = `<button disabled type="button" class="btn btn-danger archive-schedule-btn">
                            ${safeGetIcon('dash-circle')}
                            <span class="d-none d-lg-inline">Expired</span>
                        </button>`;

                    // Allow only Client to reschedule expired schedules
                    if (roleLabel === "Client" || roleLabel === "Admin_Assistant") {
                        buttons += `
                                <a href="#"
                                   class="ack page-button btn btn-navy resched-btn flex-fill d-flex align-items-center justify-content-start gap-2"
                                   data-sched-id="${item.schedule_id}"
                                   data-bs-toggle="offcanvas"
                                   data-bs-target="#reschedCanvas">
                                   ${safeGetIcon('repeat')}
                                   <span class="d-none d-lg-inline">Set Schedule</span>
                                </a>`;
                    }
                }


                // Build row
                rows += `
                    <tr class="${statusClass}">
                        <td class="text-center">
                            <div class="d-grid gap-2 w-100">
                                ${buttons}
                            </div>
                        </td>
                        <td>${status == "Completed" 
                                ? `<div class='badge text-bg-success'>Completed</div> on ${new Date(item.completed_at).toLocaleString('en-US', {
                                    month: 'long',
                                    day: 'numeric',
                                    year: 'numeric',
                                    hour: 'numeric',
                                    minute: '2-digit',
                                    hour12: true
                                  })}` 
                                : status
                              }
                        </td>
                        <td>${item.order_number || ""}</td>
                        <td>
                          ${item.scheduled_date 
                            ? new Date(item.scheduled_date).toLocaleString('en-US', {
                                month: 'long',
                                day: 'numeric',
                                year: 'numeric',
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                              }) 
                            : ""}
                        </td>

                        <td>
                          ${item.preferredSchedule 
                            ? new Date(item.preferredSchedule).toLocaleString('en-US', {
                                month: 'long',
                                day: 'numeric',
                                year: 'numeric',
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: true
                              }) 
                            : ""}
                        </td>

                        <td>${item.owner_full_name || ""}</td>
                        <td>${badgedResponse(item.HasClientAck, "Acknowledged","Pending","Denied")}</td>
                        <td>${badgedResponse(item.hasRecommendingApproval, "Recommended for Approval","Pending","Denied")}</td>
                        <td>${badgedResponse(item.hasFinalApproval, "Approved","Pending","Denied")}</td>
                        <td>${badgedResponse(item.hasInspectorAck, "Acknowledged","Pending","Denied")}</td>
                        <td>${item.ins_full_name || ""}</td>
                        <td>${item.proceed_instructions || ""}</td>
                        <td>${item.checklist_title || ""}</td>
                        <td>${item.fsic_purpose || ""}</td>
                        <td>${item.noi_desc || ""}</td>
                       
                        <td>
                            <div class="container-fluid" style="width: 300px;max-height:200px;overflow-y:scroll">
                                ${item.sched_remarks || ""}
                            </div>
                        </td>
                        <td>${
                          !item.has_defects
                            ? ""
                            : item.has_defects == "1"
                              ? "<div class='badge text-bg-warning'>Has defects</div>"
                              : "No Issues"
                        }</td>
                    </tr>`;
            });

            $("#inspectionTableBody").html(rows);
        },
        error: function (xhr, status, error) {
            console.error("Error loading inspection schedules:", error);
            $("#inspectionTableBody").html(`
                <tr>
                    <td colspan="10" class="text-center text-danger py-4">
                        Error loading data. Please try again.
                    </td>
                </tr>
            `);
        }
    });
}

// ======================================================================
// RESCHEDULE: Setup Form (bind once)
// ======================================================================
function setupRescheduleForm() {
    // Remove previous handler to avoid duplicate submissions
    $("#reschedForm").off("submit").on("submit", function (e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: "../includes/request_reschedule.php",
            method: "POST",
            data: formData,
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    alert(res.message);
                    $("#reschedForm")[0].reset();
                    const canvas = bootstrap.Offcanvas.getInstance($("#reschedCanvas")[0]);
                    if (canvas) canvas.hide();
                    loadInspectionSchedules({
                        role: currentUserRole
                    });
                } else {
                    alert("Error: " + res.message);
                }
            },
            error: function () {
                alert("An error occurred while submitting the reschedule request.");
            }
        });
    });
}

// ======================================================================
// EVENTS (delegated)
// ======================================================================

$(document).on("click", ".checkInspectionReport", function () {
    const schedId = $(this).data("sched-id");

    $.ajax({
        url: "../includes/export_inspection_report.php",
        type: "POST",
        data: {
            schedule_id: schedId
        },
        dataType: "json",
        success: function (res) {
            if (res.success) {
                const inspectionData = res.inspection_data[0];
                const tableData = res.data;
                const html = buildInspectionReportHTML(schedId, inspectionData, tableData, false);
                $("#inspectionReportModal .modal-body").html(html);
                $("#inspectionReportModal").modal("show");
                $("#exportReportBtn").attr("data-sched-id", schedId);
            } else {
                alert("Failed to load report: " + res.message);
            }
        },
        error: function (xhr, status, err) {
            console.error("Error generating report:", err);
        }
    });
});

$(document).on("click", "#exportReportBtn", function () {
    const scheduleId = $(this).data("sched-id");
    if (!scheduleId) {
        console.error("Missing schedule ID");
        return;
    }

    // Open popup immediately (allowed by browser)
    const printWindow = window.open("", "_blank");
    if (!printWindow) {
        alert("Please allow popups for this site.");
        return;
    }

    printWindow.document.write("<p style='padding:20px;'>Preparing report...</p>");

    $.ajax({
        url: "../includes/export_inspection_report.php",
        type: "POST",
        data: { schedule_id: scheduleId },
        dataType: "json",
        success: function (res) {
            if (!res.success) {
                printWindow.document.body.innerHTML = `<p>Failed to fetch report: ${res.message}</p>`;
                return;
            }

            if (!res.data || res.data.length === 0) {
                printWindow.document.body.innerHTML = `<p>No inspection data found.</p>`;
                return;
            }

            // Use existing print window
            openPrintWindow(printWindow, scheduleId, res);
        },
        error: function (xhr, status, err) {
            printWindow.document.body.innerHTML = `<p>Error fetching report data: ${err}</p>`;
            console.error("Export error:", err);
        }
    });
});
$(document).on("click", ".cancel-schedule-btn", function () {
    const schedId = $(this).data("sched-id");
    $("#cancelScheduleId").val(schedId);
    $("#cancelReason").val("");
    $("#cancelScheduleModal").modal("show");
});

$(document).on("click", "#confirmCancelBtn", function () {
    const scheduleId = $("#cancelScheduleId").val();
    const reason = $("#cancelReason").val().trim();

    if (!reason) {
        showAlert("Please enter a reason for cancellation.", "warning");
        return;
    }

    showAlert(`<div class='text-center'><p>Processing cancellation...</p></div>`, "info", 3000);

    $.ajax({
        url: "../includes/cancel_schedule.php",
        type: "POST",
        data: {
            schedule_id: scheduleId,
            reason: reason
        },
        dataType: "json",
        success: function (res) {
            if (res.success) {
                $("#cancelScheduleModal").modal("hide");
                showAlert("Schedule cancelled successfully.", "success");
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert("Failed to cancel schedule: " + res.message, "danger");
            }
        },
        error: function (xhr, status, err) {
            console.error("Cancel error:", err);
            showAlert("Error cancelling schedule.", "danger");
        }
    });
});

// Debounced search
$(document).on("keyup", "#SearchInsSched", function () {
    clearTimeout(searchTimer);
    const keyword = $(this).val().trim();
    searchTimer = setTimeout(() => {
        const user = window.currentUser || {};
        loadInspectionSchedules(user, keyword);
    }, 1000);
});

// Reschedule button: show offcanvas & set schedule id
$(document).on("click", ".resched-btn", function (e) {
    e.preventDefault();
    const schedId = $(this).data("sched-id");
    $("#schedule_id").val(schedId);
    const offcanvasEl = document.getElementById("reschedCanvas");
    if (offcanvasEl) {
        new bootstrap.Offcanvas(offcanvasEl).show();
    } else {
        console.warn("reschedCanvas element not found");
    }
});

// Start inspection
$(document).on("click", ".startInspectionBtn", function (e) {
    e.preventDefault();
    const scheduleId = $(this).data("sched-id");
    $.post("../includes/save_inspection.php", {
        schedule_id: scheduleId
    }, function (res) {
        if (res.success) {
            window.location.href = `?page=strt_ins&sched_id=${scheduleId}&insp_id=${res.inspection_id}`;
        } else {
            alert(res.message || "Failed to start inspection.");
        }
    }, "json");
});

// Simple cancel (request_cancel)
$(document).on("click", ".cancel-btn", function () {
    const schedId = $(this).data("sched-id");
    if (!confirm("Are you sure you want to cancel this schedule?")) return;

    $.ajax({
        url: "../includes/request_cancel.php",
        method: "POST",
        data: {
            schedule_id: schedId
        },
        dataType: "json",
        success: function (res) {
            if (res.success) {
                alert("Schedule successfully cancelled.");
                loadInspectionSchedules({
                    role: currentUserRole
                });
            } else {
                alert(res.message);
            }
        },
        error: function () {
            alert("An error occurred while cancelling the schedule.");
        }
    });
});

// Acknowledge button
$(document).on("click", ".ack-btn", function (e) {
    e.preventDefault();
    const $btn = $(this);
    const schedId = $btn.data("sched-id");
    const role = $btn.data("role");
    const $btns = $(".ack-btn[data-btnid='" + schedId + "']");

    if (!confirm(`Confirm to acknowledge this schedule as ${role}?`)) return;

    $btns.prop("disabled", true).html(`<div class="spinner-grow text-success"></div> Saving...`);

    $.ajax({
        url: "../includes/acknowledge.php",
        method: "GET",
        data: {
            ack_sched_id: schedId
        },
        dataType: "json",
        success: function (res) {
            if (res.success) {
                $btn.html(`${safeGetIcon('patchcheck')} Acknowledged.`).addClass("btn-success");
                setTimeout(() => $btns.fadeOut(200), 200);
                loadInspectionSchedules({
                    role: currentUserRole,
                    subrole: currentUser?.subrole ?? null,
                    user_id: currentUser?.user_id
                });
            } else {
                alert(res.message || "Failed to acknowledge.");
                $btn.prop("disabled", false).html('<i class="bi bi-check2-circle"></i> Try Again');
            }
        },
        error: function () {
            alert("Server error occurred.");
            $btn.prop("disabled", false).html('<i class="bi bi-check2-circle"></i> Acknowledge');
        }
    });
});

// ======================================================================
// BUILD REPORT HTML (unchanged logic)
// ======================================================================
function buildInspectionReportHTML(scheduleId, inspectionData, tableData, isForPrint = false) {
    const now = new Date().toLocaleString();
    const ins_score = inspectionData.inspection_score || 0;
    const has_defect_ind = inspectionData.has_Defects == 1 ? "Has Defects" : "Passed";
    const has_defect_ind_class = inspectionData.has_Defects == 1 ? "warning" : "success";
    const ins_score_class = ins_score >= 75 ? "success" : "danger";

    let html = `
        <div class="d-inline mb-2">
            <div class="badge bg-${has_defect_ind_class} p-2">${has_defect_ind}</div>
            <div class="badge bg-${ins_score_class} p-2">Score: ${ins_score}%</div>
        </div>
        <div class="small text-muted">Generated: ${now}</div>
    `;

    html += `
    <div class="container-fluid px-1">
        <div class="row">
            <div class="col-12">
               <h3 class="fw-bold mb-3">General Info</h3>
                <div class="table-responsive">
                  <table class="table table-bordered table-sm align-middle">
                    <tbody>
                      <tr><th>Building</th><td>${inspectionData.building_name}</td></tr>
                      <tr><th>Location of Construction</th><td>${inspectionData.location_of_construction}</td></tr>
                      <tr><th>Project Title</th><td>${inspectionData.project_title}</td></tr>
                      <tr><th>Owner</th><td>${inspectionData.owner_name}</td></tr>
                      <tr><th>Occupant Name</th><td>${inspectionData.occupant_name}</td></tr>
                      <tr><th>Representative Name</th><td>${inspectionData.representative_name}</td></tr>
                      <tr><th>Administrator Name</th><td>${inspectionData.administrator_name}</td></tr>
                      <tr><th>Owner Contact No.</th><td>${inspectionData.owner_contact_no}</td></tr>
                      <tr><th>Representative Contact No.</th><td>${inspectionData.representative_contact_no}</td></tr>
                      <tr><th>Other Contact Info</th><td>${inspectionData.telephone_email}</td></tr>
                      <tr><th>Business Name</th><td>${inspectionData.business_name}</td></tr>
                      <tr><th>Establishment Name</th><td>${inspectionData.establishment_name}</td></tr>
                      <tr><th>Nature of Business</th><td>${inspectionData.nature_of_business}</td></tr>
                      <tr><th>Classification of Occupancy</th><td>${inspectionData.classification_of_occupancy}</td></tr>
                      <tr><th>Healthcare Facility Name</th><td>${inspectionData.healthcare_facility_name}</td></tr>
                      <tr><th>Healthcare Facility Type</th><td>${inspectionData.healthcare_facility_type}</td></tr>
                      <tr><th>Height of Building</th><td>${inspectionData.height_of_building}</td></tr>
                      <tr><th>Number of Storeys</th><td>${inspectionData.no_of_storeys}</td></tr>
                      <tr><th>Area per Floor</th><td>${inspectionData.area_per_floor}</td></tr>
                      <tr><th>Total Floor Area</th><td>${inspectionData.total_floor_area}</td></tr>
                      <tr><th>Portion Occupied</th><td>${inspectionData.portion_occupied}</td></tr>
                      <tr><th>Bed Capacity</th><td>${inspectionData.bed_capacity}</td></tr>
                    </tbody>
                  </table>
                </div>
            </div>
        </div>
    </div>
    `;

    html += `
        <div class="table-responsive mt-2">
            <h3 class="fw-bold">Inspection Details</h3>
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:10%">Section</th>
                        <th style="width:15%">Item</th>
                        <th style="width:10%">Response</th>
                        <th style="width:35%">Criteria</th>
                        <th style="width:10%">Remarks</th>
                        <th style="width:20%">Proof</th>
                    </tr>
                </thead>
                <tbody>
    `;

    tableData.forEach(row => {
        const proofHTML = row.response_proof_img ?
            `<img src="../assets/proof/Schedule_${scheduleId}/${row.response_proof_img}" class="img-fluid rounded" style="width:80px;height:80px;object-fit:cover;">` :
            `<span class="text-muted">No Image</span>`;

        const remarksHTML = row.remarks == "1" ?
            `<span class="text-success">${safeGetIcon("patchcheck")} Pass</span>` :
            `<span class="text-danger">${safeGetIcon("patchcaution")} Failed</span>`;

        html += `
            <tr>
                <td>${row.section || ""}</td>
                <td>${row.item_text || ""}</td>
                <td>${row.response_value || ""} ${row.unit_label || ""}</td>
                <td>${row.checklist_criteria || ""}</td>
                <td>${remarksHTML}</td>
                <td>${proofHTML}</td>
            </tr>
        `;
    });

    html += `</tbody></table></div>`;

    if (isForPrint) {
        html += `
            <div class="text-center mt-4 mb-3">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print / Save as PDF
                </button>
            </div>
        `;
    }

    return html;
}

// ======================================================================
// SIGNATURE: utilities & handlers
// ======================================================================
function resizeCanvas(canvas, signaturePadInstance) {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
    if (signaturePadInstance) signaturePadInstance.clear();
}

$(document).ready(function () {
    // Initialize reschedule offcanvas behavior & form binding once
    setupRescheduleForm();

    // Initialize signature offcanvas instance
    const offcanvasEl = document.getElementById("signatureOffcanvas");
    const bsOffcanvas = offcanvasEl ? new bootstrap.Offcanvas(offcanvasEl) : null;

    // Add-signature click handler
    $(document).on("click", ".add-signature", function () {
        userId = $(this).data("user");
        role = $(this).data("role");

        if (bsOffcanvas) bsOffcanvas.show();

        // Initialize signature pad after offcanvas is shown (small delay for animation)
        setTimeout(() => {
            const canvas = document.getElementById("signatureCanvas");
            if (!canvas) return console.warn("signatureCanvas not found");
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: "rgb(255,255,255)"
            });
            resizeCanvas(canvas, signaturePad);
            window.addEventListener("resize", () => resizeCanvas(canvas, signaturePad));
        }, 300);
    });

    // Clear signature
    $("#clearSignature").on("click", function () {
        if (signaturePad) signaturePad.clear();
    });

    // Save signature (crop bounds, preview, save)
    $("#saveSignature").on("click", function () {
        if (!signaturePad || signaturePad.isEmpty()) {
            alert("Please draw your signature first.");
            return;
        }

        const canvas = signaturePad.canvas;
        const ctx = canvas.getContext("2d");
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;

        let minX = canvas.width,
            maxX = 0,
            minY = canvas.height,
            maxY = 0;
        for (let y = 0; y < canvas.height; y++) {
            for (let x = 0; x < canvas.width; x++) {
                const idx = (y * canvas.width + x) * 4;
                if (data[idx + 3] > 0) {
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                    if (y < minY) minY = y;
                    if (y > maxY) maxY = y;
                }
            }
        }

        if (minX === canvas.width) {
            alert("No signature detected!");
            return;
        }

        const cropWidth = maxX - minX + 1;
        const cropHeight = maxY - minY + 1;
        const croppedCanvas = document.createElement("canvas");
        croppedCanvas.width = cropWidth;
        croppedCanvas.height = cropHeight;

        const croppedCtx = croppedCanvas.getContext("2d");
        croppedCtx.putImageData(ctx.getImageData(minX, minY, cropWidth, cropHeight), 0, 0);

        const croppedDataUrl = croppedCanvas.toDataURL("image/jpg");
        $("#signaturePreviewImg").attr("src", croppedDataUrl);
        $("#signaturePreviewModal").modal("show");

        $("#confirmSaveSignature").off("click").on("click", function () {
            $.ajax({
                url: "../includes/save_signature.php",
                method: "POST",
                data: {
                    user_id: userId,
                    role: role,
                    image: croppedDataUrl
                },
                success: function () {
                    alert("Signature saved successfully!");
                    if (bsOffcanvas) bsOffcanvas.hide();
                    $("#signaturePreviewModal").modal("hide");
                },
                error: function () {
                    alert("Error saving signature.");
                }
            });
        });
    });

    // Initialize session & load schedules
    checkSession(function (user) {
        currentUser = user;
        currentUserRole = user.role;
        currentSubRole = user.subrole;

        loadInspectionSchedules({
            role: user.role,
            subrole: user.subrole,
            user_id: user.user_id
        });

        if (currentUserRole === "Administrator" && currentSubRole === "Admin_Assistant") {
            let $btn = $(".btn-new-schedule");
            if ($btn.hasClass("d-none")) $btn.removeClass("d-none");
        }
    });

    // Make sure clicking a resched-btn sets up the form (if not already)
    $(document).on("click", ".resched-btn", function () {
        setupRescheduleForm();
    });
});