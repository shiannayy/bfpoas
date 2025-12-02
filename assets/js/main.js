let searchTimer = null;
let currentUser = null;
let currentUserRole = null;
let currentSubRole = null;

$(document).ready(function () {

loadFavicon("../assets/img/tagasalbar.ico");
    
    enforceRoleAccess();
    autoCleanupDaily();
    
// Initialize reschedule offcanvas behavior & form binding once
    
    const info = $("#infosection").data("info") || "No information available.";
    createAlertContainer(info);
    
});

// ========================
// TOGGLE PASSWORD VISIBILITY
// ========================
$(document).on("click", ".togglePassword", function(e) {
  e.preventDefault();
  e.stopPropagation();
  
  const eyeIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
  </svg>`;
  
  const eyeSlashIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash" viewBox="0 0 16 16">
    <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/>
    <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/>
    <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/>
  </svg>`;
  
  const $input = $(this).closest(".input-group").find("input");
  
  // Defensive check: ensure input element exists before manipulating
  if ($input && $input.length > 0) {
    const currentType = $input.attr("type");
    
    if (currentType === "password") {
      $input.attr("type", "text");
      $(this).html(eyeSlashIcon);
    } else {
      $input.attr("type", "password");
      $(this).html(eyeIcon);
    }
  } else {
    console.warn("Warning: Password input not found in the parent .input-group");
  }
});

let currentDeleteId = null;

$(document).on("click", ".btn-confirm-delete", function(e){
    e.preventDefault();
    currentDeleteId = $(this).data('delete-item');
    let disableOnly = 0;
    
    if( $(this).hasClass("disable-only") ){
        $("#disableOnlyind").val(1);
        disableOnly = 1;
    }
    
    if( $(this).hasClass("enable-only")){
        $("#disableOnlyind").val(2);
        disableOnly = 2;
    }
    //disableOnlyind
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('passwordConfirmModal'));
     if(disableOnly == 1){
            $("action").text("Disable");
        } 
        else if(disableOnly == 2) {
            $("action").text("Enable");
        }
        else{
                $("action").text("Delete");
        }
        modal.show();   
       
    
        
    
});


// Handle confirm delete button click
$('#confirmDeleteBtn').on('click', function() {
    const password = $('#passwordInput').val().trim();
    const errorDiv = $('#passwordError');
    const disableOnly = $("#disableOnlyind").val().trim();
    // Validate password
    if (!password) {
        errorDiv.removeClass('d-none').text('Please enter your password.');
        return;
    }
    
    // Hide error if previously shown
    errorDiv.addClass('d-none');
    
    // Disable button and show loading state
    const $btn = $(this);
    const originalText = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');
    
    // Make the API call
    fetchData('../includes/delete_checklist.php', 'POST', {
        item_id: currentDeleteId,
        password: password,
        disableOnly : disableOnly
    })
    .then(response => {
        // Re-enable button
        $btn.prop('disabled', false).html(originalText);
        let msg  = response.message;;
        let alertClass = "danger";
        if (response.status === 'success') {
            // Hide modal
            $('#passwordConfirmModal').modal('hide');
            
            
            // Success handling
            if(response.action == 'delete'){
                $(`[data-delete-item="${currentDeleteId}"]`).closest(`li#${currentDeleteId}`).fadeOut(400, function() {
                    $(this).remove();
                });   
                
                
            }else if(response.action == 'enable'){
                $(`[data-delete-item="${currentDeleteId}"]`).closest(`li#${currentDeleteId}`).removeClass("bg-secondary bg-opacity-25");
            }
            else{
                $(`[data-delete-item="${currentDeleteId}"]`).closest(`li#${currentDeleteId}`).addClass("bg-secondary bg-opacity-25");
                
            }
            
            // Show success message
            showAlert(response.message, alertClass);
        } else {
            // Show error message
            errorDiv.removeClass('d-none').text(response.message || 'Delete failed. Please try again.');
        }
    })
    .catch(error => {
        console.warn("Error fetching data:", error);
        $btn.prop('disabled', false).html(originalText);
        errorDiv.removeClass('d-none').text('An error occurred while deleting the item.');
    });
});

// Reset modal when hidden
$('#passwordConfirmModal').on('hidden.bs.modal', function () {
    $('#passwordInput').val('');
    $('#passwordError').addClass('d-none');
    $('#confirmDeleteBtn').prop('disabled', false).html('Delete Item');
    currentDeleteId = null;
});

// Optional: Allow Enter key to trigger deletion
$('#passwordInput').on('keypress', function(e) {
    if (e.which === 13) { // Enter key
        $('#confirmDeleteBtn').click();
    }
});

// Optional: Helper function for showing alerts




