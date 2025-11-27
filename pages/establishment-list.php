<!DOCTYPE html>
<?php
include_once "../includes/_init.php";
//if(isset($_SESSION['user_id'])){
//    define("$GLOBALS['USER_LOGGED']",$_SESSION['user_id']);
//}
if(!isLoggedin()){
    header("location: ../?not_allowed_there_buddy");
    die();
};

?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>List of Establishments</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/color_pallette.css">

</head>

<body>
    
    <div class="container g-0 px-0" style="margin-top: 65px; margin-bottom:50px;">
        <?php
        if(isset($_GET['No_Record_Found'])){?>
           <div class="row">
               <div class="col-12"><?php alert("No Record Found");?></div>
           </div>

        <?php }
        ?>
        <div class="row px-0">
            <div class="col-12 mx-0 px-0">
                <div class="card shadow-sm px-0 mx-0">
                    <div class="card-header bg-navy text-white">

                        <h6>Establishments</h6> 
                        
                        <div class="input-group" id="estSearchForm">
                            <span class="input-group-text">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                                </svg>
                            </span>
                            <input type="search" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Building Name</th>
                                        <th>Location</th>
                                        <th>Owner</th>
                                        <th>Contact</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
              // Fetch general_info records
              if(isClient()){
                  $where = ["owner_id" => $GLOBALS['USER_LOGGED'] ];
              }
              else {
                  $where = null;
              }
                $buildings = select_join(
                    ["general_info gi"],
                    [
                        "gi.gen_info_id",
                        "gi.building_name",
                        "gi.location_of_construction",
                        "gi.owner_name",
                        "gi.owner_contact_no",
                        "msl.address",
                        "msl.lat",
                        "msl.lng"
                    ],
                    [
                        [
                            "type"  => "INNER",
                            "table" => "map_saved_location msl",
                            "on"    => "gi.loc_id = msl.loc_id"
                        ]
                    ],
                    $where,
                    ["msl.date_added" => "DESC"]
                );


              if ($buildings) {
                $i = 1;
                foreach ($buildings as $b) { ?>
                                    <tr>
                                        <td ><?php echo $i;?></td>
                                        <td><?php echo $b['building_name']; ?></td>
                                        <td>
                                         <a href="../pages/map.php?address=<?= $b['address'] ?>&lat=<?= $b['lat']?>&lng=<?= $b['lng'] ?>" class="btn-link btn">
                                          <?php echo $b['location_of_construction']; ?>
                                          </a>
                                        </td>
                                        <td ><?php echo $b['owner_name']; ?></td>
                                        <td ><?php echo $b['owner_contact_no']; ?></td>
                                        <?php if (isAdmin() ) { ?>
                                        <td>
                                            <a href='?page=edit_gen_info&id=<?php echo $b['gen_info_id']; ?>' class='btn btn-sm btn-warning'>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                                </svg>
                                            </a>
                                        </td>
                                        <?php } ?>
                                    </tr>
                                    <?php $i++;
                }
              } else {
                echo "<tr><td colspan='7' class='text-center text-muted'>No buildings found.</td></tr>";
              }
            ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>



            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            let today = new Date();

            // Add 1 day (tomorrow)
            today.setDate(today.getDate() + 1);

            // Format YYYY-MM-DD
            let yyyy = today.getFullYear();
            let mm = String(today.getMonth() + 1).padStart(2, '0');
            let dd = String(today.getDate()).padStart(2, '0');

            let tomorrow = `${yyyy}-${mm}-${dd}`;

            // Apply to input
            $("#date").attr("min", tomorrow);
        });
    </script>
</body>

</html>