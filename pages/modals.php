<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title" id="logoutConfirmLabel">Confirm Logout</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor"
                        class="bi bi-exclamation-triangle text-warning mb-3" viewBox="0 0 16 16">
                        <path
                            d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z" />
                        <path
                            d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z" />
                    </svg>
                    <p class="mb-0"><strong>Are you sure you want to logout?</strong></p>
                    <small class="text-muted">You will be signed out of your account.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="../pages/logout_process.php" type="button" class="btn btn-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-box-arrow-right me-2" viewBox="0 0 16 16">
                        <path fill-rule="evenodd"
                            d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 1h-8A1.5 1.5 0 0 0 0 2.5v9A1.5 1.5 0 0 0 1.5 13h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z" />
                        <path fill-rule="evenodd"
                            d="M15.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708l2.147-2.146H4.5a.5.5 0 0 1 0-1h10.293l-2.147-2.146a.5.5 0 0 1 .708-.708l3 3z" />
                    </svg>
                    Yes, Logout
                </a>
            </div>
        </div>
    </div>
</div>
<!-- Signature Preview Modal -->
<div class="modal fade" id="signaturePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Signature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="signaturePreviewImg" src="" class="img-fluid border p-2" alt="Signature Preview">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSaveSignature">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- Draw Signature Fullscreen Offcanvas -->
<div class="offcanvas offcanvas-bottom h-100" tabindex="-1" id="signatureOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Draw Signature
            <?php if(esignature($_SESSION['user_id']) !== NULL ){
                    $hasSignature = true;
                ?>
            <!-- Thumbnail -->
            <img src="../assets/signatures/<?php echo esignature($_SESSION['user_id']);?>" alt="Signature" height="50px"
                data-bs-toggle="modal" data-bs-target="#signaturePreviewModal">
            <?php } ?>

        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        <!-- Signature Canvas -->
        <canvas id="signatureCanvas"></canvas>

        <!-- Buttons -->
        <div class="d-flex justify-content-between p-3 bg-light border-top">
            <button class="btn btn-secondary" id="clearSignature">Clear</button>
            <button class="btn btn-primary" id="saveSignature">Save</button>
        </div>
    </div>
</div>
<!--   reschedule request-->
<div class="offcanvas offcanvas-start bg-navy-dark text-gold" tabindex="-1" id="reschedCanvas"
    aria-labelledby="reschedCanvasLabel">
    <div class="offcanvas-header">
        <h5 id="reschedCanvasLabel">Request Reschedule</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="reschedForm">
            <input type="text" name="schedule_id" id="schedule_id">

            <div class="mb-3">
                <label for="preferred_date" class="form-label">Preferred Date</label>
                <input type="date" class="form-control" id="preferred_date" name="preferred_date" required>
            </div>

            <div class="mb-3">
                <label for="reason" class="form-label">Reason for Rescheduling</label>
                <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">Submit Request</button>
        </form>
    </div>
</div>
<!---->

<div class="modal fade" id="cancelScheduleModal" tabindex="-1" aria-labelledby="cancelScheduleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelScheduleLabel"><i
                        class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Cancellation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Are you sure you want to <b>cancel this schedule</b>? This action cannot be undone.
                </p>

                <form id="cancelScheduleForm">
                    <input type="hidden" id="cancelScheduleId" name="schedule_id">
                    <div class="mb-3">
                        <label for="cancelReason" class="form-label fw-semibold small">Reason / Remarks:</label>
                        <textarea id="cancelReason" name="reason" class="form-control" rows="3"
                            placeholder="Enter reason for cancellation..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">Confirm Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- Inspection Report Modal -->
<div class="modal fade" id="inspectionReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content shadow-lg" style="max-height: 95vh;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Inspection Report</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <!-- Modal body now scrolls -->
            <div class="modal-body overflow-auto" style="max-height: 70vh;">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle" id="inspectionReportTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%">Section</th>
                                <th style="width: 35%">Item</th>
                                <th style="width: 15%">Response</th>
                                <th style="width: 20%">Remarks</th>
                                <th style="width: 20%">Proof</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="exportReportBtn" data-sched-id="">
                    <i class="bi bi-file-earmark-excel"></i> Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Other Details of A schedule   -->
<div class="offcanvas offcanvas-bottom h-75" tabindex="-1" id="moreDetails">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Scheduled Inspection</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        <div class="container-fluid" id="moreDetailsBody"></div>
    </div>
</div>


<!-- Confirmation Modal -->
<div class="modal fade" id="inspectionConfirmationModal" tabindex="-1" aria-labelledby="inspectionConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="inspectionConfirmationModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-exclamation-triangle-fill me-2" viewBox="0 0 16 16">
                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>
                    Confirm Inspection Completion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1"><strong>Are you sure you want to mark this inspection as complete?</strong></p>
                <p class="text-muted small mb-0">Once submitted:</p>
                <ul class="text-muted small">
                    <li>You won't be able to make further changes to this inspection</li>
                    <li>The inspection will be sent for review/processing</li>
                    <li>You may need manager approval for certain items</li>
                </ul>
                
                <!-- Optional: Show warning if required items are incomplete -->
                <div class="alert alert-danger mt-3 d-none" id="requiredItemsWarning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-circle me-1" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                        <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                    </svg>
                    <span id="warningMessage"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmInspectionSubmit">Yes, Submit Inspection</button>
            </div>
        </div>
    </div>
</div>