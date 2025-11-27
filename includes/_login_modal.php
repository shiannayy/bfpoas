<div class="modal fade modal-slide-up" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-slide modal-bottom">
    <div class="modal-content shadow-lg border-0 rounded-top-3">
      <div class="modal-header bg-navy-dark text-white">
        <h4 class="modal-title" id="loginModalLabel"> <img src="../assets/img/firefighter.gif" style="width:40px; border-radius: 10px" alt=""> Login</h4>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="loginForm">
          <!-- Username -->
          <div class="mb-3">
            <label for="username" class="form-label fw-bold">Email Address</label>
            <input type="text" class="form-control" id="username" placeholder="Enter Email Address" required>
          </div>
          <!-- Password -->
          <div class="mb-3">
            <label for="password" class="form-label fw-bold">Password</label>
            <input type="password" class="form-control" id="password" placeholder="Enter password" required>
          </div>
          <!-- Remember Me -->
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="rememberMe">
            <label class="form-check-label" for="rememberMe">Remember me</label>
          </div>
          <!-- Login Button -->
          <div class="d-grid">
            <button type="submit" class="btn btn-warning fw-bold">Login</button>
          </div>
        </form>
      </div>
      <div class="modal-footer bg-light d-flex align-content-center">
        <div id="loginfo" class="alert py-1 d-none"></div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
