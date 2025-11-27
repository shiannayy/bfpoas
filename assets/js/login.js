
$(document).ready(function() {


  // Login form handler
  $("#loginForm").on("submit", function(e) {
    e.preventDefault();

    let $loginfo = $("#loginfo");
    let firetruck = `<img src="../assets/img/fire-truck.gif" class="fire-truck" style="width:40px; mix-blend-mode:darken" alt="">`;    

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
        username: $("#username").val(),
        password: $("#password").val(),
        rememberMe: $("#rememberMe").is(":checked") ? 1 : 0
      },
      dataType: "json",
      success: function(response) {
        if (response.status === "success") {
            
            
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

  // Bootstrap modal events (using jQuery)
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
