$(document).ready(function () {
    const startHour = 6;   // 6:00 AM
    const endHour = 18;    // 6:00 PM
    const interval = 5;    // minutes
    const $datalist = $("#time-options");

    function formatAMPM(hour, min) {
        const ampm = hour >= 12 ? 'PM' : 'AM';
        let hour12 = hour % 12;
        if (hour12 === 0) hour12 = 12;
        return `${hour12}:${min.toString().padStart(2,'0')} ${ampm}`;
    }

    for (let h = startHour; h <= endHour; h++) {
        for (let m = 0; m < 60; m += interval) {
            if (h === endHour && m > 0) break; // stop at 6:00 PM exactly
            const timeVal = formatAMPM(h, m);
            $datalist.append(`<option value="${timeVal}">`);
        }
    }

    // Optional: validate input
    $("#time").on("change", function() {
        const val = $(this).val();
        const valid = $datalist.find(`option[value='${val}']`).length > 0;
        if (!valid) {
            showAlert("Please select a valid time from the list (06:00 AM to 06:00 PM, 5-min Intervals).","danger");
            $(this).val("");
        }
    });

    /** -------------------------------
     *  ALERT CLOSE BUTTON
     * ------------------------------- */
    $(document).on("click", ".btn-close-alert", function () {
        $("#alerts").fadeOut();
    });


    /** -------------------------------
     *  SET MIN DATE (TOMORROW)
     * ------------------------------- */
    let today = new Date();
    today.setDate(today.getDate() + 1);

    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, "0");
    const dd = String(today.getDate()).padStart(2, "0");
    const tomorrow = `${yyyy}-${mm}-${dd}`;

    $("#date").attr("min", tomorrow);


    /** -------------------------------
     *  AUTOCOMPLETE: PROCEED (ESTABLISHMENT)
     * ------------------------------- */
   // Shared function to fetch suggestions
function fetchProceedSuggestions(query) {
    const suggestions = $("#proceed-suggestions");

    $.ajax({
        url: "../includes/search_establishment.php",
        method: "GET",
        data: { term: query },
        dataType: "json",
        success: function (data) {
            suggestions.empty().show();

            if (!data || data.length === 0) {
                suggestions.append(`<li class="list-group-item text-muted">No results found</li>`);
            } else {
                $.each(data, function (i, item) {
                    suggestions.append(`
                        <li class="est-list-item list-group-item list-group-item-action bg-navy text-gold btn rounded-0"
                            data-id="${item.id}"
                            data-value="${item.value}"
                            data-fsed-code="${item.fsed_code}"
                            >
                            
                            ${item.label}
                        </li>
                    `);
                });
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error (search_establishment):", error);
        }
    });
    
   
}
    
    
 $(document).on("click", ".est-list-item", function() {
        let fsedCode = $(this).data('fsed-code');
        $("#checklist_id").val(fsedCode || '');  
    });

// Trigger on keyup
$("#proceed").on("keyup", function () {
    const query = $(this).val();
    fetchProceedSuggestions(query);
});

// Trigger on focus
$("#proceed").on("focus", function () {
    const query = $(this).val(); // can be empty
    fetchProceedSuggestions(query);
});


    // On suggestion click (establishment)
    $(document).on("click", "#proceed-suggestions li", function () {
        const id = $(this).data("id");
        const value = $(this).data("value");

        $("#establishment_id").val(id);
        $("#proceed").val(value);
        $("#proceed-suggestions").hide();
    });

    // Hide establishment suggestions when clicking outside
    $(document).on("click", function (e) {
        if (!$(e.target).closest("#proceed").length) {
            $("#proceed-suggestions").hide();
        }
    });


    /** -------------------------------
     *  AUTOCOMPLETE: INSPECTOR
     * ------------------------------- */
function fetchInspectors(term = "") {
    const scheduleDate = $("#date");
    const suggestions = $("#inspector-suggestions");
    suggestions.empty().show();

    // Require schedule date
    if (!scheduleDate.val()) {
        scheduleDate.addClass("border-danger");
        suggestions.append(`<li class="list-group-item text-danger">Please set Schedule Date first.</li>`);
        return;
    } else {
        scheduleDate.removeClass("border-danger");
    }

    $.ajax({
        url: "../includes/search_inspector.php",
        method: "GET",
        data: {
            term: term,
            schedule: scheduleDate.val()
        },
        dataType: "json",
        success: function (data) {
            suggestions.empty().show();

            if (!data || data.length === 0) {
                suggestions.append(`<li class="list-group-item text-muted">No Inspectors Available that day</li>`);
            } else {
                $.each(data, function (i, item) {
                    suggestions.append(`
                        <li class="list-group-item list-group-item-action bg-navy text-gold"
                            data-id="${item.user_id}"
                            data-value="${item.full_name}">
                            ${item.full_name}
                            
                        </li>
                    `);
                });
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error (search_inspector):", error);
        }
    });
}
    
    
// Trigger fetch on keyup (typed query)
$(document).on("keyup", "#to", function () {
    const query = $(this).val();
    fetchInspectors(query);
});

// Trigger fetch on focus (show full list)
$(document).on("focus", "#to", function () {
    fetchInspectors(""); // Empty term = show all
});

    // On suggestion click (inspector)
    $(document).on("click", "#inspector-suggestions li", function () {
        const id = $(this).data("id");
        const name = $(this).data("value");

        $("#inspector_id").val(id);
        $("#to").val(name);
        $("#inspector-suggestions").hide();
    });

    // Hide inspector suggestions when clicking outside
    $(document).on("click", function (e) {
        if (!$(e.target).closest("#to").length) {
            $("#inspector-suggestions").hide();
        }
    });


    /** -------------------------------
     *  FETCH MAX SCHEDULE ID
     * ------------------------------- */
    function fetchMaxSchedId() {
        $.ajax({
            url: "../includes/get_max_sched_id.php",
            type: "GET",
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    console.log(res.formatted);
                    $("#order_number")
                        .prop("readonly", false)
                        .val(res.formatted)
                        .prop("readonly", true);
                } else {
                    console.warn("⚠️ Failed to fetch max_sched_id:", res.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error (get_max_sched_id):", error);
            }
        });
    }

    
    /** -------------------------------
     *  FETCH OR Number
     * ------------------------------- */
    function fetchNewORNumber() {
        $.ajax({
            url: "../includes/get_available_or_number.php",
            type: "GET",
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    console.log(res.or_number);
                    $("#OR_Number")
                        .prop("readonly", false)
                        .val(res.or_number)
                        .prop("readonly", true);
                } else {
                    console.warn("Failed to fetch:", res.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error (get_available_or_number):", error);
            }
        });
    }

    fetchMaxSchedId();
   // fetchNewORNumber();


    /** -------------------------------
     *  FSED FORM SUBMISSION
     * ------------------------------- */
    $("form#fsed9F").on("submit", function (e) {
        e.preventDefault();

        const $form = $(this);
        let missingFields = [];
        let firstInvalidField = null;

        // Reset highlights
        $form.find(".border-danger").removeClass("border-danger");
        $form.find("label.border-danger").removeClass("border border-danger");

        // Validate required fields
        $form.find("[required]").each(function () {
            const $field = $(this);
            const fieldType = $field.attr("type");
            const tagName = $field.prop("tagName").toLowerCase();
            const value = $field.val()?.trim();

            // Radio groups
            if (fieldType === "radio") {
                const name = $field.attr("name");
                if (!$(`input[name='${name}']:checked`).length) {
                    if (!missingFields.includes(name)) missingFields.push(name);
                    $(`input[name='${name}']`).each(function () {
                        $(this).next("label").addClass("border border-danger rounded");
                    });
                    if (!firstInvalidField) firstInvalidField = $(`input[name='${name}']`).first();
                } else {
                    $(`input[name='${name}']`).next("label").removeClass("border border-danger");
                }
            }
            // Text/select/textarea
            else if (!value) {
                const fieldLabel =
                    $field.closest(".form-floating").find("label").text().trim() ||
                    $field.attr("placeholder") ||
                    $field.attr("name") ||
                    $field.attr("id");

                missingFields.push(fieldLabel);
                $field.addClass("border-danger");
                if (!firstInvalidField) firstInvalidField = $field;
            }
        });

        // If missing fields
        if (missingFields.length > 0) {
            const formattedList = missingFields.join(", ");
            showAlert("Please fill in the following required field(s):" + formattedList , "warning");

            if (firstInvalidField && firstInvalidField.length) {
                $("html, body").animate({
                    scrollTop: firstInvalidField.offset().top - 100
                }, 500);
                firstInvalidField.focus();
            }
            return;
        }

        // Submit form
        $.ajax({
            url: "../includes/save_inspection_schedule.php",
            type: "POST",
            data: $form.serialize(),
            dataType: "json",
            success: function (response) {

                if (response.success) {
                   // $(".form-reset").click();
                    fetchMaxSchedId();
                    //--send to email asynchronously
                    // Open email sending page in new tab
                        if (response.schedule_data && response.schedule_data.schedule_id) {
                            console.log("creating a form for email");
                            // Create a form to submit to the email wrapper
                            const form = document.createElement('form');
                            form.method = 'GET';
                            form.action = '../pages/wrapper_send_IO_mail.php'; // Path to your wrapper
                            form.target = '_blank'; // Open in new tab
                            
                            // Add schedule_id
                            const scheduleIdInput = document.createElement('input');
                            scheduleIdInput.type = 'hidden';
                            scheduleIdInput.name = 'schedule_id';
                            scheduleIdInput.value = response.schedule_data.schedule_id;
                            form.appendChild(scheduleIdInput);

                              // ✅ ADD EMAIL_TOKEN from response
                            if (response.schedule_data.email_token) {
                                const emailTokenInput = document.createElement('input');
                                emailTokenInput.type = 'hidden';
                                emailTokenInput.name = 'email_token';
                                emailTokenInput.value = response.schedule_data.email_token;
                                form.appendChild(emailTokenInput);
                            }
                        
                              const inspectionSchedStep = document.createElement('input');
                                inspectionSchedStep.type = 'hidden';
                                inspectionSchedStep.name = 'step';
                                inspectionSchedStep.value = 1;
                                form.appendChild(inspectionSchedStep);
                            
                            // Submit the form
                            document.body.appendChild(form);
                            form.submit();
                            document.body.removeChild(form);
                            console.log("Form Created, removed and submitted");
                            // Show notification about email sending
                            setTimeout(function() {
                                showAlert("Sending Email to Receipient...", "info");
                            }, 500);
                        }
                    //------------end send email----

                    showAlert(response.message || "Schedule saved successfully!", "success");
                    setTimeout(function(){
                            location.reload();
                    },300);
                    

                } else {
                    showAlert(response.message + ":<br>" + response.missing_fields || "Unable to save schedule.", "warning");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error (save_inspection_schedule):", error);
                showAlert("Something went wrong. Please try again later.", "error");
            }
        });
    });


    /** -------------------------------
     *  REMOVE RED BORDER ON INPUT CHANGE
     * ------------------------------- */
    $(document).on("input change", "#fsed9F [required]", function () {
        const $field = $(this);
        const fieldType = $field.attr("type");

        if (fieldType === "radio") {
            const name = $field.attr("name");
            if ($(`input[name='${name}']:checked`).length) {
                $(`input[name='${name}']`).next("label").removeClass("border border-danger");
            }
        } else if ($field.val()?.trim()) {
            $field.removeClass("border-danger");
        }
    });

});
