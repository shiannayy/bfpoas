const passedIcon = getIcon("patchcheck");
const failedIcon = getIcon("patchcaution");
const $doneBtn = $(".done-inspection");

// Declare isConfirming at the top so all functions can access it
let isConfirming = false;

// Auto-save section when any input changes
$(document).ready(function () {
    $("footer").hide();
    $("main").addClass("pb-5");
    // Scroll watcher
    $(window).on("scroll", toggleDoneButton);
    toggleDoneButton(); // on load

    // Reset button state when modal is hidden (user cancels)
    $('#inspectionConfirmationModal').on('hidden.bs.modal', function() {
        $('#confirmInspectionSubmit').html(`
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle me-1" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
            </svg>
            Yes, Submit Inspection
        `).prop('disabled', false);
    });
});


function toggleDoneButton() {
    const scrollPosition = $(window).scrollTop() + $(window).height();
    const pageHeight = $(document).height() - 10; // tolerance for bottom offset

    if (scrollPosition >= pageHeight) {
        $doneBtn.addClass("show");
        $("#alerts").fadeOut();
    } else {
        $doneBtn.removeClass("show");
    }
}
$(document).on("change", ".section-input, .proof-upload, .manualpassbtn, .notApplicableBtn",  function(){
    let $sectionCard = $(this).closest(".card");
    let section = $sectionCard.data("section");
    let scheduleId = $("input[data-name='schedule_id']").val();
    let checklistId = $("input[data-name='checklist_id']").val();
    let inspectionId = $("input[data-name='inspection_id']").val();

    // Prepare FormData to handle text + file data
    let formData = new FormData();
    formData.append("schedule_id", scheduleId);
    formData.append("checklist_id", checklistId);
    formData.append("inspection_id", inspectionId);
    formData.append("section", section);
    
    // Collect all inputs
    $sectionCard.find(".section-input").each(function () {
        let name = $(this).attr("name");
        let value;

        if ($(this).attr("type") === "checkbox") {
            value = $(this).is(":checked") ? "1" : "0";
        } else if ($(this).val() === "") {
            value = "null";
        } else {
            value = $(this).val();
        }
        formData.append(`items[${name}]`, value);
    });

    $sectionCard.find(".manualpassbtn").each(function () {
        let name = $(this).attr("name");
        let value = $(this).is(":checked") ? "1" : "0";
        formData.append(`manual_pass[${name}]`, value);
    });
    $sectionCard.find(".notApplicableBtn").each(function () {
        let name = $(this).attr("name");
        let value = $(this).is(":checked") ? "1" : "0";
        formData.append(`notApplicable[${name}]`, value);
    });

    // 🔸 Collect proof image(s) if any — with validation
    const validTypes = [
        "image/jpeg",
        "image/jpg",
        "image/png",
        "image/webp",
        "image/avif",
        "image/heic",
        "image/heif"
    ];

    let invalidFiles = [];
    $sectionCard.find(".proof-upload").each(function () {
        const itemId = $(this).data("item-id");
        if (this.files.length > 0) {
            const file = this.files[0];

            // Validate file type
            if (!validTypes.includes(file.type.toLowerCase())) {
                invalidFiles.push(file.name);
                return; // Skip invalid file
            }

            formData.append(`proof_item_${itemId}`, file);
        }
    });

    // 🚫 Stop upload if invalid files are detected
    if (invalidFiles.length > 0) {
        alert("Unsupported file format(s):\n" + invalidFiles.join("\n") + "\n\nAllowed: JPG, JPEG, PNG, WEBP, AVIF, HEIC, HEIF");
        return;
    }

    // Show spinner
    let $statusDiv = $sectionCard.find(".autosave-status");
    $statusDiv.html(`<div class="spinner-border spinner-border-sm text-primary" role="status"></div>`);

    // Send via AJAX with file support
    $.ajax({
        url: "../includes/auto_save_checklist_section.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (res) {
            if (res.success === true) {
                $statusDiv.html(`<i class="text-success">${getIcon('patchcheck')}</i> <small class="text-muted">Saved at ${res.time}</small> `);
                for (let itemId in res.remarks) {
                    let remark = res.remarks[itemId].remarks;
                    
                    let $badge = $(`.evaluation-result[data-item-id="eval_${itemId}"]`);

                    if ($badge.length) {                            
                        $badge.removeClass("text-dark text-light text-bg-danger text-bg-warning text-bg-success text-bg-light text-bg-info");

                        if (remark == 1) {
                            $badge.html(passedIcon + " Passed").addClass("text-bg-success text-light");
                            console.log("PASSED: " + itemId + ":" + remark);

                            $(`.not-applicable#na_${itemId}`).addClass("d-none");

                            //$(`.manual-pass#mp_${itemId}`).removeClass("d-none");
                            //$(`.manualpassbtn#manual_pass_${itemId}`).prop("checked",true);
                        }
                        else if (remark == 0) {
                            $badge.html(failedIcon + " Failed").addClass("text-bg-danger");
                            $(`.not-applicable#na_${itemId}`).removeClass("d-none");
                            console.log("FAILED:" + itemId + ":" + remark);
                        }
                        else if (remark == 9){
                            $badge.html("No Criteria").addClass("text-bg-warning text-dark");
                        // $(`.manual-pass#mp_${itemId}`).removeClass("d-none");
                            $(`.not-applicable#na_${itemId}`).removeClass("d-none");
                            console.log("MP:" + itemId + ":" + remark);
                        }
                        else if(remark == 8){
                            $badge.html("N/A").addClass("text-bg-info");
                            $(`.not-applicable#na_${itemId}`).removeClass("d-none");

                            console.log("N/A:" + itemId + ":" + remark);
                        }
                    }

                }


                /* 🔸 Dynamically show the image thumbnail fade-in if upload was successful */
                if (res.uploads) {
                    for (let uploadItemId in res.uploads) {
                        const upload = res.uploads[uploadItemId];

                        if (upload.upload_status === "Successful" && upload.upload_filename) {
                            const scheduleId = $("input[name='schedule_id']").val();
                            const imgPath = `../assets/proof/Schedule_${scheduleId}/${upload.upload_filename}`;

                            // Find or create a preview container near the proof input
                            let $proofContainer = $(`#proof_item_${upload.upload_item_id}`).closest(".input-group").find(".proof-preview");
                            if ($proofContainer.length === 0) {
                                $(`#proof_item_${upload.upload_item_id}`).closest(".input-group")
                                    .append(`<div class="proof-preview mt-2"></div>`);
                                $proofContainer = $(`#proof_item_${upload.upload_item_id}`).closest(".input-group").find(".proof-preview");
                            }

                            // Create thumbnail markup (same structure as backend)
                            const newThumb = $(`
                        <a href="#" class="open-proof-modal" data-proof-file="${upload.upload_filename}" data-item-id="${upload.upload_item_id}" style="display:none;">
                            <img id="img_proof_${upload.upload_item_id}" 
                                 src="${imgPath}" 
                                 alt="Proof" 
                                 class="img-thumbnail response-img-proof" 
                                 style="width:50px;height:50px;object-fit:cover;">
                        </a>
                    `);

                            // Remove old thumbnail (if any)
                            $proofContainer.find(`#img_proof_${upload.upload_item_id}`).closest("a").remove();

                            // Append and fade in new one
                            $proofContainer.append(newThumb);
                            newThumb.fadeIn(500);
                        }
                    }
                }

            } else {
                $statusDiv.text("Error saving: " + res.message);
            }
        },
        error: function (xhr, status, err) {
            console.error("AJAX error:", err);
            $statusDiv.text("Upload failed. Check console for details.");
        }
    });

}); 
    
