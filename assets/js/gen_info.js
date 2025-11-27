// Run silently when dashboard loads


$(document).ready(function () {
    let typingTimer;
    const doneTypingInterval = 5000;
    
    function autoSave($input) {
        let $section = $input.closest(".card");
        let statusDiv = $section.find(".autosave-status");
        let formData = $("#generalInfoForm").serialize();

        statusDiv.html(`<div class="spinner-border spinner-border-sm" role="status"></div>`);

        $.post("../includes/auto_save_general_info.php", formData, function (res) {
            if (res.success) {
                statusDiv.html(`
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" 
                        class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                      <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0
                               m-3.97-3.03a.75.75 0 0 0-1.08.022
                               L7.477 9.417 5.384 7.323
                               a.75.75 0 0 0-1.06 1.06
                               L6.97 11.03a.75.75 0 0 0 1.079-.02
                               l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                    <small class="text-light">Saved at ${new Date().toLocaleTimeString()}</small>
                `);
            } else {
                //console.error(res.type);
                statusDiv.html(`
                  <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Saving...</span>
                    </div>
                    <small class="text-light">Saving in Progress</small>
                `);
            }
        }, "json");
    }

    // Debounce inputs
    $("#generalInfoForm .auto-save").on("input", function () {
        let $input = $(this);
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => autoSave($input), doneTypingInterval);
    });

    $("#generalInfoForm .auto-save").on("change", function () {
        autoSave($(this));
    });

    // "Done" button = submit
    $("#generalInfoForm").on("submit", function (e) {
        e.preventDefault();
        $.post("../includes/complete_gen_info.php", {}, function (res) {
            if (res.success) {
                showAlert("Completed" + res.message + "Redirecting...","success");
                setTimeout(() => window.location.href = "../admin", 2000);
            } else {
                showAlert(res.message,"danger");
            }
        }, "json");
    });

    /// + New button clears session and reloads
    $("#newGeneralInfo").on("click", function () {
        $.get("../includes/reset_gen_info.php", function () {
            // Reset the form fields
            $("#generalInfoForm")[0].reset();

            // Clear validation and autosave indicators
            $("#generalInfoForm .is-invalid").removeClass("is-invalid");
            $(".autosave-status").empty();

            // Redirect to start fresh
            window.location.href = "?page=new_est&reset";
        });
    });
    
    
   $(document).on("click", "#genInfoDonebtn", function(e) {
       e.preventDefault();
    showConfirm(
        "Are you sure?",
        "Once confirmed, this section will be marked as completed.",
        function() {
            $("#generalInfoForm").trigger("submit");
            console.log("Proceed with update action...");
        }
        
    );

   });

   $(document).on("input","#business_name", function(){
       let $b = $("#business_name").val();
       $("#establishment_name").val($b);
       $("#building_name").val($b);
   });
    
   $(function() {
    // Prevent multiple bindings
    if ($("#owner_name").data("ui-autocomplete")) {
        $("#owner_name").autocomplete("destroy");
    }

    $("#owner_name").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "../includes/search_users.php",
                type: "GET",
                dataType: "json",
                data: { term: request.term },
                success: function(data) {
                    // Pass results to autocomplete
                    response(data);
                },
                error: function(xhr, status, error) {
                    console.error("Autocomplete error:", error);
                }
            });
        },
        minLength: 2, // start after typing 2 chars
        focus: function(event, ui) {
            // Prevent value insertion on hover
            event.preventDefault();
            $("#owner_name").val(ui.item.label);
        },
        select: function(event, ui) {
            event.preventDefault();

            // Fill form fields
            $("#owner_name").val(ui.item.label || ui.item.value);
            $("#owner_id").val(ui.item.user_id || "");
            $("#telephone_email").val(ui.item.email || "");
            $("#owner_contact_no").val(ui.item.contactNo || "");
            $("#occupant_name").val( $("#owner_name").val() );
            
        },
        change: function(event, ui) {
            // If cleared or no valid selection
            if (!ui.item) {
                $("#owner_id").val("");
                //$("#telephone_email").val("").prop("disabled", false);
                //$("#owner_contact_no").val("").prop("disabled", false);
            }
        }
    });
});

    


});