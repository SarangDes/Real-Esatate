<?php
require_once 'config/database.php';
require_once 'config/session.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitizeInput($_POST['role']);
    $phone = sanitizeInput($_POST['phone']);

    // Validate phone number
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must be exactly 10 digits.";
    }
    // Validate password
    else if ($password != $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = "This email is already registered.";
            } else {
                // Insert new user
                $sql = "INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashed_password, $role, $phone);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        header("location: login.php");
                    } else {
                        $error = "Something went wrong. Please try again later.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Real Estate Platform</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        body {
            background-color: #F8F8F8; /* Light background */
            color: #333333; /* Darker text color */
            font-family: 'Lato', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            color: #D4AF37; /* Gold for headings */
        }
        .register-form {
            background-color: #FFFFFF; /* White for form area */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
             max-width: 500px;
             margin: 50px auto;
        }
         .register-form h2 {
             color: #D4AF37; /* Gold for form title */
             margin-bottom: 20px;
             text-align: center;
         }
         .form-group label {
             color: #333333; /* Darker grey for labels */
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
             border-color: #D4AF37; /* Gold highlight on focus */
             box-shadow: 0 0 5px rgba(212, 175, 55, 0.3);
         }
         .btn-primary {
            background-color: #D4AF37;
            border-color: #D4AF37;
             color: #1E1E1E; /* Dark text */
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
            background-color: #c2a032;
            border-color: #c2a032;
             color: #1E1E1E;
        }
        .alert-danger {
            background-color: #dc3545; /* Keep default danger color */
            color: white;
            border-color: #dc3545;
        }
        .container { /* Ensure container styles don't conflict */
             max-width: 1200px;
             margin: 20px auto;
             padding: 0 15px;
        }
         .register-form p a {
             color: #D4AF37; /* Gold link */
             text-decoration: none;
             transition: color 0.3s ease;
         }
         .register-form p a:hover {
             color: #c2a032; /* Slightly darker gold on hover */
         }

         /* Basic Footer Style */
        footer {
            background-color: #005A2B; /* Deep green */
            color: white;
            padding: 20px 0;
            text-align: center;
            position: relative;
            bottom: 0;
            width: 100%;
        }
         footer p,
        footer a {
             color: white;
             text-decoration: none;
             margin: 0 10px;
         }
         footer a:hover {
             color: #D4AF37;
         }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-form">
            <h2>Register</h2>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
               
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" class="form-control" pattern="[0-9]{10}" maxlength="10" placeholder="Enter 10 digit phone number" required>
                    <small class="form-text">Please enter exactly 10 digits</small>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control" required>
                        <option value="buyer">Buyer</option>
                        <option value="seller">Seller</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Register">
                </div>
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </form>
        </div>
    </div>
    
</body>
</html> 