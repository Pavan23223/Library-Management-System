<?php

include("data_class.php");

$book = $_POST['book'];
$userselect = $_POST['userselect'];
$days = $_POST['days'];
$getdate = date("Y-m-d");                       
$returnDate = date("Y-m-d", strtotime('+'.$days.' days'));

$obj = new data();
$obj->setconnection();
$obj->issuebook($book, $userselect, $days, $getdate, $returnDate);
