<?php

include '../connectdb.php';

// get the selected hotel room from the query parameters (if any)
$selectedRoomnumber = isset($_GET['hotelroom']) ? $_GET['hotelroom'] : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Hotel Rooms</title>
    <link rel="stylesheet" href="../assets/css/general.css">
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

    <h1>The students assigned to each hotel room</h1>

    <!-- form to choose a sub-committee -->
    <form method="get" action="hotel_students.php">
        <label for="hotelroom">Select hotel room:</label>
        <select name="hotelroom" id="hotelroom">
            <option value="">--Select--</option>
            <?php
            // populate the dropdown with all hotel room numbers from the HotelRoom table
            $query = "SELECT roomNumber FROM HotelRoom";
            foreach ($connection->query($query) as $row) {
                $roomnumber = $row['roomNumber'];
                // mark the selected option if it matches the current selection
                $selected = ($selectedRoomnumber == $roomnumber) ? 'selected' : '';
                echo "<option value=\"" . htmlspecialchars($roomnumber) . "\" $selected>" . htmlspecialchars($roomnumber) . "</option>";
            }
            ?>
        </select>
        <input type="submit" value="Show Students">
    </form>

    <?php if ($selectedRoomnumber): ?>
        <h2>Members of <?php echo htmlspecialchars($selectedRoomnumber); ?></h2>
        <ul>
            <?php
            // prepare a query to get the students for the selected room number
            // this joins CommitteeMember and BelongsTo based on the member ID
            $stmt = $connection->prepare("SELECT a.firstName, a.lastName, a.email 
                                          FROM Student s 
                                          JOIN Attendee a ON s.studentID = a.attendeeID
                                          WHERE s.hotelRoomNumber = :roomnumber");
            $stmt->bindParam(':roomnumber', $selectedRoomnumber);
            $stmt->execute();
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // display each student as a list item
            if (count($students) > 0) {
                foreach ($students as $student) {
                    echo "<li>" . htmlspecialchars($student['firstName']) . " " . htmlspecialchars($student['lastName']) .
                        " (" . htmlspecialchars($student['email']) . ")</li>";
                }
            } else {
                echo "<li>No members found for this room number.</li>";
            }
            ?>
        </ul>
    <?php endif; ?>

</body>

</html>