function fetchData(url, method = 'POST', payload = {}) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url,
            method: method.toUpperCase(),
            data: payload,
            dataType: 'json',
            success: (response) => {
                
                console.log(`✅ Fetch Success from ${url}`, response);
                resolve(response);
            },
            error: (xhr, status, error) => {
                
                console.error(`❌ AJAX Error (${status}):`, error);
                reject(error);
            }
        });
    });
}
/**
 * Generic AJAX interface
 * Handles API calls and returns response data as a Promise.
 * 
 * @param {string} url - The request URL
 * @param {string} [method='GET'] - The HTTP method (GET/POST)
 * @param {object} [payload={}] - The request payload (optional)
 * @returns {Promise<any>} - Resolves with response data
 */




/**
 * General-purpose function to create a button as <a> element
 * @param {string[]} classList - array of classes to apply
 * @param {Object} props - object with attributes to apply (e.g., href, target, disabled)
 * @param {string} icon - HTML string for icon
 * @param {Object} otherAttr - object with other attributes (e.g., data-* attributes)
 * @param {string} label - optional label text
 * @param {string type = ND = non-dropdown D = dropdown}
 * @returns {string} - HTML string of the button
 
 */
function createButton({ classList = [], props = {}, icon = '', otherAttr = {}, label = '' } = {}, type = 'D') {
    const classes = classList.join(' ');

    let propsStr = '';
    for (const [key, val] of Object.entries(props)) {
        propsStr += ` ${key}="${val}"`;
    }

    let otherStr = '';
    for (const [key, val] of Object.entries(otherAttr)) {
        otherStr += ` ${key}="${val}"`;
    }
    
    
    
    if(type === 'ND'){
    return `<a href="#" class="${classes}"${propsStr}${otherStr}>
                ${icon || ''} 
                ${label ? `<span class="d-none d-lg-inline">${label}</span>` : ''}
            </a>`;
    }
    if(type === 'D'){
           return `<li><a class="btn dropdown-item ${classes}"${propsStr}${otherStr}>
                ${icon || ''} 
                ${label ? `<span class="">${label}</span>` : ''}
            </a></li>`;
        
    }
}

// ✅ Ensure Alert Container Exists or Create It
// ---------- Alerts: create container + showAlert --------------
function getRoleLabel(mainRole, subRole) {
    if (mainRole === "Administrator" && ["Recommending Approver", "Chief FSES"].includes(subRole)) {
        return "Recommending Approver";
    }
    if (mainRole === "Administrator" && ["Approver", "Fire Marshall", "City Municipal Fire Marshall"].includes(subRole)) {
        return "Approver";
    } 
    else if (mainRole === "Administrator" && subRole === "Admin_Assistant") {
        return "Admin_Assistant";
    } 
    else if (mainRole === "Inspector") {
        return "Inspector";
    } 
    else if (mainRole === "Client") {
        return "Client";
    } 
    else {
        return "Guest";
    }
}

// Create or recreate alert container. Returns jQuery object.
function createAlertContainer(defaultMessage = "No information available.") {
    // remove any existing
    $("#alerts").remove();

    // place into <main> if present, otherwise body
    const appendTo = $("main").length ? $("main") : $("body");

    const html = `
    <div id="alerts" class="container-fluid w-100 p-0 me-2 mb-2 position-fixed bottom-0 end-0" style="z-index:1000; display:none;">
      <div class="row">
        <div class="col-12 col-md-6 col-lg-4 ms-auto">
          <div class="card border-0 shadow-lg">
            <div class="card-header bg-navy text-gold d-flex align-items-center justify-content-between pt-3">
              <h6 class="card-title mb-0">FSIC</h6>
              <button type="button" class="btn-close btn-close-alert" aria-label="Close"></button>
            </div>
            <div class="card-body" id="alertsBody">${defaultMessage}</div>
          </div>
        </div>
      </div>
    </div>
    `;

    appendTo.append(html);

    // Delegated close button handler
    $(document)
        .off("click", ".btn-close-alert.alert-bound")
        .on("click", ".btn-close-alert", function () {
            $("#alerts").fadeOut(200);
        })
        .addClass("alert-bound");

    return $("#alerts");
}

// showAlert: message (string or html), type, duration (ms), buttonText optional, buttonLink optional
// ✅ showAlert() with native anchor button link
function showAlert(message, type = "info", duration = 5000, buttonText = null, buttonLink = null) {
    // Ensure container exists
    if ($("#alerts").length === 0 || $("#alertsBody").length === 0) {
        createAlertContainer();
    }

    const $alerts = $("#alerts");
    const $body = $("#alertsBody");
    const $header = $alerts.find(".card-header");

    // header class mapping
    const headerMap = {
        info: "bg-info text-dark",
        success: "bg-success bg-opacity-50 text-white",
        error: "bg-danger text-white",
        danger: "bg-danger text-white",
        warning: "bg-warning text-dark",
        navy: "bg-navy text-gold",
        gold: "bg-gold text-navy-dark"
    };
    const headerClass = headerMap[type] || "bg-info text-dark";

    // button class mapping
    const btnMap = {
        info: "btn btn-secondary",
        success: "btn btn-success",
        error: "btn btn-danger",
        danger: "btn btn-danger",
        warning: "btn btn-warning",
        navy: "btn btn-navy",
        gold: "btn btn-gold"
    };
    const btnClass = btnMap[type] || "btn btn-secondary";

    // reset animations & timeout
    $body.stop(true, true);
    clearTimeout($alerts.data("hideTimeout"));

    // build optional anchor button
    let buttonHtml = "";
    if (buttonText && buttonLink) {
        const safeText = $("<div>").text(buttonText).html();
        const safeLink = $("<div>").text(buttonLink).html();
        buttonHtml = `
            <div class="mt-3 text-end">
                <a href="${safeLink}" class="${btnClass}" id="alertDynamicLink">${safeText}</a>
            </div>`;
    }

    // update header
    $header
        .removeClass("bg-info bg-success bg-danger bg-warning bg-navy bg-gold text-white text-dark text-gold text-navy-dark")
        .addClass(headerClass);

    // replace content with fade
    $body.fadeOut(180, function () {
        $body.html(message + buttonHtml).fadeIn(260);
    });

    // show container
    $alerts.fadeIn(220);

    // auto-hide if no link
    if (!buttonLink) {
        const t = setTimeout(() => $alerts.fadeOut(300), duration);
        $alerts.data("hideTimeout", t);
    }
}



