<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Check if user is logged in
requireLogin();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $document_type = sanitizeInput($_POST['document_type']);
    
    // Handle document upload
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        $target_dir = "public/uploads/documents/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["document"]["name"], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            $error = "Only PDF, JPG, JPEG, and PNG files are allowed.";
        } else {
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
                // Insert document into database
                $sql = "INSERT INTO documents (user_id, document_type, document_path) VALUES (?, ?, ?)";
                
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "iss", $_SESSION['user_id'], $document_type, $target_file);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $success = "Document uploaded successfully!";
                    } else {
                        $error = "Something went wrong. Please try again later.";
                    }
                }
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        $error = "Please select a document to upload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Document - Real Estate Platform</title>
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

        /* Form Specific Styles (reused from add_property) */
        .document-form {
            background-color: #FFFFFF; /* Slightly lighter dark for form area */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
         .form-group,
        .form-row .form-group {
             margin-bottom: 20px;
        }
         .form-group label {
             color: #333333; /* Light grey for labels */
             display: block;
             margin-bottom: 8px;
             font-weight: 700;
         }
         .form-control {
            width: 100%;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #CCCCCC; /* Lighter border */
            background-color: #FFFFFF; /* White background */
            color: #333333;
            font-size: 1em;
         }
         .form-control:focus {
             outline: none;
             border-color: #333333; /* Changed from Gold highlight on focus */
             box-shadow: 0 0 5px rgba(51, 51, 51, 0.3);
         }

        /* Buttons (reused from add_property) */
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
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="document-form">
            <h2>Upload Document</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Document Type</label>
                    <select name="document_type" class="form-control" required>
                        <option value="id_proof">ID Proof</option>
                        <option value="address_proof">Address Proof</option>
                        <option value="property_documents">Property Documents</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Document File</label>
                    <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    <small class="form-text text-muted">Allowed file types: PDF, JPG, JPEG, PNG</small>
                </div>

                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Upload Document">
                </div>
            </form>
        </div>
    </div>
</body>
</html> 