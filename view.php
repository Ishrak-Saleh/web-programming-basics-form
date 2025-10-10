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
$popup_message = "";

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
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
</head>
<body style="background: linear-gradient(to bottom right, #202127, #414141); min-height: 200vh;" class="flex items-start justify-center p-5">
  <div class="bg-white bg-opacity-10 p-8 rounded-2xl shadow-lg w-full max-w-5xl text-center backdrop-blur-[5px]">
    <h1 class="text-white text-3xl mb-5 shadow-[2px_2px_4px_rgba(0,0,0,0.404)]">User Management</h1>

    <div class="flex justify-around mb-5">
      <button id="btn-list" onclick="showTab('list')" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">List Users</button>
      <button id="btn-register" onclick="showTab('register')" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">Register New User</button>
    </div>

    <!-- LIST -->
    <div id="list-tab" class="block">
      <?php echo $message; ?>
      <div class="overflow-x-auto">
        <table class="w-full table-auto text-center mx-auto border-separate border-spacing-0 mt-5 bg-white bg-opacity-10 rounded-lg overflow-hidden">
          <thead>
            <tr>
              <th class="bg-gray-500 text-white font-semibold p-4">ID</th>
              <th class="bg-gray-500 text-white font-semibold p-4">Full Name</th>
              <th class="bg-gray-500 text-white font-semibold p-4">User Name</th>
              <th class="bg-gray-500 text-white font-semibold p-4">Date of Birth</th>
              <th class="bg-gray-500 text-white font-semibold p-4">Email</th>
              <th class="bg-gray-500 text-white font-semibold p-4">Actions</th>
            </tr>
          </thead>
          <tbody id="user-table">
            <?php foreach ($users as $user): ?>
            <tr data-id="<?php echo htmlspecialchars($user['id']); ?>">
              <td class="p-4 text-indigo-100 border-b border-indigo-300 text-center"><?php echo htmlspecialchars($user['id']); ?></td>
              <td class="p-4 text-indigo-100 border-b border-indigo-300 text-center"><?php echo htmlspecialchars($user['Full Name']); ?></td>
              <td class="p-4 text-indigo-100 border-b border-indigo-300 text-center"><?php echo htmlspecialchars($user['User name']); ?></td>
              <td class="p-4 text-indigo-100 border-b border-indigo-300 text-center"><?php echo htmlspecialchars($user['Date of Birth']); ?></td>
              <td class="p-4 text-indigo-100 border-b border-indigo-300 text-center"><?php echo htmlspecialchars($user['Email']); ?></td>
              <td class="p-4 border-b border-indigo-300">
                <div class="flex justify-center">
                  <button class="bg-gray-500 text-white px-3 py-2 rounded hover:bg-gray-600 transition" onclick="deleteUser(<?php echo htmlspecialchars($user['id']); ?>)">Delete</button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- REGISTER -->
    <div id="register-tab" class="hidden">
      <?php echo $message; ?>
      <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="register-form" class="space-y-4">
        <div>
          <p class="text-indigo-100 font-medium mb-2">Full Name:</p>
          <input type="text" name="fullname" placeholder="Full Name" value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required class="w-full p-3 border border-indigo-300 rounded-lg bg-white bg-opacity-10 text-white focus:outline-none focus:border-gray-400 focus:bg-opacity-30">
        </div>

        <div>
          <p class="text-indigo-100 font-medium mb-2">User Name:</p>
          <input type="text" name="username" placeholder="User Name" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required class="w-full p-3 border border-indigo-300 rounded-lg bg-white bg-opacity-10 text-white focus:outline-none focus:border-gray-400 focus:bg-opacity-30">
        </div>

        <div>
          <p class="text-indigo-100 font-medium mb-2">Date of Birth:</p>
          <input type="date" name="dob" value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>" required class="w-full p-3 border border-indigo-300 rounded-lg bg-white bg-opacity-10 text-white focus:outline-none focus:border-gray-400 focus:bg-opacity-30">
        </div>

        <div>
          <p class="text-indigo-100 font-medium mb-2">Email:</p>
          <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required class="w-full p-3 border border-indigo-300 rounded-lg bg-white bg-opacity-10 text-white focus:outline-none focus:border-gray-400 focus:bg-opacity-30">
        </div>

        <div>
          <p class="text-indigo-100 font-medium mb-2">Password:</p>
          <input type="password" name="password" placeholder="Password" required minlength="6" class="w-full p-3 border border-indigo-300 rounded-lg bg-white bg-opacity-10 text-white focus:outline-none focus:border-gray-400 focus:bg-opacity-30">
        </div>

        <button type="submit" class="w-full p-3 bg-gray-500 text-white rounded-lg font-bold hover:bg-gray-600 transition">Register</button>
      </form>
    </div>
  </div>

  <script>
    function showTab(tab) {
      document.getElementById('list-tab').classList.remove('block');
      document.getElementById('list-tab').classList.add('hidden');
      document.getElementById('register-tab').classList.remove('block');
      document.getElementById('register-tab').classList.add('hidden');

      document.getElementById(tab + '-tab').classList.remove('hidden');
      document.getElementById(tab + '-tab').classList.add('block');
    }

    document.getElementById('register-form').addEventListener('submit', function(e) {
      e.preventDefault();
      const form = this;
      fetch(window.location.href, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: new FormData(form)
      })
      .then(response => response.json())
      .then(data => {
        if (data.popup) {
          alert(data.popup);
          showTab('register');
        } else if (data.success) {
          location.reload();
        } else {
          if (data.message) alert(data.message);
        }
      })
      .catch(err => { form.submit(); });
    });

    function deleteUser(id) {
      if (!confirm('Are you sure you want to delete this user?')) return;
      fetch(window.location.href, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: `action=delete&id=${id}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Delete failed');
      })
      .catch(() => location.reload());
    }
  </script>
</body>
</html>
