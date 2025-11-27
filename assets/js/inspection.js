$(document).ready(function () {

    checkSession(function (user) {
        console.log("DEBUG------");
        loadInspectionTable(user);
        console.log("DEBUG----END");
    });
    
     // Initialize signature offcanvas instance
    const offcanvasEl = document.getElementById("signatureOffcanvas");
    const bsOffcanvas = offcanvasEl ? new bootstrap.Offcanvas(offcanvasEl) : null;
    
    $(document).on("click", ".add-signature", function () {
        userId = $(this).data("user");
        role = $(this).data("role");

        if (bsOffcanvas) bsOffcanvas.show();

        // Initialize signature pad after offcanvas is shown (small delay for animation)
        setTimeout(() => {
            const canvas = document.getElementById("signatureCanvas");
            if (!canvas) return console.warn("signatureCanvas not found");
           signaturePad = new SignaturePad(canvas, {
                backgroundColor: "rgb(255,255,255)",
                penColor: "black",           // you can change to e.g. "#000" or any color you like
                minWidth: 5,                // minimum pen width
                maxWidth: 10                 // maximum pen width
            });
            resizeCanvas(canvas, signaturePad);
            window.addEventListener("resize", () => resizeCanvas(canvas, signaturePad));
        }, 300);
    });

    
function resizeCanvas(canvas, signaturePadInstance) {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
    if (signaturePadInstance) signaturePadInstance.clear();
}
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

        const croppedDataUrl = croppedCanvas.toDataURL("image/png");
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

});


$(document).on('keyup',"#SearchIns",function(){
    let val = $(this).val();
    loadInspectionTable(currentUser, val);
});




// function getRoleLabel(user) {
//     if (!user) return "Unauthorized";
//     const { role, subrole } = user;

//     if (role == "Administrator" && ["Chief FSES", "Recommending Approver"].includes(subrole)) {
//         return "Recommending Approver";
//     } else if (role == "Administrator" && ["Admin_Assistant"].includes(subrole)) {
//         return "Admin_Assistant";
//     } else if (role == "Administrator" && ["Fire Marshall"].includes(subrole)) {
//         return "Approver";
//     } else if (role == "Client" || subrole === "Client") {
//         return "Client";
//     }
//     else if (role == "Inspector" || subrole === "Fire Officer"){
//         return "Inspector";
//     }
//     else{
//         return "Unauthorized";
//     }
    
// }


$(document).on("click", ".btn-action", function (e) {
    e.preventDefault();

    const $thisbtn = $(this);
    const inspectionId = $thisbtn.data("id");
    const currentUser = JSON.parse(localStorage.getItem("currentUser")) || {};
    const roleLabel = getRoleLabel(currentUser);
    
    console.log(localStorage.getItem("currentUser"));

    let sendThis = null;

    if ($thisbtn.hasClass("btn-recommend")) {
        sendThis = { action: "recommend", id: inspectionId };
    } else if ($thisbtn.hasClass("btn-approve")) {
        sendThis = { action: "approve", id: inspectionId };
    } else if ($thisbtn.hasClass("btn-receive")) {
        sendThis = { action: "receive", id: inspectionId };
    } else{
        return;
    }

    if (sendThis) {
        $.ajax({
            url: "../includes/fsic_actions.php",
            method: "POST",
            dataType: "json",
            data: { sendThis },
            beforeSend: function () {
                $thisbtn.prop("disabled", true).html(
                    `<span class="spinner-border spinner-border-sm me-1"></span> Processing...`
                );
            },
            success: function (r) {
                showAlert(r.message, r.success ? "success" : "danger");

                // reload table only after success
                if (r.success) {
                    // Re-enable after short delay to show feedback
                    
                    setTimeout(() => {
                        $thisbtn.html(
                            `${getIcon('patchcheck')} Approved`
                        );

                        loadInspectionTable(currentUser);
                    }, 300);
                } else {
                    $thisbtn.prop("disabled", false).html(`<i class="bi bi-arrow-repeat"></i> Retry`);
                }
            },
            error: function () {
                showAlert("Failed to process the request.", "danger");
                $thisbtn.prop("disabled", false).text("Retry");
            }
        });
    }
});


