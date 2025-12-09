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

<div id="inspectionListContainer" class="list-group list-group-flush"></div>
<div class="pagination"></div>


<div class="container d-none">
       <div class="row">
    <?php 
    $ackCheck = [
        'Client' => [ ['ins.HasClientAck' => 'N'] ],
        'Inspector' => [ ['ins.HasInspectorAck' => 0] ],
        'Chief FSES' => [ ['ins.hasRecommendingApproval' => 0] ],
        'Fire Marshall' => [ ['ins.hasFinalApproval' => 0] ],
    ];
    
    foreach($ackCheck as $role => $conditions){ 
        $where = $conditions[0];
        
        $joins = [
                    [
                    'table' => 'general_info gi',
                    'on' => 'ins.gen_info_id = gi.gen_info_id',
                    'type' => 'LEFT'
                    ]
        ];
        $insSched = select_join(['inspection_schedule ins']
                            , 
                            ['ins.order_number',
                              'ins.scheduled_date',
                              'ins.schedule_time',
                              'gi.owner_name',
                              'gi.location_of_construction',
                              'ins.to_officer',
                              'ins.inspection_sched_status',
                              'ins.hasInspectorAck',
                              'ins.hasClientAck',
                              'ins.hasRecommendingApproval',
                              'ins.hasFinalApproval',
                            ],
                             $joins, 
                             $where, 
                            ['ins.created_at'=>'DESC', 'ins.scheduled_date'=>'ASC'],
                            1000);
    ?>
    <div class="col-3 pending-inspections-container list-group vh-100 overflow-y-auto" style="max-height: 80vh;">
        <a class="list-group-item bg-navy">
            <strong class="text-gold"><?= $role ?></strong>
        </a>
        <?php 
        if(empty($insSched)){ 
        ?>
            <div class="m-3 text-muted">
                No Scheduled inspections found.
            </div>
        <?php 
        } 
        else {
            
            foreach($insSched as $sched):  
                $borderClass = null;
                switch($role){
                    case 'Client':
                        $borderClass = ($sched['hasClientAck'] == 'Y') ? 'border-success' : 'border-danger';
                        break;
                    case 'Inspector':
                        $borderClass = ($sched['hasInspectorAck'] == 1) ? 'border-success' : 'border-danger';
                        break;
                    case 'Chief FSES':
                        $borderClass = ($sched['hasRecommendingApproval'] == 1) ? 'border-success' : 'border-danger';
                        break;
                    case 'Fire Marshall':
                        $borderClass = ($sched['hasFinalApproval'] == 1) ? 'border-success' : 'border-danger';
                        break;
                }
            ?>
            <div href="#" class="list-group-item">
                <div class="d-flex w-100 align-items-center justify-content-between">
                    <strong class="mb-1 text-uppercase"><?= $sched['owner_name'] === null ? "NONE INDICATED" : $sched['owner_name']  ?></strong>
                    <div class="dropdown">
                        <button class="btn btn-navy dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"> ... </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Action</a></li>
                            <li><a class="dropdown-item" href="#">Another action</a></li>
                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                        </ul>
                    </div>
                </div>
                <small><?= date('M d, Y', strtotime($sched['scheduled_date'])) . " at " . date('h:i A', strtotime($sched['schedule_time'])) ?></small>
                <div class="col-10 mb-1 small">
                    <?= $sched['to_officer'] ?>
                </div>
            </div>
        <?php 
            endforeach;
        } //end else
        ?>
    </div>
    <?php } ?>
</div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
   
</body>

</html>