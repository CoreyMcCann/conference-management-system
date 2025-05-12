
<?php

include '../connectdb.php';

// check if a company was selected from the dropdown
$selectedCompany = isset($_GET['company']) ? $_GET['company'] : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Jobs</title>
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
    <h1>List Jobs</h1>

    <!-- form to choose a sponsoring company or all companies -->
    <form method="get" action="list_jobs.php">
        <label for="company">Select Company:</label>
        <select name="company" id="company">
            <option value="">-- Select --</option>
            <!-- "All Companies" option -->
            <option value="ALL" <?php echo ($selectedCompany === 'ALL' ? 'selected' : ''); ?>>
                All Companies
            </option>
            <?php
            // populate the dropdown with company names from the SponsoringCompany table
            $query = "SELECT companyName FROM SponsoringCompany ORDER BY companyName";
            foreach ($connection->query($query) as $row) {
                $name = $row['companyName'];
                $selected = ($selectedCompany === $name) ? 'selected' : '';
                echo "<option value=\"" . htmlspecialchars($name) . "\" $selected>"
                    . htmlspecialchars($name) . "</option>";
            }
            ?>
        </select>
        <input type="submit" value="Show Jobs">
    </form>

    <?php if ($selectedCompany): ?>
        <?php
        // if the user selected "ALL", get jobs from all companies
        if ($selectedCompany === 'ALL') {
            $stmt = $connection->prepare("
                SELECT jobTitle, annualPay, city, province, sponsoringCompanyName 
                FROM JobAd
                ORDER BY jobTitle
            ");
            $stmt->execute();
            $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<h2>All Jobs</h2>";
        } else {
            // otherwise, get jobs only for the selected company
            $stmt = $connection->prepare("
                SELECT jobTitle, annualPay, city, province 
                FROM JobAd
                WHERE sponsoringCompanyName = :company
                ORDER BY jobTitle
            ");
            $stmt->bindParam(':company', $selectedCompany);
            $stmt->execute();
            $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<h2>Jobs at " . htmlspecialchars($selectedCompany) . "</h2>";
        }
        ?>

        <?php if (count($jobs) > 0): ?>
            <ul>
                <?php foreach ($jobs as $job): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($job['jobTitle']); ?></strong><br>
                        Pay: <?php echo htmlspecialchars($job['annualPay']); ?><br>
                        Location: <?php echo htmlspecialchars($job['city']); ?>,
                        <?php echo htmlspecialchars($job['province']); ?>
                        <?php if ($selectedCompany === 'ALL'): ?>
                            <br>
                            Company: <?php echo htmlspecialchars($job['sponsoringCompanyName']); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No jobs found.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>

</html>