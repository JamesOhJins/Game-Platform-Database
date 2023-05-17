<?php
// Start the session to access the saved username variable
session_start();
include_once('config.php');

// Retrieve the user's information
$user_id = $_GET['user_id'];
$_SESSION['user_id'] = $user_id;
$query = "SELECT username, online_status FROM public.\"User\" WHERE user_id = $user_id";
$result = pg_query($conn, $query);
$user = pg_fetch_assoc($result);

// Retrieve the user's friends list
$query = "SELECT COUNT(*) AS total_friends, array_agg(u.username) AS friend_names
          FROM public.\"Friends\" f
          JOIN public.\"User\" u ON f.user_b = u.user_id
          WHERE f.user_a = $user_id";
$result = pg_query($conn, $query);
$friends = pg_fetch_assoc($result);

// Retrieve the user's games list
$query = "SELECT g.app_id, g.name
          FROM public.\"Ownership\" o
          JOIN public.\"Games\" g ON o.app_id = g.app_id
          WHERE o.user_id = $user_id";
$result = pg_query($conn, $query);

$games = [];
while ($row = pg_fetch_assoc($result)) {
    $games[] = [
        'app_id' => $row['app_id'],
        'name' => $row['name'],
    ];
}


// Retrieve the user's chat history with a specific friend
$friend_id = isset($_GET['friend_id']) ? $_GET['friend_id'] : null;
$_SESSION['friend_id'] = $friend_id;

// if ($friend_id === null) {
//     echo "Error: friend_id is not provided.";
//     exit();
// }

$query = "SELECT u.username AS friend_name, c.message, c.time_sent
          FROM public.\"Chat\" c
          JOIN public.\"User\" u ON c.user_sender = u.user_id
          WHERE (c.user_sender = $user_id AND c.user_receiver = $friend_id)
            OR (c.user_sender = $friend_id AND c.user_receiver = $user_id)
          ORDER BY c.time_sent ASC";
$result = pg_query($conn, $query);

// Close the database connection
// pg_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title><?php echo $user['username']; ?></title>
</head>
<body>
<div class="container">
    <h1><?php echo $user['username']; ?></h1>
    <h2>Online Status</h2>
    <?php if ($user['online_status']=='t') { ?>
        <img style = 'width: 80px; height: 80px;'src="images/greenlight.png">
    <?php } else { ?>
        <img style = 'width: 80px; height: 80px;'src="images/redlight.png">
    <?php } ?>

    <h2>Friends</h2>
    <?php
        $total_friends = $friends['total_friends'];
        $friend_names = $friends['friend_names'] ? array_filter(explode(',', str_replace(['{', '}'], '', $friends['friend_names']))) : [];
    ?>
    <p>Total number of friends: <?php echo $total_friends; ?></p>
    <?php if (!empty($friend_names)) { ?>
        <ul>
            <?php foreach ($friend_names as $friend_name) { ?>
                <li><?php echo $friend_name; ?></li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p>No friends found</p>
    <?php } ?>

    <h2>Games</h2>
    <?php
        // $total_games = $games['total_games'];
        // $game_names = $games['game_names'] ? array_filter(explode(',', str_replace(['{', '}'], '', $games['game_names']))) : [];
        ?>
        <p>Total number of games: <?php echo count($games); ?></p>

                <ul>
                <?php foreach ($games as $game) { ?>
            <li><a href="game_details.php?app_id=<?php echo $game['app_id']; ?>"><?php echo $game['name']; ?></a></li>
        <?php } ?>
        </ul>
        
        <h2>Chat history with <?php echo isset($friend_name) ? $friend_name : 'friend'; ?></h2>
<div id="chat_history">
    <?php if ($result): ?>
        <?php while ($row = pg_fetch_assoc($result)) { ?>
            <?php if ($row['friend_name'] == $user['username']) { ?>
                <div class="message received">
                    <p><?php echo $row['message']; ?></p>
                    <span><?php echo $row['time_sent']; ?></span>
                </div>
            <?php } else { ?>
                <div class="message sent">
                    <p><?php echo $row['message']; ?></p>
                    <span><?php echo $row['time_sent']; ?></span>
                </div>
            <?php } ?>
        <?php } ?>
    <?php endif; ?>
</div>
<a href="search_result.php?search=<?php echo urlencode($_SESSION['search']); ?>">Back to Search List</a>

</div>
</body>
</html>
