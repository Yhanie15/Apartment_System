<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JRSL Apartment - Your Home Away from Home</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="home.css">

</head>
<body>

    <!-- Navigation Bar -->
    <nav>
        <div class="logo">
            <img src="img/jrsl_logo.png" alt="JRSL Logo">
        </div>
        <ul class="nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="#room">Rooms</a></li>
            <li><a href="#amenities">Amenities</a></li>
            <li><a href="#contact">Contact</a></li>
            <li><a class="login-btn"href="login.php">Log In</a><li>
        </ul>

    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>JRSL Apartment</h1>
            <p>Your Home Away from Home.</p>
            <a href="#room" class="btn">Check Available Rooms</a>
        </div>
    </section>

    <!-- Rooms Section -->
    <section id = room class="rooms-section">

        <div class="rooms-header">
            <h2>Our Rooms</h2>
            <a href="rooms.php" class="btn-view-all">View All Rooms</a>
        </div>

        <div class="rooms-container">

            <div class="room">
                <img src="img/pic1.jpg" alt="Studio Room">
                <div class="room-info">
                    <div class="room-text">
                        <h3>Studio Type</h3>
                        <p>Good for 2 pax</p>
                    </div>
                    <a href="#" class="btn">View Details</a>
                </div>
            </div>

            <div class="room">
                <img src="img/pic2.jpg" alt="Studio Room">
                <div class="room-info">
                    <div class="room-text">
                        <h3>Mid Type</h3>
                        <p>Good for 4 pax</p>
                    </div>
                    <a href="#" class="btn">View Details</a>
                </div>
            </div>

            <div class="room">
                <img src="img/pic3.jpg" alt="Studio Room">
                <div class="room-info">
                    <div class="room-text">
                        <h3>Large Type</h3>
                        <p>Good for 6 pax</p>
                    </div>
                    <a href="#" class="btn">View Details</a>
                </div>
            </div>

        </div>
    </section>

    <!-- Our Spaces Section -->
    <section id= amenities class="our-spaces-section">

        <div class="our-spaces-container">
            <div class="spaces-text">
                <h2>MAKING YOUR STAY MEMORABLE</h2>
                <p>Our Spaces</p>
            </div>
            <div class="spaces-gallery">
                <div class="space">
                    <img src="img/gym.jpg" alt="Gym">
                    <div class="hover-text">Gym Area</div>
                </div>
                <div class="space">
                    <img src="img/pool2.jpg" alt="Pool">
                    <div class="hover-text">Swimming Area</div>
                </div>
                <div class="space">
                    <img src="img/billiard.jpg" alt="billiard">
                    <div class="hover-text">Entertaiment Area</div>
                </div>
                <div class="space">
                    <img src="img/movie.jpg" alt="movie">
                    <div class="hover-text">Theather Area</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Us Section -->
    <section id= contact class="contact-section">
        <div class="contact-container">

            <div class="contact-map">
                <!-- Google Maps Embed or Image -->
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7721.903608455071!2d120.98189867770992!3d14.6018214!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c9f90683dfeb%3A0x884fa70612e32cae!2sJRSL%20Dormitory!5e0!3m2!1sen!2sph!4v1728664537845!5m2!1sen!2sph" 
                    width="600" 
                    height="400" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>

            <div class="contact-details">
                <h2>How To Get There</h2>
                <ul>
                    <li><i class="fas fa-map-marker-alt"></i> JRSL Dormitory, 584 Dalupan St, Sampaloc, Manila, 1008 Metro Manila</li>
                    <li><i class="fas fa-phone"></i> 0908 865 7758</li>
                    <li><i class="fas fa-envelope"></i> 584apartment@gmail.com</li>
                    <li><i class="fas fa-globe"></i> <a href="https://www.facebook.com/JRSLApartment" target="_blank">https://www.facebook.com/JRSLApartment</a></li>
                </ul>
            </div>
        </div>
    </section>



</body>
</html>
