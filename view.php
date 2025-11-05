<?php
include 'db.php';

$message = "";
$popup_message = "";

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // ======= UPDATE USER =======
  if (isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $fullname = trim($_POST['fullname'] ?? '');
    $form_username = trim($_POST['username'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $email = trim($_POST['email'] ?? '');

    if ($id && $fullname && $form_username && $dob && $email) {
      $stmt = $conn->prepare("UPDATE tbl_regi_form SET `Full Name`=?, `User name`=?, `Date of Birth`=?, Email=? WHERE id=?");
      $stmt->bind_param("ssssi", $fullname, $form_username, $dob, $email, $id);
      if ($stmt->execute()) {
        $msg = "User updated successfully!";
        $success = true;
      } else {
        $msg = "Error updating user: " . htmlspecialchars($conn->error);
        $success = false;
      }
      $stmt->close();
    } else {
      $msg = "All fields are required.";
      $success = false;
    }

    if ($is_ajax) {
      header('Content-Type: application/json');
      echo json_encode(['success' => $success, 'message' => $msg]);
      $conn->close();
      exit();
    }
  }

  // ======= DELETE USER =======
  if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id === false) {
      $msg = "Invalid user ID.";
      $success = false;
    } else {
      $stmt = $conn->prepare("DELETE FROM tbl_regi_form WHERE id = ?");
      $stmt->bind_param("i", $id);
      if ($stmt->execute()) {
        $msg = "User deleted successfully!";
        $success = true;
      } else {
        $msg = "Error deleting user: " . htmlspecialchars($conn->error);
        $success = false;
      }
      $stmt->close();
    }

    if ($is_ajax) {
      header('Content-Type: application/json');
      echo json_encode(['success' => $success, 'message' => $msg]);
      $conn->close();
      exit();
    } else {
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
    }
  }

  // ======= REGISTER NEW USER =======
  $fullname = trim($_POST['fullname'] ?? '');
  $form_username = trim($_POST['username'] ?? ''); 
  $dob = $_POST['dob'] ?? '';
  $email = trim($_POST['email'] ?? '');
  $form_password = $_POST['password'] ?? '';       

  if ($fullname && $form_username && $dob && $email && $form_password) {
    $check_email_stmt = $conn->prepare("SELECT id FROM tbl_regi_form WHERE Email = ?");
    $check_email_stmt->bind_param("s", $email);
    $check_email_stmt->execute();
    $email_result = $check_email_stmt->get_result();

    $check_username_stmt = $conn->prepare("SELECT id FROM tbl_regi_form WHERE `User name` = ?");
    $check_username_stmt->bind_param("s", $form_username);
    $check_username_stmt->execute();
    $username_result = $check_username_stmt->get_result();

    if ($email_result->num_rows > 0 && $username_result->num_rows > 0) {
      $popup_message = "Both username and email already exist. Please use different ones.";
      $success = false;
    } elseif ($email_result->num_rows > 0) {
      $popup_message = "Email already exists. Please use a different one.";
      $success = false;
    } elseif ($username_result->num_rows > 0) {
      $popup_message = "Username already exists. Please use a different one.";
      $success = false;
    } else {
      $hashed_pass = password_hash($form_password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO tbl_regi_form (`Full Name`, `User name`, `Date of Birth`, Email, Password) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("sssss", $fullname, $form_username, $dob, $email, $hashed_pass);

      if ($stmt->execute()) {
        $message = "<p class='text-green-500 text-center'>Registration successful!</p>";
        $success = true;
      } else {
        $message = "<p class='text-red-500 text-center'>Error: " . htmlspecialchars($conn->error) . "</p>";
        $success = false;
      }
      $stmt->close();
    }

    $check_email_stmt->close();
    $check_username_stmt->close();

    if ($is_ajax) {
      header('Content-Type: application/json');
      if (!empty($popup_message)) {
        echo json_encode(['success' => false, 'popup' => $popup_message]);
      } else {
        echo json_encode(['success' => (bool)$success, 'message' => strip_tags($message)]);
      }
      $conn->close();
      exit();
    }
  } else {
    $message = "<p class='text-red-500 text-center'>All fields are required.</p>";
    if ($is_ajax) {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => 'All fields are required.']);
      $conn->close();
      exit();
    }
  }
}

$conn->close();

include 'db.php';
$result = $conn->query("SELECT id, `Full Name`, `User name`, `Date of Birth`, Email, Password FROM tbl_regi_form");
$users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registration Form</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="main-container">
    <h1 class="title">User Management</h1>

    <div class="flex justify-around mb-5">
      <button id="btn-list" onclick="showTab('list')" class="tab-btn">List Users</button>
      <button id="btn-register" onclick="showTab('register')" class="tab-btn">Register / Edit User</button>
    </div>

    <div id="list-tab" class="block">
      <?php echo $message; ?>
      <div class="overflow-x-auto">
        <table class="user-table">
          <thead>
            <tr>
              <th>ID</th><th>Full Name</th><th>User Name</th><th>Date of Birth</th><th>Email</th><th>Actions</th>
            </tr>
          </thead>
          <tbody id="user-table">
            <?php foreach ($users as $user): ?>
            <tr data-id="<?php echo htmlspecialchars($user['id']); ?>">
              <td><?php echo htmlspecialchars($user['id']); ?></td>
              <td><?php echo htmlspecialchars($user['Full Name']); ?></td>
              <td><?php echo htmlspecialchars($user['User name']); ?></td>
              <td><?php echo htmlspecialchars($user['Date of Birth']); ?></td>
              <td><?php echo htmlspecialchars($user['Email']); ?></td>
              <td>
                <div class="flex justify-center space-x-2">
                  <button class="edit-btn" onclick='editUser(<?php echo json_encode($user); ?>)'>Edit</button>
                  <button class="delete-btn" onclick="deleteUser(<?php echo htmlspecialchars($user['id']); ?>)">Delete</button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div id="register-tab" class="hidden">
      <?php echo $message; ?>
      <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="register-form" class="space-y-4">
        <input type="hidden" name="id" id="user_id" value="">
        <input type="hidden" name="action" id="form_action" value="register">

        <div><p>Full Name:</p><input type="text" name="fullname" placeholder="Full Name" required></div>
        <div><p>User Name:</p><input type="text" name="username" placeholder="User Name" required></div>
        <div><p>Date of Birth:</p><input type="date" name="dob" required></div>
        <div><p>Email:</p><input type="email" name="email" placeholder="Email" required></div>

        <div id="password-field">
          <p>Password:</p>
          <input type="password" name="password" placeholder="Password" required minlength="6">
        </div>

        <button type="submit" class="submit-btn">Save</button>
      </form>
    </div>
  </div>
  <script src="script.js"></script>
</body>
</html>
