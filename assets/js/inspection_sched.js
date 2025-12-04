// ======================================================================
// HELPERS
// ======================================================================
let signaturePad;
let userId, role;
let inspectionSortColumn = null;
let inspectionSortDirection = "ASC";

function toggleStatusRows(status, show) {
    const normalizedStatus = status.toLowerCase().replace(/\s+/g, '-');
    const $rows = $(`.row-${normalizedStatus}`);

    if (show) {
        $rows.removeClass("d-none");
    } else {
        $rows.addClass("d-none");
    }
}

// Attach event listener
$(document).on("change", "[data-status-toggle]", function () {
    const status = $(this).data("status-toggle");
    const show = $(this).is(":checked");
    const $label = $(this).siblings("label");

    // Update label text dynamically
    $label.text(show ? `Hide ${status}` : `Show ${status}`);

    toggleStatusRows(status, show);
});



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
//function loadInspectionSchedules(user = {}, search = null, rpp = 25) {
//    const $tableBody = $("#inspectionTableBody");
//    const $pagination = $(".pagination");
//    const recordsPerPage = rpp;
//    let currentPage = 1;
//    let allData = [];
//
//    function renderTable(page) {
//        $tableBody.html("");
//        $pagination.html("");
//
//        const start = (page - 1) * recordsPerPage;
//        const end = start + recordsPerPage;
//        const pageData = allData.slice(start, end);
//
//        if (pageData.length === 0) {
//            $tableBody.html(`
//                <tr>
//                    <td colspan="10" class="text-center text-muted py-4">
//                        No inspection schedules found.
//                    </td>
//                </tr>
//            `);
//            return;
//        }
//
//        const role = user.role || "";
//        const subrole = user.subrole || "";
//        let rows = "";
//
//        pageData.forEach((item) => {
//            const status = item.sched_status || "";
//            const remarks = item.sched_remarks || "";
//            const hasdefects = remarks.toLowerCase().includes("has defects");
//            let statusClass = "";
//
//            if (status === "Completed") {
//                statusClass = hasdefects ? "bg-success bg-opacity-50 text-gold" : "bg-success bg-opacity-75 text-light";
//            } else if (status === "Rescheduled") {
//                statusClass = "bg-gold text-dark";
//            } else if (status === "Cancelled") {
//                statusClass = "bg-danger text-light bg-opacity-75";
//            }
//
//            let roleLabel = null;
//            roleLabel = getRoleLabel(role, subrole);
//
//            let buttons = "";
//            buttons = generateScheduleButtons(item, roleLabel, status);
//
//            nstatus = "";
//            nstatus = status.toLowerCase().replace(/\s+/g, '-');
//            // Build row
//            rows += `
//                    <tr class="${statusClass} row-${nstatus}">
//                        <td>${formatScheduleDate(item.created_at)}</td>
//                        <td>${status == "Completed" 
//                                ? `<div class='badge text-bg-success'>Completed</div> on ${new Date(item.completed_at).toLocaleString('en-US', {
//                                    month: 'long',
//                                    day: 'numeric',
//                                    year: 'numeric',
//                                    hour: 'numeric',
//                                    minute: '2-digit',
//                                    hour12: true
//                                  })}` 
//                                : status
//                              }
//                        </td>
//                        <td>${item.order_number || ""}</td>
//                        <td class="${status == "Completed" ? "" :  getScheduleClass(item.scheduled_date)}">
//                            ${formatScheduleDate(item.scheduled_date)}
//                        </td>
//                        <td class="d-none d-md-table-cell ${status == "Completed" ? "" : getScheduleClass(item.preferredSchedule)}">
//                            ${formatScheduleDate(item.preferredSchedule)}
//                        </td>
//                        
//
//                        <td><a href="#" class="text-decoration-none showContactInfo" data-user-id="${item.owner_id}"> ${item.owner_full_name || ""} </a></td>
//                       <td class="d-none d-md-table-cell">${badgedResponse(item.HasClientAck, "Acknowledged","Pending","Denied")}</td>
//                        <td class="d-none d-md-table-cell">${badgedResponse(item.hasRecommendingApproval, "Recommended for Approval","Pending","Denied")}</td>
//                        <td class="d-none d-md-table-cell">${badgedResponse(item.hasFinalApproval, "Approved","Pending","Denied")}</td>
//                        <td class="d-none d-md-table-cell">${badgedResponse(item.hasInspectorAck, "Acknowledged","Pending","Denied")}</td>
//                        <td class="d-none d-md-table-cell">${item.ins_full_name || ""}</td>
//                        <td class="d-none d-md-table-cell">${item.proceed_instructions || ""}</td>
//                        <td class="d-none d-md-table-cell">${item.checklist_title || ""}</td>
//                        <td class="d-none d-md-table-cell">${item.fsic_purpose || ""}</td>
//                        <td class="d-none d-md-table-cell">${item.noi_desc || ""}</td>
//
//                       
//                        <td class="d-none d-md-table-cell">
//                            <div class="container-fluid" style="width: 300px;max-height:200px;overflow-y:scroll">
//                                ${item.sched_remarks || ""}
//                            </div>
//                        </td>
//                        <td class="d-none d-md-table-cell">${
//                          !item.has_defects
//                            ? ""
//                            : item.has_defects == "1"
//                              ? "<div class='badge text-bg-warning'>Has defects</div>"
//                              : "No Issues"
//                        }</td>
//                          <td class="text-center">
//                            <div class="d-grid gap-2 w-100">
//
//                                 <div class="btn-group">
//                                  <button type="button" class="btn btn-navy dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
//                                    ${getIcon('menu')}
//                                  </button>
//                                  <ul class="dropdown-menu shadow">
//                                    ${buttons}
//                                  </ul>
//                                </div>
//                            </div>
//                        </td>
//                        
//                    </tr>`;
//        });
//
//        rows += `<tr><td class="fine-print text-center" colspan="12">**NOTHING FOLLOWS**</td></tr>`
//
//        $tableBody.html(rows);
//
//        // --- Pagination buttons ---
//        const totalPages = Math.ceil(allData.length / recordsPerPage);
//        if (totalPages > 1) {
//            let pagBtns = `
//                <div class="d-flex justify-content-center align-items-center mt-3">
//                    <button class="btn btn-outline-secondary btn-sm prev-page" ${page === 1 ? "disabled" : ""}>
//                        <i>${safeGetIcon('chev-left')}</i>
//                    </button>
//                    <span class="text-muted">Page ${page} of ${totalPages}</span>
//                    <button class="btn btn-outline-secondary btn-sm next-page" ${page === totalPages ? "disabled" : ""}>
//                        <i>${safeGetIcon('chev-right')}</i>
//                    </button>
//                </div>`;
//            $pagination.html(pagBtns);
//        }
//    }
//
//    // --- Fetch data ---
//    $.ajax({
//        url: "../includes/fetch_inspection_schedule.php",
//        method: "POST",
//        dataType: "json",
//        data: {
//            search
//        },
//        beforeSend: function () {
//            $tableBody.html(`
//                <tr>
//                    <td colspan="10" class="text-center text-muted py-4">
//                        <div class="spinner-border spinner-border-sm text-gold" role="status"></div>
//                        <div>Loading inspection schedules...</div>
//                    </td>
//                </tr>
//            `);
//            $pagination.html("");
//        },
//        success: function (res) {
//            if (!res || !res.success || !Array.isArray(res.data) || res.data.length === 0) {
//                $tableBody.html(`
//                    <tr>
//                        <td colspan="10" class="text-center text-muted py-4">
//                            No inspection schedules found.
//                        </td>
//                    </tr>
//                `);
//                return;
//            }
//
//            allData = res.data;
//            renderTable(currentPage);
//
//            // --- Pagination events ---
//            $pagination.off("click").on("click", ".prev-page", function () {
//                if (currentPage > 1) {
//                    currentPage--;
//                    renderTable(currentPage);
//                }
//            });
//
//            $pagination.on("click", ".next-page", function () {
//                const totalPages = Math.ceil(allData.length / recordsPerPage);
//                if (currentPage < totalPages) {
//                    currentPage++;
//                    renderTable(currentPage);
//                }
//            });
//        },
//        error: function () {
//            $tableBody.html(`
//                <tr>
//                    <td colspan="10" class="text-center text-danger py-4">
//                        Error loading data. Please try again.
//                    </td>
//                </tr>
//            `);
//            $pagination.html("");
//        }
//    });
//}

