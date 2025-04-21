<?php
require_once 'includes/db.php';

$search = $_GET['search'] ?? '';

// Fetching all Conditionals
if ($search) {
    $stmt = $conn->prepare("SELECT id, condition_if, consequence_then FROM conditional_knowledge WHERE condition_if LIKE ? OR consequence_then LIKE ?");
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("SELECT id, condition_if, consequence_then FROM conditional_knowledge");
}

$stmt->execute();
$result = $stmt->get_result();

// Fetching all Terms
$terms = [];
$termQuery = $conn->query("SELECT term, description FROM terminology");
while ($t = $termQuery->fetch_assoc()) {
    $terms[$t['term']] = $t['description'];
}

function linkify_terms($text, $terms) {
    foreach ($terms as $term => $desc) {
        $escaped = preg_quote($term, '/');
        $pattern = "/\\b($escaped)\\b/i";
        $replacement = "<span class='term-link' data-term=\"$term\" data-def=\"" . htmlspecialchars($desc) . "\">$1</span>";
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
        td:nth-child(2) {
            width: 50%;
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

    </style>
</head>
<body>
<<<<<<< Updated upstream
=======

<header>
    <h1>Knowledge Base</h1>
    <hr class="header-line">
</header>

>>>>>>> Stashed changes
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
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $condition = linkify_terms($row['condition_if'], $terms);
                $consequence = linkify_terms($row['consequence_then'], $terms);
                echo "<tr>
                    <td>$condition</td>
                    <td>$consequence</td>                    
                </tr>";
            }
        } else {
            echo "<tr><td colspan='2'>❌ No rules found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

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
