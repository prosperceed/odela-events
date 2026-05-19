<?php
session_start();
include '../config/db.php';

$checkColumn = function($table, $column) use ($conn) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '" . $conn->real_escape_string($column) . "'");
    return $result && $result->num_rows > 0;
};

$messages_has_admin_reply = $checkColumn('messages', 'admin_reply');
$messages_has_replied_at = $checkColumn('messages', 'replied_at');

if (!$messages_has_admin_reply) {
    $conn->query("ALTER TABLE messages ADD COLUMN admin_reply TEXT NULL");
    $messages_has_admin_reply = $checkColumn('messages', 'admin_reply');
}

if (!$messages_has_replied_at) {
    $conn->query("ALTER TABLE messages ADD COLUMN replied_at DATETIME NULL");
    $messages_has_replied_at = $checkColumn('messages', 'replied_at');
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'reply_message'
) {
    $msg_id = intval($_POST['message_id'] ?? 0);
    $reply = trim($_POST['reply'] ?? '');

    if ($msg_id <= 0 || $reply === '') {
        header("Location: messages.php?error=Reply cannot be empty");
        exit();
    }

    $reply_safe = $conn->real_escape_string($reply);
    $sql = "UPDATE messages SET admin_reply='$reply_safe', status='Read'";
    if ($messages_has_replied_at) {
        $sql .= ", replied_at=NOW()";
    }
    $sql .= " WHERE id=$msg_id";
    $conn->query($sql);
    header("Location: messages.php?success=Reply saved");
    exit();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Mark message as read if message ID is provided
if (isset($_GET['view'])) {
    $msg_id = intval($_GET['view']);
    $stmt = $conn->prepare("UPDATE messages SET status = 'Read' WHERE id = ?");
    $stmt->bind_param("i", $msg_id);
    $stmt->execute();
    $stmt->close();
}

// Delete message if requested
if (isset($_GET['delete'])) {
    $msg_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $msg_id);
    $stmt->execute();
    $stmt->close();
    header("Location: messages.php?success=Message deleted");
    exit();
}

