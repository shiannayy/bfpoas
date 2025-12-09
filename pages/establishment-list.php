<!DOCTYPE html>
<?php include_once "../includes/_init.php"; if(!isLoggedin()){  header("location: ../?not_allowed_there_buddy"); die(); }; ?>
<html lang="en">
<body>

    <div class="container g-0 px-0">
        <?php
        if(isset($_GET['No_Record_Found'])){?>
        <div class="row">
            <div class="col-12"><?php alert("No Record Found");?></div>
        </div>

        <?php }
        ?>
        <div class="row px-0">
            <div class="col-12 mx-0 px-0">
                <form action="" method="GET">
                        <div class="d-flex justify-content-between  m-3" id="estSearchForm">
                       
                        <input type="hidden" name="page" value="est_list">
                        <input name="searchEst" placeholder="Search Establishment" type="text" id="searchEst" class="form-control w-25 border border-3 border-top-0 border-start-0 border-end-0 rounded-0 border-dark">
                        <button  type="submit" class="btn btn-outline-gold rounded rounded-3 btn-sm"> <b class="my-auto me-auto align-middle"><?= getIcon("search") ?></b></button>
                    
                        <a href="?page=new_est" class="ms-auto btn btn-gold">+ New</a>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-navy">
                                <tr>
                                    <th>Building Name</th>
                                    <th>Location</th>
                                    <th>Owner</th>
                                    <th>Contact</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $where = [];
                                // Fetch general_info records
                                if(isset($_GET['searchEst'])){
                                    $searchEst = mysqli_real_escape_string($CONN, $_GET['searchEst']);
                                    $where += ['building_name' => "%${searchEst}%"];
                                }

                                if(isClient()){
                                    $where += ["owner_id" => $_SESSION['user_id'] ];
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
                                                        <td><?php echo $b['building_name']; ?></td>
                                                        <td>
                                                            <a href="../pages/map.php?address=<?= $b['address'] ?>&lat=<?= $b['lat']?>&lng=<?= $b['lng'] ?>"
                                                                class="btn-link btn">
                                                                <?php echo $b['location_of_construction']; ?>
                                                            </a>
                                                        </td>
                                                        <td><?php echo $b['owner_name']; ?></td>
                                                        <td><?php echo $b['owner_contact_no']; ?></td>
                                                        <?php if (isAdmin() ) { ?>
                                                        <td>
                                                            <a href='?page=edit_gen_info&id=<?php echo $b['gen_info_id']; ?>'
                                                                class='btn btn-sm btn-warning'>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                                    fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                                    <path
                                                                        d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                                    <path fill-rule="evenodd"
                                                                        d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
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