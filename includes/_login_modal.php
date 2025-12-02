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
            <div class="input-group">
              <input type="password" class="form-control" id="password" placeholder="Enter password" required>
              <button class="btn btn-outline-secondary togglePassword" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                </svg>
              </button>
            </div>
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
