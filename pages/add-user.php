<!DOCTYPE html>

<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/color_pallette.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <!-- Static Top Navbar -->
    <?php include_once "../includes/_nav_admin.php";?>

    <div class="container mt-5 pt-3">

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0 rounded-3 mt-3">
                    <div class="card-header bg-navy-dark text-white">
                        <h4 class="mb-0">Register New User</h4>
                    </div>
                    <div class="card-body">
                        <form id="registerForm">
                            <!-- Full Name -->
                            <div class="mb-3">
                                <label for="full_name" class="form-label fw-bold">Full Name</label>
                                <input type="text" style="text-transform: uppercase;" class="form-control focus-outline-navy" id="full_name" autocomplete="off" required>
                                <div class="invalid-feedback" id="full_name_error"></div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control focus-outline-navy" autocomplete="off" placeholder="something@domain.com" id="email" required>
                                <div class="invalid-feedback" id="email_error"></div>
                            </div>

                            <!-- Contact No -->
                            <div class="mb-3">
                                <label for="contactNo" class="form-label fw-bold">Contact No <i class="text-muted text-small">(09XXXXXXXXX)</i></label>
                                <input type="tel" pattern="[0]{1}[9]{1}[0-9]{9}" placeholder="09XXXXXXXXX" class="form-control focus-outline-navy" id="contactNo" required>
                                <div class="invalid-feedback" id="contactNo_error"></div>
                            </div>

                            <!-- Password -->
                            <div class="mb-3 position-relative">
                                <label for="password" class="form-label fw-bold">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control focus-outline-navy" id="password" required>
                                    <button type="button" class="btn togglePassword">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                        </svg>`
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="password_error"></div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-3 position-relative">
                                <label for="confpassword" class="form-label fw-bold">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control focus-outline-navy" id="confpassword" required>
                                    <button type="button" class="btn togglePassword">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                                        </svg>`
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="confpassword_error"></div>
                            </div>

                            <!-- Role -->
                            <div class="mb-3">
                                <label class="form-label fw-bold d-block">Role</label>
                                <input type="radio" class="btn-check role" name="role" id="roleInspector" value="Inspector">
                                <label class="btn btn-outline-navy rounded-4 flex-fill m-1" for="roleInspector">Inspector</label>

                                <input type="radio" class="btn-check" name="role" id="roleAdmin" value="Administrator">
                                <label class="btn btn-outline-navy rounded-4 flex-fill m-1" for="roleAdmin">Administrator</label>

                                <input type="radio" class="btn-check" name="role" id="roleClient" value="Client">
                                <label class="btn btn-outline-navy rounded-4 flex-fill m-1" for="roleClient">Client</label>
                                <div class="invalid-feedback d-block" id="role_error"></div>
                            </div>

                            <!-- Sub Role / Title -->
                            <div class="mb-3" id="choosesubrole">


                                <div id="subrole-radios" class="btn-group d-flex flex-wrap" role="group" aria-label="Subrole selection">
                                    <!-- Subrole options will be dynamically inserted here -->
                                </div>

                                <!-- Hidden input for auto-filled client subrole -->
                                <input type="hidden" id="subrole" name="subrole" value="">
                            </div>

                            <!-- Terms & Conditions -->
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="agreeTerms">
                                <label class="form-check-label" for="agreeTerms">
                                    I have read and agree to the
                                    <button type="button" class="btn btn-link p-0 m-0 align-baseline" data-bs-toggle="offcanvas" data-bs-target="#termsOffcanvas">
                                        Data Privacy Terms & Conditions
                                    </button>.
                                </label>
                                <div class="invalid-feedback d-block" id="terms_error"></div>
                            </div>

                            <!-- Submit -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-warning fw-bold">Save</button>
                            </div>
                        </form>

                        <div class="alert mt-3 d-none" id="reginfo"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms & Conditions Offcanvas -->
    <div class="offcanvas offcanvas-bottom h-75" tabindex="-1" id="termsOffcanvas" style="border-radius: 15px;">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Data Privacy Terms & Conditions</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body">
            <p class="fw-bold">Data Privacy Notice (Philippines)</p>

            <p>
                By creating an account, you voluntarily provide your personal information such as
                your name, email address, contact number, and other related data. This information
                is collected in accordance with the Republic Act No. 10173 or the
                <strong>Data Privacy Act of 2012</strong>.
            </p>

            <p class="fw-bold">How We Use Your Information</p>
            <ul>
                <li>To verify your identity and create your account.</li>
                <li>To contact you regarding system updates, notifications, or account activity.</li>
                <li>To process inspection records, reports, and related transactions.</li>
                <li>To maintain security, audit trails, and proper system functionality.</li>
            </ul>

            <p class="fw-bold">Data Protection</p>
            <p>
                We apply organizational, technical, and physical safeguards to protect your
                personal information. Your data will not be shared with any third party unless
                required by law, authorized by you, or necessary for legitimate system operations.
            </p>

            <p class="fw-bold">Your Rights Under the Data Privacy Act</p>
            <ul>
                <li>The right to access your personal data.</li>
                <li>The right to correct inaccurate or outdated information.</li>
                <li>The right to withdraw consent or request deletion (when applicable).</li>
                <li>The right to data portability.</li>
            </ul>

            <p>
                By proceeding with the registration, you acknowledge that you have read,
                understood, and agree to the above terms under the Data Privacy Act of 2012.
            </p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="../assets/js/register.js"></script>


</body>

</html>