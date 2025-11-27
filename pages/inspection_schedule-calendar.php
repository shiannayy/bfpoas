<!DOCTYPE html>
<?php
include_once "../includes/_init.php";
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Calendar of Inspections</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/color_pallette.css">

    <!--   Calendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet">

</head>

<body>



    <div class="container mt-5">
        <h4 class="mb-3">Inspection Schedules
            <a href="?page=ins_sched" class="btn btn-gold">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-table text-navy mb-1 me-1" viewBox="0 0 16 16">
                    <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm15 2h-4v3h4zm0 4h-4v3h4zm0 4h-4v3h3a1 1 0 0 0 1-1zm-5 3v-3H6v3zm-5 0v-3H1v2a1 1 0 0 0 1 1zm-4-4h4V8H1zm0-4h4V4H1zm5-3v3h4V4zm4 4H6v3h4z" />
                </svg>
                <span class="text-navy">Tabular View</span>
            </a>
        </h4>
        <div id="calendar"></div>
    </div>




    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>


    <script>
        $(document).ready(function() {
            var calendarEl = document.getElementById('calendar');

            $.ajax({
                url: "../includes/get_inspection_schedules.php",
                method: "GET",
                dataType: "json",
                success: function(data) {
                    let events = $.map(data, function(item) {
                        return {
                            id: item.schedule_id,
                            title: item.order_number + " - " + (item.to_officer || ""),
                            start: item.scheduled_date,
                            extendedProps: {
                                proceed: item.proceed_instructions,
                                purpose: item.purpose,
                                status: item.status
                            }
                        };
                    });

                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        height: "auto",
                        events: events,
                        eventClick: function(info) {
                            alert(
                                "Inspection Order: " + info.event.title +
                                "\nProceed: " + info.event.extendedProps.proceed +
                                "\nPurpose: " + info.event.extendedProps.purpose +
                                "\nStatus: " + info.event.extendedProps.status
                            );
                        }
                    });

                    calendar.render();
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching schedules:", error);
                }
            });
        });
    </script>
</body>

</html>