// ✅ Session, API, and Icon Utilities
async function getApiKey() {
    try {
        const response = await fetch("../includes/get_api_key.php");
        const data = await response.json();
        if (data.api_key) return data.api_key;
        console.error("❌ Error fetching API Key:", data.error || "Unknown error");
        return null;
    } catch (err) {
        console.error("⚠️ AJAX Error:", err);
        return null;
    }
}


function checkSession(callbackIfLoggedIn, callbackIfNotLoggedIn = null) {
    $.ajax({
        url: "../includes/_session_check.php",
        method: "POST",
        dataType: "json",
        success: function (response) {
            if (response.logged_in) {
                const user = response.user;

                // Update globals (retain your setup)
                currentUser = user;
                currentUserRole = user.role;
                currentSubRole = user.subrole;

                // Save to localStorage (persistent between tabs)
                localStorage.setItem("currentUser", JSON.stringify(user));
                

                // Optional: Also store in cookie for "Remember Me" feature later
                // setCookie("currentUser", JSON.stringify(user), 7); // 7 days expiry

                if (typeof callbackIfLoggedIn === "function") {
                    callbackIfLoggedIn(user);
                }
            } else {
                // Clear globals and storage
                currentUser = null;
                currentUserRole = null;
                currentSubRole = null;
                localStorage.removeItem("currentUser");
                deleteCookie("currentUser");

                if (typeof callbackIfNotLoggedIn === "function") {
                    callbackIfNotLoggedIn();
                } else {
                    window.location.href = "../?login";
                }
            }
        },
        error: function () {
            console.error("Error checking session.");
        }
    });
}

function setCookie(name, value, days) {
    const d = new Date();
    d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${encodeURIComponent(value)};expires=${d.toUTCString()};path=/`;
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let c of ca) {
        while (c.charAt(0) === ' ') c = c.substring(1);
        if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length));
    }
    return null;
}

function deleteCookie(name) {
    document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;`;
}

function enforceRoleAccess() {
    checkSession(function (user) {
        const currentPath = window.location.pathname.toLowerCase();

        // Define allowed folder per role
        const roleAccess = {
            "administrator": ["/admin"],
            "client": ["/client"],
            "inspector": ["/inspector"],
            "approver": ["/approver"]
        };

        let restricted = false;

        // Check if user’s current path matches another role’s folder
        for (const role in roleAccess) {
            roleAccess[role].forEach(folder => {
                if (currentPath.includes(folder) && user.role.toLowerCase() !== role) {
                    restricted = true;
                }
            });
        }

        if (restricted) {
            showAlert(`Access denied: ${user.role} cannot access ${currentPath}`, "danger");

            // Redirect to correct home page based on their role
            const redirectMap = {
                "client": "../client/",
                "administrator": "../admin/",
                "inspector": "../inspector/",
                "approver": "../approver/"
            };

            const redirectPath = redirectMap[user.role.toLowerCase()] || "../?err=404";
            window.location.href = redirectPath;
        }

    }, function () {
        // 🚫 No redirect when no user is logged in
        console.warn("No active session. Skipping role enforcement.");
        // You can optionally show a message or do nothing.
    });
}

$(document).on("click", ".showContactInfo", function () {
    let userId = $(this).data("user-id");
    showContactModal(userId);
});

