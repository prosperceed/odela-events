<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$trade_filter = isset($_GET['trade']) ? trim($_GET['trade']) : '';

// Build query
$query = "SELECT id, fullname, email, dob, trade_area, department, status, created_at FROM applicants WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (fullname LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($trade_filter)) {
    $query .= " AND trade_area = ?";
    $params[] = $trade_filter;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

// Execute query
if (!empty($types)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applicants - Wing Commander</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include "header.php"; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Manage Applicants</h1>
            <a href="dashboard.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Search Name/Email</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600" placeholder="Enter name or email">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Filter by Trade Area</label>
                    <select name="trade" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600">
                        <option value="">All Trade Areas</option>
                        <option value="Welding & Fabrication" <?php echo $trade_filter === 'Welding & Fabrication' ? 'selected' : ''; ?>>Welding & Fabrication</option>
                        <option value="Electrical Installation" <?php echo $trade_filter === 'Electrical Installation' ? 'selected' : ''; ?>>Electrical Installation</option>
                        <option value="Automotive Technology" <?php echo $trade_filter === 'Automotive Technology' ? 'selected' : ''; ?>>Automotive Technology</option>
                        <option value="ICT & Digital Skills" <?php echo $trade_filter === 'ICT & Digital Skills' ? 'selected' : ''; ?>>ICT & Digital Skills</option>
                        <option value="Plumbing & Pipes" <?php echo $trade_filter === 'Plumbing & Pipes' ? 'selected' : ''; ?>>Plumbing & Pipes</option>
                        <option value="Painting & Finishing" <?php echo $trade_filter === 'Painting & Finishing' ? 'selected' : ''; ?>>Painting & Finishing</option>
                        <option value="Carpentry & Wood Work" <?php echo $trade_filter === 'Carpentry & Wood Work' ? 'selected' : ''; ?>>Carpentry & Wood Work</option>
                        <option value="HVAC Systems" <?php echo $trade_filter === 'HVAC Systems' ? 'selected' : ''; ?>>HVAC Systems</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <a href="applicants.php" class="flex-1 bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500 text-center">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Applicants Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Trade Area</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">DOB</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date Applied</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $status_color = 'bg-green-100 text-green-800';
                                if ($row['status'] === 'Inactive') $status_color = 'bg-yellow-100 text-yellow-800';
                                if ($row['status'] === 'Graduated') $status_color = 'bg-blue-100 text-blue-800';
                                
                                echo '<tr class="border-b hover:bg-gray-50">';
                                echo '<td class="px-6 py-4 text-gray-900 font-semibold">' . htmlspecialchars($row['fullname']) . '</td>';
                                echo '<td class="px-6 py-4 text-gray-600">' . htmlspecialchars($row['email']) . '</td>';
                                echo '<td class="px-6 py-4 text-gray-600">' . htmlspecialchars($row['trade_area']) . '</td>';
                                echo '<td class="px-6 py-4 text-gray-600">' . htmlspecialchars($row['department']) . '</td>';
                                echo '<td class="px-6 py-4 text-gray-600">' . date('M d, Y', strtotime($row['dob'])) . '</td>';
                                echo '<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-sm font-semibold ' . $status_color . '">' . htmlspecialchars($row['status']) . '</span></td>';
                                echo '<td class="px-6 py-4 text-gray-600">' . date('M d, Y', strtotime($row['created_at'])) . '</td>';
                                echo '<td class="px-6 py-4 text-center">';
                                echo '<a href="delete_applicant.php?id=' . $row['id'] . '" onclick="return confirm(\'Delete this applicant?\');" class="inline-block bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 mr-2">';
                                echo '<i class="fas fa-trash mr-1"></i>Delete';
                                echo '</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-600">No applicants found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <?php $conn->close(); ?>
