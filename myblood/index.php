<?php include 'homepage_header.php'; ?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <title>MYBlood | Booking Blood Donation</title>
    <style>
      body {
        font-family: Arial, sans-serif;
      }
      .carousel-caption {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: justify;
        color: white;
        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7);
        z-index: 10;
        width: 80%;
      }
      .carousel-caption h3 {
        font-size: 2rem;
        margin-bottom: 10px;
      }
      .carousel-caption p {
        font-size: 1.2rem;
        margin-bottom: 20px;
      }
      .carousel-caption button {
        background-color: #000000; /* Red */
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 1rem;
        cursor: pointer;
      }
      .carousel-caption button:hover {
        background-color: #cc0000; /* Darker red */
      }
      
      .why-choose-us .row {
        border: 1px solid #ddd;
        margin-bottom: 10px;
        padding: 15px;
        font-weight: bold;
      }
      .why-choose-us .row:nth-child(odd) {
        background-color: #000000; /* Red */
        color: white;
      }
      .why-choose-us .row:nth-child(even) {
        background-color: #f9f9f9;
        text-align: justify;
      }
      .review-box {
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
        padding: 20px;
        margin: 10px;
        text-align: justify;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }
      .card-header.bg-primary {
  background-color: #000000 !important; /* Force red background */
  color: white !important; /* Ensure text remains visible */
}

      /* Ensuring all cards are the same height and content is visible */
      .card.h-100 {
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
      }

      .card-body {
        flex-grow: 1;
        z-index: 1;
        padding: 15px; /* Ensuring space inside the card body */
      }

      .card-header {
        z-index: 2;
        background-color: #000000; /* Red */
        color: white; /* Ensure the text color is white on red */
      }

      .card-body p {
        z-index: 1;
        margin-top: 10px;
      }

      /* Optional: Add padding/margin if the content appears too tight */
      .card-body {
        padding: 15px;
      }

      /* Optional: Adjust card size and spacing */
      .card-deck .card {
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        height: 100%;
      }
      .card.border-primary {
  border-color: #000000 !important; /* Red border */
}

      .card-deck .card-body {
        padding: 20px;
        text-align: justify;
      }

      .card-title {
        color: #000000; /* Red */
        font-weight: bold;
      }

      /* Ensure cards inside rows are aligned properly */
      .row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
      }

      .card-deck .card {
        flex: 1 1 calc(33.333% - 20px); /* Ensure all cards are same size and spaced equally */
        margin: 10px;
      }
      /* Perkhidmatan Kami Section Styles */
      .card-body {
        padding: 20px;
      }

      .card-body h5 {
        font-size: 1.2rem;
        font-weight: bold;
      }

      .card-body p {
        font-size: 1rem;
        color: #555;
      }

      .card-body i {
        color: #000000; /* Red icon color */
        margin-bottom: 10px; /* Space between icon and title */
      }

      /* Adjust Icon and Text Layout */
      .card-body.d-flex {
        display: flex;
        align-items: center;
      }

      .card-body .mr-3 {
        margin-right: 15px;
      }

      .card-header {
        font-weight: bold;
      }

      .card h-100 {
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        border: none; /* Ensure no border */
      }

      /* Adjusting the icons' color and size */
      .bi {
        font-size: 3rem;
      }

      /* Centering cards and spacing */
      .row.justify-content-center {
        display: flex;
        justify-content: center;
      }

      .no-gutters {
        margin-right: 0;
        margin-left: 0;
      }

      /* Ensuring all cards are the same height and attached side by side */
      .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-radius: 0; /* Ensure no border-radius for side-by-side effect */
        border: 1px solid #ddd;
      }

      .card-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
      }

      /* Review Box Styling */
      .review-box {
        background-color: #f8f9fa;  /* Light grey background */
        border: 1px solid #ddd;     /* Border around the box */
        border-radius: 10px;        /* Rounded corners */
        padding: 15px 20px;         /* Reduced padding */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);  /* Soft shadow for depth */
        margin-bottom: 20px;        /* Bottom margin to give space between reviews */
        max-width: 500px;           /* Limit max width of review box */
        margin: 0 auto;             /* Center align the review box */
      }

      .review-box p {
        font-size: 1rem;
        color: #333;  /* Dark grey for text */
      }

      .review-box .font-italic {
        font-size: 0.9rem;
        color: #666;  /* Lighter grey for author's name */
      }

      /* Red Arrow Styling */
      .carousel-control-prev-icon,
      .carousel-control-next-icon {
        background-color: #FF0000; /* Red */
        border-radius: 50%;
        width: 30px;
        height: 30px;
      }

      .carousel-control-prev-icon:focus,
      .carousel-control-next-icon:focus {
        outline: none;
      }

      /* Margin for the Carousel and Footer Space */
      .container.mt-5.mb-5 {
        margin-bottom: 50px;  /* Add some space between the reviews section and footer */
      }

