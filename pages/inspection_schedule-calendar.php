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