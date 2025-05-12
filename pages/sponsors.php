<?php

include '../connectdb.php';

$message = '';

// process form submission to add a new sponsoring company
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = trim($_POST['companyName'] ?? '');
    $sponsorshipTier = trim($_POST['sponsorshipTier'] ?? '');
    $emailsSent = trim($_POST['emailsSent'] ?? '');

    if ($companyName && $sponsorshipTier !== '' && $emailsSent !== '') {
        // insert new sponsoring company
        $stmt = $connection->prepare("INSERT INTO SponsoringCompany (companyName, sponsorshipTier, emailsSent) VALUES (:companyName, :sponsorshipTier, :emailsSent)");
        $stmt->bindParam(':companyName', $companyName);
        $stmt->bindParam(':sponsorshipTier', $sponsorshipTier);
        $stmt->bindParam(':emailsSent', $emailsSent);
        if ($stmt->execute()) {
            $message = "Sponsoring Company '$companyName' added successfully.";
        } else {
            $message = "Error adding sponsoring company.";
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}

// process deletion of a sponsoring company if requested
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['company'])) {
    $companyToDelete = $_GET['company'];
    $stmt = $connection->prepare("DELETE FROM SponsoringCompany WHERE companyName = :companyName");
    $stmt->bindParam(':companyName', $companyToDelete);
    if ($stmt->execute()) {
        $message = "Sponsoring Company '$companyToDelete' and its associated data deleted successfully.";
    } else {
        $message = "Error deleting sponsoring company.";
    }
}

// retrieve the list of sponsoring companies
$stmt = $connection->query("SELECT * FROM SponsoringCompany ORDER BY companyName");
$sponsoringCompanies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sponsoring Companies</title>
    <link rel="stylesheet" href="../assets/css/general.css">
    <link rel="stylesheet" href="../assets/css/sponsors.css">
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
    <div class="sponsor-page">
        <h1>Sponsoring Companies</h1>

        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <fieldset>
            <legend>Add New Sponsoring Company</legend>
            <form method="post" action="sponsors.php">
                <div class="form-row">
                    <label for="companyName">Company Name:</label>
                    <input type="text" name="companyName" id="companyName" required>
                </div>

                <div class="form-row">
                    <label for="sponsorshipTier">Sponsorship Tier:</label>
                    <select name="sponsorshipTier" id="sponsorshipTier" required>
                        <option value="">-- Select Tier --</option>
                        <option value="Platinum">Platinum ($10,000)</option>
                        <option value="Gold">Gold ($5,000)</option>
                        <option value="Silver">Silver ($3,000)</option>
                        <option value="Bronze">Bronze ($1,000)</option>
                    </select>
                </div>

                <div class="form-row">
                    <label for="emailsSent">Emails Sent (initial):</label>
                    <input type="number" name="emailsSent" id="emailsSent" value="0" required>
                </div>

                <div class="submit-row">
                    <input type="submit" value="Add Company">
                </div>
            </form>
        </fieldset>

        <table>
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Sponsorship Tier</th>
                    <th>Emails Sent</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($sponsoringCompanies) > 0): ?>
                    <?php foreach ($sponsoringCompanies as $company): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($company['companyName']); ?></td>
                            <td><?php echo htmlspecialchars($company['sponsorshipTier']); ?></td>
                            <td><?php echo htmlspecialchars($company['emailsSent']); ?></td>
                            <td>
                                <a class="delete-link" href="sponsors.php?action=delete&company=<?php echo urlencode($company['companyName']); ?>" onclick="return confirm('Are you sure you want to delete this company and all associated data?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No sponsoring companies found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>