// Handle form submission
$(document).on("submit", "form#inspectionForm", function (e) {
    // If user hasn't confirmed through the modal, prevent submission
    if (!isConfirming) {
        e.preventDefault();
        return false;
    }
    
    // Reset the flag for next time (do this immediately to prevent double submission)
    isConfirming = false;
    e.preventDefault(); // Always prevent default since we're using AJAX
    
    // Store form data before disabling buttons
    const formData = $(this).serialize();
    
    // Get schedule ID from form data
    const scheduleId = $(this).find('input[name="schedule_id"]').val();
    
    // Store for later use
    currentScheduleId = scheduleId;
    
    // Disable submit button and show loading
    const $submitBtn = $('#confirmInspectionSubmit');
    $submitBtn.html(`
        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
        Submitting...
    `).prop('disabled', true);
    
    // Hide done button if it exists
    const $doneBtn = $('#doneBtn'); // Assuming this exists
    if ($doneBtn.length) {
        $doneBtn.hide();
    }
    
    $.ajax({
        url: "../includes/complete_inspection.php",
        method: "POST",
        data: formData,
        dataType: "json",
        success: async function (res) {
            if (res.status === "success") {
                // Extract email info from response
                const ownerEmail = res.email_info.ownerEmail || '';
                //const recommendingEmail = res.email_info.recommendingEmail || '';
                //const finalApproverEmail = res.email_info.finalApprover || '';
                const orderNumber = res.email_info.orderNumber || '';
                // Create recipients array (filter out empty emails)
                const recipients = [];
                if (ownerEmail) recipients.push(ownerEmail);
                //if (recommendingEmail) recipients.push(recommendingEmail);
                //if (finalApproverEmail) recipients.push(finalApproverEmail);
                
                // Show success message
                showAlert("Inspection submitted successfully!", "success", 5000);
                
                try {
                    // 1. Build report for display (with print capabilities)
                    const displayResult = await buildInspectionReport(
                        scheduleId, 
                        'reportModalBody', 
                        { isForPrint: true }
                    );
                    
                    // Show report in canvas
                    showReportCanvas(displayResult.html, res);
                    
                    // 2. Send email if compliance > 75% AND there are recipients
                    if (res.compliance_rate > 75 && recipients.length > 0) {
                        // Show loading indicator for email sending
                        const $emailLoader = $('.done-inspection');
                        if ($emailLoader.length) {
                            $emailLoader.html('<div class="spinner-border text-danger" role="status"></div><span class="fw-light ms-1 my-auto">Sending email...</span>');
                        }
                        
                        try {
                            // Generate token using cert_token.php
                            const tokenResponse = await $.post('../includes/cert_token.php', {
                                schedule_id: scheduleId
                            });
                            
                            if (tokenResponse.success) {
                                const acknowledgementToken = tokenResponse.data.token;
                                
                                // FIXED: Include ALL required parameters in the link
                                const acknowledgementLink = `<?= Config::WEBSITE_BASE_URL ?>/email_ack/cert.php?token=${acknowledgementToken}&schedule_id=${scheduleId}&role=client`;
                                
                                // Build report specifically for email
                                const emailResult = await buildInspectionReport(
                                    scheduleId,
                                    null,
                                    { 
                                        isForEmail: true,
                                        includeAcknowledgement: true,
                                        acknowledgementLink: acknowledgementLink,
                                        scheduleId: scheduleId,
                                        role: 'client'
                                    }
                                );
                                
                                // Send email with proper email HTML
                                await sendInspectionReportEmail(
                                    orderNumber, 
                                    emailResult.html, 
                                    res, 
                                    recipients,
                                    acknowledgementLink
                                );
                                
                                // Clear loading indicator
                                if ($emailLoader.length) {
                                    showAlert("Email Sent!", "success", 5000,null,null,"start-50 top-0");
                                    $emailLoader.html(`${getIcon("patchcheck")} Email Sent!`);
                                }
                            } else {
                                showAlert("Report generated but token creation failed. Email not sent.", "warning", 5000);
                            }
                        } catch (error) {
                            console.error("Error in email process:", error);
                            showAlert("Email process failed.", "warning");
                        }
                    } else if (recipients.length === 0) {
                        console.warn("No email recipients found. Email not sent.");
                        showAlert("Report generated but no email recipients found.", "info", 5000);
                    } else {
                        console.log(`Compliance rate ${res.compliance_rate}% is below 75%, email not sent.`);
                    }
                    
                } catch (error) {
                    console.error("Error in report/email process:", error);
                    showAlert("Report generated but email process failed.", "warning");
                }
                
            } else {
                showAlert("Error: " + res.message, "danger", 99999);
            }
            
            // Reset button
            resetSubmitButton($submitBtn);
            
            // Show done button again
            if ($doneBtn.length) {
                $doneBtn.show();
            }
        },
        error: function (xhr, status, err) {
            console.error("AJAX Error:", err);
            showAlert("An unexpected error occurred.", "danger");
            resetSubmitButton($('#confirmInspectionSubmit'));
            
            // Show done button again on error
            if ($doneBtn.length) {
                $doneBtn.show();
            }
        }
    });
    return false;
});

