<?php

include 'connectdb.php';

// query total registration from Attendee
$regStmt = $connection->query("SELECT SUM(entryPrice) AS totalRegistration FROM Attendee");
$regRow = $regStmt->fetch(PDO::FETCH_ASSOC);
$totalRegistration = $regRow['totalRegistration'] ?? 0;

// query total sponsorship (derived from sponsorshipTier)
$sponsorStmt = $connection->query("
  SELECT SUM(
    CASE 
      WHEN sponsorshipTier = 'Platinum' THEN 10000
      WHEN sponsorshipTier = 'Gold' THEN 5000
      WHEN sponsorshipTier = 'Silver' THEN 3000
      WHEN sponsorshipTier = 'Bronze' THEN 1000
      ELSE 0
    END
  ) AS totalSponsorship
  FROM SponsoringCompany
");
$sponsorRow = $sponsorStmt->fetch(PDO::FETCH_ASSOC);
$totalSponsorship = $sponsorRow['totalSponsorship'] ?? 0;

// calculate total income
$totalIncome = $totalRegistration + $totalSponsorship;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Conference Management System</title>
  <link rel="stylesheet" href="assets/css/conference.css">
  <style>
    /* background image for the entire page */
    body {
      background-image: url('assets/images/background.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-attachment: fixed;
    }
  </style>
</head>

<body>
  <div class="container">
    <header class="header">
      <h1>Conference Management System</h1>
    </header>

    <main class="main-content">
      <section class="revenue-section">
        <h2>Conference Revenue</h2>
        <div class="revenue-grid">
          <div class="revenue-card">
            <h3>Total Registration</h3>
            <p>$<?php echo number_format($totalRegistration, 2); ?></p>
          </div>

          <div class="revenue-card">
            <h3>Total Sponsorship</h3>
            <p>$<?php echo number_format($totalSponsorship, 2); ?></p>
          </div>

          <div class="revenue-card highlight">
            <h3>Total Income</h3>
            <p>$<?php echo number_format($totalIncome, 2); ?></p>
          </div>
        </div>
      </section>

      <section class="navigation-section">
        <h2>Quick Links</h2>
        <div class="nav-links">
          <ul>
            <li><a href="pages/list_attendees.php">Attendees</a></li>
            <li><a href="pages/hotel_students.php">Hotel Students</a></li>
            <li><a href="pages/list_jobs.php">Job Listings</a></li>
            <li><a href="pages/list_subcommittee.php">Sub-Committees</a></li>
            <li><a href="pages/schedule.php">Schedule</a></li>
            <li><a href="pages/sponsors.php">Sponsors</a></li>
          </ul>
        </div>
      </section>
    </main>

    <footer class="footer">
      <p>© <?php echo date('Y'); ?> Conference Management System</p>
    </footer>
  </div>
</body>

</html>