      <?php
     session_start();
     if(isset($_SESSION['admin'])){
     header('location: try/voters.php');
     }
 
  ?>

  <!DOCTYPE html>
  <html>
  <head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
  	<title>electors-info</title>
  </head>
  <body>
   
 <p>hello world</p> 

  </body>
  </html>