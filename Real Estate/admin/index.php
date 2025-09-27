<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Check if user is logged in and is a seller
requireLogin();
requireSeller();

// Get all properties
$sql = "SELECT p.*, u.name as seller_name 
        FROM properties p 
        JOIN users u ON p.seller_id = u.id 
        WHERE p.seller_id = ?
        ORDER BY p.created_at DESC";
$properties = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $properties[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Get all users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$users = [];

if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - Real Estate Platform</title>
    <link rel="stylesheet" href="../public/css/style.css">
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

    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <h1>Seller Dashboard</h1>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="../dashboard.php">Dashboard</a>
                <a href="../list_property.php">List Property</a>
                <a href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="admin-dashboard">
            <!-- Properties Section -->
            <section class="dashboard-section">
                <h2>All Properties</h2>
                <a href="../add_property.php" class="btn btn-primary">Add New Property</a>
                
                <div class="property-grid">
                    <?php foreach ($properties as $property): ?>
                        <div class="property-card">
                            <div class="property-info">
                                <h3 class="property-title"><?php echo $property['title']; ?></h3>
                                <p class="property-price">Rs <?php echo number_format($property['price']); ?></p>
                                <p class="property-details">
                                    <?php echo $property['property_type']; ?> | 
                                    <?php echo $property['city']; ?>, <?php echo $property['state']; ?>
                                </p>
                                <p>Posted by: <?php echo $property['seller_name']; ?></p>
                                <div class="property-actions">
                                    <a href="edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">Edit</a>
                                    <a href="delete_property.php?id=<?php echo $property['id']; ?>" class="btn btn-danger">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Users Section -->
            <?php
            // Removed the "All Users" section as requested.
            // The original code for displaying users was here:
            /*
            <section class="dashboard-section">
                <h2>All Users</h2>
                <div class="users-list">
                    <?php foreach ($users as $user): ?>
                        <div class="user-card">
                            <h4><?php echo $user['name']; ?></h4>
                            <p>Email: <?php echo $user['email']; ?></p>
                            <p>Role: <?php echo $user['role']; ?></p>
                            <p>Joined: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            */
            ?>
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
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="../search.php?type=buy">Buy</a></li>
                        <li><a href="../search.php?type=rent">Rent</a></li>
                        <li><a href="../add_property.php">Sell</a></li>
                        <li><a href="../contact.php">Contact Us</a></li>
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