<?php
include 'db_connect.php'; // Database connection

// Handle course deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['code'])) {
    $code = $_GET['code'];

    // Prepare SQL delete query
    $sql = "DELETE FROM Course WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $stmt->close();
}

// Fetch all courses
$courses = [];
$result = $conn->query("SELECT * FROM Course");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
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
        .btn {
            background-color: white;
            color: black;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<h2>Course List</h2>

<!-- Course Table -->
<table>
    <tr>
        <th>Course Code</th>
        <th>Course Name</th>
        <th>Year</th>
        <th>Semester</th>
        <th>Department Name</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($courses as $course): ?>
    <tr>
        <td><?php echo htmlspecialchars($course['code']); ?></td>
        <td><?php echo htmlspecialchars($course['name']); ?></td>
        <td><?php echo htmlspecialchars($course['year']); ?></td>
        <td><?php echo htmlspecialchars($course['semester']); ?></td>
        <td><?php echo htmlspecialchars($course['department_name']); ?></td>
        <td>
            <a href="edit_course.php?code=<?php echo urlencode($course['code']); ?>" class="btn">Edit</a>
            <a href="?action=delete&code=<?php echo urlencode($course['code']); ?>" class="btn" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
