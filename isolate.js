
function generateScheduleButtons(item, roleLabel, status) {
    let buttons = '';

    // ---------- Client: Set Schedule ----------
    if (roleLabel === "Client" && status !== "Cancelled") {
        const hasClientAck = String(item.HasClientAck ?? "").toUpperCase();
        if (hasClientAck === "N") {
            buttons += createButton({
                classList: ['ack', 'page-button', 'resched-btn', 'flex-fill', 'd-flex', 'align-items-center', 'justify-content-start', 'gap-2'],
                icon: safeGetIcon('repeat'),
                otherAttr: {
                    'data-sched-id': item.schedule_id,
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#reschedCanvas'
                },
                label: 'Set Schedule'
            });
        }
    }

    // ---------- Admin_Assistant: Cancel ----------
    if (roleLabel === "Admin_Assistant" && status !== "Cancelled" && status !== "Completed") {
        buttons += createButton({
            classList: ['page-button',  'cancel-schedule-btn'],
            icon: safeGetIcon('ban'),
            otherAttr: {
                'data-sched-id': item.schedule_id
            },
            label: 'Cancel Schedule'
        });
    }

    // ---------- Admin_Assistant: Approve Reschedule ----------
    if (roleLabel === "Admin_Assistant" && status === "Rescheduled") {
        const remarksText = `Reschedule to ${item.preferredSchedule || 'N/A'} due to: ${item.rescheduleReason || 'unspecified reason.'}`;
        buttons += createButton({
            classList: [  'page-button'],
            icon: '',
            otherAttr: {
                'href': `?page=ins_sched&action=reschedule&sched_id=${item.schedule_id}&remarks=${encodeURIComponent(remarksText)}`,
                'data-sched-id': item.schedule_id
            },
            label: `<small class="fine-print small text-small">${item.preferredSchedule}</small><hr class="my-0">Approve Rescheduling?`
        });
    }

    // ---------- Cancelled ----------
    if (status === "Cancelled") {
        buttons = createButton({
            classList: ['page-button','btn-danger','btn-disabled'],
            icon: safeGetIcon('dash-circle'),
            label: 'Cancelled'
        });

        buttons += createButton({
            classList: ['page-button', 'btn-danger', 'archive-schedule-btn'],
            icon: safeGetIcon('trash'),
            otherAttr: {
                'data-sched-id': item.schedule_id
            },
            label: 'Archive'
        });
    }

    // ---------- Acknowledgment Flow ----------
    if (status !== "Cancelled" && status !== "Rescheduled" && status !== "Completed") {
        const ackMap = {
            "Client": "HasClientAck",
            "Recommending Approver": "hasRecommendingApproval",
            "Fire Marshall": "hasRecommendingApproval",
            "Final Approver": "hasFinalApproval",
            "Inspector": "hasInspectorAck"
        };

        const ackField = ackMap[roleLabel];
        const val = String(item[ackField] ?? "").toLowerCase().trim();
        const clientAck = String(item["HasClientAck"] ?? "").toLowerCase().trim();
        const recAck = String(item["hasRecommendingApproval"] ?? "").toLowerCase().trim();
        const finalAck = String(item["hasFinalApproval"] ?? "").toLowerCase().trim();

        const isAcked = v => v === "y" || v === "1" || v === 1;

        let canAcknowledge = false;
        let reason = "";

        switch (roleLabel) {
            case "Client":
                canAcknowledge = !isAcked(clientAck);
                break;
            case "Recommending Approver":
            case "Fire Marshall":
                if (!isAcked(clientAck)) reason = "Waiting for Client";
                else canAcknowledge = !isAcked(recAck);
                break;
            case "Final Approver":
                if (!isAcked(recAck)) reason = "Waiting for Recommendation";
                else canAcknowledge = !isAcked(finalAck);
                break;
            case "Inspector":
                if (!isAcked(finalAck)) reason = "Waiting for Final Approval";
                else canAcknowledge = !isAcked(item["hasInspectorAck"]);
                break;
        }

        if (canAcknowledge) {
            buttons += createButton({
                classList: ['ack-btn', 'page-button',  'btn-outline-danger', 'flex-fill', 'd-flex', 'align-items-center', 'justify-content-start', 'gap-2'],
                icon: safeGetIcon('circle-check'),
                otherAttr: {
                    'data-sched-id': item.schedule_id,
                    'data-role': roleLabel,
                    'data-btnid': item.schedule_id
                },
                label: `Acknowledge as ${roleLabel}`
            });
        } else if (reason) {
            buttons += createButton({
                classList: ['page-button',  'btn-outline-secondary', 'flex-fill', 'd-flex', 'align-items-center', 'justify-content-start', 'gap-2'],
                icon: safeGetIcon('hourglass'),
                label: reason
            });
        }
    }

    // ---------- Inspector Controls ----------
    if (roleLabel === "Inspector" && item.hasInspectorAck == 1 && item.hasRecommendingApproval == 1 &&
        item.hasFinalApproval == 1 && item.HasClientAck == "Y" && status !== "Cancelled") {

        const address = item.gi_location || "";
        buttons += createButton({
            classList: [ 'btn-navy-dark', 'page-button', 'flex-fill', 'd-flex', 'align-items-center', 'justify-content-start', 'gap-2'],
            icon: safeGetIcon("geo"),
            otherAttr: {
                'href': `../pages/map.php?address=${encodeURIComponent(address)}`
            },
            label: 'View Location'
        });

        if (status === "Scheduled") {
            let disabledAttr = "";
            const schedDate = new Date(item.scheduled_date);
            const today = new Date();
            schedDate.setHours(0, 0, 0, 0);
            today.setHours(0, 0, 0, 0);
            const isFuture = schedDate > today;
            disabledAttr = ""; // override if needed

            buttons += createButton({
                classList: [ 'btn-primary', 'page-button', 'startInspectionBtn', 'flex-fill', 'd-flex', 'align-items-center', 'justify-content-start', 'gap-2'],
                icon: safeGetIcon('checklist'),
                otherAttr: {
                    'data-sched-id': item.schedule_id,
                    'disabled': disabledAttr
                },
                label: 'Start Inspection'
            });
        }

        if (status === "Completed" && item.Inspection_status === "Completed" && item.has_Defects === 1) {
            buttons += createButton({
                classList: [  'page-button', 'startInspectionBtn', 'flex-fill', 'd-flex', 'align-items-center', 'justify-content-start', 'gap-2'],
                icon: safeGetIcon('checklist'),
                otherAttr: {
                    'data-sched-id': item.schedule_id
                },
                label: 'Re-Inspect for defects'
            });
        }

        if ((status === "Completed" || status === "Pending" || status === "Rescheduled") && item.Inspection_status === "Completed") {
            let classDefect = item.has_defects === 0 ? "success" : "outline-danger bg-warning bg-opacity-75 shadow shadow-sm";
            let iconBtn = item.has_defects === 0 ? safeGetIcon('patchcheck') : safeGetIcon('patchcaution');

            buttons += createButton({
                classList: [ `btn-${classDefect}`, 'page-button', 'checkInspectionReport', 'flex-fill', 'd-flex', 'align-items-center', 'justify-content-start', 'gap-2'],
                icon: iconBtn,
                otherAttr: {
                    'data-sched-id': item.schedule_id
                },
                label: 'Completed / Inspection Report'
            });
        }
    }

    // ---------- Print / View FSED-9F ----------
    if (status !== "Cancelled") {
        buttons += createButton({
            classList: ['page-button',  'btn-gold', 'flex-fill', 'd-flex', 'align-items-center', 'justify-content-start', 'gap-2'],
            icon: safeGetIcon('pdf'),
            otherAttr: {
                'href': `../pages/print_inspection_order.php?id=${item.schedule_id}`,
                'target': '_blank'
            },
            label: 'View FSED-9F'
        });
    }

    // ---------- Expired Schedules ----------
    const scheduleDate = new Date(item.scheduled_date);
    const now = new Date();
    if (scheduleDate < now && status !== "Completed" && status !== "Rescheduled") {
        buttons = createButton({
            classList: [  'page-button'],
            icon: safeGetIcon('dash-circle'),
            label: 'Expired'
        });

        buttons += createButton({
            classList: ['page-button',   'archive-schedule-btn'],
            icon: safeGetIcon('trash'),
            otherAttr: {
                'data-sched-id': item.schedule_id
            },
            label: 'Archive'
        });

        if (roleLabel === "Client" || roleLabel === "Admin_Assistant") {
            buttons += createButton({
                classList: ['ack', 'page-button',   'resched-btn', 'flex-fill', 'd-flex', 'align-items-center', 'justify-content-start', 'gap-2'],
                icon: safeGetIcon('repeat'),
                otherAttr: {
                    'data-sched-id': item.schedule_id,
                    'data-bs-toggle': 'offcanvas',
                    'data-bs-target': '#reschedCanvas'
                },
                label: 'Set Schedule'
            });
        }
    }

    // ---------- Archived ----------
    if (status === 'Archived') {
        buttons = createButton({
            classList: ['page-button',  'btn-secondary', 'btn-disabled'],
            icon: safeGetIcon('trash'),
            label: 'Archived'
        });
    }

    return buttons;
}
