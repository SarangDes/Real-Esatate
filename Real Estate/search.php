<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Get search parameters with validation
$city = isset($_GET['city']) ? sanitizeInput($_GET['city']) : '';
$property_type = isset($_GET['property_type']) ? sanitizeInput($_GET['property_type']) : '';
$min_price = isset($_GET['min_price']) ? filter_var($_GET['min_price'], FILTER_VALIDATE_FLOAT) : '';
$max_price = isset($_GET['max_price']) ? filter_var($_GET['max_price'], FILTER_VALIDATE_FLOAT) : '';

// Validate price range
if ($min_price !== false && $max_price !== false && $min_price > $max_price) {
    $_SESSION['error'] = "Minimum price cannot be greater than maximum price.";
    header("Location: search.php");
    exit();
}

// Build search query
$sql = "SELECT p.*, u.name as seller_name, 
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY id ASC LIMIT 1) as first_image
        FROM properties p 
        JOIN users u ON p.seller_id = u.id 
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($city)) {
    $sql .= " AND p.city LIKE ?";
    $params[] = "%$city%";
    $types .= "s";
}

if (!empty($property_type)) {
    $sql .= " AND p.property_type = ?";
    $params[] = $property_type;
    $types .= "s";
}

if ($min_price !== false && $min_price !== '') {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price !== false && $max_price !== '') {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

$sql .= " ORDER BY p.created_at DESC";

$properties = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $properties[] = $row;
        }
    } else {
        $_SESSION['error'] = "Error executing search query.";
    }
} else {
    $_SESSION['error'] = "Error preparing search query.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Properties - Real Estate Platform</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
     <style>
        body {
            background-color: #F8F8F8; /* Dark background */
            color: #333333; /* Light text color */
            font-family: 'Lato', sans-serif;
        }
         h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
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

        /* Search Page Specifics */
        .search-section {
            background-color: #FFFFFF; /* Slightly lighter dark for search section */
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
             box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .search-form .form-group,
        .search-form .form-row .form-group {
            margin-bottom: 15px;
        }
         .search-form label {
             color: #333333; /* Light grey for labels */
             display: block;
             margin-bottom: 5px;
         }
        .search-form input[type="text"],
        .search-form input[type="number"],
        .search-form select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #CCCCCC; /* Lighter border */
            background-color: #FFFFFF; /* White background */
            color: #333333;
        }

        /* Property Card Styling (reused from index) */
         .property-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        .property-card {
            background-color: #FFFFFF; /* Slightly lighter dark for cards */
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .property-image-container {
            position: relative;
            width: 100%;
            height: 200px; /* Fixed height for consistency */
            overflow: hidden;
        }
        .property-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Cover the container */
             transition: transform 0.5s ease;
        }
        .property-card:hover .property-image-container img {
             transform: scale(1.1); /* Slightly zoom on hover */
        }
        .property-info {
            padding: 20px;
        }
        .property-title {
            font-size: 1.5em;
            margin-top: 0;
            margin-bottom: 10px;
             color: #333333; /* White for property titles */
        }
        .property-price {
             color: #333333; /* Changed from Gold for price */
             font-size: 1.2em;
             margin-bottom: 10px;
        }
        .property-details,
        .seller-info {
            font-size: 0.9em;
            color: #555555; /* Lighter grey for details */
            margin-bottom: 5px;
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

    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <h1>Real Estate Platform</h1>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="search-section">
            <h2>Search Properties</h2>
            <form action="search.php" method="GET" class="search-form">
                <div class="form-group">
                    <input type="text" name="city" placeholder="City" class="form-control" value="<?php echo $city; ?>">
                </div>
                
                <div class="form-group">
                    <select name="property_type" class="form-control">
                        <option value="">Property Type</option>
                        <option value="flat" <?php echo $property_type == 'flat' ? 'selected' : ''; ?>>Flat</option>
                        <option value="plot" <?php echo $property_type == 'plot' ? 'selected' : ''; ?>>Plot</option>
                        <option value="bungalow" <?php echo $property_type == 'bungalow' ? 'selected' : ''; ?>>Bungalow</option>
                        <option value="house" <?php echo $property_type == 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="room" <?php echo $property_type == 'room' ? 'selected' : ''; ?>>Room</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <input type="number" name="min_price" placeholder="Min Price" class="form-control" value="<?php echo $min_price; ?>">
                    </div>
                    
                    <div class="form-group">
                        <input type="number" name="max_price" placeholder="Max Price" class="form-control" value="<?php echo $max_price; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        <section class="search-results">
            <h3>Search Results</h3>
            <div class="property-grid">
                <?php foreach ($properties as $property): ?>
                    <div class="property-card">
                        <div class="property-info">
                            <h3 class="property-title"><?php echo $property['title']; ?></h3>
                            <p class="property-price">Rs<?php echo number_format($property['price']); ?></p>
                            <p class="property-details">
                                <?php echo $property['property_type']; ?> | 
                                <?php echo $property['city']; ?>, <?php echo $property['state']; ?>
                            </p>
                            <p class="seller-info">Seller: <?php echo $property['seller_name']; ?></p>
                            <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
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
</body>
</html> 