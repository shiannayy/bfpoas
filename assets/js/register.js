    $(document).ready(function () {

function cleanFullName(name) {
    // Trim leading/trailing spaces
    name = name.trim();

    // Remove all characters except letters and spaces
    name = name.replace(/[^A-Za-z\s]/g, "");

    // Collapse multiple spaces into one
    name = name.replace(/\s+/g, " ");

    // Trim again to remove any leading/trailing spaces
    name = name.trim();

    // Convert to uppercase
    return name.toUpperCase();
}



        // ---------------------------
        // HELPER FUNCTIONS
        // ---------------------------

        function showFieldError(id, msg) {
            $("#" + id + "_error").text(msg).show();
            $("#" + id).addClass("is-invalid");
        }

        function clearFieldError(id) {
            $("#" + id + "_error").text("").hide();
            $("#" + id).removeClass("is-invalid");
        }


        const allowedDomains = [
        "gmail.com", "yahoo.com", "msn.com",
        "bicol-u.edu.ph", "oas.gov.ph", "fsic.gov.ph",
        "fsic.gov", "microsoft.com"
    ];

        function validateEmailStrict(email) {
            const basicFormat = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!basicFormat.test(email)) return "Please enter a valid email address.";

            const domain = email.split("@")[1].toLowerCase();
            if (allowedDomains.includes(domain)) return true;
            if (domain.endsWith(".gov") || domain.includes(".gov.")) return true;

            return "Email domain is not allowed. Only gmail/yahoo/etc or .gov emails are accepted.";
        }

        // ---------------------------
        // BLUR VALIDATIONS
        // ---------------------------

        $("#full_name").on("blur", function () {
            clearFieldError("full_name");
            let val = $(this).val();
            let cleaned = cleanFullName(val);
            $(this).val(cleaned);

            if (!/^[A-Z .]+$/.test(cleaned) && cleaned !== "") {
                showFieldError("full_name", "Full Name may only contain letters, spaces, and periods.");
            } else {
                clearFieldError("full_name");
            }
        });

        $("#email").on("blur", function () {
             clearFieldError("email");
            const val = $(this).val().trim();
            const check = validateEmailStrict(val);
            if (check !== true) showFieldError("email", check);
            else clearFieldError("email");
        });

        $("#contactNo").on("blur", function () {
            const val = $(this).val().trim();
            if (!/^[0-9]{11}$/.test(val) && val !== "") {
                showFieldError("contactNo", "Contact number must be exactly 11 numeric digits.");
            } else clearFieldError("contactNo");
        });

        $("#password").on("blur", function () {
             clearFieldError("password");
            const pw = $(this).val();
            const pwRegex = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/;
            if (pw && !pwRegex.test(pw)) showFieldError("password", "Password must be at least 8 characters, include uppercase, number & symbol.");
            else clearFieldError("password");
        });

        $("#confpassword").on("blur", function () {
            clearFieldError("confpassword");
            const pw = $("#password").val();
            const conf = $(this).val();
            if (pw && conf && pw !== conf) showFieldError("confpassword", "Passwords do not match.");
            else clearFieldError("confpassword");
        });

        // ---------------------------
        // FORM SUBMIT
        // ---------------------------
        $("#registerForm").on("submit", function (e) {
            e.preventDefault();

            // Clear all errors first
        ["full_name", "email", "contactNo", "password", "confpassword", "role", "terms"].forEach(id => clearFieldError(id));

            const fullName = cleanFullName($("#full_name").val());
            const email = $("#email").val().trim();
            const contactNo = $("#contactNo").val().trim();
            const password = $("#password").val();
            const confPass = $("#confpassword").val();
            const role = $("input[name='role']:checked").val();
            const subrole = $("#subrole").val();
            let valid = true;

            if (!fullName) {
                showFieldError("full_name", "Full Name is required.");
                valid = false;
            }
            if (!email) {
                showFieldError("email", "Email is required.");
                valid = false;
            }
            if (!contactNo) {
                showFieldError("contactNo", "Contact number is required.");
                valid = false;
            }
            if (!password) {
                showFieldError("password", "Password is required.");
                valid = false;
            }
            if (!confPass) {
                showFieldError("confpassword", "Confirm password is required.");
                valid = false;
            }
            if (!role) {
                showFieldError("role", "Please select a role.");
                valid = false;
            }
            if (!$("#agreeTerms").is(":checked")) {
                showFieldError("terms", "You must agree to the Terms & Conditions.");
                valid = false;
            }

            const emailCheck = validateEmailStrict(email);
            if (emailCheck !== true) {
                showFieldError("email", emailCheck);
                valid = false;
            }

            if (!/^[0-9]{11}$/.test(contactNo)) {
                showFieldError("contactNo", "Contact number must be exactly 11 numeric digits.");
                valid = false;
            }

            const pwRegex = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/;
            if (!pwRegex.test(password)) {
                showFieldError("password", "Password must be at least 8 characters, include uppercase, number & symbol.");
                valid = false;
            }
            if (password !== confPass) {
                showFieldError("confpassword", "Passwords do not match.");
                valid = false;
            }

            if (!valid) return;

            // AJAX submit
            $("#reginfo").removeClass("alert-danger alert-success").addClass("alert alert-info").text("Registering user... please wait.");

            $.ajax({
                url: "../includes/register_process.php",
                type: "POST",
                data: {
                    full_name: fullName,
                    email,
                    password,
                    contactNo,
                    role,
                    subrole
                },
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        $("#reginfo").removeClass("alert-info alert-danger").addClass("alert alert-success").text(response.message);
                        $("#registerForm")[0].reset();
                        $("input[name='role']").prop("checked", false);
                        $("#subrole").val("");
                        $("#subrole-input").addClass("d-none").val("");
                        $("#subrole-radios").addClass("d-none").find("#subrole-options").empty();
                        showAlert(fullName.toString() + ' has been successfully added');
                    } else {
                        $("#reginfo").removeClass("alert-info alert-success").addClass("alert alert-danger").text(response.message);
                    }
                },
                error: function () {
                    $("#reginfo").removeClass("alert-info alert-success").addClass("alert alert-danger").text("Something went wrong. Try again.");
                }
            });
        });



        // =========================
        // TOGGLE PASSWORD VISIBILITY
        // =========================
        $(document).on("click", ".togglePassword", function () {
            let eye = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
      <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
      <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
    </svg>`;
            let noeye = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash" viewBox="0 0 16 16">
      <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/>
      <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/>
      <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/>
    </svg>`;
            // Grab the nearest password input inside the same group
            let input = $(this).closest(".input-group").find("input");

            let type = input.attr("type") === "password" ? "text" : "password";
            input.attr("type", type);

            // Switch the icon
            $(this).html(type === "password" ? eye : noeye);
        });


        // =========================
        // ROLE & SUBROLE HANDLING
        // =========================
        $(document).on("change", "input[name='role']", function () {
            const selectedRole = $(this).val();
            const $subroleContainer = $("#subrole-radios");
            const $subroleInput = $("#subrole");

            $subroleContainer.empty().removeClass("d-none");
            $subroleInput.val(""); // reset value

            // Inspector Role
            if (selectedRole === "Inspector") {
                $subroleContainer.append(`
                    <input type="radio" class="btn-check" checked name="subrole" id="fireOfficer" value="Fire Officer">
                    <label class="btn btn-outline-secondary flex-fill m-1" for="fireOfficer">Fire Officer</label> 
                `);
            }

            // Administrator Role
            else if (selectedRole === "Administrator") {
                $subroleContainer.append(`
                    <input type="radio" class="btn-check" name="subrole" id="fireMarshall" value="Fire Marshall">
                    <label class="btn btn-outline-secondary flex-fill m-1" for="fireMarshall">City/Municipal Fire Marshall</label>

                    <input type="radio" class="btn-check" name="subrole" id="chief" value="Chief FSES">
                    <label class="btn btn-outline-secondary flex-fill m-1" for="chief">Chief FSES</label>

                    <input type="radio" class="btn-check" name="subrole" id="adminAssistant" value="Admin_Assistant">
                    <label class="btn btn-outline-secondary flex-fill m-1" for="adminAssistant">Admin_Assistant</label>
      `);
            }

            // Client Role
            else if (selectedRole === "Client") {
                $subroleContainer.addClass("d-none");
                $subroleInput.val("Client");
            }
        });

        // Capture subrole selection
        $(document).on("change", "input[name='subrole']", function () {
            $("#subrole").val($(this).val());
        });
    });