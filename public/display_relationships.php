<?php
require_once 'includes/db.php';

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
$offset = ($page - 1) * $per_page;

$count_stmt = $search
    ? $conn->prepare("SELECT COUNT(*) FROM conditional_knowledge WHERE condition_if LIKE ? OR consequence_then LIKE ?")
    : $conn->prepare("SELECT COUNT(*) FROM conditional_knowledge");
if ($search) {
    $like = "%$search%";
    $count_stmt->bind_param("ss", $like, $like);
}
$count_stmt->execute();
$count_stmt->bind_result($total_rows);
$count_stmt->fetch();
$count_stmt->close();
$total_pages = ceil($total_rows / $per_page);

if ($search) {
    $stmt = $conn->prepare("SELECT id, condition_if, consequence_then FROM conditional_knowledge WHERE condition_if LIKE ? OR consequence_then LIKE ? LIMIT ? OFFSET ?");
    $stmt->bind_param("ssii", $like, $like, $per_page, $offset);
} else {
    $stmt = $conn->prepare("SELECT id, condition_if, consequence_then FROM conditional_knowledge LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

$terms = [];
$termQuery = $conn->query("SELECT term, description FROM terminology");
while ($t = $termQuery->fetch_assoc()) {
    $terms[$t['term']] = $t['description'];
}

function linkify_terms($text, $terms) {
    foreach ($terms as $term => $desc) {
        $escaped = preg_quote($term, '/');
        $safe_desc = htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');  // Escapes quotes properly
        $pattern = "/\\b($escaped)\\b/i";
        $replacement = '<span class="term-link" data-term="$1" data-def="' . $safe_desc . '">$1</span>';
        $text = preg_replace($pattern, $replacement, $text);
    }
    return $text;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Conditional Knowledge Base</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            vertical-align: top;
            text-align: left;
        }

        .term-link {
            color: blue;
            font-weight: bold;
            cursor: pointer;
            text-decoration: underline;
        }

        .term-link:hover {
            color: darkblue;
        }

        .search-bar {
            margin: 15px 0;
        }

        .search-bar input[type="text"] {
            width: 250px;
            padding: 6px;
        }

        .search-bar button {
            padding: 6px 12px;
            font-weight: bold;
        }

        #definition-modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        #definition-modal .modal-content {
            background: white;
            padding: 20px;
            max-width: 500px;
            border-radius: 8px;
            text-align: center;
        }

        #definition-modal button {
            margin-top: 15px;
            padding: 6px 12px;
            font-weight: bold;
        }

        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .pagination a, .pagination span {
            padding: 6px 10px;
            margin: 2px;
            border: 1px solid #4CAF50;
            color: #4CAF50;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination a:hover {
            background-color: #4CAF50;
            color: white;
        }

        .pagination .active {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>

<header>
    <h1>Knowledge Base</h1>
    <hr class="header-line">
</header>

<?php include 'includes/navbar.php'; ?>

<div class="container">
    <h2 class="section-title">Conditional Knowledge</h2>

    <form method="GET" class="search-bar">
        <label for="search">Search for something specific:</label>
        <input type="text" name="search" placeholder="Search IF or THEN..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <table>
        <thead>
            <tr>
                <th style="background-color: #4CAF50; color: white;">If</th>
                <th style="background-color: #4CAF50; color: white;">Then</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= linkify_terms($row['condition_if'], $terms) ?></td>
                    <td><?= linkify_terms($row['consequence_then'], $terms) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="2">❌ No rules found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <!-- Left: entries per page -->
        <form method="GET">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <label>Entries per page:
                <select name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 20, 30, 50, 100] as $n): ?>
                        <option value="<?= $n ?>" <?= $n == $per_page ? 'selected' : '' ?>><?= $n ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>

        <!-- Right: pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&per_page=<?= $per_page ?>&search=<?= urlencode($search) ?>">« Prev</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&per_page=<?= $per_page ?>&search=<?= urlencode($search) ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&per_page=<?= $per_page ?>&search=<?= urlencode($search) ?>">Next »</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Definition modal -->
<div id="definition-modal">
    <div class="modal-content">
        <h3 id="modal-term"></h3>
        <p id="modal-definition"></p>
        <button onclick="closeModal()">Go Back</button>
    </div>
</div>

<script>
    document.querySelectorAll('.term-link').forEach(link => {
        link.addEventListener('click', function () {
            document.getElementById('modal-term').innerText = this.dataset.term;
            document.getElementById('modal-definition').innerText = this.dataset.def;
            document.getElementById('definition-modal').style.display = 'flex';
        });
    });

    function closeModal() {
        document.getElementById('definition-modal').style.display = 'none';
    }
</script>

<footer>
    <p>© 2025 Read Books Project</p>
</footer>
</body>
</html>
