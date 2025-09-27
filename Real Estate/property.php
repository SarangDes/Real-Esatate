<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$property_id = sanitizeInput($_GET['id']);

// Get property details
$sql = "SELECT p.*, u.name as seller_name, u.email as seller_email, u.phone as seller_phone 
        FROM properties p 
        JOIN users u ON p.seller_id = u.id 
        WHERE p.id = ?";

$property = null;

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $property_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $property = $row;
    } else {
        $_SESSION['error'] = "Property not found.";
        header("Location: index.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Database error occurred.";
    header("Location: index.php");
    exit();
}

// Get property images
$sql = "SELECT * FROM property_images WHERE property_id = ?";
$images = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $property_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $images[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $property['title']; ?> - Real Estate Platform</title>
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

        /* Property Details Page Specifics */
        .property-details {
            background-color: #FFFFFF; /* White for content area */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        .property-gallery,
        .property-info {
            flex: 1;
            min-width: 300px;
        }
        .property-gallery img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .thumbnail-images img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            margin-right: 10px;
            border-radius: 5px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s ease;
        }
        .thumbnail-images img.active,
        .thumbnail-images img:hover {
            border-color: #CCCCCC; /* Changed from Gold border for active/hover thumbnail */
        }

        .property-price {
            color: #333333; /* Changed from Gold for price */
            font-size: 1.8em;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .property-meta p,
        .property-description p {
            color: #555555; /* Slightly darker grey for details */
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .property-meta strong {
            color: #333333; /* Dark grey for labels */
        }
        .property-contact h3,
        .property-verification h3,
        .property-description h3 {
             color: #333333; /* Changed from Gold for section headings */
             margin-top: 20px;
             margin-bottom: 10px;
        }
         .property-contact p i {
             color: #555555; /* Changed from Gold icons in contact section */
             margin-right: 5px;
         }
         .property-contact a {
             color: #333333; /* Changed from Gold link in contact section */
             text-decoration: none;
             transition: color 0.3s ease;
         }
          .property-contact a:hover {
             color: #555555; /* Slightly darker gold on hover -> Changed to darker grey */
         }
        .verification-doc {
            max-width: 100%;
            height: auto;
            margin-top: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
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

        /* Image Modal */
        .image-modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            padding-top: 50px; /* Location of the box */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.9); /* Black w/ opacity */
        }
        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 900px;
        }
        .modal-image {
            width: 100%;
            height: auto;
            display: block;
        }
        .modal-close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }
        .modal-close:hover,
        .modal-close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }
        .modal-nav button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            padding: 10px 15px;
            font-size: 20px;
            color: white;
            background-color: rgba(0,0,0,0.5);
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .modal-nav button:hover {
            background-color: rgba(0,0,0,0.8);
        }
        .modal-nav button:first-child {
            left: 15px;
        }
        .modal-nav button:last-child {
            right: 15px;
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
        <div class="property-details">
            <!-- Property Images -->
            <div class="property-gallery">
                <?php if (!empty($images)): ?>
                    <div class="main-image">
                        <img src="<?php echo $images[0]['image_path']; ?>" alt="<?php echo $property['title']; ?>" id="mainImage">
                    </div>
                    <div class="thumbnail-images">
                        <?php foreach ($images as $index => $image): ?>
                            <img src="<?php echo $image['image_path']; ?>" 
                                 alt="<?php echo $property['title']; ?>"
                                 class="<?php echo $index === 0 ? 'active' : ''; ?>"
                                 onclick="updateMainImage(this.src, this)">
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="main-image">
                        <img src="public/images/default.jpg" alt="<?php echo $property['title']; ?>" id="mainImage">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Property Information -->
            <div class="property-info">
                <h1><?php echo $property['title']; ?></h1>
                <p class="property-price">Rs <?php echo number_format($property['price']); ?></p>
                
                <div class="property-meta">
                    <p><strong>Property Type:</strong> <?php echo ucfirst($property['property_type']); ?></p>
                    <p><strong>Transaction Type:</strong> <?php echo ucfirst($property['transaction_type']); ?></p>
                    <p><strong>Location:</strong> <?php echo $property['location']; ?></p>
                    <p><strong>City:</strong> <?php echo $property['city']; ?></p>
                    <p><strong>State:</strong> <?php echo $property['state']; ?></p>
                    <p><strong>Area:</strong> <?php echo (isset($property['area']) && $property['area'] !== null ? number_format($property['area']) . ' sq ft' : 'N/A'); ?></p>
                    
                    <?php if ($property['property_type'] === 'flat' || $property['property_type'] === 'house' || $property['property_type'] === 'bungalow'): ?>
                        <p><strong>Bedrooms:</strong> <?php echo (isset($property['bedrooms']) && $property['bedrooms'] !== null ? $property['bedrooms'] : 'N/A'); ?></p>
                        <p><strong>Bathrooms:</strong> <?php echo (isset($property['bathrooms']) && $property['bathrooms'] !== null ? $property['bathrooms'] : 'N/A'); ?></p>
                        <p><strong>Furnishing:</strong> <?php echo (!empty($property['furnishing']) ? ucfirst($property['furnishing']) : 'N/A'); ?></p>
                        
                        <?php if ($property['property_type'] === 'flat'): ?>
                            <p><strong>Floor:</strong> <?php echo (isset($property['floor']) && $property['floor'] !== null ? $property['floor'] : 'N/A'); ?></p>
                            <p><strong>Parking:</strong> <?php echo (!empty($property['parking']) ? ucfirst($property['parking']) : 'N/A'); ?></p>
                        <?php elseif ($property['property_type'] === 'house'): ?>
                            <p><strong>Floors:</strong> <?php echo (isset($property['floors']) && $property['floors'] !== null ? $property['floors'] : 'N/A'); ?></p>
                            <p><strong>Age:</strong> <?php echo (isset($property['age']) && $property['age'] !== null ? $property['age'] . ' years' : 'N/A'); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($property['property_type'] === 'bungalow'): ?>
                            <p><strong>Plot Size:</strong> <?php echo (isset($property['plot_size']) && $property['plot_size'] !== null ? number_format($property['plot_size']) . ' sq ft' : 'N/A'); ?></p>
                        <?php endif; ?>
                        
                    <?php elseif ($property['property_type'] === 'plot'): ?>
                        <p><strong>Plot Size:</strong> <?php echo (isset($property['plot_size']) && $property['plot_size'] !== null ? number_format($property['plot_size']) . ' sq ft' : 'N/A'); ?></p>
                        <p><strong>Plot Type:</strong> <?php echo (!empty($property['plot_type']) ? ucfirst($property['plot_type']) : 'N/A'); ?></p>
                        <p><strong>Facing:</strong> <?php echo (!empty($property['facing']) ? ucfirst($property['facing']) : 'N/A'); ?></p>
                        <p><strong>Boundary Wall:</strong> <?php echo (!empty($property['boundary_wall']) ? ucfirst($property['boundary_wall']) : 'N/A'); ?></p>
                        
                    <?php elseif ($property['property_type'] === 'room'): ?>
                        <p><strong>Room Type:</strong> <?php echo (!empty($property['room_type']) ? ucfirst($property['room_type']) : 'N/A'); ?></p>
                        <p><strong>Furnishing:</strong> <?php echo (!empty($property['furnishing']) ? ucfirst($property['furnishing']) : 'N/A'); ?></p>
                        <p><strong>Floor:</strong> <?php echo (isset($property['floor']) && $property['floor'] !== null ? $property['floor'] : 'N/A'); ?></p>
                        <p><strong>Bathroom Attached:</strong> <?php echo (!empty($property['bathroom_attached']) ? ucfirst($property['bathroom_attached']) : 'N/A'); ?></p>
                    <?php endif; ?>
                </div>

                <div class="property-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br($property['description']); ?></p>
                </div>

                <!-- Contact Information -->
                <?php if (isLoggedIn() && $_SESSION['user_id'] != $property['seller_id']): ?>
                    <div class="property-contact">
                        <h3>Contact Seller</h3>
                        <p><i class="fas fa-user"></i> <?php echo $property['seller_name']; ?></p>
                        <p><i class="fas fa-envelope"></i> <?php echo $property['seller_email']; ?></p>
                        <p><i class="fas fa-phone"></i> <?php echo $property['seller_phone']; ?></p>
                    </div>
                <?php else: ?>
                    <div class="property-contact">
                        <h3>Contact Seller</h3>
                        <?php if (!isLoggedIn()): ?>
                            <p>Please <a href="login.php">log in</a> to view seller contact details.</p>
                        <?php else: /* is logged in but is the seller */ ?>
                             <p>This is your property listing. You can manage it from the <a href="dashboard.php">Dashboard</a>.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Verification Document -->
                <div class="property-verification">
                    <h3>Verification Document</h3>
                    <?php if ($property['verification_doc']): ?>
                        <?php
                        $doc_ext = strtolower(pathinfo($property['verification_doc'], PATHINFO_EXTENSION));
                        if ($doc_ext === 'pdf'): ?>
                            <embed src="<?php echo htmlspecialchars($property['verification_doc']); ?>" type="application/pdf" width="100%" height="600px">
                        <?php else: ?>
                            <img src="<?php echo htmlspecialchars($property['verification_doc']); ?>" alt="Verification Document" class="verification-doc">
                        <?php endif; ?>
                    <?php else: ?>
                        <p>No verification document available.</p>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div class="property-actions">
                    <?php if (isLoggedIn() && $_SESSION['user_id'] == $property['seller_id']): ?>
                        <a href="admin/edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">Edit Property</a>
                        <a href="delete_property.php?id=<?php echo $property['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this property? This action cannot be undone.');">Delete Property</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="image-modal" id="imageModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <img src="" alt="Property Image" class="modal-image" id="modalImage">
            <div class="modal-nav">
                <button onclick="navigateImage(-1)">❮</button>
                <button onclick="navigateImage(1)">❯</button>
            </div>
        </div>
    </div>

    <script>
        let currentImageIndex = 0;
        const images = <?php echo json_encode(array_column($images, 'image_path')); ?>;
        const mainImage = document.getElementById('mainImage');
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');

        function updateMainImage(src, thumbnail) {
            mainImage.src = src;
            currentImageIndex = Array.from(thumbnail.parentElement.children).indexOf(thumbnail);
            
            // Update active state of thumbnails
            document.querySelectorAll('.thumbnail-images img').forEach(img => {
                img.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }

        function openModal() {
            modal.style.display = 'block';
            modalImage.src = mainImage.src;
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function navigateImage(direction) {
            currentImageIndex = (currentImageIndex + direction + images.length) % images.length;
            modalImage.src = images[currentImageIndex];
            mainImage.src = images[currentImageIndex];
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail-images img').forEach((img, index) => {
                img.classList.toggle('active', index === currentImageIndex);
            });
        }

        // Event Listeners
        mainImage.addEventListener('click', openModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (modal.style.display === 'block') {
                if (e.key === 'Escape') {
                    closeModal();
                } else if (e.key === 'ArrowLeft') {
                    navigateImage(-1);
                } else if (e.key === 'ArrowRight') {
                    navigateImage(1);
                }
            }
        });
    </script>

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