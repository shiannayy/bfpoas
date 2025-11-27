<?php
class Checklist {
    public $checklist_id;
    public $fsed_code;
    public $title;
    public $description;
    public $version;
    public $status;
    public $items = []; // will hold ChecklistItem objects

    public function __construct($row) {
        $this->checklist_id = $row['checklist_id'];
        $this->fsed_code = $row['fsed_code'];
        $this->title = $row['title'];
        $this->description = $row['description'];
        $this->version = $row['version'];
        $this->status = $row['checklist_status'];
    }

    public function addItem(ChecklistItem $item) {
        $this->items[] = $item;
    }
}

class ChecklistItem {
    public $item_id;
    public $section;
    public $item_no;
    public $item_text;
    public $input_type;
    public $unit_label;
    public $required;

    public function __construct($row) {
        $this->item_id = $row['item_id'];
        $this->section = $row['section'];
        $this->item_no = $row['item_no'];
        $this->item_text = $row['item_text'];
        $this->input_type = $row['input_type'];
        $this->unit_label = $row['unit_label'];
        $this->required = $row['required'];
    }

    // Render as HTML form input
    public function renderField() {
        $required = $this->required ? "required" : "";
        $label = htmlspecialchars($this->item_text);

        switch ($this->input_type) {
            case 'checkbox':
                return "<label><input type='checkbox' class='form-control' name='item_{$this->item_id}' $required> $label</label>";
            case 'text':
                return "<label>$label <input type='text' class='form-control' name='item_{$this->item_id}' $required></label>";
            case 'number':
                return "<label>$label <input type='number' class='form-control' name='item_{$this->item_id}' $required> {$this->unit_label}</label>";
            case 'date':
                return "<label>$label <input type='date' class='form-control' name='item_{$this->item_id}' $required></label>";
            case 'select':
                // For now, hardcode example options
                return "<label>$label <select class='form-select' name='item_{$this->item_id}' $required>
                            <option value=''>Select...</option>
                            <option value='Yes'>Yes</option>
                            <option value='No'>No</option>
                        </select></label>";
            default:
                return "<label>$label <input type='text' class='form-control' name='item_{$this->item_id}' $required></label>";
        }
    }
}
