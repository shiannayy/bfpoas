document.onreadystatechange = function () {
    if (document.readyState === "interactive") {
        // HTML parsed, DOM built, spinner can still show
    }
    if (document.readyState === "complete") {
        // Page fully loaded (DOM + assets)
        $("#checklist-loader").fadeOut(20, function () {
            $("#checklistAccordion").removeClass("d-none").hide().fadeIn(20);
        });
    }
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
};

/**
 * getOptions(itemId, inputType, targetDropdown)
 * - itemId: for edit = numeric item_id
 *           for add = "<checklistId>-<sectionId>" (string)
 * - inputType: 'checkbox','text','number','date','textarea','select'
 * - targetDropdown: jQuery element (optional). If omitted, function will attempt to detect dropdown by
 *                   edit -> #editCriteriaSelect{itemId} or add -> #criteria-add-{itemId}
 *
 * Returns HTML string for synchronous options OR loads via AJAX for 'select' type and populates the target.
 */
function getOptions(itemId, inputType, targetDropdown) {

    let allowedCriteria = "";

    switch (inputType) {

        case 'checkbox':
            allowedCriteria = `
             <option selected >-SELECT-</option>
             <option value="yes_no">Either Yes or No</option>
             `;
            break;

        case 'text':
        case 'textarea':
            allowedCriteria = `
            <option selected>-SELECT-</option>
            <option value="textvalue">Compare Text Value</option>
            `
            ;
            break;

        case 'number':
            allowedCriteria = `
                <option selected >-SELECT-</option>
                <option value="range">Set Min - Max (Range)</option>
                <option value="min_val">Set Minimum Value</option>
                <option value="max_val">Set Maximum Value</option>
            `;
            break;

        case 'date':
            allowedCriteria = `<option selected>-SELECT-</option>
            <option value="days">Max Elapse No. of Days</option>`;
            break;

        case 'select':
            // If targetDropdown param not provided, attempt to resolve common IDs
            if (!targetDropdown) {
                targetDropdown = $("#editCriteriaSelect" + itemId);
                if (!targetDropdown.length) {
                    targetDropdown = $("#criteria-add-" + itemId);
                }
            }

            //console.log("DEBUG: Loading select options for itemId " + itemId);
            if((".add-select-form[data-sel-option-id="+ itemId + "]").length){
                $(".add-select-form[data-sel-option-id="+ itemId +"]").removeClass("d-none").fadeIn(200);
                console.log("DEBUG: Found add-select-form for itemId " + itemId);
            }

            $.ajax({
                url: "../includes/get_select_options.php",
                type: "POST",
                data: { item_id: (String(itemId).indexOf('-') === -1 ? itemId : (itemId || '')) }, // for add forms itemId may be composite; server expects numeric item_id so leave blank in that case
                dataType: "json",
                success: function (res) {

                    let html = `<option selected>-SELECT-</option>`;

                    if (Array.isArray(res) && res.length > 0) {
                        res.forEach(opt => {
                            html += `<option value="${opt.option_value}">${opt.option_label} (${opt.option_value})</option>`;
                        });
                        
                    } else {
                        html += `<option disabled>No Added Options Yet.</option>`;
                    }

                    if (targetDropdown && targetDropdown.length) {
                        targetDropdown.html(html);

                        // If the dropdown has data-saved attribute, re-select that value
                        let saved = targetDropdown.data("saved");
                        if (saved) {
                            targetDropdown.val(saved);
                        }

                        // After populating options via AJAX, trigger threshold toggle for this item
                        let criteriaVal = targetDropdown.val();
                        // detect if this is edit (numeric itemId) or add (composite)
                        let editId = String(itemId).indexOf('-') === -1 ? itemId : null;
                        if (editId) {
                            toggleThresholdFields(editId, criteriaVal);
                        } else {
                            // For add composite keys, itemId format `checklistId-sectionId`
                            toggleThresholdFields(itemId, criteriaVal);
                        }
                    }
                },
                error: function () {
                    if (targetDropdown && targetDropdown.length) {
                        targetDropdown.html(`<option disabled>Error loading options</option>`);
                    }
                }
            });

            return null;

        default:
            allowedCriteria = `<option disabled>--SELECT TYPE--</option>`;
    }

    // If targetDropdown provided, set it and return (caller will insert)
    return allowedCriteria;
}

