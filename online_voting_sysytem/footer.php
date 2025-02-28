<?php
 include 'includes/db.php';
 include 'map-election.php';

  ?>


  <!DOCTYPE html>
  <html>
  <head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
  	<title>footer</title>
   <link rel="stylesheet" type="text/css" href="css/footer.css">

  </head>
  <body>
  <footer class="footer">
    <div class="container">
        <div class="row">
            <!-- About Section -->
            <div class="col-md-4 footer-about">
                <h5>About the Voting System</h5>
                <p>A secure and transparent online voting system ensuring fair and credible elections.</p>
            </div>

            <!-- Quick Links -->
            <div class="col-md-4 footer-links">
                <h5>Quick Links</h5>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="results.php">Election Results</a></li>
                    <li><a href="register.php">Register to Vote</a></li>
                    <li><a href="faq.php">FAQs</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>

            <!-- Contact & Social Media -->
            <div class="col-md-4 footer-contact">
                <h5>Contact Us</h5>
                <p>Email: support@votingsystem.com</p>
                <p>Phone: +1 (123) 456-7890</p>
                <div class="social-icons">
                    <a href="#"><img src="images/facebook-icon.png" alt="Facebook"></a>
                    <a href="#"><img src="images/twitter-icon.png" alt="Twitter"></a>
                    <a href="#"><img src="images/linkedin-icon.png" alt="LinkedIn"></a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> Voting System. All rights reserved.</p>
    </div>
</footer>

  </body>
  </html>