.hero-section {
    position: relative;
    height: 60vh; /* Reduced height */
    overflow: hidden;
}

.hero-section img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-content {
    position: absolute;
    top: 50%;
    left: 50px;
    transform: translateY(-50%);
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    z-index: 2;
}

.hero-content h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.hero-content p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
}

.donate-btn {
    background-color: #000000;
    color: white;
    padding: 10px 30px;
    border: none;
    border-radius: 5px;
    font-size: 1.1rem;
    cursor: pointer;
    text-decoration: none;
}

/* Add overlay to make text more readable */
.hero-section::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.3);
}

</style>
  </head>
  <body>

    <!-- Carousel Section -->
    <div id="introCarousel" class="carousel slide" data-ride="carousel">
  <ol class="carousel-indicators">
    <li data-target="#introCarousel" data-slide-to="0" class="active"></li>
    <li data-target="#introCarousel" data-slide-to="1"></li>
    <li data-target="#introCarousel" data-slide-to="2"></li>
  </ol>
  <div class="carousel-inner">
    <!-- Slide 1 -->
    <div class="carousel-item active">
        <img class="d-block w-100 " src="./assets/images/pic1.jpg" alt="Welcome to MJ Medic Care">
        <div class="carousel-caption">
            <h3>Welcome to Malaysia’s Blood Donation Portal</h3>
            <p>Join our mission to save lives. Register today and book your first blood donation appointment!</p>
            <button onclick="window.location.href='signup.php'" class="btn btn-primary">Donate Now</button>
        </div>
    </div>

    <!-- Slide 2 -->
<div class="carousel-item">
    <img class="d-block w-100" src="./assets/images/pic2.jpg" alt="Blood Donation Safety">
    <div class="carousel-caption">
        <h3>Donating Blood is Safe and Life-Saving!</h3>
        <ul style="text-align: left; display: inline-block;">
            <li>Donating blood is a quick and simple way to help save lives.</li>
            <li><strong>Approved by Medical Authorities:</strong> All blood donations are regulated for safety by health agencies.</li>
            <li><strong>Eligibility:</strong> Blood donors must be healthy and meet the necessary criteria.</li>
            <li><strong>Donation Process:</strong> The process is safe and supervised by certified medical professionals.</li>
            <li><strong>Aftercare:</strong> Donors receive post-donation care to ensure their well-being.</li>
            <li><strong>Stay Healthy:</strong> Blood donation is a safe process and contributes to a healthier community.</li>
        </ul>
    </div>
</div>


    <!-- Slide 3 -->
    <div class="carousel-item">
        <img class="d-block w-100" src="./assets/images/pic3.jpg" alt="Location">
        <div class="carousel-caption">
            <h3>Find the Nearest Blood Donation Center</h3>
            <p>Locate a donation center near you and book an appointment with ease.</p>
            <button onclick="window.location.href='location.php'" class="btn btn-primary">Find a Center</button>
        </div>
    </div>