$(document).ready(function () {
    setTimeout(function () {
        $(".select-input-type, .edit-select-input-type").trigger("change");
    }, 200);

    // Initialize ADD forms: populate criteria dropdown based on the default selected input_type
    $(".select-input-type").each(function () {
        const $select = $(this);
        const checklistId = $select.data("checklist");
        const sectionId = $select.data("section");
        const compositeId = checklistId + "-" + sectionId;
        const inputType = $select.val();
        console.log("compositeId: " + compositeId)

        const $criteriaDropdown = $("#criteria-add-" + compositeId);
        // Synchronous option HTML (if any)
        const opts = getOptions(compositeId, inputType, $criteriaDropdown);
        if (opts) {
            $criteriaDropdown.html(opts);
        }

        // show/hide threshold fields for add form
        // threshold-add-{checklistId}-{sectionId}
        const thresholdBox = $("#threshold-add-" + compositeId);
        if (thresholdBox.length) {
            thresholdBox.show();
            // hide all then show correct
            thresholdBox.find(".range-fields, .minval-field, .maxval-field, .yesno-field, .days-field, .textvalue-field").addClass("d-none");
            switch (inputType) {
                case 'number':
                    // number default: show nothing until criteria chosen; we keep hidden
                    break;
                default:
                    // if criteria dropdown has a value (seldom in add) toggle relevant add fields
                    const cVal = $criteriaDropdown.val();
                    if (cVal) {
                        toggleThresholdFields(compositeId, cVal);
                    }
            }
        }
    });
// Initialize EDIT forms: populate criteria dropdown based on input_type and saved criteria, then show thresholds
    $(".edit-select-input-type").each(function () {
        const $select = $(this);
        const itemId = $select.data("item-id");
        const inputType = $select.val();
        const $criteriaDropdown = $("#editCriteriaSelect" + itemId);

        // If criteriaDropdown doesn't have options yet, populate
        const opts = getOptions(itemId, inputType, $criteriaDropdown);
        if (opts) {
            $criteriaDropdown.html(opts);
            // set saved criteria if present
            const saved = $criteriaDropdown.data("saved");
            if (saved) {
                $criteriaDropdown.val(saved);
            }
            // show threshold fields based on saved or current value
            toggleThresholdFields(itemId, $criteriaDropdown.val());
        } 
    });
});

// WHEN input_type in ADD form changes (select-input-type)
$(document).on("change", ".select-input-type", function () {
    const $this = $(this);
    const checklistId = $this.data("checklist");
    const sectionId = $this.data("section");
    const compositeId = checklistId + "-" + sectionId;
    const inputType = $this.val();

    const $criteriaDropdown = $("#criteria-add-" + compositeId);
    $criteriaDropdown.html(""); // clear

    const opts = getOptions(compositeId, inputType, $criteriaDropdown);
    if (opts) {
        $criteriaDropdown.append(opts);
    }

    // show the threshold container for add and toggle based on current criteria
    const thresholdBox = $("#threshold-add-" + compositeId);
    thresholdBox.show();
    // If no criteria chosen yet, hide all specific fields
    if (!$criteriaDropdown.val()) {
        thresholdBox.find(".range-fields, .minval-field, .maxval-field, .yesno-field, .days-field, .textvalue-field").addClass("d-none");
    } else {
        toggleThresholdFields(compositeId, $criteriaDropdown.val());
    }
});

// WHEN input_type in EDIT form changes (edit-select-input-type)
$(document).on("change", ".edit-select-input-type", function () {
    const $this = $(this);
    const itemId = $this.data("item-id");
    const inputType = $this.val();
    const $criteriaDropdown = $("#editCriteriaSelect" + itemId);

    $criteriaDropdown.html(""); // clear existing

    const opts = getOptions(itemId, inputType, $criteriaDropdown);
    if (opts) {
        $criteriaDropdown.append(opts);
        // after inject, toggle thresholds based on first option
        toggleThresholdFields(itemId, $criteriaDropdown.val());
    }
});


