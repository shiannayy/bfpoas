$(document).ready(function () {
    const formId = "#generalInfoForm";

    // Restore values from localStorage
    $(formId).find("input, textarea, select").each(function () {
        const name = $(this).attr("name");
        const type = $(this).attr("type");
        const key = "general_info_" + name;

        if (!name) return; // skip unnamed fields

        let savedValue = localStorage.getItem(key);

        if (savedValue !== null) {
            if (type === "checkbox") {
                $(this).prop("checked", savedValue === "true");
            } else if (type === "radio") {
                if ($(this).val() === savedValue) {
                    $(this).prop("checked", true);
                }
            } else {
                $(this).val(savedValue);
            }
        }
    });

    // Save on change/input
    $(formId).on("input change", "input, textarea, select", function () {
        const name = $(this).attr("name");
        const type = $(this).attr("type");
        const key = "general_info_" + name;

        if (!name) return;

        if (type === "checkbox") {
            localStorage.setItem(key, $(this).is(":checked"));
        } else if (type === "radio") {
            if ($(this).is(":checked")) {
                localStorage.setItem(key, $(this).val());
            }
        } else {
            localStorage.setItem(key, $(this).val());
        }
    });

    // Clear storage on submit
    $(formId).on("submit", function () {
        $(formId).find("input, textarea, select").each(function () {
            const name = $(this).attr("name");
            if (name) localStorage.removeItem("general_info_" + name);
        });
    });
});