</div>



  <a class="carousel-control-prev" href="#introCarousel" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#introCarousel" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>

    <!-- Why Section -->
    <div class="container mt-5">
  <h2 class="text-center mb-4">Why Donate Blood?</h2>
  <div class="row">
    <div class="col-md-4 mb-4">
      <div class="card text-center border-primary h-100">
        <div class="card-header bg-primary text-white">
          <h5 class="card-title mb-0" style="color: white;">Save Lives</h5>
        </div>
        <div class="card-body bg-white border border-primary">
          <p class="card-text">Your donation can save up to three lives. Be a hero today.</p>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card text-center border-primary h-100">
        <div class="card-header bg-primary text-white">
          <h5 class="card-title mb-0" style="color: white;">Easy Process</h5>
        </div>
        <div class="card-body bg-white border border-primary">
          <p class="card-text">Book appointments and track your donation history online.</p>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card text-center border-primary h-100">
        <div class="card-header bg-primary text-white">
          <h5 class="card-title mb-0" style="color: white;">Community Impact</h5>
        </div>
        <div class="card-body bg-white border border-primary">
          <p class="card-text">Help maintain adequate blood supply for hospitals in Malaysia.</p>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Our Service Section -->
<div class="container mt-5">
  <h2 class="text-center mb-4">Our Service</h2>
  <div class="row justify-content-center no-gutters">
    <!-- Card 1: Book a Donation -->
    <div class="col-md-3 mb-4">
      <div class="card text-center h-100">
        <div class="card-body">
          <!-- Icon for booking -->
          <i class="bi bi-calendar2-check fa-3x text-info mb-3"></i>
          <h5 class="card-title">Book a Donation</h5>
          <p class="card-text">Easily schedule your blood donation online at your convenience.</p>
        </div>
      </div>
    </div>
    <!-- Card 2: Check Eligibility -->
    <div class="col-md-3 mb-4">
      <div class="card text-center h-100">
        <div class="card-body">
          <!-- Icon for eligibility -->
          <i class="bi bi-check-circle fa-3x text-success mb-3"></i>
          <h5 class="card-title">Check Eligibility</h5>
          <p class="card-text">Find out if you meet the requirements to donate blood today.</p>
        </div>
      </div>
    </div>
    <!-- Card 3: Locate Donation Centers -->
    <div class="col-md-3 mb-4">
      <div class="card text-center h-100">
        <div class="card-body">
          <!-- Icon for location -->
          <i class="bi bi-geo-alt fa-3x text-warning mb-3"></i>
          <h5 class="card-title">Locate Donation Centers</h5>
          <p class="card-text">Search for the nearest blood donation center in your area.</p>
        </div>
      </div>
    </div>
    <!-- Card 4: Support Team Availability -->
    <div class="col-md-3 mb-4">
      <div class="card text-center h-100">
        <div class="card-body">
          <!-- Icon for support -->
          <i class="bi bi-headset fa-3x text-primary mb-3"></i>
          <h5 class="card-title">Support Team 24/7</h5>
          <p class="card-text">Our team is available round the clock to assist you with any inquiries.</p>
        </div>
      </div>
    </div>
  </div>
</div>


   <!-- Blood Donation Experience Carousel -->
<div class="container mt-5 mb-5">
  <h2 class="text-center mb-4">Blood Donation Experiences</h2>
  <div id="donationCarousel" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
      <!-- Experience 1 -->
      <div class="carousel-item active">
        <div class="review-box">
          <p>"Donating blood felt like a small act, but knowing it could save lives made it so rewarding. I'm glad I donated!"</p>
          <p class="text-right font-italic">- Sarah Lee</p>
        </div>
      </div>
      <!-- Experience 2 -->
      <div class="carousel-item">
        <div class="review-box">
          <p>"The process was quick and easy. I feel proud to help those in need by donating blood. I encourage everyone to do the same!"</p>
          <p class="text-right font-italic">- Ahmed Khan</p>
        </div>
      </div>
      <!-- Experience 3 -->
      <div class="carousel-item">
        <div class="review-box">
          <p>"It was my first time donating blood, and it was a fulfilling experience. Knowing I could save lives motivated me to keep donating!"</p>
          <p class="text-right font-italic">- Emily Zhang</p>
        </div>
      </div>
    </div>
    <!-- Carousel controls -->
    <a class="carousel-control-prev" href="#donationCarousel" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#donationCarousel" role="button" data-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
    </a>
  </div>
</div>


    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  </body>
</html>
