let signaturePad;
let userId, role;

function resizeCanvas(canvas, signaturePad) {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
    signaturePad.clear();
}

$(document).ready(function () {
    const offcanvasEl = document.getElementById("signatureOffcanvas");
    const bsOffcanvas = new bootstrap.Offcanvas(offcanvasEl);

    // When button is clicked → open offcanvas
    $(document).on("click", ".add-signature", function () {
        userId = $(this).data("user");
        role = $(this).data("role");

        bsOffcanvas.show();

        // Init signature pad after showing
        setTimeout(() => {
            let canvas = document.getElementById("signatureCanvas");
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: "rgb(255,255,255)"
            });
            resizeCanvas(canvas, signaturePad);
            window.addEventListener("resize", () => resizeCanvas(canvas, signaturePad));
        }, 300); // wait for offcanvas animation
    });

    // Clear signature
    $("#clearSignature").on("click", function () {
        signaturePad.clear();
    });
    let croppedSignature = null; // hold cropped data

    // Crop and show preview before saving
    $("#saveSignature").on("click", function () {
        if (signaturePad.isEmpty()) {
            alert("Please draw your signature first.");
            return;
        }

        let canvas = signaturePad.canvas;
        let ctx = canvas.getContext("2d");
        let imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        let data = imageData.data;

        let minX = canvas.width,
            maxX = 0;
        let minY = canvas.height,
            maxY = 0;

        // Detect non-transparent bounds
        for (let y = 0; y < canvas.height; y++) {
            for (let x = 0; x < canvas.width; x++) {
                let idx = (y * canvas.width + x) * 4;
                if (data[idx + 3] > 0) { // alpha > 0
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                    if (y < minY) minY = y;
                    if (y > maxY) maxY = y;
                }
            }
        }

        if (minX === canvas.width) {
            alert("No signature detected!");
            return;
        }

        // Crop to bounding box only
        let cropWidth = maxX - minX + 1;
        let cropHeight = maxY - minY + 1;

        let croppedCanvas = document.createElement("canvas");
        croppedCanvas.width = cropWidth;
        croppedCanvas.height = cropHeight;

        let croppedCtx = croppedCanvas.getContext("2d");
        croppedCtx.putImageData(ctx.getImageData(minX, minY, cropWidth, cropHeight), 0, 0);

        // Use cropped PNG for preview
        let croppedDataUrl = croppedCanvas.toDataURL("image/png");
        $("#signaturePreviewImg").attr("src", croppedDataUrl);
        $("#signaturePreviewModal").modal("show");

        // Save
        $("#confirmSaveSignature").off("click").on("click", function () {
            $.ajax({
                url: "../includes/save_signature.php",
                method: "POST",
                data: {
                    user_id: userId,
                    role: role,
                    image: croppedDataUrl
                },
                success: function () {
                    alert("Signature saved successfully!");
                    bsOffcanvas.hide();
                    $("#signaturePreviewModal").modal("hide");
                },
                error: function () {
                    alert("Error saving signature.");
                }
            });
        });
    });


    $(document).on("click","#startInspectionBtn", function () {
        let scheduleId = $(this).data("schedule-id");

        $.post("../includes/save_inspection.php", {
            schedule_id: scheduleId
        }, function (res) {
            if (res.success) {

                // redirect with GET params
                window.location.href = "?page=strt_ins&sched_id=" + scheduleId + "&insp_id=" + res.inspection_id;
            } else {
                alert(res.message);
            }
        }, "json");
    });



});




$(document).on('click', '.ack-btn', function (e) {
    e.preventDefault();

    const btn = $(this);
    const schedId = btn.data('sched-id');
    const role = btn.data('role');

    if (!confirm(`Confirm to acknowledge this schedule as ${role}?`)) return;

    btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Saving...');

    $.ajax({
        url: "../includes/acknowledge.php",
        method: "GET",
        data: {
            ack_sched_id: schedId
        },
        dataType: "json",
        success: function (res) {
            if (res.success) {
                btn.html('<i class="bi bi-check-circle"></i> Acknowledged').addClass('btn-success');
                setTimeout(() => btn.fadeOut(500), 1000);
            } else {
                alert(res.message || "Failed to acknowledge.");
                btn.prop('disabled', false).html('<i class="bi bi-check2-circle"></i> Try Again');
            }
        },
        error: function () {
            alert("Server error occurred.");
            btn.prop('disabled', false).html('<i class="bi bi-check2-circle"></i> Acknowledge');
        }
    });

});