// WHEN criteria selection changes (both add and edit)
$(document).on("change", ".criteria-select, .edit-criteria-select", function () {
    const $this = $(this);
    const idAttr = $this.attr("id") || "";
    //const dataItem = $this.data("item-id") || $this.data("checklist") || null;

    // Determine whether this is edit (id like editCriteriaSelect{itemId}) or add (criteria-add-{checklist}-{section})
    if (idAttr.indexOf("editCriteriaSelect") === 0) {
        const itemId = $this.data("item-id");
        toggleThresholdFields(itemId, $this.val());
    } else {
        // add case: id format criteria-add-{checklistId}-{sectionId}
        const fullId = idAttr.replace("criteria-add-", "");
        toggleThresholdFields(fullId, $this.val());
    }
});



$(document).ready(function () {
    // Add new section
    $(document).on("submit", ".addSectionForm", function(e){
        e.preventDefault();
        let $form = $(this);
        let data = $form.serialize();
        $.post("../includes/add_section_checklist.php", data, function (res) {
            if (res.success) {
                console.log("New section added!");
                location.reload();
            } else {
                console.error("Error: " + res.message);
            }
        }, "json");
    });

    // Add / Edit checklist item
    $(document).on("submit", ".addItemForm, .editItemForm", function (e) {
        e.preventDefault();
        let $form = $(this);

        // Clear hidden threshold fields (clear inputs under d-none)
        $form.find(".threshold-fields .d-none").each(function () {
            $(this).find("input, select, textarea").each(function () {
                if ($(this).is(":checkbox") || $(this).is(":radio")) {
                    $(this).prop("checked", false);
                } else {
                    $(this).val("");
                }
            });
        });

        // Attach item_id for edit forms
        if ($form.hasClass("editItemForm")) {
            let item_id = $form.data("item");
            if (!$form.find("input[name='item_id']").length) {
                $form.append('<input type="hidden" name="item_id" value="' + item_id + '">');
            }
        }

        let data = $form.serialize();

        $.post("../includes/save_checklist_item.php", data, function (res) {
            if (res.success) {
                console.log(res.message);
                if ($form.hasClass("addItemForm")) {
                    location.reload();
                    console.log(res.message);//showAlert(res.message);
                } else {
                    showAlert(res.message);
                }
            } else {
                console.error("Error: " + res.message);
            }
        }, "json");
    });

    // Add Option
    $(document).on("submit", ".addOptionForm", function (e) {
        e.preventDefault();
        let $form = $(this);
        $.post("../includes/save_option.php", $form.serialize(), function (res) {
            if (res.success) {
                showAlert("Option added!");
                setTimeout(function(){ location.reload(); }, 3000);
            } else {
                showAlert("Error: " + res.message);
            }
        }, "json");
    });

    // Delete Option
    $(document).on("click", ".delete-option", function () {
        if (!confirm("Delete this option?")) return;
        let optionId = $(this).data("id");
        $.post("../includes/delete_option.php", { option_id: optionId }, function (res) {
            if (res.success) {
                alert("Option deleted!");
                setTimeout(function(){ location.reload(); }, 3000);
            } else {
                alert("Error: " + res.message);
            }
        }, "json");
    });

    // The toggleThresholdFields function for both add & edit lives below so other handlers can call it
   

    // Because toggleThresholdFields is declared inside this ready block above, we need a global reference for earlier code.
    // Expose globally (so other code above can call it)
    window.toggleThresholdFields = function(itemId, criteria) {
        // reuse the inner implementation above
        let box;
        if (String(itemId).indexOf('-') === -1) {
            box = $("#threshold-edit-" + itemId);
        } else {
            box = $("#threshold-add-" + itemId);
        }
        if (!box.length) return;
        box.find(".range-fields, .minval-field, .maxval-field, .yesno-field, .days-field, .textvalue-field").addClass("d-none");
        switch (criteria) {
            case "range": box.find(".range-fields").removeClass("d-none"); break;
            case "min_val": box.find(".minval-field").removeClass("d-none"); break;
            case "max_val": box.find(".maxval-field").removeClass("d-none"); break;
            case "yes_no": box.find(".yesno-field").removeClass("d-none"); break;
            case "days": box.find(".days-field").removeClass("d-none"); break;
            case "textvalue": box.find(".textvalue-field").removeClass("d-none"); break;
        }
        box.show();
    };
});