function showContactModal(userId) {

    // If modal doesn't exist, create it
    if ($("#contactModal").length === 0) {
        $("body").append(`
            <div class="modal fade" id="contactModal" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Contact Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body" id="contactModalBody">
                    <!-- dynamic -->
                  </div>
                </div>
              </div>
            </div>
        `);
    }

    // Load spinner first
    $("#contactModalBody").html("<div class='text-center p-3'>Loading...</div>");

    // Show modal
    let modal = new bootstrap.Modal(document.getElementById("contactModal"));
    modal.show();

    // AJAX Request
    $.ajax({
        url: "../includes/get_contact_info.php",
        type: "POST",
        data: { user_id: userId },
        dataType: "json",
        success: function (res) {
            if (res.status === "success") {
                let d = res.data;

                let html = `
                    <p><strong>Email:</strong> ${d.email ?? "N/A"}</p>
                    <p><strong>Contact No:</strong> ${d.contact_no ?? "N/A"}</p>
                    <hr>
                    <p><strong>Owner Contact:</strong> ${d.owner_contact_no ?? "N/A"}</p>
                    <p><strong>Address:</strong> ${d.location_of_construction ?? "N/A"}</p>
                    <p><strong>Postal Address:</strong> ${d.postal_address ?? "N/A"}</p>
                `;

                $("#contactModalBody").html(html);
            } else {
                $("#contactModalBody").html("<div class='text-danger'>Unable to load contact info.</div>");
            }
        },
        error: function () {
            $("#contactModalBody").html("<div class='text-danger'>Server error.</div>");
        }
    });
}



function checkSessionRole() {
    $.ajax({
        url: "../includes/_session_check.php",
        method: "POST",
        dataType: "json",
        success: function (response) {
            if (response.logged_in) {
                const user = response.user;
                console.log(`Logged in as: ${user.name} | Role: ${user.role}`);

                window.currentUser = user;

                switch (user.role.toLowerCase()) {
                    case "administrator":
                        handleAdministrator(user);
                        break;
                    case "inspector":
                        handleInspector(user);
                        break;
                    case "client":
                        handleClient(user);
                        break;
                    case "approver":
                        handleApprover(user);
                        break;
                    default:
                        handleDefault(user);
                        break;
                }
            } else {
                // 🚫 No redirect if not logged in
                console.warn("No active session detected. Staying on current page.");
            }
        },
        error: function () {
            console.error("Error checking session role.");
        },
    });
}


/* --------------------------
   ROLE-BASED FUNCTIONS
--------------------------- */

function handleAdministrator(user) {
    console.log("Admin view loaded for", user.name);
    $("#userRoleLabel").text("Administrator");

    // Load all schedules
    loadInspectionSchedules(user);
}

function handleInspector(user) {
    console.log("Inspector view loaded for", user.name);
    $("#userRoleLabel").text("Inspector");

    // Load only schedules assigned to inspector_id
    loadInspectionSchedules(user, {
        assigned_to: user.id
    });
}

function handleClient(user) {
    console.log("Client view loaded for", user.name);
    $("#userRoleLabel").text("Client");

    // Load only schedules that belong to this client
    loadInspectionSchedules(user, {
        owner_id: user.id
    });
}

function handleApprover(user) {
    console.log("Approver view loaded for", user.name);
    $("#userRoleLabel").text("Approver");

    // Load schedules pending approval
    loadInspectionSchedules(user, {
        status: "Pending Approval"
    });
}

function handleDefault(user) {
    console.log("Default user access for", user.name);
    $("#userRoleLabel").text(user.role || "User");
    loadInspectionSchedules(user);
}

