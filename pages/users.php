<?php
include_once "../includes/_init.php";
$alert_type = ""; $alert = "";




if(isset($_GET['action']) && isset($_GET['u'])){
    $action = $_GET['action'];
    $val = ($action == "x" ? 0 : ($action == "y" ? 1 : null));
    $user_id = decrypt_id($_GET['u']);
    
    var_dump($user_id);
    
    $exists = select("users", ["user_id" => $user_id]);
    
    if(!empty($exists)){
        $u = update_data("users",["is_active" => $val , "updated_at" => date("Y-m-d H:i:s")],["user_id" => $user_id]);
        
        if($u > 0){
            $alert = "User has been disabled";
            $alert_type = "alert-warning";
        }
    }
    else {
        $alert = "User not found.";
            $alert_type = "alert-danger";
    }
} ?>




<?php
        
if (isset($_SESSION['rolelabel'])) {

    $roleLabel = $_SESSION['rolelabel'];
    $where = null;
    $limit = null;
    $wherebit = null;

    //--------------------------------------------
    // 🔍 SEARCH
    //--------------------------------------------
    if (isset($_GET['searchUser'])) {
        $str = htmlentities($conn->real_escape_string($_GET['searchUser']));
        $where = [
            "full_name" => "%{$str}%",
            "email"     => "%{$str}%",
            "role"      => "%{$str}%"
        ];
        $wherebit = "OR";
    }

    //--------------------------------------------
    // 🔽 SORT TOGGLE SYSTEM
    //--------------------------------------------
    // Default states
    $nameOrder = "ASC"; 
    $roleOrder = "ASC"; 
    $creationOrder = "ASC";

    $nameSortIcon = "";
    $roleSortIcon = "";
    $creationSortIcon = "";

    $orderby = [];

    if (isset($_GET['sortby']) && isset($_GET['order'])) {

        $sortby = htmlentities($_GET['sortby']);
        $order  = htmlentities($_GET['order']);

        // Toggle ASC ↔ DESC
        $nextOrder = ($order === "ASC") ? "DESC" : "ASC";

        switch ($sortby) {
            case 'name':
                $nameOrder = $nextOrder;
                $orderby = ["full_name" => $order];
                break;

            case 'role':
                $roleOrder = $nextOrder;
                $orderby = ["role" => $order];
                break;

            case 'creation':
                $creationOrder = $nextOrder;
                $orderby = ["created_at" => $order];
                break;

            default:
                $orderby = [];
        }
    }

    // Set icons
    $nameSortIcon     = ($nameOrder === "DESC") ? getIcon("caretdown") : getIcon("caretup");
    $roleSortIcon     = ($roleOrder === "DESC") ? getIcon("caretdown") : getIcon("caretup");
    $creationSortIcon = ($creationOrder === "DESC") ? getIcon("caretdown") : getIcon("caretup");

    //--------------------------------------------
    // 👮 ROLE-BASED FILTERING (kept untouched)
    //--------------------------------------------
    if (in_array($roleLabel, ['Recommending Approver', 'Approver'])) {
        $where = [
            'is_active' => 1,
            'role'      => ['Client', 'Inspector']
        ];
        // keep your original admin order IF no sort was manually chosen
        if (empty($orderby)) {
            $orderby = ['role' => 'ASC', 'created_at' => 'ASC'];
        }
    }

    if (in_array($roleLabel, ['Admin_Assistant'])) {
        if (empty($orderby)) {
            $orderby = ['role' => 'ASC', 'created_at' => 'ASC'];
        }
    }

    //--------------------------------------------
    // 📌 FINAL QUERY
    //--------------------------------------------
    $userList = select("users", $where, $orderby, $limit, $wherebit);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>List of Establishments</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <!--    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/color_pallette.css">
    <style>
        .dropdown-toggle::after {
            display: none !important;
        }

        #signatureCanvas {
            width: 100%;
            height: 100%;
            display: block;
            border: 1px solid #ccc;
            touch-action: none;
            /* prevent scrolling while drawing */
        }

        .page-button {
            margin-left: 5px;
            margin-right: 5px;
        }
    </style>