// Get all messages
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$query = "SELECT id, fullname, email, subject, message, status, created_at" . ($messages_has_admin_reply ? ", admin_reply, replied_at" : "") . " FROM messages WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (fullname LIKE ? OR email LIKE ? OR subject LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($status_filter) && $status_filter !== 'All') {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

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
    <title>Manage Messages - Wing Commander</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include '../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Manage Messages</h1>
            <a href="dashboard.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Search Name/Email/Subject</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600" placeholder="Enter search term">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Filter by Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600">
                        <option value="All" <?php echo $status_filter === 'All' ? 'selected' : ''; ?>>All Messages</option>
                        <option value="Unread" <?php echo $status_filter === 'Unread' ? 'selected' : ''; ?>>Unread</option>
                        <option value="Read" <?php echo $status_filter === 'Read' ? 'selected' : ''; ?>>Read</option>
                        <option value="Archived" <?php echo $status_filter === 'Archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <a href="messages.php" class="flex-1 bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500 text-center">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Messages Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">From</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $status_color = 'bg-yellow-100 text-yellow-800';
                                if ($row['status'] === 'Read') $status_color = 'bg-green-100 text-green-800';
                                if ($row['status'] === 'Archived') $status_color = 'bg-gray-100 text-gray-800';
                                
                                echo '<tr class="border-b hover:bg-gray-50 ' . ($row['status'] === 'Unread' ? 'bg-blue-50' : '') . '">';
                                echo '<td class="px-6 py-4">';
                                echo '<div class="font-semibold text-gray-900">' . htmlspecialchars($row['fullname']) . '</div>';
                                echo '<div class="text-sm text-gray-600">' . htmlspecialchars($row['email']) . '</div>';
                                echo '</td>';
                                echo '<td class="px-6 py-4 text-gray-700">' . htmlspecialchars($row['subject']) . '</td>';
                                echo '<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-sm font-semibold ' . $status_color . '">' . htmlspecialchars($row['status']) . '</span></td>';
                                echo '<td class="px-6 py-4 text-gray-600">' . date('M d, Y', strtotime($row['created_at'])) . '</td>';
                                echo '<td class="px-6 py-4 text-center">';
                                echo '<button onclick="viewMessage(' . $row['id'] . ', ' . json_encode($row['message']) . ', ' . json_encode($row['fullname']) . ', ' . json_encode($row['email']) . ', ' . json_encode($row['subject']) . ', ' . json_encode($row['admin_reply'] ?? '') . ', ' . json_encode($row['replied_at'] ?? '') . ')" class="inline-block bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 mr-2">';
                                echo '<i class="fas fa-eye mr-1"></i>View';
                                echo '</button>';
                                echo '<a href="messages.php?delete=' . $row['id'] . '" onclick="return confirm(\'Delete this message?\');" class="inline-block bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">';
                                echo '<i class="fas fa-trash mr-1"></i>Delete';
                                echo '</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-600">No messages found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full max-h-96 overflow-auto">
            <div class="sticky top-0 bg-gray-100 border-b px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-900">Message Details</h2>
                <button onclick="closeModal()" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <label class="text-sm text-gray-600">From:</label>
                    <p id="modalFrom" class="font-semibold text-gray-900"></p>
                </div>
                <div class="mb-4">
                    <label class="text-sm text-gray-600">Email:</label>
                    <p id="modalEmail" class="text-gray-700"></p>
                </div>
                <div class="mb-4">
                    <label class="text-sm text-gray-600">Subject:</label>
                    <p id="modalSubject" class="font-semibold text-gray-900"></p>
                </div>
                <div class="mb-4">
                    <label class="text-sm text-gray-600">Message:</label>
                    <p id="modalMessage" class="text-gray-700 whitespace-pre-wrap bg-gray-50 p-4 rounded"></p>
                </div>

                <div id="modalAdminReplySection" class="mb-4 hidden">
                    <label class="text-sm text-gray-600">Existing Reply:</label>
                    <div id="modalAdminReply" class="text-gray-700 whitespace-pre-wrap bg-pink-50 p-4 rounded"></div>
                    <p id="modalRepliedAt" class="text-xs text-gray-500 mt-2"></p>
                </div>

                <form method="POST" id="modalReplyForm" class="mb-4 hidden">
                    <input type="hidden" name="action" value="reply_message">
                    <input type="hidden" name="message_id" id="modalReplyMessageId" value="">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Reply to this message</label>
                    <textarea id="modalReplyTextarea" name="reply" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600" placeholder="Write your reply here..." required></textarea>
                    <button type="submit" class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-paper-plane mr-2"></i>Send Reply
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function viewMessage(id, message, from, email, subject, adminReply, repliedAt) {
            document.getElementById('modalFrom').textContent = from;
            document.getElementById('modalEmail').textContent = email;
            document.getElementById('modalSubject').textContent = subject;
            document.getElementById('modalMessage').textContent = message;
            document.getElementById('modalReplyMessageId').value = id;
            document.getElementById('modalReplyTextarea').value = adminReply || '';

            if (adminReply) {
                document.getElementById('modalAdminReplySection').classList.remove('hidden');
                document.getElementById('modalAdminReply').textContent = adminReply;
                document.getElementById('modalRepliedAt').textContent = repliedAt ? 'Replied ' + repliedAt : '';
            } else {
                document.getElementById('modalAdminReplySection').classList.add('hidden');
                document.getElementById('modalAdminReply').textContent = '';
                document.getElementById('modalRepliedAt').textContent = '';
            }

            document.getElementById('modalReplyForm').classList.remove('hidden');
            document.getElementById('messageModal').classList.remove('hidden');

            // Mark as read in the background without leaving the page
            fetch('messages.php?view=' + id, { credentials: 'same-origin' });
        }

        function closeModal() {
            document.getElementById('messageModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

    <?php include '../includes/footer.php'; ?>
    <?php $conn->close(); ?>
