<?php
if(isset($_GET['sched_id']) && isset($_GET['insp_id'])){
include_once "../includes/_init.php";

$inspection_id = intval($_GET['insp_id']);
$schedule_id = intval($_GET['sched_id']);


    
$schedule = select("inspection_schedule", ["schedule_id" => $schedule_id], null, 1);
if (!$schedule) die("Invalid schedule");

    
$inspection = select("inspections" ,["inspection_id" => $inspection_id], null, 1);

    //get the general info.
$gen_info_id = $schedule[0]['gen_info_id'];
$gen_info = select("general_info", ["gen_info_id" => $gen_info_id], null, 1);
if (!$gen_info) die("No Establishment found.");
    $gi = $gen_info[0];

    // get checklist_id from the inspection schedule
$checklist_id = $schedule[0]['checklist_id'];

//get the checklist_type
$cl = select("checklists", ["checklist_id" => $checklist_id], 1); 
    $fsed_code = $cl[0]['fsed_code'];
    $fsed_title = $cl[0]['title'];
// get checklist items, ordered by section + item_no
$items = select_join(
    ['checklist_items ci'],       // main table with alias
    ['ci.*', 'cs.checklist_section_id as section_id', 'cs.section as section_name'],  // columns to select (you can add more from cs if needed)
    [
        [
            'table' => 'checklist_sections cs',   // join table
            'on'    => 'ci.section = cs.checklist_section_id',
            'type'  => 'INNER'                   // join type
        ]
    ],
    ['ci.checklist_id' => $checklist_id, 'ci.chk_item_status' => 1],        // where clause
    ['ci.section' => 'ASC', 'ci.item_no' => 'ASC'] // order by
);
$responses = getInspectionResponses($schedule_id); 


// group by section
$grouped = [];
foreach ($items as $item) {
    $section_id = $item['section_id'];
    $section_name = $item['section_name'];

    if (!isset($grouped[$section_id])) {
        $grouped[$section_id] = [
            'name'  => $section_name,
            'items' => []
        ];
    }

    $grouped[$section_id]['items'][] = $item;
}
?>
<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Uploaded Proof</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="proofModalBody">
                <!-- Dynamic content via JS -->
            </div>
        </div>
    </div>
</div>
<div id="alerts"></div>
<section id="infosection" data-info="Inspecting an Establishment"></section>
<div class="container-fluid" style="margin-top:70px">
    <div class="row m-0 p-0">
        <div class="col-12">
            <h3 class="display-6 fw-bold"><?php echo $fsed_code . " - " . $fsed_title; ?></h3>
            <div class="card border-0 bg-light mb-3">
                <div class="card-header">
                    <h5 class="text-primary card-title">
                        <button class="btn mb-1" type="button" data-bs-toggle="collapse" data-bs-target="#genInfoDetail"
                            aria-expanded="false" aria-controls="genInfoDetail">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-chevron-expand" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M3.646 9.146a.5.5 0 0 1 .708 0L8 12.793l3.646-3.647a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 0-.708m0-2.292a.5.5 0 0 0 .708 0L8 3.207l3.646 3.647a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 0 0 0 .708" />
                            </svg>
                        </button>
                        General Information
                    </h5>
                </div>
                <div class="card-body container-fluid collapse" id="genInfoDetail">
                    <div class="row">
                        <div class="col-12">
                            <b class="fw-bold small text-small">Building: </b> <?php echo $gi['building_name']; ?>
                        </div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Location of
                                Construction:</b></div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['location_of_construction'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Project Title:</b>
                        </div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['project_title'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Owner:</b></div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['owner_name'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Occupant Name:</b>
                        </div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['occupant_name'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Representative
                                Name:</b></div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['representative_name'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Administrator
                                Name:</b></div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['administrator_name'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Owner Contact No.:</b>
                        </div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['owner_contact_no'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Representative Contact
                                No.:</b></div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['representative_contact_no'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Other Contact
                                Info:</b></div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['telephone_email'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Business Name:</b>
                        </div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['business_name'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Establishment
                                Name:</b></div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['establishment_name'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Nature of
                                Business:</b></div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['nature_of_business'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Classification of
                                Occupancy:</b></div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['classification_of_occupancy'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Healthcare Facility
                                Name:</b></div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['healthcare_facility_name'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Healthcare Facility
                                Type:</b></div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['healthcare_facility_type'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Height of
                                Building:</b></div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['height_of_building'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Number of Storeys:</b>
                        </div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['no_of_storeys'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Area per floor:</b>
                        </div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['area_per_floor'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Total Floor Area:</b>
                        </div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['total_floor_area'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Portion Occupied:</b>
                        </div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['portion_occupied'];?></div>
                        <div class="col-6 col-sm-12 col-md-3"><b class="fw-bold small text-small">Bed Capacity:</b>
                        </div>
                        <div class="col-6 col-sm-12 col-md-3"><?php echo $gi['bed_capacity'];?></div>

                    </div>


                </div>
            </div>
        </div>
        <div class="col-12">
            <form id="inspectionForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" data-name="checklist_id" name="checklist_id" value="<?php echo $checklist_id; ?>">
                <input type="hidden" data-name="schedule_id"  name="schedule_id" value="<?php echo $schedule_id; ?>">
                <input type="hidden" data-name="inspection_id" name="inspection_id" value="<?php echo $inspection_id; ?>">
                
                <?php 
                if(!empty($grouped)){
                foreach ($grouped as $section_id => $sectionData) { ?>
                <div class="card mb-1 shadow border-0 bg-light" data-section="<?= htmlspecialchars($section_id) ?>">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-primary mb-0">
                            <?= htmlspecialchars($sectionData['name']) ?>
                        </h5>
                        <div class="autosave-status small text-muted"></div>

                    </div>

                    <div class="card-body">
                       
                        <?php 
                        if(!empty($sectionData['items'])){
                        foreach ($sectionData['items'] as $item) { 
                           $savedValue   = $responses[$item['item_id']]['response_value'] ?? null;
                           $savedRemarks = $responses[$item['item_id']]['remarks'] ?? null;
                           $savedImg     = $responses[$item['item_id']]['response_proof_img'] ?? null;
                           $savedResponseId   = $responses[$item['item_id']]['response_id'] ?? null;
                           $isRequired = $item['required'];
                            $colorBG = "";
                            $icon = "";
                            $manualOverride = null; //bool
                            $notapp = null;
    
                            $isNotAppChecked = false;
                            $isManOverrideChecked = false;
                                        
    
                                    if($isRequired !== 1){
                                        if($savedRemarks == "1"){ //pass
                                            $colorBG = "text-bg-success"; 
                                            $icon = getIcon("patchcheck") . " Passed";

                                            //show or hide manual override and not applicable
                                            $manualOverride = false;
                                            $notapp = false;

                                            $isNotAppChecked = false;
                                            $isManOverrideChecked = false;

                                          } else if($savedRemarks == "0") { //failed
                                            $colorBG = "text-bg-danger"; 
                                            $icon = getIcon("patchcaution") ." Failed";
                                            
                                            $manualOverride = false;
                                            $notapp = true;

                                            $isManOverrideChecked = false;
                                            $isNotAppChecked = false;

                                          } else if($savedRemarks == "9") { //no criteria
                                            $colorBG = "text-bg-warning"; 
                                            $icon = "No Criteria Set";
                                            
                                            $manualOverride = false;
                                            $notapp = true;

                                            $isNotAppChecked = false;
                                            $isManOverrideChecked = false;

                                          } else if($savedRemarks == "8") {  //not applicable
                                            $colorBG = "text-bg-info"; 
                                            $icon = "N/A";
                                            $manualOverride = false;
                                            $notapp = true;

                                            $isNotAppChecked = true;
                                            $isManOverrideChecked = false;
                                          } else{ 
                                            $colorBG = "text-bg-light";
                                            $icon = "";
                                            $manualOverride = "";
                                            $notapp = null;
                                          } 
                                    }
                                    else{
                                            $manualOverride = false;
                                            $notapp = false;

                                            $isNotAppChecked = false;
                                            $isManOverrideChecked = false;
                                    }
                            ?>
                        <div class="mb-2 input-group align-content-center flex-wrap">
                            <!-- Thumbnail / Preview Link -->
                            <div class="proof-preview align-content-center" id="proof_preview_<?= $item['item_id'] ?>">
                                <?php if (!empty($savedImg)): ?>
                                <a href="#" class="open-proof-modal"
                                    data-proof-file="<?= htmlspecialchars($savedImg) ?>"
                                    data-item-id="<?= $savedResponseId ?>">
                                    <img id="img_proof_<?= $savedResponseId ?>"
                                        src="../assets/proof/Schedule_<?= $schedule_id . '/' . htmlspecialchars($savedImg); ?>"
                                        alt="Proof" class="img-thumbnail response-img-proof"
                                        style="width:50px;height:50px;object-fit:cover;">
                                </a>
                                <?php endif; ?>

                            </div>
                            <label title="Attach Proof" for="proof_item_<?= $item['item_id'] ?>" class="btn btn-navy rounded-2 ms-0 align-content-center"><?= getIcon("attach") ?></label>
                            <input type="file" class="form-control proof-upload d-none"
                                data-item-id="<?= $item['item_id'] ?>" id="proof_item_<?= $item['item_id'] ?>"
                                name="proof_item_<?= $item['item_id'] ?>">

                            
                            <div class="align-content-center d-inline not-applicable ms-1 p-0 <?= (!$notapp) ? 'd-none' : '' ?>"  id="na_<?= $item['item_id'] ?>">  
                                <input id="notApplicable_<?= $item['item_id'] ?>" 
                                    <?= ($isNotAppChecked) ? 'checked' : '' ?>
                                    type="checkbox"
                                    name="notApplicable_<?= $item['item_id'] ?>" 
                                    class="notApplicableBtn btn-check"
                                    value="1" 
                                    autocomplete="off" />
                                <label for="notApplicable_<?= $item['item_id'] ?>"
                                        class="pt-2 m-0 me-1 h-100 align-content-center btn btn-outline-warning text-secondary"> 
                                        <b class="d-lg-none d-md-none">N/A</b> 
                                        <b class="d-none d-lg-inline d-md-inline">Not Applicable</b> 
                                </label>
                            </div>

                            <div class="dropdown align-content-center d-inline manual-pass ms-1 p-0 <?= (!$manualOverride) ? 'd-none' : '' ?>"
                                id="mp_<?= $item['item_id'] ?>">
                                <input id="manual_pass_<?= $item['item_id'] ?>" 
                                    <?= $isManOverrideChecked ? 'checked' : ''?>
                                    type="checkbox"
                                    name="manual_pass_<?= $item['item_id'] ?>" 
                                    class="btn-check manualpassbtn" value="1"
                                    autocomplete="off" />
                                <label for="manual_pass_<?= $item['item_id'] ?>"
                                    class="btn btn-outline-success pt-2 m-0 h-100 align-content-center"> 
                                        <b class="d-lg-none d-md-none">PASS</b> 
                                        <b class="d-none d-lg-inline d-md-inline"><?= getIcon("patchcaution") ?> OVERRIDE: Pass</b> 
                                </label>
                            </div>
                            


                            <div class="border-0 fw-light d-flex me-auto align-content-center align-middle flex-grow-1 min-width-0">
                                <span data-item-id="eval_<?= $item['item_id'] ?>"
                                    class="evaluation-result badge <?= $colorBG ?>">
                                    <?= $icon ?>
                                </span>
                                <span class="text-wrap my-auto flex-grow-1 min-width-0">
                                    <?php echo htmlspecialchars($item['item_text']);
                                        if ($item['required']) { ?><span class="text-danger">*</span><?php } ?>
                                </span>
                            </div>

                            <?php if ($item['input_type'] === "checkbox") {   ?>
                            <div class="ms-1 input-group-text border-0">
                                <div class="form-check">
                                    <input type="checkbox" class="btn-check section-input"
                                        name="item_<?= $item['item_id'] ?>" value="1"
                                        <?= ((int)$savedValue == 1) ? "checked" : "" ?>>
                                    <label class="btn btn-sm btn-outline-secondary">
                                        <?= $item['unit_label'] ?: "YES" ?>
                                    </label>
                                </div>
                            </div>
                            <?php
                            }
                            else if ($item['input_type'] === "select") { 
                            $options = select( "checklist_item_select_options", ["item_id" => $item['item_id']], ["sort_order" => "ASC"] ); ?>
                            <select class="ms-1 border-0 form-select section-input" name="item_<?php echo $item['item_id'] ?>"
                                <?php echo $item['required'] ? 'required' : '' ?>>
                                <option value="">-- Select --</option>
                                <?php foreach ($options as $opt) { ?>
                                <option value="<?php echo htmlspecialchars($opt['option_value']) ?>"
                                    <?php echo ($savedValue == $opt['option_value']) ? "selected" : "" ?>>
                                    <?php echo htmlspecialchars($opt['option_label']) ?>
                                </option>
                                <?php } ?>
                            </select>
                            <?php 
                            }
                            else if ($item['input_type'] === "text") { ?>
                                <input type="text" class="ms-1 form-control section-input" name="item_<?= $item['item_id'] ?>"
                                    value="<?= htmlspecialchars($savedValue ?? '') ?>"
                                    <?= $item['required'] ? 'required' : '' ?>>
                                <?php } 
                            elseif ($item['input_type'] === "number") { ?>
                            <input type="number" step="0.1" class="ms-1 form-control section-input"
                                name="item_<?= $item['item_id'] ?>" value="<?= htmlspecialchars($savedValue ?? '') ?>"
                                <?= $item['required'] ? 'required' : '' ?> />
                                <?php if ($item['unit_label']) { ?>
                                <span class="input-group-text"><?= htmlspecialchars($item['unit_label']) ?></span>
                                <?php } ?>

                            <?php 
                            } elseif ($item['input_type'] === "date") { ?>
                            <input type="date" class="ms-1 form-control section-input" name="item_<?= $item['item_id'] ?>"
                                value="<?= htmlspecialchars($savedValue ?? '') ?>"
                                <?= $item['required'] ? 'required' : '' ?> />

                            <?php 
                            } elseif ($item['input_type'] === "textarea") { ?>
                            <textarea class="ms-1 form-control section-input" name="item_<?= $item['item_id'] ?>"
                                <?= $item['required'] ? 'required' : '' ?>><?= htmlspecialchars($savedValue ?? '') ?>
                                </textarea>
                            <?php 
                            } else{
                                echo "Not Valid";
                            } ?>


                        </div>
                        <?php } 
                        }else{ ?>
                            <div class="text-center">No Active Items Available</div>
                        <?php }
                        ?>
                    </div>
                </div>
                <?php } 
                }
                else{ ?>
                    <div class="text-center">No Active Items Available</div>
                <?php } ?>

                <button type="submit"
                    class="done-inspection btn btn-gold shadow rounded rounded-5 align-center align-middle position-fixed bottom-0 end-0 mb-2 me-2 pt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-check-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                        <path
                            d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05" />
                    </svg>
                    Done <i class="d-none d-lg-inline"> with Inspection?</i>
                </button>

            </form>




        </div>
    </div>
</div>

<?php } ?>