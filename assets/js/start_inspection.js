    const passedIcon = getIcon("patchcheck");
    const failedIcon = getIcon("patchcaution");
    const $doneBtn = $(".done-inspection");
    
// Auto-save section when any input changes
$(document).ready(function () {
    $("footer").hide();
    $("main").addClass("pb-5");

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
    // Scroll watcher
    $(window).on("scroll", toggleDoneButton);
    toggleDoneButton(); // on load

    // 🔹 AUTO-SAVE AND UPLOAD HANDLER
        // Wait for a short delay to ensure inputs are ready
});



$(document).on("change", ".section-input, .proof-upload, .manualpassbtn, .notApplicableBtn",  function(){
        let $sectionCard = $(this).closest(".card");
        let section = $sectionCard.data("section");
        let scheduleId = $("input[name='schedule_id']").val();
        let checklistId = $("input[name='checklist_id']").val();
        let inspectionId = $("input[name='inspection_id']").val();

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
    


    // 🔹 SUBMIT FORM FINALIZATION
    $(document).on("submit", "#inspectionForm", function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const $doneBtn = $(".done-inspection");

        $doneBtn.removeClass("show");

        $.ajax({
            url: "../includes/complete_inspection.php",
            method: "POST",
            data: formData,
            dataType: "json",
            success: function (res) {
                if (res.status === "success") {
                    let message = "";
                    if (res.has_defects) {
                        message += `Inspection completed with defects.
                                        <br>Compliance Rate: <b>${res.compliance_rate}%</b>
                                        <br>
                                        <hr>`;
                        message += `<b>Issues Found:</b><ul style="margin-top:5px;">`;
                        res.issues.forEach((issue) => {
                            message += `<li><b>${issue.item_text}</b> (${issue.criteria}) - Response: <i>${issue.response_value}</i></li>`;
                        });
                        message += `</ul>`;
                        showAlert(message, "gold", 99999, "Done", "?page=ins_sched&has_defects");
                    } else {
                        message = `Inspection completed successfully!
                                        <br>All items passed.
                                        <br>Compliance Rate: 
                                        <b>${res.compliance_rate}%</b>
                                        <br>Passed Score Rate: 
                                        <b>${res.score}%</b>
                                        `;
                        showAlert(message, "success", 99999, "Done", "?page=ins_sched");
                    }
                } else {
                    showAlert("Error: " + res.message, "danger", 99999);
                }
            },
            error: function (xhr, status, err) {
                console.error("AJAX Error:", err);
                showAlert("An unexpected error occurred.", "danger");
            }
        });
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