function loadInspectionTable(user = {}, search = "") {
    
    $("#inspectionTableBody").html(`
        <tr><td colspan="14" class="text-center text-muted py-4">
            Loading inspections...
        </td></tr>
    `);

    $.ajax({
        url: "../includes/fetch_inspections.php",
        method: "GET",
        dataType: "json",
        data: {
            search: search || "",
            role: user.role || "",
            subrole: user.subrole || "",
            user_id: user.id || ""
        },
        success: function (res) {
            if (res.success && res.data && res.data.length) {
                let rows = "";

                // Determine role label
                let roleLabel = "Unauthorized";

                roleLabel = getRoleLabel(user.role, user.subrole);

                // Generate table rows
                res.data.forEach(item => {
                    const{} = item;

                    const {
                        schedule_id,
                        inspection_id,
                        order_number,
                        scheduled_date,
                        schedule_time,
                        started_at,
                        completed_at,
                        building_name,
                        location_of_construction,
                        owner_name,
                        hasRecoApproval,
                        hasFinalApproval,
                        hasBeenReceived,
                        dateRecommended,
                        dateApproved,
                        dateReceived,
                        inspector_name,
                        checklist_type,
                        // New statistics columns
                        total_items,
                        passed_items,
                        failed_items,
                        not_applicable_items,
                        required_items,
                        required_passed,
                        compliance_rate,
                        inspection_score,
                        has_Defects
                    } = item;

                    // ====== ROLE-BASED ACTION BUTTONS ======
                    let actionButtons = "";
                if(!has_Defects) {
                    if (roleLabel === "Admin_Assistant") {
                        actionButtons += createButton({
                            classList: ['btn', 'btn-sm', 'btn-outline-secondary', 'btn-download', 'downloadCertificate'],
                            props: { type: 'button' },
                            otherAttr: { 
                                'data-id': inspection_id,
                                title: 'Download certificate'
                            },
                            icon: getIcon('pdf'),
                            label: 'Download FSED-5F'
                        });
                    } else if (roleLabel === "Recommending Approver" || roleLabel === "Chief FSES") {
                        const isRecommended = ["1", 1, "Y"].includes(hasRecoApproval);
                        const disabled = isRecommended ? "disabled" : "";
                        const title = isRecommended ? "Already recommended" : "Recommend for approval";

                        actionButtons = createButton({
                            classList: ['btn-action', 'btn', 'btn-sm', 'btn-warning', 'btn-recommend', 'me-1', disabled].filter(Boolean),
                            props: { type: 'button' },
                            otherAttr: { 
                                'data-id': inspection_id,
                                title: title,
                                disabled: isRecommended ? 'disabled' : undefined
                            },
                            icon: '<i class="bi bi-check2-square"></i>',
                            label: title
                        });
                    } else if (roleLabel === "Final Approver" || roleLabel === "Approver" || roleLabel === "Fire Marshall") {
                        const isApproved = ["1", 1, "Y"].includes(hasFinalApproval);
                        const disabled = isApproved ? "disabled" : "";
                        const title = isApproved ? "Already Approved" : "Approve Certificate";

                        actionButtons = createButton({
                            classList: ['btn-action', 'btn', 'btn-sm', 'btn-success', 'btn-approve', 'me-1', disabled].filter(Boolean),
                            props: { type: 'button' },
                            otherAttr: { 
                                'data-id': inspection_id,
                                title: title,
                                disabled: isApproved ? 'disabled' : undefined
                            },
                            icon: '<i class="bi bi-check-circle"></i>',
                            label: title
                        });
                    } else if (roleLabel === "Client") {
                        const isReceived = ["1", 1, "Y"].includes(hasBeenReceived);
                        const disabled = isReceived ? "disabled" : "";
                        const title = isReceived ? "Certificate already received" : "Mark as received";

                        actionButtons = createButton({
                            classList: ['btn-action', 'btn', 'btn-sm', 'btn-primary', 'btn-receive', 'me-1', disabled].filter(Boolean),
                            props: { type: 'button' },
                            otherAttr: { 
                                'data-id': inspection_id,
                                title: title,
                                disabled: isReceived ? 'disabled' : undefined
                            },
                            icon: '<i class="bi bi-hand-thumbs-up"></i>',
                            label: title
                        }) + createButton({
                            classList: ['btn-action', 'btn', 'btn-sm', 'btn-outline-secondary', 'btn-download'],
                            props: { type: 'button' },
                            otherAttr: { 
                                'data-id': inspection_id,
                                title: 'Download certificate'
                            },
                            icon: getIcon('pdf'),
                            label: 'Download'
                        });
                    } else {
                        actionButtons = `<span class="text-muted fst-italic">No actions available</span>`;
                    }
                }

                    // Add View Report button for all roles
                    actionButtons += createButton({
                        classList: ['btn-action', 'btn', 'btn-sm', 'btn-info', 'btn-view-report', 'mt-1'],
                        props: { type: 'button' },
                        otherAttr: { 
                            'data-sched-id': schedule_id,
                            title: 'View detailed inspection report'
                        },
                        icon: getIcon('eye'),
                        label: 'View Report'
                    });

                    // ====== FORMAT DATES ======
                    const formatDate = (date) =>
                        date
                            ? new Date(date).toLocaleString("en-US", {
                                  month: "long",
                                  day: "numeric",
                                  year: "numeric",
                                  hour: "numeric",
                                  minute: "2-digit",
                                  hour12: true
                              })
                            : "-";

                    // ====== STATUS BADGES ======
                    const recoBadge = badgedResponse(
                        hasRecoApproval,
                        "Recommended for Approval",
                        "Pending",
                        "Denied",
                        dateRecommended
                    );

                    let finalBadge =
                        ["1", 1].includes(hasRecoApproval)
                            ? badgedResponse(
                                  hasFinalApproval,
                                  "Approved",
                                  "Pending",
                                  "Denied",
                                  dateApproved
                              )
                            : `<div class="badge text-bg-secondary p-2">Waiting for Recommendation</div>`;

                    let receiveBadge =
                        ["1", 1].includes(hasFinalApproval)
                            ? badgedResponse(
                                  hasBeenReceived,
                                  "Received",
                                  "For Claiming",
                                  "Denied",
                                  dateReceived
                              )
                            : `<div class="badge text-bg-secondary p-2">Waiting for Approval</div>`;

                    // ====== INSPECTION RESULTS BADGES ======
                    const hasDefects = ["1", 1].includes(has_Defects);
                    const defectBadge = hasDefects ? 
                        `<span class="badge bg-warning">Has Defects</span>` : 
                        `<span class="badge bg-success">Passed</span>`;

                    const scoreClass = inspection_score >= 75 ? "success" : "danger";
                    const scoreBadge = `<span class="badge bg-${scoreClass}">${inspection_score || 0} %</span>`;

                    // ====== COMPREHENSIVE STATISTICS DISPLAY ======
                    let statisticsHTML = "";
                    if (total_items > 0) {
                        statisticsHTML = `
                            <div class="small">
                                <div class="d-flex justify-content-between">
                                    <span class="text-success">${passed_items} Passed</span>
                                    <span class="text-danger">${failed_items} Failed</span>
                                    <span class="text-info">${not_applicable_items} N/A</span>
                                </div>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div class="progress-bar bg-success" 
                                         style="width: ${(passed_items / total_items) * 100}%" 
                                         title="Passed"></div>
                                    <div class="progress-bar bg-danger" 
                                         style="width: ${(failed_items / total_items) * 100}%" 
                                         title="Failed"></div>
                                    <div class="progress-bar bg-info" 
                                         style="width: ${(not_applicable_items / total_items) * 100}%" 
                                         title="Not Applicable"></div>
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        Compliance: <strong>${compliance_rate}%</strong> | 
                                        Required: ${required_passed}/${required_items} passed
                                    </small>
                                </div>
                            </div>
                        `;
                    } else {
                        statisticsHTML = `<span class="text-muted small">No inspection data</span>`;
                    }
                    const formatDateOnly = (date) =>
                        date
                            ? new Date(date).toLocaleDateString("en-US", {
                                month: "long",
                                day: "numeric",
                                year: "numeric"
                            })
                            : "-";

                    // ====== TABLE ROW ======
                    rows += `
                        <tr class="text-center align-middle">
                            <td>${order_number || "—"}</td>
                            <td>${formatDateOnly(scheduled_date)} at ${schedule_time}</td>
                            <td>${formatDate(started_at)}</td>
                            <td>${formatDate(completed_at)}</td>
                            <td class="text-start">
                                <b>${building_name || "—"}</b><br>
                                <span style="font-size:8pt">
                                    ${location_of_construction || ""}<br>
                                    Owned by: ${owner_name || ""}
                                </span>
                            </td>
                            <td>
                                ${defectBadge}<br>
                                ${scoreBadge}
                            </td>
                            <td>${statisticsHTML}</td>
                            <td>${recoBadge}</td>
                            <td>${finalBadge}</td>
                            <td>${receiveBadge}</td>
                            <td>${inspector_name || "—"}</td>
                            <td>${checklist_type || "—"}</td>
                               <td class="text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-navy dropdown-toggle" data-bs-toggle="dropdown">
                                ${getIcon('menu')}
                            </button>
                            <ul class="dropdown-menu shadow">
                                ${actionButtons}
                            </ul>
                        </div>
                    </td>
                        </tr>`;
                });

                $("#inspectionTableBody").html(rows);
                
                // Re-bind event handlers for the new buttons
                bindInspectionActionHandlers();
                
            } else {
                $("#inspectionTableBody").html(`
                    <tr><td colspan="14" class="text-center text-muted py-4">
                        No inspection records found.
                    </td></tr>
                `);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error loading inspections:", error);
            $("#inspectionTableBody").html(`
                <tr><td colspan="14" class="text-center text-danger py-4">
                    Failed to load inspection data. Please try again.
                </td></tr>
            `);
        }
    });
}

// Helper function to bind event handlers for action buttons
function bindInspectionActionHandlers() {
    // View Report button
    $(document).off("click", ".btn-view-report").on("click", ".btn-view-report", function () {
        const schedId = $(this).data("sched-id");
        loadInspectionReport(schedId);
    });

    // Download button
    $(document).off("click", ".btn-download").on("click", ".btn-download", function () {
        const inspectionId = $(this).data("id");
        downloadCertificate(inspectionId);
    });

    // // Recommend button
    // $(document).off("click", ".btn-recommend").on("click", ".btn-recommend", function () {
    //     const inspectionId = $(this).data("id");
    //     recommendInspection(inspectionId);
    // });

    // // Approve button
    // $(document).off("click", ".btn-approve").on("click", ".btn-approve", function () {
    //     const inspectionId = $(this).data("id");
    //     approveInspection(inspectionId);
    // });

    // // Receive button
    // $(document).off("click", ".btn-receive").on("click", ".btn-receive", function () {
    //     const inspectionId = $(this).data("id");
    //     receiveCertificate(inspectionId);
    // });
}

// Helper function to load inspection report
function loadInspectionReport(scheduleId) {
    console.log("loadInspectionReport:" + scheduleId);
    $.ajax({
        url: "../includes/export_inspection_report.php",
        type: "POST",
        data: { schedule_id: scheduleId },
        dataType: "json",
        success: function (res) {
            if (res.success) {
                const reportData = res.data;
                const html = buildInspectionReportHTML(scheduleId, reportData, false);
                $("#inspectionReportModal .modal-body").html(html);
                $("#inspectionReportModal").modal("show");
            } else {
                alert("Failed to load report: " + res.message);
            }
        },
        error: function (xhr, status, err) {
            console.error("Error loading report:", err);
            alert("Error loading inspection report.");
        }
    });
}

// Placeholder functions for other actions
function downloadCertificate(inspectionId) {
    const currentUser = JSON.parse(localStorage.getItem("currentUser")) || {};
    const roleLabel = getRoleLabel(currentUser);
    
    // Simple redirect to the certificate page
    const url = `../pages/print_certificate.php?inspection_id=${inspectionId}&roleLabel=${encodeURIComponent(roleLabel)}`;
    window.open(url, '_blank');
}

// function recommendInspection(inspectionId) {
//     console.log("Recommend inspection:", inspectionId);
//     // Implement recommendation logic
// }

// function approveInspection(inspectionId) {
//     console.log("Approve inspection:", inspectionId);
//     // Implement approval logic
// }

// function receiveCertificate(inspectionId) {
//     console.log("Receive certificate for inspection:", inspectionId);
//     // Implement receive logic
// }