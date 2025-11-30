<?php
require_once "../includes/_init.php";
?>

<!-- Password Confirmation Modal -->
<div class="modal fade" id="passwordConfirmModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordModalLabel">Confirm <action></action></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <action></action> this item? This action cannot be undone.</p>
                <div class="mb-3">
                    <label for="passwordInput" class="form-label">Enter your password to confirm:</label>
                    <input type="password" class="form-control" id="passwordInput" placeholder="Enter your password">
                    <input type="hidden" id="disableOnlyind" value="0" />
                </div>
                <div id="passwordError" class="alert alert-danger d-none" role="alert">
                    Please enter your password.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn"><action></action> Item</button>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid" style="margin-top:75px">
    <div class="row">
        <div class="col-12">
            <h4 class="text-navy-dark fw-bold">MANAGE CHECKLISTS</h4>

            <ul class="nav nav-underline" id="pills-tab" role="tablist">
                <?php
                $collapse = 0;
                $checklists = select("checklists", ["checklist_status" => 1], ["fsed_code" => "ASC"]);
                $active = "";
                foreach ($checklists as $cl) {
                    if (isset($_GET['checklist_id'])) {
                        $active = ($_GET['checklist_id'] == $cl['checklist_id'] ? "active" : "");
                    }
                    ?>
                    <li class="nav-item">
                        <a class="<?= $active ?> nav-link text-navy"
                           href="?page=view_checklists&checklist_id=<?= $cl['checklist_id']?>"
                           class="btn btn-link"><?= $cl['fsed_code'] ?></a>
                    </li>
                <?php
                } ?>
            </ul>

            <?php if (isset($_GET['page']) && isset($_GET['checklist_id'])) {
                $checklist_id = intval(htmlentities($_GET['checklist_id']));
                $chklist = select("checklists", ["checklist_status" => 1, "checklist_id" => $checklist_id ], ["fsed_code" => "ASC"])[0];
                ?>

                <!-- CHECKLIST LEVEL -->
                <div class="container-fluid card border-0 shadow mb-1">
                    <div class="card-header" id="heading-<?php echo $chklist['checklist_id']; ?>">
                        <h5 class="fw-bold">
                            <?php echo htmlspecialchars($chklist['fsed_code'] . " - " . $chklist['title']); ?>
                        </h5>
                    </div>
                    <div class="card-body">

                        <!-- ADD SECTION FORM -->
                        <div class="card mb-3" id="forSection">
                            <div class="card-header bg-navy text-gold">
                                <small class="small">+ Section</small>
                            </div>
                            <div class="card-body">
                                <form class="addSectionForm" data-checklist="<?php echo $chklist['checklist_id']; ?>">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-10">
                                            <input type="text" name="section_name" class="form-control form-control-sm"
                                                   placeholder="New Section" required>
                                        </div>
                                        <div class="col-2 d-flex">
                                            <input type="hidden" name="checklist_id" value="<?php echo $chklist['checklist_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-navy">+ <span class="d-none d-lg-inline">Add Section</span></button>
                                            <button class="btn btn-sm btn-gold ms-2" type="reset">Reset</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- SECTION LEVEL -->
                        <?php
                        $sections = select_data("checklist_sections", ["checklist_id" => $chklist['checklist_id'] ], "checklist_section_id DESC");
                        if (!empty($sections)) {
                            foreach ($sections as $s) { //section list
                                ?>
                                <div class="section-block mb-3">
                                    <h6 class="fw-bold text-uppercase text-navy">
                                        <a href="#section<?php echo $s['checklist_section_id'].$s['checklist_id']; ?>"
                                           data-bs-toggle="collapse" role="button" aria-expanded="false"
                                           aria-controls="section<?php echo $s['checklist_section_id'].$s['checklist_id']; ?>"
                                           class="btn btn-outline-navy me-2 py-1 px-2"><?php echo getIcon("checklist");?></a>
                                        <?php echo htmlspecialchars($s['section']); ?>
                                    </h6>

                                    <!-- ADD CHECKLIST ITEM FORM -->
                                    <div class="card mb-3 collapse show"
                                         id=section<?php echo $s['checklist_section_id'] . $s['checklist_id']; ?>>
                                        <div class="card-header bg-navy text-gold">
                                            <small class="small">+ New Checklist Item</small>
                                        </div>
                                        <div class="card-body">
                                            <form class="addItemForm" data-checklist="<?php echo $chklist['checklist_id']; ?>" data-section="<?php echo $s['checklist_section_id']; ?>">
                                                <div class="row g-2 align-items-center">
                                                    <div class="col-3">
                                                        <input type="hidden" name="section" value="<?php echo $s['checklist_section_id']; ?>">
                                                        <input type="text" name="item_text" class="form-control form-control-sm" placeholder="New item text" required>
                                                    </div>
                                                    <div class="col-3">
                                                        <select name="input_type" class="form-select form-select-sm select-input-type" data-checklist="<?= $chklist['checklist_id']?>" data-section="<?= $s['checklist_section_id']?>">
                                                            <option value="checkbox">checkbox</option>
                                                            <option value="text">text</option>
                                                            <option value="number">number</option>
                                                            <option value="date">date</option>
                                                            <option value="textarea">textarea</option>
                                                            <option value="select">select</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-1">
                                                        <input type="text" name="unit_label" class="form-control form-control-sm" placeholder="Unit (optional)">
                                                    </div>
                                                    <div class="col-3">
                                                        <select name="checklist_criteria"
                                                                id="criteria-add-<?php echo $chklist['checklist_id'] . '-' . $s['checklist_section_id']; ?>"
                                                                data-section="<?php echo $s['checklist_section_id'];?>"
                                                                data-checklist="<?php echo $chklist['checklist_id']; ?>"
                                                                class="form-select form-select-sm criteria-select"></select>
                                                    </div>
                                                    <div class="col-1 text-center">
                                                        <input id="crit-check-<?php echo $chklist['checklist_id']; ?>-<?php echo $s['checklist_section_id']; ?>" class="form-check-input border border-2 border-dark" type="checkbox" name="required" value="1">
                                                        <label for="crit-check-<?php echo $chklist['checklist_id']; ?>-<?php echo $s['checklist_section_id']; ?>">Required?</label>
                                                    </div>

                                                    <!-- THRESHOLD FIELDS -->
                                                    <div class="threshold-fields mt-2 col-12"
                                                         id="threshold-add-<?php echo $chklist['checklist_id']. "-" . $s['checklist_section_id'];?>"
                                                         style="display:none;">
                                                        <div class="row range-fields d-none" id="rangefield-add-<?php echo $chklist['checklist_id']. "-" . $s['checklist_section_id'];?>">
                                                            <div class="col"><input type="number" step="0.001" name="threshold_range_min" class="form-control form-control-sm" placeholder="Min"></div>
                                                            <div class="col"><input type="number" step="0.001" name="threshold_range_max" class="form-control form-control-sm" placeholder="Max"></div>
                                                        </div>
                                                        <div class="row minval-field d-none" id="minvalfield-add-<?php echo $chklist['checklist_id']. "-" . $s['checklist_section_id']; ?>">
                                                            <div class="col"><input type="number" step="0.001" name="threshold_min_val" class="form-control form-control-sm" placeholder="Min"></div>
                                                        </div>
                                                        <div class="row maxval-field d-none" id="maxvalfield-add-<?php echo $chklist['checklist_id']. "-" . $s['checklist_section_id']; ?>">
                                                            <div class="col"><input type="number" step="0.001" name="threshold_max_val" class="form-control form-control-sm" placeholder="Max"></div>
                                                        </div>
                                                        <div class="row yesno-field d-none" id="yesnofield-add-<?php echo $chklist['checklist_id']. "-" . $s['checklist_section_id']; ?>">
                                                            <div class="col">
                                                                <select name="threshold_yes_no" class="form-select form-select-sm">
                                                                    <option value="1">Yes</option>
                                                                    <option value="0">No</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row days-field d-none" id="daysfield-add-<?php echo $chklist['checklist_id']. "-" . $s['checklist_section_id']; ?>">
                                                            <div class="col"><input type="number" step="0.001" name="threshold_elapse_day" class="form-control form-control-sm" placeholder="No. of days"></div>
                                                        </div>
                                                        <div class="row textvalue-field d-none" id="textvalue-add-<?php echo $chklist['checklist_id']. "-" . $s['checklist_section_id']; ?>">
                                                            <div class="col"><input type="text" name="threshold_text_value" class="form-control form-control-sm" placeholder="Text Value"></div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-2 col-1 text-end">
                                                        <input type="hidden" name="checklist_id" value="<?php echo $chklist['checklist_id']; ?>">
                                                        <button class="btn btn-sm btn-primary">+ <span class="d-none d-lg-inline">Add Item</span></button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="card-footer">
                                            <?php
                                            $items = select("checklist_items",
                                                [
                                                    "checklist_id" => $chklist['checklist_id'],
                                                    "section" => $s['checklist_section_id']
                                                ],
                                                ["section" => "ASC", "item_no" => "ASC"]);

                                            if ($items) { ?>
                                                <ul class="mb-3 shadow list-group section-checklist-items" data-section-id=<?= $s['checklist_section_id'] ?>>
                                                    <li class="list-group-item bg-navy text-gold d-none d-md-inline d-lg-inline">
                                                        <div class="row g-2 align-items-center">
                                                            <div class="col-3 text-center">Checklist Item</div>
                                                            <div class="col-3 text-center">Input Type</div>
                                                            <div class="col-1 text-center">Unit</div>
                                                            <div class="col-2 text-center">Criteria</div>
                                                            <div class="col-1 text-center">Req?</div>
                                                            <div class="col-2 text-center"></div>
                                                        </div>
                                                    </li>
                                                    <?php foreach ($items as $item):
                                                        // Normalize criteria
                                                        $criteria = trim(strtolower($item['checklist_criteria'] ?? ''));

                                                        // Prepare criteria text for display
                                                        switch ($criteria) {
                                                            case 'range':
                                                                $criteria_text = "Range: " . $item['threshold_range_min'] . $item['unit_label'] . " - " . $item['threshold_range_max'] . $item['unit_label'];
                                                                break;
                                                            case 'min_val':
                                                                $criteria_text = "Minimum: " . $item['threshold_min_val'] . $item['unit_label'];
                                                                break;
                                                            case 'max_val':
                                                                $criteria_text = "Maximum: " . $item['threshold_max_val'] . $item['unit_label'];
                                                                break;
                                                            case 'yes_no':
                                                                $criteria_text = $item['threshold_yes_no'] ? "Yes" : "No";
                                                                break;
                                                            case 'days':
                                                                $criteria_text = $item['threshold_elapse_day'] . " day(s)";
                                                                break;
                                                            case 'textvalue':
                                                                $criteria_text = $item['threshold_text_value'];
                                                                break;
                                                            default:
                                                                $criteria_text = '';
                                                        }
                                                         
                                                         $disabledClass = ($item['chk_item_status'] == 0 ? "bg-secondary bg-opacity-25" : '');
                                                         $isDisabledInd =($item['chk_item_status'] == 0 ? "disabled" : '');
                                                        ?>
                                                        <li class="list-group-item shadow mb-2 <?= $disabledClass ?>" id="<?= $item['item_id'] ?>" >
                                                            <!--  /************************************************/ -->
                                                            <!--                                                            -->
                                                            <!--  /************************************************/ -->
                                                            
            
                                                            
                                                            <form class="editItemForm" data-item="<?= $item['item_id'] ?>">
                                                                <div class="row g-2">
                                                                    <div class="col-lg-2">
                                                                        <input type="hidden" class="form-control" name="checklist_id" value="<?= $item['checklist_id'] ?>">
                                                                        <input type="hidden" class="form-control" name="section" value="<?= $s['checklist_section_id'] ?>">
                                                                        <input type="text" name="item_text" class=" <?= $disabledClass ?> form-control form-control-sm fw-bold" value="<?= htmlspecialchars($item['item_text']) ?>">
                                                                    </div>
                                                                    <div class="col-lg-2">
                                                                        <select name="input_type" data-item-id="<?= $item['item_id']?>" class=" <?= $disabledClass ?> form-select form-select-sm edit-select-input-type">
                                                                            <?php
                                                                            $types = ['checkbox','text','number','date','select'];
                                                                            foreach($types as $type) {
                                                                                $sel = $item['input_type'] === $type ? 'selected' : '';
                                                                                echo "<option value='$type' $sel>$type</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-lg-1">
                                                                        <input type="text" name="unit_label" class="<?= $disabledClass ?> form-control form-control-sm" value="<?= htmlspecialchars($item['unit_label']) ?>">
                                                                    </div>
                                                                    <div class="col-lg-2">
                                                                        <select name="checklist_criteria" id="editCriteriaSelect<?= $item['item_id']?>" class="<?= $disabledClass ?> form-select form-select-sm edit-criteria-select" data-item-id="<?= $item['item_id']?>" data-saved="<?= $item['checklist_criteria']?>"></select>
                                                                    </div>
                                                                    <div class="col-lg-1 d-flex">
                                                                        <input type="checkbox" class="btn-check" id="isRequired<?= $item['item_id'] ?>" name="required" value="1" <?= $item['required'] ? 'checked' : '' ?>>
                                                                        <label class="rounded-0 btn btn-sm btn-outline-navy align-content-center align-middle" for="isRequired<?= $item['item_id'] ?>" class="d-lg-none ">Required?</label>
                                                                        <span class="input-group-text rounded-0 border border-1 bg-navy text-light border-navy" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip" data-bs-title="if this is selected, this item should have a value during inspection or else it, inspection cannot be saved."> ! </span>
                                                                    </div>
                                                                    <div class="col-lg-3 position-relative d-flex">
                                                                        <button type="submit" class="btn btn-sm btn-navy ms-auto">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16">
                                                                                <path d="M11 2H9v3h2z"/>
                                                                                <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z"/>
                                                                            </svg>
                                                                            <b class="">Save</b>
                                                                        </button>
                                                                        <div class="dropdown ms-1">
                                                                                  <a class="btn btn-outline-navy text-dark dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"></a>
                                                                                    <ul class="dropdown-menu">
                                                                                        
                                                                                        <?php if($item['chk_item_status'] == 1) { ?>
                                                                                        <li><a class="dropdown-item btn-confirm-delete disable-only" hhref="?page=view_checklists&checklist_id=<?= $item['checklist_id'] ?>" data-delete-item="<?= $item['item_id'] ?>">Disable</a></li>
                                                                                        <?php } else { ?>
                                                                                        <li><a class="dropdown-item btn-confirm-delete enable-only" hhref="?page=view_checklists&checklist_id=<?= $item['checklist_id'] ?>" data-delete-item="<?= $item['item_id'] ?>" >Enable </a></li>
                                                                                            <?php }?>
                                                                                        <li class="text-bg-danger"><a class="dropdown-item btn-confirm-delete" href="?page=view_checklists&checklist_id=<?= $item['checklist_id'] ?>" data-delete-item="<?= $item['item_id'] ?>">Delete</a></li>
                                                                                    </ul>
                                                                        </div>
                                                                    </div>

                                                                    <!-- THRESHOLD FIELDS -->
                                                                    <div class="col-12 mt-2 threshold-fields" id="threshold-edit-<?= $item['item_id'] ?>">
                                                                        <div class="threshold-field border range-fields border-secondary rounded-2 input-group <?= $criteria !== 'range' ? 'd-none' : '' ?>">
                                                                            <div class="form-floating">
                                                                                <input type="number" id="threshold_range_min_<?= $item['item_id'] ?>" name="threshold_range_min" class="<?= $disabledClass ?> form-control form-control-sm mb-2" step="0.01" placeholder="Min" value="<?= isset($item['threshold_range_min']) ? (float)$item['threshold_range_min'] : 0 ?>">
                                                                                <label for="threshold_range_min_<?= $item['item_id'] ?>">Min Value</label>
                                                                            </div>
                                                                            <div class="form-floating">
                                                                                <input type="number" id="threshold_range_max_<?= $item['item_id'] ?>" name="threshold_range_max" class="<?= $disabledClass ?> form-control form-control-sm mb-2" step="0.01" placeholder="Max" value="<?= isset($item['threshold_range_max']) ? (float)$item['threshold_range_max'] : 0 ?>">
                                                                                <label for="threshold_range_max_<?= $item['item_id'] ?>">Max Value</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="threshold-field minval-field form-floating <?= $criteria !== 'min_val' ? 'd-none' : '' ?>">
                                                                            <input type="number" id="threshold_min_val_<?= $item['item_id'] ?>" name="threshold_min_val" class="<?= $disabledClass ?> minval-field form-control form-control-sm mb-2" step="0.01" placeholder="Min" value="<?= isset($item['threshold_min_val']) ? (float)$item['threshold_min_val'] : 0 ?>">
                                                                            <label for="threshold_min_val_<?= $item['item_id'] ?>">Min Value</label>
                                                                        </div>
                                                                        <div class="threshold-field maxval-field form-floating <?= $criteria !== 'max_val' ? 'd-none' : '' ?>">
                                                                            <input type="number" id="threshold_max_val_<?= $item['item_id'] ?>" name="threshold_max_val" class="<?= $disabledClass ?> form-control form-control-sm mb-2" step="0.01" placeholder="Max" value="<?= isset($item['threshold_max_val']) ? (float)$item['threshold_max_val'] : 0 ?>">
                                                                            <label for="threshold_max_val_<?= $item['item_id'] ?>">Max Value</label>
                                                                        </div>
                                                                        <select name="threshold_yes_no" class="<?= $disabledClass ?> threshold-field yesno-field form-select form-select-sm mb-2 <?= $criteria !== 'yes_no' ? 'd-none' : '' ?>">
                                                                            <option value="1" <?= $item['threshold_yes_no'] == 1 ? 'selected' : '' ?>>Yes</option>
                                                                            <option value="0" <?= $item['threshold_yes_no'] == 0 ? 'selected' : '' ?>>No</option>
                                                                        </select>
                                                                        <div class="<?= $disabledClass ?> threshold-field days-field form-floating <?= $criteria !== 'days' ? 'd-none' : '' ?>">
                                                                            <input type="number" id="threshold_elapse_day_<?= $item['item_id'] ?>" name="threshold_elapse_day" class="form-control form-control-sm mb-2 " placeholder="No. of days" value="<?= isset($item['threshold_elapse_day']) ? (float)$item['threshold_elapse_day'] : 0 ?>">
                                                                            <label for="threshold_elapse_day_<?= $item['item_id'] ?>">Elapse Day</label>
                                                                        </div>
                                                                        <div class="<?= $disabledClass ?> threshold-field textvalue-field form-floating <?= $criteria !== 'textvalue' ? 'd-none' : '' ?>">
                                                                            <input type="text" id="threshold_text_value_<?= $item['item_id'] ?>" name="threshold_text_value" class="form-control form-control-sm mb-2" placeholder="Value" value="<?= isset($item['threshold_text_value']) ? $item['threshold_text_value'] : "" ?>">
                                                                            <label for="threshold_text_value_<?= $item['item_id'] ?>">Text Value</label>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </form>

                                                            <?php $options = [];
                                                            $options = select("checklist_item_select_options", [
                                                                "item_id" => $item['item_id']
                                                            ], ["option_value" => "ASC"]);
                                                            ?>
                                                            <div class="card m-0 border-1 shadow-sm d-none add-select-form" id="<?= $item['item_id'] ?>" data-sel-option-id="<?= $item['item_id'] ?>">
                                                                <div class="card-header border-0 my-0"><span class="small">Add Select Option</span></div>
                                                                <div class="card-body border-0">
                                                                    <form class="addOptionForm mt-2" data-item="<?php echo $item['item_id']; ?>">
                                                                        <div class="input-group input-group-sm">
                                                                            <input type="text" name="option_value" class="form-control" placeholder="New option" required>
                                                                            <input type="text" name="option_label" class="form-control" placeholder="Option Label" required>
                                                                            <input type="hidden" name="item_id" class="form-control" value="<?php echo $item['item_id']; ?>">
                                                                            <button class="btn btn-primary">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-floppy" viewBox="0 0 16 16">
                                                                                    <path d="M11 2H9v3h2z" />
                                                                                    <path d="M1.5 0h11.586a1.5 1.5 0 0 1 1.06.44l1.415 1.414A1.5 1.5 0 0 1 16 2.914V14.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0M1 1.5v13a.5.5 0 0 0 .5.5H2v-4.5A1.5 1.5 0 0 1 3.5 9h9a1.5 1.5 0 0 1 1.5 1.5V15h.5a.5.5 0 0 0 .5-.5V2.914a.5.5 0 0 0-.146-.353l-1.415-1.415A.5.5 0 0 0 13.086 1H13v4.5A1.5 1.5 0 0 1 11.5 7h-7A1.5 1.5 0 0 1 3 5.5V1H1.5a.5.5 0 0 0-.5.5m3 4a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 .5-.5V1H4zM3 15h10v-4.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5z" />
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                                <div class="card-footer bg-secondary bg-opacity-50 border-0 my-0 px-0">
                                                                    <ul class="d-inline my-0">
                                                                        <li class="decoration-none d-inline my-0 p-0">Options: </li>
                                                                        <?php foreach ($options as $opt) { ?>
                                                                            <li class="decoration-none d-inline my-0 p-0">
                                                                                <span class="badge rounded-pill bg-navy text-light ps-2 pt-2">
                                                                                    <?php echo htmlspecialchars($opt['option_value']); ?>
                                                                                    <button class="btn btn-sm decoration-none delete-option m-0 p-0 text-small" data-id="<?php echo $opt['option_id']; ?>">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x mb-1 text-light" viewBox="0 0 16 16">
                                                                                            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708" /></svg>
                                                                                    </button>
                                                                                </span>
                                                                            </li>
                                                                        <?php } ?>
                                                                    </ul>
                                                                </div>

                                                            </div>

                                                            <!-- Display existing criteria -->
                                                            <div class="<?= $disabledClass ?>  card criteria-details p-2 mt-2 bg-light">
                                                                <strong>Criteria: <?= htmlspecialchars($criteria_text) ?> </strong>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>

                                                </ul>
                                            <?php } //end if($items) ?>
                                        </div>

                                    </div>
                                </div>
                            <?php }
                        } // END SECTIONS IF ?>
                </div>

            </div>
        <?php } //end if get page and checklistid ?>

        </div>
    </div>
</div>