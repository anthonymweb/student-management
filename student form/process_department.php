<?php
include 'db_connect.php'; // Database connection

// Collect form data
$name = $_POST['name'];
$head = $_POST['head'];

// Check if the department already exists
$checkSql = "SELECT * FROM Department WHERE name = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("s", $name);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    // Department already exists
    echo "Error: Department with this name already exists!";
} else {
    // Prepare SQL insert query
    $sql = "INSERT INTO Department (name, head) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind and execute
    $stmt->bind_param("ss", $name, $head);
    if ($stmt->execute()) {
        echo "Department added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$checkStmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Department Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h2 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
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
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
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
        .success-message {
            color: green;
        }
        .error-message {
            color: red;
        }
    </style>
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this department?")) {
                window.location.href = `process_department.php?action=delete&id=${id}`;
            }
        }
    </script>
</head>
<body>

<?php
include 'db_connect.php'; // Database connection

// Handle different actions
$action = isset($_GET['action']) ? $_GET['action'] : 'view';

// Create or update department
if ($action === 'add' || $action === 'edit') {
    $department_id = isset($_GET['id']) ? $_GET['id'] : null;
    $name = '';
    $head = '';

    // If editing, fetch the current department details
    if ($action === 'edit' && $department_id) {
        $result = $conn->query("SELECT * FROM Department WHERE id = $department_id");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $name = $row['name'];
            $head = $row['head'];
        }
    }

    // Handle form submission for add/edit
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $head = $_POST['head'];

        if ($action === 'add') {
            // Check if the department already exists
            $checkSql = "SELECT * FROM Department WHERE name = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $name);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                // Department already exists
                echo "<p class='error-message'>Error: Department with this name already exists!</p>";
            } else {
                // Prepare SQL insert query
                $sql = "INSERT INTO Department (name, head) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    die("Error preparing statement: " . $conn->error);
                }

                // Bind and execute
                $stmt->bind_param("ss", $name, $head);
                if ($stmt->execute()) {
                    echo "<p class='success-message'>Department added successfully!</p>";
                } else {
                    echo "<p class='error-message'>Error: " . $stmt->error . "</p>";
                }

                $stmt->close();
            }

            $checkStmt->close();
        } else {
            // Update existing department
            if ($department_id !== null) { // Check if department_id is set
                $sql = "UPDATE Department SET name=?, head=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    die("Error preparing statement: " . $conn->error);
                }

                $stmt->bind_param("ssi", $name, $head, $department_id);
                if ($stmt->execute()) {
                    echo "<p class='success-message'>Department updated successfully!</p>";
                } else {
                    echo "<p class='error-message'>Error: " . $stmt->error . "</p>";
                }

                $stmt->close();
            }
        }
    }

    // Form for adding or editing departments
    ?>
    <h2><?php echo $action === 'add' ? 'Add Department' : 'Edit Department'; ?></h2>
    <form method="POST">
        Name: <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required><br>
        Head: <input type="text" name="head" value="<?php echo htmlspecialchars($head); ?>" required><br>
        <input type="submit" value="<?php echo $action === 'add' ? 'Add Department' : 'Update Department'; ?>">
    </form>
    <?php
}

// Displaying departments
if ($action === 'view' || $action === 'delete') {
    echo "<h2>Departments List</h2>";
    echo "<table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Head</th>
                <th>Actions</th>
            </tr>";

    $result = $conn->query("SELECT * FROM Department");
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['name']}</td>
                <td>{$row['head']}</td>
                <td>
                    <a href='process_department.php?action=edit&id={$row['id']}'>Edit</a>
                    <a href='#' onclick='confirmDelete({$row['id']})'>Delete</a>
                </td>
            </tr>";
    }
    echo "</table>";
}

// Handle deletion of a department
if ($action === 'delete' && isset($_GET['id'])) {
    $department_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM Department WHERE id=?");
    $stmt->bind_param("i", $department_id);

    if ($stmt->execute()) {
        echo "<p class='success-message'>Department deleted successfully!</p>";
    } else {
        echo "<p class='error-message'>Error deleting record: " . $stmt->error . "</p>";
    }

    $stmt->close();
}


$conn->close();
?>

</body>
</html>
