<?php
$servername = "localhost";
$username = "root"; // Adjust if necessary
$password = "";
$dbname = "school_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert data into the student table
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $program = $_POST['program'];
    $access_no = $_POST['access_no'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $sex = $_POST['sex'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $age = $_POST['age'];
    $department_name = $_POST['department_name'];

    $stmt = $conn->prepare("INSERT INTO student (name, program, access_no, address, contact, sex, email, username, password, age, department_name) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissssssis", $name, $program, $access_no, $address, $contact, $sex, $email, $username, $password, $age, $department_name);

    if ($stmt->execute()) {
        echo "<p class='success-message'>New student record created successfully.</p>";
    } else {
        echo "<p class='error-message'>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h2 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 80%;
            margin-top: 20px;
            background-color: #fff;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #21A3F1;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        form {
            background-color: #fff;
            padding: 20px;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            width: 50%;
        }
        input[type="text"], input[type="number"], input[type="email"], input[type="password"], input[type="submit"] {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .action-links a {
            margin-right: 10px;
            color: #4CAF50;
            text-decoration: none;
        }
        .action-links a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this student?")) {
                window.location.href = `process_student.php?action=delete&id=${id}`;
            }
        }
    </script>
</head>
<body>

<?php
$servername = "localhost";
$username = "root"; // Adjust if necessary
$password = "";
$dbname = "school_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$action = isset($_GET['action']) ? $_GET['action'] : 'view';

if ($action == 'add' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $program = $_POST['program'];
    $access_no = $_POST['access_no'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $sex = $_POST['sex'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $age = $_POST['age'];
    $department_name = $_POST['department_name'];

    $sql = "INSERT INTO student (name, program, access_no, address, contact, sex, email, username, password, age, department_name) 
            VALUES ('$name', '$program', '$access_no', '$address', '$contact', '$sex', '$email', '$username', '$password', $age, '$department_name')";

    if ($conn->query($sql) === TRUE) {
        echo "<p>New student record created successfully.</p>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

if ($action == 'view') {
    $result = $conn->query("SELECT * FROM student");

    echo "<h2>Student Records</h2>";
    echo "<table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Program</th>
                <th>Access No</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['name']}</td>
                <td>{$row['program']}</td>
                <td>{$row['access_no']}</td>
                <td>{$row['contact']}</td>
                <td>{$row['email']}</td>
                <td class='action-links'>
                    <a href='process_student.php?action=edit&id={$row['id']}'>Edit</a>
                    <a href='#' onclick='confirmDelete({$row['id']})'>Delete</a>
                </td>
            </tr>";
    }
    echo "</table>";
}

if ($action == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM student WHERE id=$id");
    $student = $result->fetch_assoc();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $program = $_POST['program'];
        $access_no = $_POST['access_no'];
        $address = $_POST['address'];
        $contact = $_POST['contact'];
        $sex = $_POST['sex'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $age = $_POST['age'];
        $department_name = $_POST['department_name'];

        $sql = "UPDATE student SET 
                name='$name', program='$program', access_no='$access_no', address='$address', 
                contact='$contact', sex='$sex', email='$email', username='$username', 
                password='$password', age=$age, department_name='$department_name' 
                WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            echo "<p>Student record updated successfully.</p>";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        ?>
        <h2>Edit Student</h2>
        <form method="POST">
            Name: <input type="text" name="name" value="<?php echo $student['name']; ?>"><br>
            Program: <input type="text" name="program" value="<?php echo $student['program']; ?>"><br>
            Access No: <input type="text" name="access_no" value="<?php echo $student['access_no']; ?>"><br>
            Address: <input type="text" name="address" value="<?php echo $student['address']; ?>"><br>
            Contact: <input type="text" name="contact" value="<?php echo $student['contact']; ?>"><br>
            Sex: <input type="text" name="sex" value="<?php echo $student['sex']; ?>"><br>
            Email: <input type="email" name="email" value="<?php echo $student['email']; ?>"><br>
            Username: <input type="text" name="username" value="<?php echo $student['username']; ?>"><br>
            Password: <input type="password" name="password"><br>
            Age: <input type="number" name="age" value="<?php echo $student['age']; ?>"><br>
            Department Name: <input type="text" name="department_name" value="<?php echo $student['department_name']; ?>"><br>
            <input type="submit" value="Update">
        </form>
        <?php
    }
}

// Handle deleting a student
if ($action == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM student WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<p class='success-message'>Student record deleted successfully.</p>";
    } else {
        echo "<p class='error-message'>Error deleting record: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

$conn->close();
?>
</body>
</html>
