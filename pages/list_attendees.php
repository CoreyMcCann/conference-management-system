<?php

include '../connectdb.php';

// process form submission if data is posted
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form values
    $firstName = trim($_POST['firstname'] ?? '');
    $lastName  = trim($_POST['lastname'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $category  = trim($_POST['category'] ?? '');

    // validate required fields
    if ($firstName && $lastName && $email && $category) {

        // determine entry price based on category
        if ($category === 'student') {
            $entryPrice = 50;
        } elseif ($category === 'professional') {
            $entryPrice = 100;
        } elseif ($category === 'sponsor') {
            $entryPrice = 0;
        } else {
            $entryPrice = 100;
        }

        // insert new attendee into Attendee table
        $stmt = $connection->prepare("INSERT INTO Attendee (firstname, lastname, entryPrice, email) VALUES (:firstname, :lastname, :entryPrice, :email)");
        $stmt->bindParam(':firstname', $firstName);
        $stmt->bindParam(':lastname', $lastName);
        $stmt->bindParam(':entryPrice', $entryPrice);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // get the new attendeeID
        $newID = $connection->lastInsertId();

        // insert into the appropriate category table
        if ($category === 'student') {
            // for students, check if a hotel room was selected
            $hotelRoom = trim($_POST['hotelRoom'] ?? '');
            if ($hotelRoom && $hotelRoom !== 'none') {
                // the hotelRoom value is in the format "roomNumber-numBeds"
                list($roomNumber, $numBeds) = explode('-', $hotelRoom);
                $stmt = $connection->prepare("INSERT INTO Student (studentID, hotelRoomNumber, hotelNumBeds) VALUES (:studentID, :roomNumber, :numBeds)");
                $stmt->bindParam(':studentID', $newID);
                $stmt->bindParam(':roomNumber', $roomNumber);
                $stmt->bindParam(':numBeds', $numBeds);
                $stmt->execute();
            } else {
                // insert with NULL values for hotel room details
                $stmt = $connection->prepare("INSERT INTO Student (studentID, hotelRoomNumber, hotelNumBeds) VALUES (:studentID, NULL, NULL)");
                $stmt->bindParam(':studentID', $newID);
                $stmt->execute();
            }
        } elseif ($category === 'professional') {
            $stmt = $connection->prepare("INSERT INTO Professional (professionalID) VALUES (:professionalID)");
            $stmt->bindParam(':professionalID', $newID);
            $stmt->execute();
        } elseif ($category === 'sponsor') {
            // for sponsors, check if a sponsoring company was selected
            $company = trim($_POST['company'] ?? '');
            if ($company) {
                $stmt = $connection->prepare("INSERT INTO Sponsor (sponsorID, sponsoringCompanyName) VALUES (:sponsorID, :company)");
                $stmt->bindParam(':sponsorID', $newID);
                $stmt->bindParam(':company', $company);
                $stmt->execute();
            }
        }
        $message = "New attendee added successfully.";
    } else {
        $message = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conference Attendees</title>
    <link rel="stylesheet" href="../assets/css/general.css">
    <style>
        /* background image for the entire page */
        body {
            background-image: url('../assets/images/background.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
    
        fieldset {
            margin: 20px auto;
            max-width: 600px;
            padding: 20px;
        }

        legend {
            font-size: 1.2em;
            font-weight: bold;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }

        .note {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <h1>Conference Attendees</h1>

    <!-- Form to add a new attendee -->
    <fieldset>
        <legend>Add New Attendee</legend>
        <?php if ($message): ?>
            <p style="color:green;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="post" action="list_attendees.php">
            <label for="firstname">First Name:</label>
            <input type="text" name="firstname" id="firstname" required>

            <label for="lastname">Last Name:</label>
            <input type="text" name="lastname" id="lastname" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="">-- Select Category --</option>
                <option value="student">Student ($50) [Fill hotel room if applicable]</option>
                <option value="professional">Professional ($100)</option>
                <option value="sponsor">Sponsor (Free) [Fill sponsoring company]</option>
            </select>

            <label for="hotelRoom">Hotel Room (for Students only):</label>
            <select name="hotelRoom" id="hotelRoom">
                <option value="none">None</option>
                <?php
                // Populate hotel room options from HotelRoom table
                $roomQuery = "SELECT roomNumber, numBeds FROM HotelRoom ORDER BY roomNumber";
                foreach ($connection->query($roomQuery) as $room) {
                    $value = $room['roomNumber'] . "-" . $room['numBeds'];
                    echo "<option value=\"" . htmlspecialchars($value) . "\">Room " . htmlspecialchars($room['roomNumber']) . " (Beds: " . htmlspecialchars($room['numBeds']) . ")</option>";
                }
                ?>
            </select>

            <label for="company">Sponsoring Company (for Sponsors only):</label>
            <select name="company" id="company">
                <option value="">-- Select Company --</option>
                <?php
                // Populate sponsoring companies from SponsoringCompany table
                $companyQuery = "SELECT companyName FROM SponsoringCompany ORDER BY companyName";
                foreach ($connection->query($companyQuery) as $comp) {
                    echo "<option value=\"" . htmlspecialchars($comp['companyName']) . "\">" . htmlspecialchars($comp['companyName']) . "</option>";
                }
                ?>
            </select>

            <br>
            <input type="submit" value="Add Attendee">
        </form>
    </fieldset>

    <!-- Display lists of attendees -->
    <section>
        <h2>Students</h2>
        <ul>
            <?php
            $queryStudents = "SELECT a.attendeeID, a.firstname, a.lastname, a.email 
                        FROM Student s 
                        JOIN Attendee a ON s.studentID = a.attendeeID 
                        ORDER BY a.firstname";
            $resultStudents = $connection->query($queryStudents);
            while ($row = $resultStudents->fetch(PDO::FETCH_ASSOC)) {
                echo "<li>" . htmlspecialchars($row['firstname']) . " " . htmlspecialchars($row['lastname']) . " (" . htmlspecialchars($row['email']) . ")</li>";
            }
            ?>
        </ul>
    </section>

    <section>
        <h2>Professionals</h2>
        <ul>
            <?php
            $queryProfessionals = "SELECT a.attendeeID, a.firstname, a.lastname, a.email 
                             FROM Professional p 
                             JOIN Attendee a ON p.professionalID = a.attendeeID 
                             ORDER BY a.firstname";
            $resultProfessionals = $connection->query($queryProfessionals);
            while ($row = $resultProfessionals->fetch(PDO::FETCH_ASSOC)) {
                echo "<li>" . htmlspecialchars($row['firstname']) . " " . htmlspecialchars($row['lastname']) . " (" . htmlspecialchars($row['email']) . ")</li>";
            }
            ?>
        </ul>
    </section>

    <section>
        <h2>Sponsors</h2>
        <ul>
            <?php
            $querySponsors = "SELECT a.attendeeID, a.firstname, a.lastname, a.email, s.sponsoringCompanyName 
                        FROM Sponsor s 
                        JOIN Attendee a ON s.sponsorID = a.attendeeID 
                        ORDER BY a.firstname";
            $resultSponsors = $connection->query($querySponsors);
            while ($row = $resultSponsors->fetch(PDO::FETCH_ASSOC)) {
                echo "<li>" . htmlspecialchars($row['firstname']) . " " . htmlspecialchars($row['lastname']) . " (" . htmlspecialchars($row['email']) . ") - Company: " . htmlspecialchars($row['sponsoringCompanyName']) . "</li>";
            }
            ?>
        </ul>
    </section>
</body>

</html>