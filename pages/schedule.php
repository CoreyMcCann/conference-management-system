<?php

include '../connectdb.php';

// process the update form if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateSession'])) {
    $sessionID = $_POST['sessionID'];
    $day = $_POST['day'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $room = $_POST['room'];

    $stmt = $connection->prepare("UPDATE Session SET day = :day, startTime = :startTime, endTime = :endTime, room = :room WHERE sessionID = :sessionID");
    $stmt->bindParam(':day', $day);
    $stmt->bindParam(':startTime', $startTime);
    $stmt->bindParam(':endTime', $endTime);
    $stmt->bindParam(':room', $room);
    $stmt->bindParam(':sessionID', $sessionID);
    $stmt->execute();

    $updateMessage = "Session updated successfully.";
}

// check if a session is to be edited
$sessionToEdit = null;
if (isset($_GET['editSession'])) {
    $editSessionID = $_GET['editSession'];
    $stmt = $connection->prepare("SELECT * FROM Session WHERE sessionID = :sessionID");
    $stmt->bindParam(':sessionID', $editSessionID);
    $stmt->execute();
    $sessionToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Schedule</title>
    <link rel="stylesheet" href="../assets/css/tables.css">
    <style>
        /* background image for the entire page */
        body {
            background-image: url('../assets/images/background.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <h1>The Session Schedule</h1>

    <!-- if editing a session, display the update form -->
    <?php if ($sessionToEdit): ?>
        <h2>Edit Session: <?php echo htmlspecialchars($sessionToEdit['sessionName']); ?></h2>
        <?php if (isset($updateMessage)): ?>
            <p style="color: green; text-align: center;"><?php echo htmlspecialchars($updateMessage); ?></p>
        <?php endif; ?>
        <form method="post" action="schedule.php" class="edit-form">
            <input type="hidden" name="sessionID" value="<?php echo htmlspecialchars($sessionToEdit['sessionID']); ?>">

            <label for="day">Day:</label>
            <select name="day" id="day" required>
                <option value="Day 1" <?php if ($sessionToEdit['day'] === 'Day 1') echo 'selected'; ?>>Day 1</option>
                <option value="Day 2" <?php if ($sessionToEdit['day'] === 'Day 2') echo 'selected'; ?>>Day 2</option>
            </select>

            <label for="startTime">Start Time:</label>
            <input type="text" name="startTime" id="startTime"
                value="<?php echo htmlspecialchars($sessionToEdit['startTime']); ?>" required>

            <label for="endTime">End Time:</label>
            <input type="text" name="endTime" id="endTime"
                value="<?php echo htmlspecialchars($sessionToEdit['endTime']); ?>" required>

            <label for="room">Room:</label>
            <input type="text" name="room" id="room"
                value="<?php echo htmlspecialchars($sessionToEdit['room']); ?>" required>

            <input type="submit" name="updateSession" value="Update Session">
        </form>

        <hr>
    <?php endif; ?>

    <h2>Day 1 Schedule</h2>
    <table>
        <thead>
            <tr>
                <th>Session Name</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Room</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // order by startTime so earlier sessions appear first
            $query = "SELECT * FROM Session WHERE day = 'Day 1' ORDER BY startTime ASC";
            $result = $connection->query($query);

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['sessionName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['startTime']) . "</td>";
                echo "<td>" . htmlspecialchars($row['endTime']) . "</td>";
                echo "<td>" . htmlspecialchars($row['room']) . "</td>";
                echo "<td><a href=\"schedule.php?editSession=" . urlencode($row['sessionID']) . "\">Edit</a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <h2>Day 2 Schedule</h2>
    <table>
        <thead>
            <tr>
                <th>Session Name</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Room</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Same ordering for Day 2
            $query = "SELECT * FROM Session WHERE day = 'Day 2' ORDER BY startTime ASC";
            $result = $connection->query($query);

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['sessionName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['startTime']) . "</td>";
                echo "<td>" . htmlspecialchars($row['endTime']) . "</td>";
                echo "<td>" . htmlspecialchars($row['room']) . "</td>";
                echo "<td><a href=\"schedule.php?editSession=" . urlencode($row['sessionID']) . "\">Edit</a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</body>

</html>