
<?php
// Connect to your PostgreSQL database
include_once('config.php');
session_start();
$app_id = $_GET['app_id'];
$query = "SELECT * FROM \"Games\" WHERE app_id = $app_id";
$result = pg_query($conn, $query);
$game = pg_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPD - Game Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1><?php echo $game['name']; ?></h1>
        <p><strong>App ID:</strong> <?php echo $game['app_id']; ?></p>
        <p><strong>Developer:</strong> <?php echo $game['developer']; ?></p>
        <p><strong>Publisher:</strong> <?php echo $game['publisher']; ?></p>
        <p><strong>Release Date:</strong> <?php echo $game['release_date']; ?></p>
        <a href="user_details.php?user_id=<?php echo $_SESSION['user_id']; ?>&friend_id=<?php echo $_SESSION['friend_id']; ?>">Back to Profile</a>
    </div>
</body>
</html>
