<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Check if property ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$property_id = sanitizeInput($_GET['id']);

// Get property details to verify ownership
$sql = "SELECT * FROM properties WHERE id = ? AND seller_id = ?";
$property = null;

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $property_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $property = $row;
    } else {
        header("Location: dashboard.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Delete property images first
        $sql = "SELECT image_path FROM property_images WHERE property_id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $property_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                if (file_exists($row['image_path'])) {
                    unlink($row['image_path']);
                }
            }
        }
        
        // Delete property images from database
        $sql = "DELETE FROM property_images WHERE property_id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $property_id);
            mysqli_stmt_execute($stmt);
        }
        
        // Delete property
        $sql = "DELETE FROM properties WHERE id = ? AND seller_id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $property_id, $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                mysqli_commit($conn);
                $_SESSION['success'] = "Property deleted successfully.";
                header("Location: dashboard.php");
                exit();
            } else {
                throw new Exception("Failed to delete property.");
            }
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error deleting property: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Property - Real Estate Platform</title>
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

        /* Delete Confirmation Specific Styles */
        .delete-confirmation {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: #FFFFFF; /* Slightly lighter dark for the box */
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: #333333; /* Light text */
        }

        .delete-confirmation h2 {
            color: #333333; /* Changed from Gold for heading */
            margin-bottom: 1.5rem;
        }

        .property-details {
            margin: 1.5rem 0;
            padding: 1rem;
            background: #EEEEEE; /* Slightly lighter dark for details box */
            border-radius: 4px;
             color: #555555; /* Lighter grey for details */
        }

        .confirmation-message {
            margin: 1.5rem 0;
            padding: 1rem;
            background: #444400; /* Darker gold/brown for warning */
            border: 1px solid #555555; /* Changed from Gold border */
            border-radius: 4px;
            color: #EEEEEE; /* Light text */
        }

        .delete-form {
            margin-top: 2rem;
        }

        /* Buttons */
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
             margin-right: 1rem; /* Keep spacing */
        }
        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d; /* Keep default secondary color */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
             transition: background-color 0.3s ease;
            cursor: pointer;
            font-family: 'Lato', sans-serif;
            font-weight: 700;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
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
        <div class="delete-confirmation">
            <h2>Delete Property</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="property-details">
                <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                <p><strong>Property Type:</strong> <?php echo ucfirst($property['property_type']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></p>
                <p><strong>Price:</strong> $<?php echo number_format($property['price']); ?></p>
            </div>

            <div class="confirmation-message">
                <p>Are you sure you want to delete this property? This action cannot be undone.</p>
                <p>All associated images will also be deleted.</p>
            </div>

            <form action="delete_property.php?id=<?php echo $property_id; ?>" method="POST" class="delete-form">
                <div class="form-group">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">Yes, Delete Property</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 