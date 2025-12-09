<!DOCTYPE html>
<html lang="en">

<body>
   <div class="container-fluid px-0 mt-3">
       <div class="row m-0 p-0">
           <div class="col-12 px-0">
               <?php 
        
        if (isset($_GET['calendar_view'])){
            include_once "inspection_schedule-calendar.php";
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