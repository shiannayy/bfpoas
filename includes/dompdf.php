<?php

require '../dompdf/autoload.inc.php';

use Dompdf\Dompdf;

// Instantiate and use the dompdf class
$dompdf = new Dompdf();
$dompdf->loadHtml('<h1>Hello World</h1>');

// (Optional) Set paper size to legal
$dompdf->setPaper('legal', 'portrait');

// Render and stream the PDF
$dompdf->render();
$dompdf->stream("document.pdf", ["Attachment" => false]);
