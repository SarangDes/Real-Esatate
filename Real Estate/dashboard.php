<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user's properties
$sql = "SELECT * FROM properties WHERE seller_id = ? ORDER BY created_at DESC";
$properties = [];

    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $properties[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Real Estate Platform</title>
    <link rel="stylesheet" href="/public/css/style.css">
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

        /* Existing styles for dashboard */
        .dashboard h2 {
             color: #333333; /* Changed from Gold for dashboard title */
             margin-bottom: 20px;
        }
         .dashboard-actions {
             margin-bottom: 30px;
         }

        .property-image-container,
        .property-image-wrapper {
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
        .property-card {
             display: flex;
             flex-direction: column;
             background-color: #FFFFFF; /* Slightly lighter dark for cards */
             border-radius: 10px;
             box-shadow: 0 5px 15px rgba(0,0,0,0.1);
             padding: 15px;
             margin-bottom: 20px;
             transition: transform 0.3s ease, box-shadow 0.3s ease;
             overflow: hidden;
        }
        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
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
         .property-actions .btn {
             margin-right: 10px;
         }
          .property-actions .btn:last-child {
             margin-right: 0;
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
            background-color: #dc3545; /* Keep default danger color */
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

        /* Buyer Dashboard specific styles */
         .dashboard-content h3 {
             color: #333333; /* Gold for buyer section headings */
             margin-bottom: 15px;
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
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
            
            <?php if ($_SESSION['role'] === 'seller'): ?>
                <div class="dashboard-actions">
                    <a href="add_property.php" class="btn btn-primary">Add New Property</a>
                </div>
                    
                <div class="properties-list">
                    <h3>Your Properties</h3>
                    <?php if (empty($properties)): ?>
                        <p>You haven't listed any properties yet.</p>
                    <?php else: ?>
                    <div class="property-grid">
                        <?php foreach ($properties as $property): ?>
                            <div class="property-card">
                                    <div class="property-image-wrapper">
                                        <?php
                                        // Get first image for the property
                                        $image_sql = "SELECT image_path FROM property_images WHERE property_id = ? LIMIT 1";
                                        if ($img_stmt = mysqli_prepare($conn, $image_sql)) {
                                            mysqli_stmt_bind_param($img_stmt, "i", $property['id']);
                                            mysqli_stmt_execute($img_stmt);
                                            $img_result = mysqli_stmt_get_result($img_stmt);
                                            if ($img_row = mysqli_fetch_assoc($img_result)) {
                                                echo '<img src="' . htmlspecialchars($img_row['image_path']) . '" alt="' . htmlspecialchars($property['title']) . '">';
                                            } else {
                                                echo '<img src="public/images/default.jpg" alt="Default Property Image">';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <div class="property-info">
                                        <h4><?php echo htmlspecialchars($property['title']); ?></h4>
                                        <p class="price">Rs <?php echo number_format($property['price']); ?></p>
                                        <p class="location"><?php echo htmlspecialchars($property['location']); ?></p>
                                    <div class="property-actions">
                                            <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-info">View</a>
                                            <a href="admin/edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                            <a href="delete_property.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this property?');">Delete</a>
                                        </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="dashboard-content">
                    <h3>Saved Properties</h3>
                    <p>You haven't saved any properties yet.</p>
                    
                    <h3>Recent Searches</h3>
                    <p>No recent searches found.</p>
                    </div>
            <?php endif; ?>
        </div>
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