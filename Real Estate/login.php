<?php
require_once 'config/database.php';
require_once 'config/session.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // First check if website column exists
    $check_column = "SHOW COLUMNS FROM users LIKE 'website'";
    $column_exists = false;
    if ($result = mysqli_query($conn, $check_column)) {
        $column_exists = mysqli_num_rows($result) > 0;
    }

    // Modify query based on whether website column exists
    if ($column_exists) {
        $sql = "SELECT id, name, email, password, role, website FROM users WHERE email = ?";
    } else {
        $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
    }
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) == 1) {
                if ($column_exists) {
                    mysqli_stmt_bind_result($stmt, $id, $name, $email, $hashed_password, $role, $website);
                } else {
                    mysqli_stmt_bind_result($stmt, $id, $name, $email, $hashed_password, $role);
                    $website = null;
                }
                
                if (mysqli_stmt_fetch($stmt)) {
                    if (password_verify($password, $hashed_password)) {
                        session_start();
                        
                        $_SESSION["user_id"] = $id;
                        $_SESSION["user_name"] = $name;
                        $_SESSION["user_email"] = $email;
                        $_SESSION["role"] = $role;
                        $_SESSION["user_website"] = $website;
                        
                        if ($role === 'admin') {
                            header("Location: admin/index.php");
                        } else {
                            header("Location: dashboard.php");
                        }
                        exit();
                    } else {
                        $error = "Invalid email or password.";
                    }
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Oops! Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Real Estate Platform</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #F8F8F8; /* Dark background */
            color: #333333; /* Light text color */
            font-family: 'Lato', sans-serif;
        }
         h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            color: #D4AF37; /* Gold for headings */
        }
        .login-form {
            background-color: #FFFFFF; /* Slightly lighter dark for form area */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
             max-width: 500px;
             margin: 50px auto;
        }
         .login-form h2 {
             color: #D4AF37; /* Gold for form title */
             margin-bottom: 20px;
             text-align: center;
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
             border-color: #D4AF37; /* Gold highlight on focus */
             box-shadow: 0 0 5px rgba(212, 175, 55, 0.3);
         }
         .btn-primary {
            background-color: #D4AF37; /* Gold button */
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
            background-color: #c2a032; /* Slightly darker gold on hover */
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
         .login-form p a {
             color: #D4AF37; /* Gold link */
             text-decoration: none;
             transition: color 0.3s ease;
         }
         .login-form p a:hover {
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
        <div class="login-form">
            <h2>Login</h2>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                <div class="form-group mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                    <div class="invalid-feedback">
                        Please enter a valid email address.
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="invalid-feedback">
                        Please enter your password.
                    </div>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Login">
                </div>
                <p class="mt-3">Don't have an account? <a href="register.php">Sign up now</a></p>
            </form>
        </div>
    </div>

   
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html> 