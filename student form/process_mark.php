<?php
include 'db_connect.php'; // Database connection

// Collect form data
$student_id = $_POST['student_id'];
$course_code = $_POST['course_code'];
$mark = $_POST['mark'];
$grade = $_POST['grade'];
$comment = $_POST['comment'];

// Prepare SQL insert query
$sql = "INSERT INTO Mark (student_id, course_code, mark, grade, comment) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind and execute
$stmt->bind_param("isiss", $student_id, $course_code, $mark, $grade, $comment);
if ($stmt->execute()) {
    echo "Mark added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mark Management</title>
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
        input[type="text"], input[type="number"], input[type="submit"] {
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
            if (confirm("Are you sure you want to delete this mark?")) {
                window.location.href = `process_mark.php?action=delete&id=${id}`;
            }
        }
    </script>
</head>
<body>

<?php
include 'db_connect.php'; // Database connection

// Handle different actions
$action = isset($_GET['action']) ? $_GET['action'] : 'view';

// Create or update mark
if ($action === 'add' || $action === 'edit') {
    $mark_id = isset($_GET['id']) ? $_GET['id'] : null;
    $student_id = '';
    $course_code = '';
    $mark = '';
    $grade = '';
    $comment = '';

    // If editing, fetch the current mark details
    if ($action === 'edit' && $mark_id) {
        $result = $conn->query("SELECT * FROM Mark WHERE id = $mark_id");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $student_id = $row['student_id'];
            $course_code = $row['course_code'];
            $mark = $row['mark'];
            $grade = $row['grade'];
            $comment = $row['comment'];
        }
    }

    // Handle form submission for add/edit
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $student_id = $_POST['student_id'];
        $course_code = $_POST['course_code'];
        $mark = $_POST['mark'];
        $grade = $_POST['grade'];
        $comment = $_POST['comment'];

        if ($action === 'add') {
            // Insert new mark
            $sql = "INSERT INTO Mark (student_id, course_code, mark, grade, comment) VALUES (?, ?, ?, ?, ?)";
        } else {
            // Update existing mark
            $sql = "UPDATE Mark SET student_id=?, course_code=?, mark=?, grade=?, comment=? WHERE id=?";
        }

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        if ($action === 'add') {
            $stmt->bind_param("isiss", $student_id, $course_code, $mark, $grade, $comment);
        } else {
            $stmt->bind_param("isissi", $student_id, $course_code, $mark, $grade, $comment, $mark_id);
        }

        if ($stmt->execute()) {
            echo "<p class='success-message'>Mark saved successfully!</p>";
        } else {
            echo "<p class='error-message'>Error: " . $stmt->error . "</p>";
        }
        
        $stmt->close();
    }

    // Form for adding or editing marks
    ?>
    <h2><?php echo $action === 'add' ? 'Add Mark' : 'Edit Mark'; ?></h2>
    <form method="POST">
        Student ID: <input type="number" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>" required><br>
        Course Code: <input type="text" name="course_code" value="<?php echo htmlspecialchars($course_code); ?>" required><br>
        Mark: <input type="number" name="mark" value="<?php echo htmlspecialchars($mark); ?>" required><br>
        Grade: <input type="text" name="grade" value="<?php echo htmlspecialchars($grade); ?>" required><br>
        Comment: <input type="text" name="comment" value="<?php echo htmlspecialchars($comment); ?>" required><br>
        <input type="submit" value="<?php echo $action === 'add' ? 'Add Mark' : 'Update Mark'; ?>">
    </form>
    <?php
}

// Displaying marks
if ($action === 'view' || $action === 'delete') {
    echo "<h2>Marks List</h2>";
    echo "<table>
            <tr>
                <th>ID</th>
                <th>Student ID</th>
                <th>Course Code</th>
                <th>Mark</th>
                <th>Grade</th>
                <th>Comment</th>
                <th>Actions</th>
            </tr>";

    $result = $conn->query("SELECT * FROM Mark");
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['student_id']}</td>
                <td>{$row['course_code']}</td>
                <td>{$row['mark']}</td>
                <td>{$row['grade']}</td>
                <td>{$row['comment']}</td>
                <td>
                    <a href='process_mark.php?action=edit&id={$row['id']}'>Edit</a>
                    <a href='#' onclick='confirmDelete({$row['id']})'>Delete</a>
                </td>
            </tr>";
    }
    echo "</table>";
}

// Handle deletion of a mark
if ($action === 'delete' && isset($_GET['id'])) {
    $mark_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM Mark WHERE id=?");
    $stmt->bind_param("i", $mark_id);

    if ($stmt->execute()) {
        echo "<p class='success-message'>Mark deleted successfully!</p>";
    } else {
        echo "<p class='error-message'>Error deleting record: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

$conn->close();
?>

</body>
</html>
