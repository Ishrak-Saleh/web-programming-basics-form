<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "webprogramming01";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $fullname = $_POST['fullname'];
  $username = $_POST['username'];
  $dob = $_POST['dob'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO tbl_regi_form (`Full Name`, `User Name`, `Date of Birth`, Email, Password) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $fullname, $username, $dob, $email, $password);

  if ($stmt->execute()) {
    header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
    exit();
  } else {
    header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode($conn->error));
    exit();
  }
  
  $stmt->close();
}

$conn->close();

if (isset($_GET['success'])) {
  $message = "<p style='color:green; text-align:center;'>Registration successful!</p>";
} elseif (isset($_GET['error'])) {
  $message = "<p style='color:red; text-align:center;'>Error: " . htmlspecialchars($_GET['error']) . "</p>";
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Registration Form</title>
    <link rel="stylesheet" type="text/css" href="./style.css">
</head>
<body>
    <div class="registration-form">
        <h1>Registration Form</h1>
        
        <?php if (!empty($errors)): ?>
            <div style="color: red;">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div style="color: green;">
                <p><?php echo $success; ?></p>
            </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <p>Full Name: </p>
            <input type="text" name="fullname" placeholder="Full Name" value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" required>
            
            <p>User Name: </p>
            <input type="text" name="username" placeholder="User Name" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            
            <p>Date of Birth: </p>
            <input type="date" name="dob" value="<?php echo isset($_POST['dob']) ? $_POST['dob'] : ''; ?>" required>
            
            <p>Email: </p>
            <input type="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            
            <p>Password: </p>
            <input type="password" name="password" placeholder="Password" required minlength="6">
            
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>