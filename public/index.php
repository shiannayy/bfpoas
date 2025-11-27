<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fire Inspection System</title>

    <!-- Bootstrap CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/color_pallette.css">
    <style>
        .mobile-carousel-img {
            /*  width: 100%;*/
            height: 90vh;
            /* fill full viewport height */
            object-fit: cover;
            /* keep the whole image visible */
            object-position: 60% 10%;
            background-color: #000;
            /* optional: black bars if aspect ratio differs */
        }
    </style>
</head>

<body>
    <!-- Login Modal -->
    <?php include_once "../includes/_login_modal.php";?>

    <!-- Static Top Navbar -->
    <?php include_once "../includes/_nav.php";?>


    <!-- Carousel Banner -->
    <?php include_once "../includes/_carousel.php"; ?>
    <!-- Footer -->
    <?php include_once "../includes/_footer.php"; ?>

    <!-- Bootstrap Bundle JS -->
<!--    <script src="../assets/js/jquery.js"></script>-->
<!--    <script src="../assets/js/bootstrap.bundle.min.js"></script>-->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
   
   <script src="../assets/js/main.js"></script>
    <script src="../assets/js/navbar.js"></script>
    <script src="../assets/js/login.js"></script>
    

</body>

</html>