function loadInspectionSchedules(user = {}, search = null, rpp = 25, sortBy = null) {
    const $tableHeader = $("#inspectionTableHeader");
    const $tableBody = $("#inspectionTableBody");
    const $pagination = $(".pagination");
    const recordsPerPage = rpp;
    let currentPage = 1;
    let allData = [];

    // -------------------------------------------
    // RENDER TABLE HEADER WITH SORT CLICK EVENTS
    // -------------------------------------------
    function renderHeader() {
        const headers = [
            { key: "created_at", label: "Created" },
            { key: "sched_status", label: "Scheduled Inspection Status" },
            { key: "order_number", label: "Order No." },
            { key: "scheduled_date", label: "Scheduled Date" },
            { key: "preferredSchedule", label: "Preferred Date", class: "d-none d-md-table-cell" },
            { key: "owner_full_name", label: "Owner" },
            { key: "HasClientAck", label: "Client Acknowledgement", class: "d-none d-md-table-cell" },
            { key: "hasInspectorAck", label: "Inspector Acknowledgement", class: "d-none d-md-table-cell" },
            { key: "hasRecommendingApproval", label: "Chief FSES Acknowledgement", class: "d-none d-md-table-cell" },
            { key: "hasFinalApproval", label: "Fire Marshall Acknowledgement", class: "d-none d-md-table-cell" },
            { key: "ins_full_name", label: "Assigned Inspector", class: "d-none d-md-table-cell" },
            { key: "proceed_instructions", label: "Establishment", class: "d-none d-md-table-cell" },
            { key: "checklist_title", label: "Checklist Type", class: "d-none d-md-table-cell" },
            { key: "fsic_purpose", label: "FSIC Purpose", class: "d-none d-md-table-cell" },
            { key: "noi_desc", label: "Nature of Inspection", class: "d-none d-md-table-cell" },
            { key: "sched_remarks", label: "Remarks", class: "d-none d-md-table-cell" },
            { key: "has_defects", label: "Has Defects", class: "d-none d-md-table-cell" },
            { key: "actions", label: "" }
        ];

        let html = `<tr class="text-center align-middle">`;

        headers.forEach(h => {
            const className = h.class ? h.class : "";

            if (h.key === "actions") {
                html += `<th class="${className}"></th>`;
                return;
            }

            const icon =
                inspectionSortColumn === h.key
                    ? (inspectionSortDirection === "ASC" ? " ▲" : " ▼")
                    : "";

            html += `
                <th class="${className} sort-header" data-col="${h.key}" style="cursor:pointer;">
                    ${h.label}${icon}
                </th>
            `;
        });

        html += `</tr>`;
        $tableHeader.html(html);
    }

    // -------------------------------------------
    // APPLY SORT
    // -------------------------------------------
    function applySorting() {
        if (!inspectionSortColumn) return;

        allData.sort((a, b) => {
            const x = (a[inspectionSortColumn] ?? "").toString().toLowerCase();
            const y = (b[inspectionSortColumn] ?? "").toString().toLowerCase();

            if (inspectionSortDirection === "ASC") {
                return x > y ? 1 : x < y ? -1 : 0;
            } else {
                return x < y ? 1 : x > y ? -1 : 0;
            }
        });
    }

    // -------------------------------------------
    // RENDER TABLE BODY
    // -------------------------------------------
    function renderTable(page) {
        applySorting();

        $tableBody.html("");
        $pagination.html("");

        const start = (page - 1) * recordsPerPage;
        const end = start + recordsPerPage;
        const pageData = allData.slice(start, end);

        if (pageData.length === 0) {
            $tableBody.html(`
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        No inspection schedules found.
                    </td>
                </tr>
            `);
            return;
        }

        const role = user.role || "";
        const subrole = user.subrole || "";
        let rows = "";

        pageData.forEach(item => {
            const status = item.sched_status || "";
            const remarks = item.sched_remarks || "";
            const hasdefects = remarks.toLowerCase().includes("has defects");

            let statusClass = "";
            if (status === "Completed") {
                statusClass = hasdefects
                    ? "bg-success bg-opacity-50 text-gold"
                    : "bg-success bg-opacity-75 text-light";
            } else if (status === "Rescheduled") {
                statusClass = "bg-gold text-dark";
            } else if (status === "Cancelled") {
                statusClass = "bg-danger text-light bg-opacity-75";
            }

            let roleLabel = getRoleLabel(role, subrole);
            let buttons = generateScheduleButtons(item, roleLabel, status);
            let nstatus = status.toLowerCase().replace(/\s+/g, '-');

            rows += `
                <tr class="${statusClass} row-${nstatus}">
                    <td>${formatScheduleDate(item.created_at)}</td>
                    <td>${status == "Completed"
                        ? `<div class='badge text-bg-success'>Completed</div> on ${new Date(item.completed_at).toLocaleString('en-US', {
                            month: 'long', day: 'numeric', year: 'numeric',
                            hour: 'numeric', minute: '2-digit', hour12: true
                        })}`
                        : status}</td>
                    <td>${item.order_number || ""}</td>
                    <td class="${status == "Completed" ? "" : getScheduleClass(item.scheduled_date)}">
                        ${formatScheduleDateOnly(item.scheduled_date)} at ${item.schedule_time}
                    </td>
                    <td class="d-none d-md-table-cell ${status == "Completed" ? "" : getScheduleClass(item.preferredSchedule)}">
                        ${formatScheduleDate(item.preferredSchedule)}
                    </td>
                    <td>
                        <a href="#" class="text-decoration-none fw-bold showContactInfo" data-user-id="${item.owner_id}">${item.owner_full_name || ""}</a>
                        <span>${item.proceed_instructions || ""} </span>
                    </td>
                    <td class="d-none d-md-table-cell">${badgedResponse(item.HasClientAck, "Acknowledged", "Pending", "Denied")}</td>
                    <td class="d-none d-md-table-cell">${badgedResponse(item.hasInspectorAck, "Acknowledged", "Pending", "Denied")}</td>
                    <td class="d-none d-md-table-cell">${badgedResponse(item.hasRecommendingApproval, "Recommended for Approval", "Pending", "Denied")}</td>
                    <td class="d-none d-md-table-cell">${badgedResponse(item.hasFinalApproval, "Approved", "Pending", "Denied")}</td>
                    <td class="d-none d-md-table-cell">${item.ins_full_name || ""}</td>
                    <td class="d-none d-md-table-cell">${item.checklist_title || ""}</td>
                    <td class="d-none d-md-table-cell">${item.fsic_purpose || ""}</td>
                    <td class="d-none d-md-table-cell">${item.noi_desc || ""}</td>
                    <td class="d-none d-md-table-cell">
                        <div class="container-fluid" style="width:300px;max-height:200px;overflow-y:scroll;">
                            ${item.sched_remarks || ""}
                        </div>
                    </td>
                    <td class="d-none d-md-table-cell">
                        ${
                          !item.has_defects
                            ? ""
                            : item.has_defects == "1"
                              ? "<div class='badge text-bg-warning'>Has defects</div>"
                              : "No Issues"
                        }
                    </td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-navy dropdown-toggle" data-bs-toggle="dropdown">
                                ${getIcon('menu')}
                            </button>
                            <ul class="dropdown-menu shadow">
                                ${buttons}
                            </ul>
                        </div>
                    </td>
                </tr>`;
        });

        rows += `<tr><td class="fine-print text-center" colspan="12">**NOTHING FOLLOWS**</td></tr>`;
        $tableBody.html(rows);

        // PAGINATION
        const totalPages = Math.ceil(allData.length / recordsPerPage);
        if (totalPages > 1) {
            $pagination.html(`
                <div class="d-flex justify-content-center align-items-center mt-3">
                    <button class="btn btn-outline-secondary btn-sm prev-page" ${currentPage === 1 ? "disabled" : ""}>
                        <i>${safeGetIcon('chev-left')}</i>
                    </button>
                    <span class="text-muted">Page ${currentPage} of ${totalPages}</span>
                    <button class="btn btn-outline-secondary btn-sm next-page" ${currentPage === totalPages ? "disabled" : ""}>
                        <i>${safeGetIcon('chev-right')}</i>
                    </button>
                </div>
            `);

            $pagination.off("click").on("click", ".prev-page", () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable(currentPage);
                }
            });

            $pagination.on("click", ".next-page", () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable(currentPage);
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
            $tableBody.html(`
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm text-gold"></div>
                        <div>Loading inspection schedules...</div>
                    </td>
                </tr>
            `);
            $pagination.html("");
        },
        success: function (res) {
            if (!res || !res.success || !Array.isArray(res.data)) {
                $tableBody.html(`
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No inspection schedules found.</td>
                    </tr>
                `);
                return;
            }

            allData = res.data;

            // Render header first
            renderHeader();

            // Render body
            renderTable(currentPage);
        }
    });

    // -------------------------------------------
    // SORT HEADER CLICK EVENT
    // -------------------------------------------
    $(document).off("click", ".sort-header").on("click", ".sort-header", function () {
        const col = $(this).data("col");

        if (inspectionSortColumn === col) {
            inspectionSortDirection = inspectionSortDirection === "ASC" ? "DESC" : "ASC";
        } else {
            inspectionSortColumn = col;
            inspectionSortDirection = "ASC";
        }

        renderHeader();       // update ▲▼ icons  
        renderTable(1);       // refresh sorted table
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
$(document).on("click", ".cancel-schedule-btn", function () {
    const schedId = $(this).data("sched-id");
    $("#cancelScheduleId").val(schedId);
    $("#cancelReason").val("");
    $("#cancelScheduleModal").modal("show");
});

$(document).on("click", ".more-details-btn", function(e){
    e.preventDefault();

    let sched_id = $(this).data("sched-id");

    // Clear old content
    $("#moreDetailsBody").html(`<div class='p-3 text-center'>Loading...</div>`);

    // Open offcanvas
    let offcanvas = new bootstrap.Offcanvas('#moreDetails');
    offcanvas.show();

    $.ajax({
        url: "../includes/fetch_inspection_schedule.php",
        type: "POST",
        data: { schedule_id: sched_id },
        dataType: "json",
        success: function(res){
            let data;
            data = res.data[0];
            if (!data) {
                $("#moreDetailsBody").html(`<div class='p-3 text-danger'>No data found.</div>`);
                return;
            }

            // Helper to format null
            const f = (v) => v ? v : "<i class='text-muted'>None Specified</i>";

           let html = `
<table class="table table-bordered table-sm mb-0">

    <tr><th>Scheduled Inspection Status</th>
        <td>
            ${
                data.Inspection_status === "Completed"
                ? `<div class='badge text-bg-success'>Completed</div> on ${new Date(data.completed_at).toLocaleString('en-US', {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                })}`
                : data.Inspection_status || data.sched_status 
            }
        </td>
    </tr>

    <tr><th>Order No.</th>
        <td>${data.order_number || ""}</td>
    </tr>

    <tr><th>Scheduled Date</th>
        <td class="${getScheduleClass(data.scheduled_date)}">
            ${formatScheduleDate(data.scheduled_date)}
        </td>
    </tr>

    <tr><th>Preferred Date</th>
        <td class="${getScheduleClass(data.preferredSchedule)}">
            ${formatScheduleDate(data.preferredSchedule)}
        </td>
    </tr>

    <tr><th>Owner</th>
        <td>${data.owner_full_name || ""}</td>
    </tr>

    <tr><th>Client Acknowledgement</th>
        <td>${badgedResponse(data.HasClientAck, "Acknowledged","Pending","Denied")}</td>
    </tr>


    <tr><th>Inspector Acknowledgement</th>
        <td>${badgedResponse(data.hasInspectorAck, "Acknowledged","Pending","Denied")}</td>
    </tr>

    <tr><th>Fire Marshall Acknowledgement</th>
        <td>${badgedResponse(data.hasRecommendingApproval, "Recommended for Approval","Pending","Denied")}</td>
    </tr>

    <tr><th>Chief Officer Acknowledgement</th>
        <td>${badgedResponse(data.hasFinalApproval, "Approved","Pending","Denied")}</td>
    </tr>


    <tr><th>Assigned Inspector</th>
        <td>${data.ins_full_name || ""}</td>
    </tr>

    <tr><th>Establishment</th>
        <td>${data.proceed_instructions || ""}</td>
    </tr>

    <tr><th>Checklist Type</th>
        <td>${data.checklist_title || ""}</td>
    </tr>

    <tr><th>FSIC Purpose</th>
        <td>${data.fsic_purpose || ""}</td>
    </tr>

    <tr><th>Nature of Inspection</th>
        <td>${data.noi_desc || ""}</td>
    </tr>

    <tr><th>Remarks</th>
        <td>
            <div style="max-height:180px;overflow-y:auto;">
                ${data.sched_remarks || ""}
            </div>
        </td>
    </tr>

    <tr><th>Has Defects</th>
        <td>
            ${
                !data.has_defects
                    ? ""
                    : data.has_defects == "1"
                        ? "<div class='badge text-bg-warning'>Has defects</div>"
                        : "No Issues"
            }
        </td>
    </tr>

</table>
`;

$("#moreDetailsBody").html(html);

        },
        error: function(){
            $("#moreDetailsBody").html(`<div class='p-3 text-danger'>Error loading details.</div>`);
        }
    });

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

$(document).on("click", ".archive-schedule-btn", function () {
    const schedId = $(this).data("sched-id");
    if (!confirm("Are you sure you want to cancel this schedule?")) return;

    $.ajax({
        url: "../includes/archive_schedule.php",
        method: "POST",
        data: {
            schedule_id: schedId
        },
        dataType: "json",
        success: function (res) {
            if (res.success) {
                showAlert("Schedule Archived", "warning");
                loadInspectionSchedules({
                    role: currentUserRole
                });
            } else {
                showAlert(res.message, "danger");
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
// BUILD REPORT HTML 
// ======================================================================
// Simplified frontend - just fetches and displays pre-computed data
function fetchInspectionReport(scheduleId) {
    $.ajax({
        url: 'get_inspection_report.php',
        method: 'POST',
        data: { schedule_id: scheduleId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                buildInspectionReportHTML(scheduleId, response.data);
            } else {
                alert('Failed to load report: ' + response.message);
            }
        },
        error: function() {
            alert('Error fetching inspection report');
        }
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


function formatScheduleDate(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
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



            
function getStatusClassBGColor(status){
    if(status.toLowerCase() === "completed"){
        return "bg-success";
    }
    else if(status.toLowerCase() === "inprogress"){
        return "bg-warning";
    }
    else if(status.toLowerCase() === "pending"){
        return "bg-warning";
    }
    else{
        return "bg-secondary";
    }
}

function getScheduleClass(dateString) {
    if (!dateString) return "";
    const today = new Date();
    const schedDate = new Date(dateString);

    // Calculate difference in full days
    const diffTime = schedDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 2) {
        return "bg-warning";
    } else if (diffDays <= 1) {
        return "bg-danger text-white";
    }
    return "";
}

// ======================================================================
// SIGNATURE: utilities & handlers
// ======================================================================

$(document).ready(function () {

    // Initialize session & load schedules
    checkSession(function (user) {
        currentUser = user;
        console.log(currentUser);
        currentUserRole = user.role;
        console.log(currentUserRole);
        currentSubRole = user.subrole;
        console.log(currentSubRole);

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


function resizeCanvas(canvas, signaturePadInstance) {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
    if (signaturePadInstance) signaturePadInstance.clear();
}


$(document).ready(function () {
    const offcanvasEl = document.getElementById("signatureOffcanvas");
    const bsOffcanvas = new bootstrap.Offcanvas(offcanvasEl);

    // When button is clicked → open offcanvas
    $(document).on("click", ".add-signature", function () {
        userId = $(this).data("user");
        role = $(this).data("role");

        bsOffcanvas.show();

        // Init signature pad after showing
        setTimeout(() => {
            let canvas = document.getElementById("signatureCanvas");
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: "rgb(255,255,255)",
                minWidth: 1, // minimum stroke width
                maxWidth: 2, // maximum stroke width
                penColor: "rgb(0,0,0)" // optional: change pen color
            });
            resizeCanvas(canvas, signaturePad);
            window.addEventListener("resize", () => resizeCanvas(canvas, signaturePad));
        }, 300); // wait for offcanvas animation
    });

    // Clear signature
    $("#clearSignature").on("click", function () {
        signaturePad.clear();
    });
    let croppedSignature = null; // hold cropped data

    // Crop and show preview before saving
    $("#saveSignature").on("click", function () {
        if (signaturePad.isEmpty()) {
            alert("Please draw your signature first.");
            return;
        }

        let canvas = signaturePad.canvas;
        let ctx = canvas.getContext("2d");
        let imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        let data = imageData.data;

        let minX = canvas.width,
            maxX = 0;
        let minY = canvas.height,
            maxY = 0;

        // Detect non-transparent bounds
        for (let y = 0; y < canvas.height; y++) {
            for (let x = 0; x < canvas.width; x++) {
                let idx = (y * canvas.width + x) * 4;
                if (data[idx + 3] > 0) { // alpha > 0
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

        // Crop to bounding box only
        let cropWidth = maxX - minX + 1;
        let cropHeight = maxY - minY + 1;

        let croppedCanvas = document.createElement("canvas");
        croppedCanvas.width = cropWidth;
        croppedCanvas.height = cropHeight;

        let croppedCtx = croppedCanvas.getContext("2d");
        croppedCtx.putImageData(ctx.getImageData(minX, minY, cropWidth, cropHeight), 0, 0);

        // Use cropped PNG for preview
        let croppedDataUrl = croppedCanvas.toDataURL("image/png");
        $("#signaturePreviewImg").attr("src", croppedDataUrl);
        $("#signaturePreviewModal").modal("show");

        // Save
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
                    bsOffcanvas.hide();
                    $("#signaturePreviewModal").modal("hide");
                },
                error: function () {
                    alert("Error saving signature.");
                }
            });
        });
    });

    //


});