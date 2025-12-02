$(document).ready(function() {
  // ========================
  // RESTORE REMEMBER ME
  // ========================
  const savedEmail = localStorage.getItem("bfp_remember_email");
  const savedPassword = localStorage.getItem("bfp_remember_password");
  
  if (savedEmail && savedPassword) {
    $("#username").val(savedEmail);
    $("#password").val(savedPassword);
    $("#rememberMe").prop("checked", true);
  }

  // ========================
  // LOGIN FORM HANDLER
  // ========================
  $("#loginForm").on("submit", function(e) {
    e.preventDefault();
    let $loginfo = $("#loginfo");
    let firetruck = `<img src="../assets/img/fire-truck.gif" class="fire-truck" style="width:40px; mix-blend-mode:darken" alt="">`;
    
    const username = $("#username").val();
    const password = $("#password").val();
    const rememberMe = $("#rememberMe").is(":checked");
    
    // Show loading message
    $loginfo
      .removeClass()
      .addClass("alert alert-info")
      .html("Authenticating... Please wait. " + firetruck)
      .show();
    
    $.ajax({
      url: "../pages/login_process.php",
      type: "POST",
      data: {
        username: username,
        password: password,
        rememberMe: rememberMe ? 1 : 0
      },
      dataType: "json",
      success: function(response) {
        if (response.status === "success") {
          // Handle Remember Me
          if (rememberMe) {
            localStorage.setItem("bfp_remember_email", username);
            localStorage.setItem("bfp_remember_password", password);
          } else {
            // Clear stored credentials if unchecked
            localStorage.removeItem("bfp_remember_email");
            localStorage.removeItem("bfp_remember_password");
          }

          showAlert(response.message + firetruck, "success");

          $loginfo
            .removeClass()
            .addClass("alert alert-success")
            .html(response.message + firetruck);
          
          // Redirect after a short delay
          setTimeout(function() {
            window.location.href = response.redirect;
          }, 1500);
        } else {
          $loginfo
            .removeClass()
            .addClass("alert alert-danger")
            .html(response.message);
        }
      },
      error: function() {
        $loginfo
          .removeClass()
          .addClass("alert alert-danger")
          .html("Something went wrong. Please try again.");
      }
    });
  });

  // ========================
  // BOOTSTRAP MODAL EVENTS
  // ========================
  const $loginModal = $("#loginModal");
  $loginModal.on("show.bs.modal", function() {
    // remove aria-hidden while animating open
    $(this).removeAttr("aria-hidden");
  });

  $loginModal.on("hidden.bs.modal", function() {
    // restore aria-hidden after fully closed
    $(this).attr("aria-hidden", "true");
  });
});