</head>

<body>

    <div class="container-fluid px-0" style="margin-top:65px">
        <div class="d-flex align-items-center gap-2 flex-wrap px-2 my-2">
            <span class="fs-4 mb-0 me-3">Users</span>

            <?php if(isDataEntry()) { ?>
            <a href="?page=new_user" class="btn btn-gold my-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus" viewBox="0 0 16 16">
                    <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H1s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z" />
                    <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5" />
                </svg>
                <b class="fw-bold">Add New User</b>
            </a>
            <?php } ?>





            <form action="" method="get">
                <div class="input-group">
                    <input type="hidden" name="page" value="user_list">
                    <input type="text" id="SearchUser" name="searchUser" class="<?php if(isset($_GET['searchUser'])){ echo "bg-gold"; } ?> form-control form-control-sm w-auto" placeholder="Search..." value="<?php if(isset($_GET['searchUser'])){ echo $_GET['searchUser']; } ?>">
                    <button class="btn btn-navy">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                        </svg>
                    </button>
                </div>
            </form>
            <div class="py-1 mt-3 alert <?= $alert_type ?>"><?php echo $alert ?></div>
        </div>

        <div class="table-responsive overflow-y-scroll px-0">
            <div class="container pagination"></div>
            <table class="mx-0 w-100 table table-striped table-responsive table-hover table-bordered align-middle" id="scheduleTable">
                <thead class="table-navy">
                    <tr>
                        <th>ID</th>
                        <th> <a href="?page=user_list&sortby=name&order=<?= $nameOrder ?>" class="btn btn-gold my-2">
                                Name <?= $nameSortIcon ?>
                            </a></th>
                        <th>Email</th>
                        <th> <a href="?page=user_list&sortby=role&order=<?= $roleOrder ?>" class="btn btn-gold my-2">
                                Role <?= $roleSortIcon ?>
                            </a>
                        </th>
                        <th>Status</th>
                        <th>Sub Role</th>
                        <th> <a href="?page=user_list&sortby=creation&order=<?= $creationOrder ?>" class="btn btn-gold my-2">
                                Date Added <?= $creationSortIcon ?>
                            </a></th>
                        <th></th>
                    </tr>
                </thead>

                <tbody id="usersTable" class="overflow-y-scroll">
                    <?php foreach($userList as $u){ ?>
                    <tr>
                        <td class="text-center align-content-middle"><?= $u['user_id'] ?></td>
                        <td class="text-start align-content-middle"><?= $u['full_name'] ?></td>
                        <td class="text-start align-content-middle"><?= $u['email'] ?></td>
                        <td class="text-start align-content-middle"><?= $u['role'] ?></td>

                        <td class="text-start align-content-middle <?php echo $u['is_active'] == 0 ? "text-danger" : ""; ?>"><?php echo ($u["is_active"] == 0) ? "Disabled" : "Active"; ?></td>
                        <td class="text-start align-content-middle"><?= str_replace("_"," ", $u['sub_role']) ?></td>
                        <td class="text-start align-content-middle"><?= $u['created_at'] ?></td>
                        <td class="text-center align-content-middle">
                            <div class="dropdown">
                                <button class="btn btn-navy dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo getIcon('menudots');?>
                                </button>
                                <ul class="dropdown-menu bg-gold">
                                    <?php if($u['is_active'] == 1) { ?>
                                    <li>
                                        <a class="dropdown-item" href="?page=user_list&action=x&u=<?= encrypt_id($u['user_id']); ?>">
                                            Disable Account
                                        </a>
                                    </li>
                                    <?php } else{ ?>
                                    <li>
                                        <a class="dropdown-item" href="?page=user_list&action=y&u=<?= encrypt_id($u['user_id']); ?>">
                                            Enable Account
                                        </a>
                                    </li>

                                    <?php } ?>


                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php } ?>
        </div>
    </div>


</body>

</html>