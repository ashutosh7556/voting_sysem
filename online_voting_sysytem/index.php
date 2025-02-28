<?php
// Start session to check if the user is logged in
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="css/index.css">
    
 </head>
<body>
    
    <?php include 'header.php';
 
     ?>

<div class="container_fluid">
        <div class="logo">
            <a href="#">
                <img src="images/eci-logo.svg" alt="ECI Logo">
            </a>
        </div>
        <div id="header" class="sarch">
            <span class="btn"> 
                <a href="#"><i class="fa-solid fa-magnifying-glass"></i></a>
            </span>
        </div>
    </div>

 <div class="slideshow-container">
    <img class="slides" src="images/ele-1.jpg" alt="Image 1">
    <img class="slides" src="images/ele-2.jpg" alt="Image 2">
    <img class="slides" src="images/ele-3.jpg" alt="Image 3">
    <img class="slides" src="images/ele-4.jpg" alt="Image 4">
    <img class="slides" src="images/ele-5.jpg" alt="Image 5">

    <button class="prev" onclick="plusSlides(-1)">&#10094;</button>
    <button class="next" onclick="plusSlides(1)">&#10095;</button>
</div>

<script src="javascript/slide.js"></script>

<div class="press-link-icon">
    <div class="fact-icon">
        <a href="#">
            <img src="">
            <span>myth vs reality</span>
        </a>
    </div>
        <div class="fact-icon">
        <a href="#">
            <img src="">
            <span>maharastra and jharakhand assembly election result</span>
        </a>
    </div>
        <div class="fact-icon">
        <a href="#">
            <img src="">
            <span>assembly election:photo gallery</span>
        </a>
    </div>
        <div class="fact-icon">
        <a href="#">
            <img src="">
            <span>press relesase</span>
        </a>
    </div>
</div>
 
</body>
</html>