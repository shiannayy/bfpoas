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


    <!--    reschedule request-->
    <div class="offcanvas offcanvas-start bg-navy-dark text-gold" tabindex="-1" id="reschedCanvas" aria-labelledby="reschedCanvasLabel">
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

    <!-- Fullscreen Offcanvas -->
    <div class="offcanvas offcanvas-bottom h-100" tabindex="-1" id="signatureOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Draw Signature
                <?php if(esignature($_SESSION['user_id']) !== NULL ){
                        $hasSignature = true;
                    ?>
                <!-- Thumbnail -->
                <img src="../assets/signatures/<?php echo esignature($_SESSION['user_id']);?>" alt="Signature" height="50px" data-bs-toggle="modal" data-bs-target="#signaturePreviewModal">
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


      <div class="modal fade" id="cancelScheduleModal" tabindex="-1" aria-labelledby="cancelScheduleLabel"
        aria-hidden="true">
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
    <!--    reschedule request-->
    <div class="offcanvas offcanvas-start bg-navy-dark text-gold" tabindex="-1" id="reschedCanvas"
        aria-labelledby="reschedCanvasLabel">
        <div class="offcanvas-header">
            <h5 id="reschedCanvasLabel">Request Reschedule</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="reschedForm">
                <input type="hidden" name="schedule_id" id="schedule_id">

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
    <!-- Fullscreen Offcanvas -->
    <div class="offcanvas offcanvas-bottom h-100" tabindex="-1" id="signatureOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Draw Signature
                <?php if(esignature($_SESSION['user_id']) !== NULL ){
                        $hasSignature = true;
                    ?>
                <!-- Thumbnail -->
                <img src="../assets/signatures/<?php echo esignature($_SESSION['user_id']);?>" alt="Signature"
                    height="50px" data-bs-toggle="modal" data-bs-target="#signaturePreviewModal">
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