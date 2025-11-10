<?php

include("data_class.php");
$branch = $_POST['branch'];
$addmobile=$_POST['addmobile'];
$addid=$_POST['addid'];
$addnames=$_POST['addname'];
$addpass= $_POST['addpass'];
$addemail= $_POST['addemail'];
$type= $_POST['type'];


$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$defaultPhoto = "default_photo.jpg"; // put this file in your "uploads" folder

if (isset($_FILES['profileimg']) && $_FILES['profileimg']['error'] === 0) {
    if (in_array($_FILES['profileimg']['type'], $allowedTypes)) {
        // Add timestamp to prevent overwriting
        $photo = time() . "_" . basename($_FILES['profileimg']['name']);
        move_uploaded_file($_FILES['profileimg']['tmp_name'], "uploads/".$photo);
    } else {
        // Invalid file type, use default
        $photo = $defaultPhoto;
    }
} else {
    // No file uploaded, use default
    $photo = $defaultPhoto;
}


$obj=new data();
$obj->setconnection();
$obj->addnewuser($addid,$addnames,$addpass,$addemail,$addmobile,$type,$branch,$photo);
echo "<script>
        alert('User added successfully!');
        window.history.back();
      </script>";
exit;
?>