// Function to show report in Bootstrap 5 canvas (offcanvas)
function showReportCanvas(html, inspectionResult) {
    // Create or get the offcanvas element
    let $offcanvas = $('#reportOffcanvas');
    
    if ($offcanvas.length === 0) {
        // Create offcanvas if it doesn't exist
        $('body').append(`
            <div class="offcanvas offcanvas-bottom" tabindex="-1" id="reportOffcanvas" 
                 aria-labelledby="reportOffcanvasLabel" style="height: 85vh;">
                <div class="offcanvas-header bg-light d-flex">
                    <h5 class="offcanvas-title" id="reportOffcanvasLabel">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Inspection Report
                        <span class="badge ms-2 bg-${inspectionResult.has_defects ? 'warning' : 'success'}">
                            ${inspectionResult.has_defects ? 'Has Defects' : 'Passed'}
                        </span>
                    </h5>
                    <div class="ms-auto my-auto">
                        <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="printReport()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                </div>
                <div class="offcanvas-body p-0" id="reportModalBody">
                    <!-- Report content will go here -->
                </div>
                <div class="offcanvas-footer bg-light p-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">
                                Compliance Rate: <strong>${inspectionResult.compliance_rate}%</strong> | 
                                Score: <strong>${inspectionResult.score}</strong>
                            </span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary btn-sm me-2" data-bs-dismiss="offcanvas">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        $offcanvas = $('#reportOffcanvas');
    }
    
    // Set the content
    $('#reportModalBody').html(html);
    
    // Show the offcanvas
    const offcanvas = new bootstrap.Offcanvas($offcanvas[0]);
    offcanvas.show();
    
    // Add event listener for when offcanvas is hidden
    $offcanvas.on('hidden.bs.offcanvas', function () {
        // Clean up if needed
        $('#reportModalBody').empty();
    });
}

// Function to send inspection report email
async function sendInspectionReportEmail(orderNumber, emailHtml, inspectionResult, recipients, acknowledgementLink = '') {
    try {
        // Determine pass/fail status
        const isPass = inspectionResult.compliance_rate > 75;
        const statusText = isPass ? 'PASS' : 'FAIL';
        
        // Create email subject
        const subject = `Inspection Report Order Number ${orderNumber} - Status: ${statusText}`;
        
        // Send the email
        const emailResponse = await sendEmail(
            subject,
            recipients,
            emailHtml
        );
        
        if (emailResponse.success) {
            const recipientCount = recipients.length;
            //const recipientList = recipients.join(', ');
            showAlert(`Report sent to ${recipientCount} recipient(s)!`, "success", 5000);
        } else {
            showAlert("Report generated but email could not be sent.", "warning", 5000);
        }
        
        return emailResponse;
        
    } catch (error) {
        console.error("Error sending email:", error);
        showAlert("Error sending email: " + error.message, "danger", 5000);
        throw error;
    }
}
// Helper function to reset submit button
function resetSubmitButton($button) {
    $button.html(`
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle me-1" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
            <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
        </svg>
        Yes, Submit Inspection
    `).prop('disabled', false);
}

// Download PDF function (you need to implement this)
function downloadReportPDF() {
    // Implement PDF generation and download
    showAlert("PDF download feature coming soon!", "info");
}

// Helper function to reset submit button
function resetSubmitButton($button) {
    $button.html(`
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle me-1" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
            <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
        </svg>
        Yes, Submit Inspection
    `).prop('disabled', false);
}


// Handle "Done with Inspection?" button click
$(document).on("click", ".done-inspection", function(e) {
    e.preventDefault();
    $('#inspectionConfirmationModal').modal('show');
});

// Handle confirmation button click
$(document).on("click", "#confirmInspectionSubmit", function() {
    const $confirmBtn = $(this);
    const originalText = $confirmBtn.html();
    console.log(originalText);
    // Add loading state
    $confirmBtn.html(`
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        Submitting...
    `);
    $confirmBtn.prop("disabled", true);
    console.log("button disabled");
    // Set flag to indicate we're confirming
    isConfirming = true;
    
    // Hide the modal using jQuery
    $('#inspectionConfirmationModal').modal('hide');
    
    // Trigger the form submission
    $("#inspectionForm").trigger("submit");
    
    // Reset button after 3 seconds if nothing happens (fallback)
    setTimeout(function() {
        // Only reset if still in loading state (submission didn't complete)
        if ($confirmBtn.html().includes('spinner-border')) {
            $confirmBtn.html(originalText);
            $confirmBtn.prop("disabled", false);
            isConfirming = false;
        }
    }, 3000);
});

$(document).on("click", ".open-proof-modal", function (e) {
    e.preventDefault();
    console.log("clicked open-proof-modal");
    // Get response ID and proof file
    const responseId = $(this).data("item-id");
    const proofFile = $(this).data("proof-file");
    const scheduleId = $("input[name='schedule_id']").val();

    // Build image path (you can adjust this path if needed)
    const imagePath = `../assets/proof/Schedule_${scheduleId}/${proofFile}`;

    // Insert image dynamically into modal body
    $("#proofModalBody").html(`
    <img src="${imagePath}" 
            alt="Proof Image" 
            class="img-fluid rounded shadow-sm" 
            style="max-height:80vh;object-fit:contain;">
`);

    // Optional: Add filename or details
    $(".modal-title").text(`Uploaded Proof (Response #${responseId})`);

    // Show modal
    const proofModal = new bootstrap.Modal(document.getElementById("proofModal"));
    proofModal.show();
});




/**
 * Fetches and displays inspection report data
 * @param {number|string} scheduleId - The schedule ID
 * @param {boolean} showModal - Whether to show the modal immediately
 * @returns {Promise} Promise that resolves with report data
 */
function fetchInspectionReport(scheduleId, showModal = true) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "../includes/export_inspection_report.php",
            type: "POST",
            data: {
                schedule_id: scheduleId
            },
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    const reportData = res.data; // Contains inspection_details, statistics, and inspection_items
                    
                    if (showModal) {
                        const html = buildInspectionReportHTML(scheduleId, reportData, false);
                        $("#inspectionReportModal .modal-body").html(html);
                        $("#inspectionReportModal").modal("show");
                        $("#exportReportBtn").attr("data-sched-id", scheduleId);
                        console.log("DEBUG: #exportReportBtn " + scheduleId);
                    }
                    
                    resolve({
                        success: true,
                        data: reportData,
                        scheduleId: scheduleId,
                        html: showModal ? html : null
                    });
                    
                } else {
                    if (showModal) {
                        alert("Failed to load report: " + res.message);
                    }
                    reject({
                        success: false,
                        message: res.message,
                        scheduleId: scheduleId
                    });
                }
            },
            error: function (xhr, status, err) {
                console.error("Error generating report:", err);
                if (showModal) {
                    alert("Error loading inspection report. Please try again.");
                }
                reject({
                    success: false,
                    error: err,
                    scheduleId: scheduleId
                });
            }
        });
    });
}