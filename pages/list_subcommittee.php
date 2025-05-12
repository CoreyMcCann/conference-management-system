
<?php

include '../connectdb.php';

// get the selected sub-committee from the query parameters (if any)
$selectedSubcommittee = isset($_GET['subcommittee']) ? $_GET['subcommittee'] : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sub-Committee Members</title>
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
    <h1>Organizing Sub-Committee Members</h1>

    <!-- form to choose a sub-committee -->
    <form method="get" action="list_subcommittee.php">
        <label for="subcommittee">Select Sub-Committee:</label>
        <select name="subcommittee" id="subcommittee">
            <option value="">--Select--</option>
            <?php
            // populate the dropdown with sub-committee names from the SubCommittee table
            $query = "SELECT subCommitteeName FROM SubCommittee";
            foreach ($connection->query($query) as $row) {
                $name = $row['subCommitteeName'];
                // mark the selected option if it matches the current selection
                $selected = ($selectedSubcommittee == $name) ? 'selected' : '';
                echo "<option value=\"" . htmlspecialchars($name) . "\" $selected>" . htmlspecialchars($name) . "</option>";
            }
            ?>
        </select>
        <input type="submit" value="Show Members">
    </form>

    <?php if ($selectedSubcommittee): ?>
        <h2>Members of <?php echo htmlspecialchars($selectedSubcommittee); ?></h2>
        <ul>
            <?php
            // prepare a query to get the members for the selected sub-committee
            // this joins CommitteeMember and BelongsTo based on the member ID
            $stmt = $connection->prepare("SELECT cm.memberID, cm.firstName, cm.lastName, cm.email 
                                          FROM CommitteeMember cm 
                                          JOIN BelongsTo b ON cm.memberID = b.committeeMemberID 
                                          WHERE b.subCommitteeName = :subcommittee");
            $stmt->bindParam(':subcommittee', $selectedSubcommittee);
            $stmt->execute();
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // display each member as a list item
            if (count($members) > 0) {
                foreach ($members as $member) {
                    echo "<li>" . htmlspecialchars($member['firstName']) . " " . htmlspecialchars($member['lastName']) .
                        " (" . htmlspecialchars($member['email']) . ")</li>";
                }
            } else {
                echo "<li>No members found for this sub-committee.</li>";
            }
            ?>
        </ul>
    <?php endif; ?>

</body>

</html>