function getIcon(type) {
    switch (type) {
        case "menu":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
  <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
</svg>`;
        case "expand":
            return `<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-chevron-bar-expand' viewBox='0 0 16 16'><path fill-rule='evenodd' d='M3.646 10.146a.5.5 0 0 1 .708 0L8 13.793l3.646-3.647a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 0-.708m0-4.292a.5.5 0 0 0 .708 0L8 2.207l3.646 3.647a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 0 0 0 .708M1 8a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 8'/></svg>`;
        case "checklist":
            return `<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-ui-checks' viewBox='0 0 16 16'><path d='M7 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zM2 1a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm0 8a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2zm.854-3.646a.5.5 0 0 1-.708 0l-1-1a.5.5 0 1 1 .708-.708l.646.647 1.646-1.647a.5.5 0 1 1 .708.708zm0 8a.5.5 0 0 1-.708 0l-1-1a.5.5 0 1 1 .708-.708l.646.647 1.646-1.647a.5.5 0 1 1 .708.708zM7 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 8a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5'/></svg>`;
        case "patchcheck":
            return `<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-patch-check-fill' viewBox='0 0 16 16'><path d='M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01zm.287 5.984-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708.708'/></svg>`;
        case "patchcaution":
            return `<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-patch-exclamation-fill' viewBox='0 0 16 16'><path d='M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01zM8 4c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995A.905.905 0 0 1 8 4m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2'/></svg>`;
        case "circle-check":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>`;
        case "pdf":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filetype-pdf" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z"/>
</svg>`;
        case "ban":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ban" viewBox="0 0 16 16">
  <path d="M15 8a6.97 6.97 0 0 0-1.71-4.584l-9.874 9.875A7 7 0 0 0 15 8M2.71 12.584l9.874-9.875a7 7 0 0 0-9.874 9.874ZM16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0"/>
</svg>`;
        case "dash-circle":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-circle" viewBox="0 0 16 16">
  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
  <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8"/>
</svg>`;
        case "repeat":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-repeat-1" viewBox="0 0 16 16">
  <path d="M11 4v1.466a.25.25 0 0 0 .41.192l2.36-1.966a.25.25 0 0 0 0-.384l-2.36-1.966a.25.25 0 0 0-.41.192V3H5a5 5 0 0 0-4.48 7.223.5.5 0 0 0 .896-.446A4 4 0 0 1 5 4zm4.48 1.777a.5.5 0 0 0-.896.446A4 4 0 0 1 11 12H5.001v-1.466a.25.25 0 0 0-.41-.192l-2.36 1.966a.25.25 0 0 0 0 .384l2.36 1.966a.25.25 0 0 0 .41-.192V13h6a5 5 0 0 0 4.48-7.223Z"/>
  <path d="M9 5.5a.5.5 0 0 0-.854-.354l-1.75 1.75a.5.5 0 1 0 .708.708L8 6.707V10.5a.5.5 0 0 0 1 0z"/>
</svg>`;
        case "geo":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M8 1a3 3 0 1 0 0 6 3 3 0 0 0 0-6M4 4a4 4 0 1 1 4.5 3.969V13.5a.5.5 0 0 1-1 0V7.97A4 4 0 0 1 4 3.999zm2.493 8.574a.5.5 0 0 1-.411.575c-.712.118-1.28.295-1.655.493a1.3 1.3 0 0 0-.37.265.3.3 0 0 0-.057.09V14l.002.008.016.033a.6.6 0 0 0 .145.15c.165.13.435.27.813.395.751.25 1.82.414 3.024.414s2.273-.163 3.024-.414c.378-.126.648-.265.813-.395a.6.6 0 0 0 .146-.15l.015-.033L12 14v-.004a.3.3 0 0 0-.057-.09 1.3 1.3 0 0 0-.37-.264c-.376-.198-.943-.375-1.655-.493a.5.5 0 1 1 .164-.986c.77.127 1.452.328 1.957.594C12.5 13 13 13.4 13 14c0 .426-.26.752-.544.977-.29.228-.68.413-1.116.558-.878.293-2.059.465-3.34.465s-2.462-.172-3.34-.465c-.436-.145-.826-.33-1.116-.558C3.26 14.752 3 14.426 3 14c0-.599.5-1 .961-1.243.505-.266 1.187-.467 1.957-.594a.5.5 0 0 1 .575.411"/>
</svg>`;
        case "checklist": 
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list-check" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3.854 2.146a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 3.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 7.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/>
</svg>`;
        case "card-checklist":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16">
  <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
  <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0M7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/>
</svg>`;
        case "trash":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
  <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/>
</svg>`;
        case "chev-right":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
</svg>`;
        case "chev-left":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
</svg>`;
        case "hourglass":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hourglass-split" viewBox="0 0 16 16">
  <path d="M2.5 15a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1zm2-13v1c0 .537.12 1.045.337 1.5h6.326c.216-.455.337-.963.337-1.5V2zm3 6.35c0 .701-.478 1.236-1.011 1.492A3.5 3.5 0 0 0 4.5 13s.866-1.299 3-1.48zm1 0v3.17c2.134.181 3 1.48 3 1.48a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351z"/>
</svg>`;
        case "more":
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
  <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
</svg>`;
        default:

            return "";
    }
}

function autoCleanupDaily() {
    const lastRun = localStorage.getItem('cleanup_last_run');
    const now = Date.now();

    // Run only if > 24 hours since last run
    if (!lastRun || now - lastRun > 3 * 60 * 60 * 1000) {
        $.ajax({
            url: "../includes/clear_gen_info_table.php",
            method: "POST",
            dataType: "json",
            success: function(res) {
                if (res.success) {
                    console.info(`Auto cleanup completed: ${res.deleted_count} deleted`);
                    if (res.deleted_count > 0) {
                        console.table(res.data); // show which records were removed
                    } else {
                        console.info("No expired drafts to remove.");
                    }
                } else {
                    console.warn("Auto cleanup failed:", res.message);
                }

                // Record run time regardless of success/failure
                localStorage.setItem('cleanup_last_run', now);
            },
            error: function(xhr, status, err) {
                console.error("Cleanup request failed:", err);
            }
        });
    }
}

function badgedResponse(resp, resp_positive = "Acknowledged", resp_warning = "Pending", resp_negative = "Pending", other_content = null) {
    let html = "";
    if (resp === "1" || resp === 1 || resp === true || resp === "Y") {
        html += `<div class="badge text-bg-success p-2">${resp_positive}</div>`;
    } else if (resp === "0" || resp === 0 || resp === false || resp === "N") {
        html += `<div class="badge text-bg-warning p-2">${resp_warning}</div>`;
    } else {
        html += `<div class="badge text-bg-danger p-2">${resp_negative}</div>`;
    }
    
    if(other_content !== "" && other_content){
        html += `<br class="my-0"><span class="fine-print small">${other_content}</small>`;
    }
    
    return html;
}
// ✅ Initialize Dark Mode and Base Alert Once



function appNavBtn(roleLabel, linkHref = "#", externalElementClass = "col-lg-4 col-6", icon = "menu") {
    return `
        <div class="${externalElementClass}">
            <a href="${linkHref}" class="page-btn btn btn-gold text-navy mb-2 w-100">
                <div class="mt-3 text-navy">
                    ${getIcon(icon)}
                </div>
                <div class="fine-print">
                    <hr class="my-1">
                    <span class="small text-small text-uppercase">${roleLabel}</span>
                </div>
            </a>
        </div>`;
}




$(document).on("click","#DarkMode", function () {
    $("body , .card > .card-body").removeClass("text-muted").addClass("bg-dark text-light");
    $("label, textarea").addClass("text-gold");
    $(".form-control").removeClass("text-muted text-dark").addClass("text-bg-dark text-light");
});
$(document).on("click", "#alertDynamicLink", function (e) {
    const href = $(this).attr("href");
    if (href && href !== "#") {
        e.preventDefault();
        $("#alerts").fadeOut(300, () => window.location.href = href);
    }
});
// Add-signature click handler


$(document).on("input", "input[type=number]", function () {
    // Skip if element allows negative values
    if ($(this).is("[allownegative]")) return;

    let val = $(this).val();

    // Remove existing message first
    $(this).next(".num-error-msg").remove();

    // Negative check
    if (val < 0) {
        $(this).val(0);
        $(this).after('<small class="text-danger num-error-msg">Cannot be negative.</small>');
    }

    // Enforce max if max attribute exists
    const max = $(this).attr("max");
    if (max && val > Number(max)) {
        $(this).val(max);
        // Optional: show max message
        $(this).after('<small class="text-danger num-error-msg">Cannot exceed ' + max + '.</small>');
    }
});


function buildInspectionReportHTML(scheduleId, reportData, isForPrint = false) {
    console.log("DEBUG: buildInspectionReportHTML starting");
    const { inspection_details, statistics, inspection_items } = reportData;
    const now = new Date().toLocaleString();
    
    // Use pre-computed statistics from backend
    const { 
        total_items, 
        passed_items, 
        failed_items, 
        not_applicable_items,
        required_items, 
        required_passed, 
        compliance_rate,
        passed_percentage, 
        failed_percentage, 
        not_applicable_percentage,
        required_passed_percentage,
        inspection_score, 
        has_defects 
    } = statistics;

    const has_defect_ind = has_defects ? "Has Defects" : "Passed";
    const has_defect_ind_class = has_defects ? "warning" : "success";
    const ins_score_class = inspection_score >= 75 ? "success" : "danger";
    let html =`<input type="hidden" id="getSchedIdHere" value="${inspection_details.schedule_id}">`;

     html += `
        <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
            <div class="badge bg-${has_defect_ind_class} p-2 fs-6">${has_defect_ind}</div>
            <div class="badge bg-${ins_score_class} p-2 fs-6">Score: ${inspection_score}%</div>
            <div class="badge bg-info p-2 fs-6">Items: ${total_items}</div>
            <div class="badge bg-success p-2 fs-6">Passed: ${passed_items}</div>
            <div class="badge bg-danger p-2 fs-6">Failed: ${failed_items}</div>
            <div class="badge bg-warning p-2 fs-6">Required: ${required_items}</div>
            <div class="badge bg-secondary p-2 fs-6">N/A: ${not_applicable_items}</div>
        </div>
        <div class="small text-muted mb-3">Generated: ${now}</div>
    `;

    // Statistics Summary Card
    html += `
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Inspection Summary</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-6 col-md-3 mb-3">
                    <div class="border rounded p-3 bg-white">
                        <div class="h4 text-primary mb-1">${total_items}</div>
                        <div class="small text-muted">Total Items</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="border rounded p-3 bg-white">
                        <div class="h4 text-success mb-1">${passed_items}</div>
                        <div class="small text-muted">Passed</div>
                        <div class="small text-muted">${passed_percentage}%</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="border rounded p-3 bg-white">
                        <div class="h4 text-danger mb-1">${failed_items}</div>
                        <div class="small text-muted">Failed</div>
                        <div class="small text-muted">${failed_percentage}%</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="border rounded p-3 bg-white">
                        <div class="h4 text-info mb-1">${not_applicable_items}</div>
                        <div class="small text-muted">Not Applicable</div>
                        <div class="small text-muted">${not_applicable_percentage}%</div>
                    </div>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-6 col-md-3 mb-3">
                    <div class="border rounded p-3 bg-white">
                        <div class="h4 text-warning mb-1">${required_items}</div>
                        <div class="small text-muted">Required Items</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="border rounded p-3 bg-white">
                        <div class="h4 text-success mb-1">${required_passed}</div>
                        <div class="small text-muted">Required Passed</div>
                        <div class="small text-muted">${required_passed_percentage}%</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="border rounded p-3 bg-white">
                        <div class="h4 text-danger mb-1">${required_items - required_passed}</div>
                        <div class="small text-muted">Required Failed</div>
                        <div class="small text-muted">${100 - required_passed_percentage}%</div>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <div class="border rounded p-3 bg-white">
                        <div class="h4 text-secondary mb-1">${compliance_rate}%</div>
                        <div class="small text-muted">Compliance Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    `;

    // General Information Section
    html += `
    <div class="container-fluid px-1">
        <div class="row">
            <div class="col-12">
               <h3 class="fw-bold mb-3">General Information</h3>
                <div class="table-responsive">
                  <table class="table table-bordered table-sm align-middle">
                    <tbody>
                      <tr><th>Building</th><td>${inspection_details.building_name || ''}</td></tr>
                      <tr><th>Location of Construction</th><td>${inspection_details.location_of_construction || ''}</td></tr>
                      <tr><th>Project Title</th><td>${inspection_details.project_title || ''}</td></tr>
                      <tr><th>Owner</th><td>${inspection_details.owner_name || ''}</td></tr>
                      <tr><th>Occupant Name</th><td>${inspection_details.occupant_name || ''}</td></tr>
                      <tr><th>Representative Name</th><td>${inspection_details.representative_name || ''}</td></tr>
                      <tr><th>Administrator Name</th><td>${inspection_details.administrator_name || ''}</td></tr>
                      <tr><th>Owner Contact No.</th><td>${inspection_details.owner_contact_no || ''}</td></tr>
                      <tr><th>Representative Contact No.</th><td>${inspection_details.representative_contact_no || ''}</td></tr>
                      <tr><th>Other Contact Info</th><td>${inspection_details.telephone_email || ''}</td></tr>
                      <tr><th>Business Name</th><td>${inspection_details.business_name || ''}</td></tr>
                      <tr><th>Establishment Name</th><td>${inspection_details.establishment_name || ''}</td></tr>
                      <tr><th>Nature of Business</th><td>${inspection_details.nature_of_business || ''}</td></tr>
                      <tr><th>Classification of Occupancy</th><td>${inspection_details.classification_of_occupancy || ''}</td></tr>
                      <tr><th>Healthcare Facility Name</th><td>${inspection_details.healthcare_facility_name || ''}</td></tr>
                      <tr><th>Healthcare Facility Type</th><td>${inspection_details.healthcare_facility_type || ''}</td></tr>
                      <tr><th>Height of Building</th><td>${inspection_details.height_of_building || ''}</td></tr>
                      <tr><th>Number of Storeys</th><td>${inspection_details.no_of_storeys || ''}</td></tr>
                      <tr><th>Area per Floor</th><td>${inspection_details.area_per_floor || ''}</td></tr>
                      <tr><th>Total Floor Area</th><td>${inspection_details.total_floor_area || ''}</td></tr>
                      <tr><th>Portion Occupied</th><td>${inspection_details.portion_occupied || ''}</td></tr>
                      <tr><th>Bed Capacity</th><td>${inspection_details.bed_capacity || ''}</td></tr>
                    </tbody>
                  </table>
                </div>
            </div>
        </div>
    </div>
    `;

    // Inspection Details Table
    html += `
        <div class="table-responsive mt-4">
            <h3 class="fw-bold">Inspection Details</h3>
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:10%">Section</th>
                        <th style="width:15%">Item</th>
                        <th style="width:10%">Response</th>
                        <th style="width:30%">Criteria</th>
                        <th style="width:10%">Remarks</th>
                        <th style="width:15%">Proof</th>
                        <th style="width:5%">Required</th>
                        <th style="width:5%">Status</th>
                    </tr>
                </thead>
                <tbody>
    `;

    inspection_items.forEach(row => {
        const proofHTML = row.response_proof_img ?
            `<img src="../assets/proof/Schedule_${scheduleId}/${row.response_proof_img}" class="img-fluid rounded" style="width:80px;height:80px;object-fit:cover;" alt="Proof Image">` :
            `<span class="text-muted">No Image</span>`;

        let remarksHTML, statusBadge;
        const remarks = parseInt(row.remarks);
        
        if (remarks === 1) {
            remarksHTML = `<span class="text-success">${getIcon("patchcheck")} Pass</span>`;
            statusBadge = `<span class="badge bg-success">Pass</span>`;
        } else if (remarks === 8) {
            remarksHTML = `<span class="text-info">${getIcon("dash-circle")} N/A</span>`;
            statusBadge = `<span class="badge bg-info">N/A</span>`;
        } else {
            remarksHTML = `<span class="text-danger">${getIcon("patchcaution")} Failed</span>`;
            statusBadge = `<span class="badge bg-danger">Failed</span>`;
        }

        const requiredBadge = row.required == 1 ? 
            '<span class="badge bg-warning">YES</span>' : 
            '<span class="badge bg-secondary">NO</span>';

        html += `
            <tr>
                <td>${row.section || ""}</td>
                <td>${row.item_text || ""}</td>
                <td>${row.response_value || ""} ${row.unit_label || ""}</td>
                <td>${row.checklist_criteria || ""}</td>
                <td>${remarksHTML}</td>
                <td>${proofHTML}</td>
                <td>${requiredBadge}</td>
                <td>${statusBadge}</td>
            </tr>
        `;
    });

    html += `</tbody></table></div>`;

    // Print Button (if needed)
    if (isForPrint) {
        html += `
            <div class="text-center mt-4 mb-3">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print / Save as PDF
                </button>
            </div>
        `;
    }
    console.log("DEBUG: buildInspectionReportHTML End.");
    return html;
    
}



$(document).on("click", ".checkInspectionReport", function () {
    
    const schedId = $(this).data("sched-id") || $("#getSchedIdHere").val();

    $.ajax({
        url: "../includes/export_inspection_report.php",
        type: "POST",
        data: {
            schedule_id: schedId
        },
        dataType: "json",
        success: function (res) {
            if (res.success) {
                // Use the new data structure from the refactored backend
                const reportData = res.data; // Contains inspection_details, statistics, and inspection_items
                const html = buildInspectionReportHTML(schedId, reportData, false);
                $("#inspectionReportModal .modal-body").html(html);
                $("#inspectionReportModal").modal("show");
                $("#exportReportBtn").attr("data-sched-id", schedId);
                console.log("DEBUG: #exportReportBtn " + schedId);
                
            }
            else {
                alert("Failed to load report: " + res.message);
            }
        },
        error: function (xhr, status, err) {
            console.error("Error generating report:", err);
            alert("Error loading inspection report. Please try again.");
        }
    });
});


$(document).on("click", "#exportReportBtn", function () {
    

    const scheduleId = $(this).data("sched-id") || $("#getSchedIdHere").val();
    

    console.log("Verifying attribute:", $("#exportReportBtn").attr("data-sched-id"));
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
        data: {
            schedule_id: scheduleId
        },
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



function openPrintWindow(printWindow, scheduleId, res) {
    const htmlContent = buildInspectionReportHTML(scheduleId, res.data, true);

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



// Separate function to bind export button
function bindExportButtonHandler(scheduleId) {
    // Remove any existing handlers to prevent duplicates
    $(document).off("click", "#exportReportBtn");
    
    $(document).on("click", "#exportReportBtn", function () {
        console.log("✅ Export button handler triggered");
        console.log("Schedule ID from button:", $(this).data("sched-id"));
        console.log("Schedule ID from function param:", scheduleId);
        
        const id = $(this).data("sched-id") || scheduleId;
        
        if (!id) {
            console.error("❌ No schedule ID found");
            return;
        }
        
        // Open popup and continue with your existing code...
        const printWindow = window.open("", "_blank");
        if (!printWindow) {
            alert("Please allow popups for this site.");
            return;
        }

        printWindow.document.write("<p style='padding:20px;'>Preparing report...</p>");

        $.ajax({
            url: "../includes/export_inspection_report.php",
            type: "POST",
            data: { schedule_id: id },
            dataType: "json",
            success: function (res) {
                if (!res.success) {
                    printWindow.document.body.innerHTML = `<p>Failed to fetch report: ${res.message}</p>`;
                    return;
                }
                openPrintWindow(printWindow, id, res);
            },
            error: function (xhr, status, err) {
                printWindow.document.body.innerHTML = `<p>Error fetching report data: ${err}</p>`;
                console.error("Export error:", err);
            }
        });
    });
}



 function toggleThresholdFields(itemId, criteria) {

        // itemId for edit is numeric (e.g. 123)
        // itemId for add is composite 'checklistId-sectionId'
        let box;
        if (String(itemId).indexOf('-') === -1) {
            // edit
            box = $("#threshold-edit-" + itemId);
        } else {
            // add
            box = $("#threshold-add-" + itemId);
        }

        if (!box.length) return;

        // Hide all threshold sub-blocks inside this box
        box.find(".range-fields, .minval-field, .maxval-field, .yesno-field, .days-field, .textvalue-field").addClass("d-none");

        switch (criteria) {
            case "range":
                box.find(".range-fields").removeClass("d-none");
                break;
            case "min_val":
                box.find(".minval-field").removeClass("d-none");
                break;
            case "max_val":
                box.find(".maxval-field").removeClass("d-none");
                break;
            case "yes_no":
                box.find(".yesno-field").removeClass("d-none");
                break;
            case "days":
                box.find(".days-field").removeClass("d-none");
                break;
            case "textvalue":
                box.find(".textvalue-field").removeClass("d-none");
                break;
            default:
                // none
                break;
        }

        // make sure container is visible for add/edit
        box.show();
    }

    function loadFavicon(faviconPath = "../assets/img/tagasalbar.ico") {
    // Remove existing favicon if any
    const existingFavicon = document.querySelector('link[rel="icon"], link[rel="shortcut icon"]');
    if (existingFavicon) {
        existingFavicon.remove();
    }
    
    // Create new favicon link
    const favicon = document.createElement('link');
    favicon.rel = 'icon';
    favicon.type = 'image/x-icon';
    favicon.href = faviconPath;
    
    // Add to head
    document.head.appendChild(favicon);
    
    console.log('Favicon loaded:', faviconPath);
}
