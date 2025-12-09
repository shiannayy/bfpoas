<!DOCTYPE html>
<html lang="en">

<body>
   <div class="container-fluid px-0 mt-3">
 <div class="d-flex align-items-center gap-2 flex-wrap px-2 mb-2">
            <a href="?page=ins_sched&view=cal" class="btn btn-gold btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-calendar3 text-navy mb-1 me-1" viewBox="0 0 16 16">
                    <path
                        d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z" />
                    <path
                        d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                </svg>
                <span class="text-navy d-none d-lg-inline">Calendar View</span>
            </a>

            <a href="?page=ins_sched&view=list" class="btn btn-gold btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-table text-navy mb-1 me-1" viewBox="0 0 16 16">
                    <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm15 2h-4v3h4zm0 4h-4v3h4zm0 4h-4v3h3a1 1 0 0 0 1-1zm-5 3v-3H6v3zm-5 0v-3H1v2a1 1 0 0 0 1 1zm-4-4h4V8H1zm0-4h4V4H1zm5-3v3h4V4zm4 4H6v3h4z" />
                </svg>
                <span class="text-navy">Tabular View</span>
            </a>

            <a href="?page=ins_sched&view=stat" class="btn btn-gold btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-table text-navy mb-1 me-1" viewBox="0 0 16 16">
                    <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm15 2h-4v3h4zm0 4h-4v3h4zm0 4h-4v3h3a1 1 0 0 0 1-1zm-5 3v-3H6v3zm-5 0v-3H1v2a1 1 0 0 0 1 1zm-4-4h4V8H1zm0-4h4V4H1zm5-3v3h4V4zm4 4H6v3h4z" />
                </svg>
                <span class="text-navy">Progress View</span>
            </a>


            <?php if( !isDataEntry() && !isInspector() ){ ?>
            <a href="#" class="btn btn-gold btn-sm add-signature position-relative"
                data-user="<?php echo $_SESSION['user_id']; ?>" data-role="<?php echo $_SESSION['role']; ?>">
                <?php if(!$hasSignature){?>
                <span class="px-1 bg-danger border-rounded rounded-2 position-absolute top-0 start-100 translate-middle"
                    style="font-size:8pt;">
                    <span class="text-uppercase text-light">Sign</span>
                </span>
                <?php  } ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen"
                    viewBox="0 0 16 16">
                    <path
                        d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z" />
                </svg>
                <span class="text-navy d-none d-lg-inline">
                    <?php echo $hasSignature ? 'Update' : 'Set New'; ?> E-Signature
                </span>
            </a>
            <?php } ?>
            <!-- <div class="form-check form-switch">
                <input checked class="form-check-input" type="checkbox" id="toggleArchived"
                    data-status-toggle="Archived">
                <label class="form-check-label" for="toggleArchived">Show Archived</label>
            </div>

            <div class="form-check form-switch">
                <input checked class="form-check-input" type="checkbox" id="toggleCompleted"
                    data-status-toggle="Completed">
                <label class="form-check-label" for="toggleCompleted">Show Completed</label>
            </div>

            <div class="form-check form-switch">
                <input checked class="form-check-input" type="checkbox" id="toggleCancelled"
                    data-status-toggle="Cancelled">
                <label class="form-check-label" for="toggleCancelled">Show Cancelled</label>
            </div> -->

        <?php if(isset($_GET['view']) && $_GET['view'] === 'list' ){ ?>
            <input type="text" id="SearchInsSched" class="form-control form-control-sm w-auto" placeholder="Search...">
        <?php } ?>
            <a href="?page=sched_ins" class="ms-auto btn btn-gold btn-sm d-none btn-new-schedule">
                + New Schedule
            </a>
        </div>

       <div class="row m-0 p-0">
           <div class="col-12 px-0">
               <?php 
        
        if (isset($_GET['view'])){
            switch($_GET['view']){
                case 'cal': include_once "inspection_schedule-calendar.php";
                break;
                case 'stat': include_once "inspection_schedule-status.php";
                break;
                case 'list': include_once "inspection_schedule-list.php";
                break;
                default:
                include_once "inspection_schedule-list.php";
            }
        }
        else{
            include_once "inspection_schedule-list.php";
        }
        
    
    ?>
               
           </div>
       </div>
   </div>
    

    

</body>

</html>