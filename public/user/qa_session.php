<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/config.php';
require_once '../includes/auth_helpers.php';

$feedback = '';
$search = $_GET['search'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Legal QA Session</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .delete-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .delete-btn:hover {
            background-color: #2980b9;
        }

        .delete-all-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        .delete-all-btn:hover {
            background-color: #2980b9;
        }

        .search-bar {
            margin-bottom: 15px;
        }

        .search-bar input[type="text"] {
            padding: 6px;
            width: 250px;
        }

        .search-bar button {
            padding: 6px 12px;
            font-weight: bold;
        }

        .rules-box {
            background-color: #f4f4f4;
            padding: 10px;
            border-left: 4px solid #4CAF50;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .rules-box ul {
            margin: 0;
            padding-left: 20px;
        }

        #loading-bar-container {
            display: none;
            width: 100%;
            background-color: #ddd;
            margin-top: 15px;
            border-radius: 5px;
        }

        #loading-bar {
            width: 0%;
            height: 10px;
            background-color: #007bff;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<header>
    <h1>Q&A Session</h1>
    <hr class="header-line">
</header>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Ask a Legal Question</h2>

    <form method="POST" onsubmit="showLoadingBar()">
        <input type="text" name="question" placeholder="Enter your legal situation..." required>
        <button type="submit">Ask</button>
    </form>

    <div id="loading-bar-container">
        <div id="loading-bar"></div>
    </div>

    <div style="margin-top: 15px;">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['question'])) {
            $raw_question = $_POST['question'];
            $escaped_question = escapeshellarg($raw_question);
            $command = "/home/khalidt2/venvs/legalqa/bin/python /var/projects/s25-03/html/v1/public/predict.py $escaped_question";
            $output = shell_exec($command . " 2>&1");

            preg_match('/\{.*\}/s', $output, $matches);
            $json = $matches[0] ?? null;
            $data = json_decode($json, true);

            echo "<script>document.getElementById('loading-bar-container').style.display = 'none';</script>";

            if (json_last_error() === JSON_ERROR_NONE && isset($data["answer"])) {
                $answer = htmlspecialchars($data["answer"]);
                $confidence = htmlspecialchars($data["confidence"]);
                $rule = isset($data["rule"]) ? htmlspecialchars($data["rule"]) : '';

                echo "<p><strong>Question:</strong> " . htmlspecialchars($raw_question) . "</p>";
                echo "<p><strong>Answer:</strong> $answer</p>";
                echo "<p><strong>Confidence:</strong> $confidence%</p>";
                echo "<p><strong>Matched Rule:</strong> $rule</p>";

                if (!empty($data["rules"]) && is_array($data["rules"])) {
                    echo "<div class='rules-box'><strong>Legal Insight:</strong><ul>";
                    foreach ($data["rules"] as $r) {
                        echo "<li>" . htmlspecialchars($r) . "</li>";
                    }
                    echo "</ul></div>";
                }

                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $stmt = $conn->prepare("INSERT INTO qa_history (user_id, question, answer) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $user_id, $raw_question, $answer);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                echo "<p style='color:red;'>❌ Error: Could not get prediction.</p>";
                echo "<pre>" . htmlspecialchars($output) . "</pre>";
            }
        }

        if (isset($_POST['clear_history']) && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $conn->query("DELETE FROM qa_history WHERE user_id = $user_id");
            $feedback = "<p style='color:green;'>✅ History cleared successfully.</p>";
        }

        if (isset($_POST['delete_entry'], $_POST['timestamp']) && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $timestamp = $conn->real_escape_string($_POST['timestamp']);
            $conn->query("DELETE FROM qa_history WHERE user_id = $user_id AND created_at = '$timestamp'");
            $feedback = "<p style='color:green;'>✅ Entry deleted successfully.</p>";
        }
        ?>
        <br>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <h3 class="section-title">Your Question History</h3>

        <?php if (!empty($feedback)) echo $feedback; ?>

        <form method="POST" style="margin-bottom: 10px;">
            <input type="hidden" name="clear_history" value="1">
            <button type="submit" class="delete-all-btn" onclick="return confirm('Are you sure you want to clear your entire history?')">
                🗑️ Clear All History
            </button>
        </form>

        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Search question or answer..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>

        <table>
            <thead>
            <tr>
                <th style="background-color: #4CAF50; color: white;">Question</th>
                <th style="background-color: #4CAF50; color: white;">Answer</th>
                <th style="background-color: #4CAF50; color: white;">Time</th>
                <th style="background-color: #4CAF50; color: white;">Action</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $uid = $_SESSION['user_id'];

            if ($search) {
                $stmt = $conn->prepare("SELECT question, answer, created_at FROM qa_history WHERE user_id = ? AND (question LIKE ? OR answer LIKE ?) ORDER BY created_at DESC");
                $like = "%$search%";
                $stmt->bind_param("iss", $uid, $like, $like);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $stmt = $conn->prepare("SELECT question, answer, created_at FROM qa_history WHERE user_id = ? ORDER BY created_at DESC");
                $stmt->bind_param("i", $uid);
                $stmt->execute();
                $result = $stmt->get_result();
            }

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['question']) . "</td>
                        <td>" . htmlspecialchars($row['answer']) . "</td>
                        <td>" . htmlspecialchars($row['created_at']) . "</td>
                        <td>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='delete_entry' value='1'>
                                <input type='hidden' name='timestamp' value='" . htmlspecialchars($row['created_at']) . "'>
                                <button type='submit' class='delete-btn' onclick=\"return confirm('Delete this entry?')\">❌ Delete</button>
                            </form>
                        </td>
                      </tr>";
            }
            ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<footer>
    <p>© 2025 Legal KB Project</p>
</footer>

<script>
function showLoadingBar() {
    const container = document.getElementById("loading-bar-container");
    const bar = document.getElementById("loading-bar");
    container.style.display = "block";
    bar.style.width = "0%";
    let width = 0;

    const interval = setInterval(() => {
        if (width >= 90) return;
        width += 1;
        bar.style.width = width + "%";
    }, 15);

    window.addEventListener("load", () => {
        clearInterval(interval);
        bar.style.width = "100%";
        setTimeout(() => {
            container.style.display = "none";
            bar.style.width = "0%";
        }, 400);
    });
}
</script>

</body>
</html>
