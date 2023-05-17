<?php
session_start();
// Connect to your PostgreSQL database
include_once('config.php');
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
if (isset($_GET['search'])) {
    $_SESSION['search'] = $_GET['search'];
}

$search_query = pg_escape_string($conn, $_SESSION['search']);
// $search_query = pg_escape_string($conn, $_GET['search']);
$limit = 50;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

if (is_numeric($search_query)) {
    $query = "SELECT * FROM \"User\" WHERE user_id = $search_query LIMIT $limit OFFSET $offset";
} else {
    $query = "SELECT * FROM \"User\" WHERE username ILIKE '%" . $search_query . "%' LIMIT $limit OFFSET $offset";
}
if (is_numeric($search_query)) {
    $query = "SELECT u.*, f.user_b as friend_id FROM \"User\" u LEFT JOIN \"Friends\" f ON u.user_id = f.user_a WHERE u.user_id = $search_query LIMIT $limit OFFSET $offset";
} else {
    $query = "SELECT u.*, f.user_b as friend_id FROM \"User\" u LEFT JOIN \"Friends\" f ON u.user_id = f.user_a WHERE u.username ILIKE '%" . $search_query . "%' LIMIT $limit OFFSET $offset";
}

$result = pg_query($conn, $query);

// Get the total number of results for pagination
$query_total = "SELECT COUNT(*) FROM \"User\" WHERE username ILIKE '%" . pg_escape_string($conn, $search_query) . "%'";
$query = "SELECT * FROM \"User\" WHERE username ILIKE '%" . pg_escape_string($conn, $search_query) . "%' LIMIT $limit OFFSET $offset";

$result_total = pg_query($conn, $query_total);
$total_users = pg_fetch_result(pg_query($conn, $query_total), 0, 0);
$total_pages = ceil($total_users / $limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPD - Search Results</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Search Results</h1>
        <ul>
            <?php while ($row = pg_fetch_assoc($result)): ?>
                <li>
                <a href="user_details.php?user_id=<?php echo $row['user_id']; ?>&friend_id=<?php echo $row['friend_id']; ?>">
                    <strong><?php echo $row['username']; ?></strong> - user ID: <?php echo $row['user_id']; ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $page - 1; ?>">&laquo; Previous</a>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?search=<?php echo urlencode($search_query); ?>&page=<?php echo $page + 1; ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>

        <a href="index.php">Back to search</a>
    </div>
</body>
</html>






