 <?php include 'images.php'; 
 include 'includes/db.php';
 ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Current Sections</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="/links/script.php">
    <link rel="stylesheet" href="/links/fonts.html">
    <link rel="stylesheet" type="text/css" href="css/current.css">
</head>
<body>

<section class="current-section p-4">
  <div class="container">
    <ul class="nav nav-tabs" id="mytabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab1" data-bs-toggle="tab" data-bs-target="#current-issues" type="button">
          <i class="fa-regular fa-file-lines"></i> CURRENT ISSUES
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab2" data-bs-toggle="tab" data-bs-target="#press-releases" type="button">
          <i class="fa-regular fa-file-lines"></i> PRESS RELEASES
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab3" data-bs-toggle="tab" data-bs-target="#instructions" type="button">
          <i class="fa-regular fa-file-lines"></i> INSTRUCTIONS
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab4" data-bs-toggle="tab" data-bs-target="#tenders-vacancies" type="button">
          <i class="fa-regular fa-file-lines"></i> TENDER & VACANCIES
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab5" data-bs-toggle="tab" data-bs-target="#election-stories" type="button">
          <i class="fa-regular fa-file-lines"></i> ELECTION STORIES
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab6" data-bs-toggle="tab" data-bs-target="#faqs" type="button">
          <i class="fa-regular fa-file-lines"></i> FAQs
        </button>
      </li>
    </ul>

    <div class="tab-content mt-3" id="mytabcontent">
      <div class="tab-pane fade show active" id="current-issues">
        <div class="container-fluid">
          <div class="list-group">
            <a href="https://www.eci.gov.in/eci-backend/public/api/download?url=LMAhAK6sOPBp..." class="list-group-item list-group-item-action">
              <i class="fa-regular fa-circle-check"></i> 
              CEC Shri Gyanesh Kumar calls on President Smt. Droupadi Murmu & Vice President Shri Jagdeep Dhankhar
              <span class="text-muted d-block"><i class="fa-regular fa-clock"></i> Thursday 20 Feb 2025, 5:08 PM</span>
            </a>

            <a href="https://www.eci.gov.in/eci-backend/public/api/download?url=LMAhAK6sOPBp..." class="list-group-item list-group-item-action">
              <i class="fa-regular fa-circle-check"></i> 
              Dr. Vivek Joshi assumes charge as Election Commissioner
              <span class="text-muted d-block"><i class="fa-regular fa-clock"></i> Wednesday 19 Feb 2025, 1:39 PM</span>
            </a>

            <a href="https://www.eci.gov.in/eci-backend/public/api/download?url=LMAhAK6sOPBp..." class="list-group-item list-group-item-action">
              <i class="fa-regular fa-circle-check"></i> 
              Shri Gyanesh Kumar assumes charge as the 26th CEC of India
              <span class="text-muted d-block"><i class="fa-regular fa-clock"></i> Wednesday 19 Feb 2025, 9:33 AM</span>
            </a>
          </div>
          
          <div class="d-flex justify-content-end mt-3">
            <a class="btn btn-outline-primary" href="https://www.eci.gov.in/issue-details-page/current-issue">
              View More <i class="fa-solid fa-arrow-right-long"></i>
            </a>
          </div>
        </div>
      </div>
      
      <div class="tab-pane fade" id="press-releases">
 
      <div class="tab-pane fade show active" id="current-issues">
        <div class="container-fluid">
          <div class="list-group">
            <a href="https://www.eci.gov.in/eci-backend/public/api/download?url=LMAhAK6sOPBp..." class="list-group-item list-group-item-action">
              <i class="fa-regular fa-circle-check"></i> 
              CEC Shri Gyanesh Kumar calls on President Smt. Droupadi Murmu & Vice President Shri Jagdeep Dhankhar
              <span class="text-muted d-block"><i class="fa-regular fa-clock"></i> Thursday 20 Feb 2025, 5:08 PM</span>
            </a>

            <a href="https://www.eci.gov.in/eci-backend/public/api/download?url=LMAhAK6sOPBp..." class="list-group-item list-group-item-action">
              <i class="fa-regular fa-circle-check"></i> 
              Dr. Vivek Joshi assumes charge as Election Commissioner
              <span class="text-muted d-block"><i class="fa-regular fa-clock"></i> Wednesday 19 Feb 2025, 1:39 PM</span>
            </a>

            <a href="https://www.eci.gov.in/eci-backend/public/api/download?url=LMAhAK6sOPBp..." class="list-group-item list-group-item-action">
              <i class="fa-regular fa-circle-check"></i> 
              Shri Gyanesh Kumar assumes charge as the 26th CEC of India
              <span class="text-muted d-block"><i class="fa-regular fa-clock"></i> Wednesday 19 Feb 2025, 9:33 AM</span>
            </a>
          </div>
          
          <div class="d-flex justify-content-end mt-3">
            <a class="btn btn-outline-primary" href="https://www.eci.gov.in/issue-details-page/current-issue">
              View More <i class="fa-solid fa-arrow-right-long"></i>
            </a>
          </div>
        </div>          


       </div>
      <div class="tab-pane fade" id="instructions">
        <p>Instructions content will be displayed here...</p>
      </div>
      <div class="tab-pane fade" id="tenders-vacancies">
        <p>Tenders & Vacancies content will be displayed here...</p>
      </div>
      <div class="tab-pane fade" id="election-stories">
        <p>Election Stories content will be displayed here...</p>
      </div>
      <div class="tab-pane fade" id="faqs">
        <p>FAQs content will be displayed here...</p>
      </div>
    </div>
  </div>
</section>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
