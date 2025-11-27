<!-- Inspection Report Modal -->
<div class="modal fade" id="inspectionReportModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content shadow-lg">
         <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Inspection Report</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="table-responsive">
               <table class="table table-bordered table-striped align-middle" id="inspectionReportTable">
                  <thead class="table-light">
                     <tr>
                        <th style="width: 10%">Section</th>
                        <th style="width: 40%">Item</th>
                        <th style="width: 15%">Response</th>
                        <th style="width: 20%">Remarks</th>
                        <th style="width: 15%">Proof</th>
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
            <button type="button" class="btn btn-success" id="exportReportBtn">
               <i class="bi bi-file-earmark-excel"></i> Export
            </button>
         </div>
      </div>
   </div>
</div>
