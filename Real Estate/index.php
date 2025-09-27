<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Get properties from database with their first image
$sql = "SELECT p.*, u.name as seller_name, 
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY id ASC LIMIT 1) as first_image
        FROM properties p 
        JOIN users u ON p.seller_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT 12";

$properties = [];
if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $properties[] = $row;
    }
    mysqli_free_result($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real Estate Platform</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #F8F8F8; /* Light background */
            color: #333333; /* Darker text color */
            font-family: 'Lato', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            color: #333333; /* Changed from Gold for headings */
        }
        .navbar,
        footer {
            background-color: #005A2B; /* Deep green */
            color: white;
            padding: 15px 0;
        }
        .navbar .container,
        footer .container {
             display: flex;
             justify-content: space-between;
             align-items: center;
             max-width: 1200px;
             margin: 0 auto;
             padding: 0 15px;
        }
        .navbar h1 {
            color: white;
            margin: 0;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 700;
            transition: color 0.3s ease;
        }
         .nav-links a:hover {
            color: #333333; /* Changed from Gold on hover */
        }
         .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            padding: 20px 0;
        }
         .footer-brand,
        .footer-links,
        .footer-contact {
            flex: 1;
            min-width: 200px;
            margin: 10px;
        }
         .footer-brand h2 {
            color: white; /* Ensure footer brand title is white */
        }
        footer p {
            margin: 5px 0;
            font-size: 0.9em;
        }
        footer a {
            text-decoration: none;
            transition: color 0.3s ease;
        }
        footer a:hover {
             color: #CCCCCC; /* Changed from Gold on hover */
        }
        .social-links a {
            margin-right: 10px;
            font-size: 1.2em;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }

        /* Property Card Styling */
        .property-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .property-card {
             background-color: #FFFFFF; /* White for cards */
             border-radius: 10px;
             overflow: hidden;
             box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
             transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .property-image-container,
        .property-image-wrapper { /* Keep existing image wrapper class */
            position: relative;
            width: 100%;
            height: 200px; /* Fixed height for consistency */
            overflow: hidden;
        }
        .property-image-container img,
        .property-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Cover the container */
            transition: transform 0.5s ease;
        }
        .property-card:hover .property-image-container img,
        .property-card:hover .property-image-wrapper img {
             transform: scale(1.1); /* Slightly zoom on hover */
        }
        .property-info {
            padding: 20px;
        }
        .property-title {
            font-size: 1.5em;
            margin-top: 0;
            margin-bottom: 10px;
             color: #333333; /* Dark text for property titles */
        }
        .property-price {
             color: #333333; /* Changed from Gold for price */
             font-size: 1.2em;
             margin-bottom: 10px;
        }
        .property-details,
        .property-location,
        .seller-info {
            font-size: 0.9em;
            color: #555555; /* Slightly darker grey for details */
            margin-bottom: 5px;
        }
        .property-details i,
        .property-location i,
        .seller-info i {
             color: #555555; /* Changed from Gold icons */
             margin-right: 5px;
        }

        /* Buttons */
        .btn-primary {
            display: inline-block;
            background-color: #005A2B; /* Changed from Gold button */
            color: white; /* Changed from Dark text */
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: 'Lato', sans-serif;
            font-weight: 700;
        }
        .btn-primary:hover {
            background-color: #004520; /* Slightly darker green on hover */
             color: white;
        }
         .btn-danger {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: 'Lato', sans-serif;
            font-weight: 700;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }

         /* Badges */
        .property-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #005A2B; /* Changed from Gold badge */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8em;
            font-weight: 700;
            z-index: 1;
        }
        .badge-rent {
             background-color: #005A2B; /* Deep green badge for Rent */
             color: white;
        }
        .badge-sale {
             background-color: #005A2B; /* Changed from Gold badge for Sale */
             color: white;
        }
         /* Add styles for .badge-new and .badge-verified if needed */

    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <h1>Real Estate Platform</h1>
            <div class="nav-links">
                <a href="search.php" class="btn btn-primary">Search Properties</a>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <?php if (isSeller()): ?>
                        <a href="admin/">Seller Dashboard</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Featured Properties -->
        <section style="padding:15px;" class="featured-properties">
            <h2>Featured Properties</h2>
            <div class="property-grid">
                <?php foreach ($properties as $property): ?>
                    <div class="property-card">
                        <div class="property-image-container">
                            <?php
                            // Determine badge type based on property data
                            $badge_class = '';
                            $badge_text = '';
                            if ($property['is_new'] ?? false) {
                                $badge_class = 'badge-new';
                                $badge_text = 'New';
                            } elseif ($property['is_verified'] ?? false) {
                                $badge_class = 'badge-verified';
                                $badge_text = 'Verified';
                            } else {
                                $badge_class = $property['transaction_type'] === 'rent' ? 'badge-rent' : 'badge-sale';
                                $badge_text = $property['transaction_type'] === 'rent' ? 'For Rent' : 'For Sale';
                            }
                            ?>
                            <span class="property-badge <?php echo $badge_class; ?>">
                                <?php echo $badge_text; ?>
                            </span>
                            <img src="<?php echo !empty($property['first_image']) ? $property['first_image'] : 'public/images/default.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                 class="property-image">
                        </div>
                        <div class="property-info">
                            <h3 class="property-title"><?php echo $property['title']; ?></h3>
                            <p class="property-price">
                                <i class="fas fa-tag"></i>
                                Rs <?php echo number_format($property['price']); ?>
                            </p>
                            <div class="property-details">
                                <span class="property-detail-item">
                                    <i class="fas fa-home"></i>
                                    <?php echo ucfirst($property['property_type']); ?>
                                </span>
                                <?php if ($property['property_type'] !== 'plot'): ?>
                                    <span class="property-detail-item">
                                        <i class="fas fa-bed"></i>
                                        <?php echo (isset($property['bedrooms']) && $property['bedrooms'] !== null ? $property['bedrooms'] : 'N/A'); ?>
                                    </span>
                                    <span class="property-detail-item">
                                        <i class="fas fa-bath"></i>
                                        <?php echo (isset($property['bathrooms']) && $property['bathrooms'] !== null ? $property['bathrooms'] : 'N/A'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="property-detail-item">
                                        <i class="fas fa-ruler-combined"></i>
                                        <?php echo (isset($property['plot_size']) && $property['plot_size'] !== null ? number_format($property['plot_size']) . ' sq ft' : 'N/A'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="property-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo $property['city']; ?>, <?php echo $property['state']; ?>
                            </p>
                            <p class="seller-info">
                                <i class="fas fa-user"></i>
                                Seller: <?php echo $property['seller_name']; ?>
                            </p>
                            <div class="property-actions">
                                <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <h2>Real Estate Platform</h2>
                    <p>Your trusted partner in finding the perfect property</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="search.php?type=buy">Buy</a></li>
                        <li><a href="search.php?type=rent">Rent</a></li>
                        <li><a href="add_property.php">Sell</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Real Estate Street, City, Country</p>
                    <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                    <p><i class="fas fa-envelope"></i> info@realestateplatform.com</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Real Estate Platform. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>
