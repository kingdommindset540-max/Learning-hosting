<?php
// admin_dashboard.php - Admin dashboard showing job and intern applications
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.html');
    exit;
}

// XAMPP MySQL connection settings
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';

// Fetch intern applications
$interns = [];
try {
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, 'interns_reg');
    if (!$mysqli->connect_errno) {
        $result = $mysqli->query("SELECT * FROM interns_application ORDER BY submitted_at DESC");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $interns[] = $row;
            }
        }
        $mysqli->close();
    }
} catch (Exception $e) {
    // Handle error silently
}

// Fetch job applications
$jobs = [];
try {
    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, 'Job_reg');
    if (!$mysqli->connect_errno) {
        $result = $mysqli->query("SELECT * FROM job_applicants ORDER BY submitted_at DESC");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $jobs[] = $row;
            }
        }
        $mysqli->close();
    }
} catch (Exception $e) {
    // Handle error silently
}

// Handle sorting
$intern_sort = isset($_GET['intern_sort']) ? $_GET['intern_sort'] : 'newest';
$job_sort = isset($_GET['job_sort']) ? $_GET['job_sort'] : 'newest';

// Apply sorting to interns
if ($intern_sort === 'name_asc') {
    usort($interns, function($a, $b) {
        return strcmp($a['first_name'] . ' ' . $a['last_name'], $b['first_name'] . ' ' . $b['last_name']);
    });
} elseif ($intern_sort === 'name_desc') {
    usort($interns, function($a, $b) {
        return strcmp($b['first_name'] . ' ' . $b['last_name'], $a['first_name'] . ' ' . $a['last_name']);
    });
} elseif ($intern_sort === 'oldest') {
    usort($interns, function($a, $b) {
        return strtotime($a['submitted_at']) - strtotime($b['submitted_at']);
    });
}
// 'newest' is already the default from the query

// Apply sorting to jobs
if ($job_sort === 'name_asc') {
    usort($jobs, function($a, $b) {
        return strcmp($a['first_name'] . ' ' . $a['last_name'], $b['first_name'] . ' ' . $b['last_name']);
    });
} elseif ($job_sort === 'name_desc') {
    usort($jobs, function($a, $b) {
        return strcmp($b['first_name'] . ' ' . $b['last_name'], $a['first_name'] . ' ' . $a['last_name']);
    });
} elseif ($job_sort === 'oldest') {
    usort($jobs, function($a, $b) {
        return strtotime($a['submitted_at']) - strtotime($b['submitted_at']);
    });
}
// 'newest' is already the default from the query

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="media-queries.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins, Roboto, sans-serif;
        }
        body {
            background: #f5f7fa;
        }
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: clamp(15px, 4vw, 40px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            flex-wrap: wrap;
            gap: 15px;
        }
        .admin-header h1 {
            font-size: clamp(20px, 5vw, 28px);
        }
        .admin-header .user-info {
            display: flex;
            align-items: center;
            gap: clamp(10px, 2vw, 20px);
            flex-wrap: wrap;
            font-size: clamp(12px, 2vw, 16px);
        }
        .logout-btn {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            min-height: 48px;
            font-size: clamp(12px, 2vw, 14px);
        }
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
        }
        .container {
            max-width: 1200px;
            margin: clamp(20px, 5vw, 40px) auto;
            padding: 0 clamp(15px, 3vw, 20px);
        }
        .section {
            margin-bottom: clamp(30px, 5vw, 40px);
        }
        .section h2 {
            color: #333;
            margin-bottom: clamp(15px, 3vw, 20px);
            font-size: clamp(20px, 4vw, 24px);
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .applications-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: clamp(15px, 3vw, 20px);
        }
        .app-card {
            background: white;
            padding: clamp(15px, 3vw, 20px);
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .app-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }
        .app-card h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: clamp(16px, 3vw, 18px);
        }
        .app-card p {
            margin: 8px 0;
            color: #555;
            font-size: clamp(12px, 2vw, 14px);
        }
        .app-card strong {
            color: #333;
        }
        .app-date {
            color: #999;
            font-size: clamp(11px, 1.5vw, 12px);
            margin-top: 10px;
        }
        .details-btn {
            background: #667eea;
            color: white;
            padding: 12px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            font-size: clamp(12px, 2vw, 14px);
            min-height: 48px;
            width: 100%;
        }
        .details-btn:hover {
            background: #764ba2;
        }
        .details-btn:active {
            background: #5a3f8f;
        }
        .empty-message {
            text-align: center;
            color: #999;
            padding: clamp(20px, 5vw, 40px);
            font-size: clamp(14px, 2vw, 16px);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }
        .modal-content {
            background-color: #fff;
            margin: clamp(20px, 5vh, 60px) auto;
            padding: clamp(20px, 3vw, 30px);
            border-radius: 8px;
            width: 95%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .modal-header h2 {
            color: #667eea;
            font-size: clamp(18px, 4vw, 24px);
        }
        .close {
            font-size: clamp(24px, 5vw, 32px);
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            min-width: 48px;
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .close:hover {
            color: #000;
        }
        .modal-body p {
            margin: 12px 0;
            color: #555;
            font-size: clamp(12px, 2vw, 14px);
            word-break: break-word;
        }
        .modal-body strong {
            color: #333;
        }

        /* Tablet & Mobile Responsiveness */
        @media (max-width: 768px) {
            .applications-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            .admin-header {
                flex-direction: column;
                text-align: center;
            }
            .admin-header .user-info {
                justify-content: center;
                width: 100%;
            }
            .modal-content {
                width: 95%;
                margin: 30px auto;
            }
        }

        @media (max-width: 480px) {
            .applications-grid {
                grid-template-columns: 1fr;
            }
            .admin-header {
                padding: 15px 10px;
            }
            .admin-header h1 {
                width: 100%;
            }
            .admin-header .user-info {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }
            .section h2 {
                font-size: 18px;
            }
            .modal-content {
                width: 98%;
                padding: 20px;
                margin: 20px auto;
                border-radius: 6px;
            }
            .modal-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .close {
                align-self: flex-end;
            }
        }
        .sort-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .sort-btn {
            background: #667eea;
            color: white;
            padding: 10px 16px;
            border: 2px solid #667eea;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .sort-btn:hover {
            background: #764ba2;
            border-color: #764ba2;
        }
        .sort-btn.active {
            background: #ff6b6b;
            border-color: #ff6b6b;
        }
        .sort-label {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: #333;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="?action=logout" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <!-- Intern Applications Section -->
        <div class="section">
            <h2><i class="fas fa-graduation-cap"></i> Internship Applications (<?php echo count($interns); ?>)</h2>
            <?php if (count($interns) > 0): ?>
                <div class="sort-controls">
                    <span class="sort-label">Sort by:</span>
                    <a href="?intern_sort=name_asc" class="sort-btn <?php echo $intern_sort === 'name_asc' ? 'active' : ''; ?>">
                        <i class="fas fa-arrow-up"></i> Name (A-Z)
                    </a>
                    <a href="?intern_sort=name_desc" class="sort-btn <?php echo $intern_sort === 'name_desc' ? 'active' : ''; ?>">
                        <i class="fas fa-arrow-down"></i> Name (Z-A)
                    </a>
                    <a href="?intern_sort=newest" class="sort-btn <?php echo $intern_sort === 'newest' ? 'active' : ''; ?>">
                        <i class="fas fa-clock"></i> Newest First
                    </a>
                    <a href="?intern_sort=oldest" class="sort-btn <?php echo $intern_sort === 'oldest' ? 'active' : ''; ?>">
                        <i class="fas fa-hourglass-end"></i> Oldest First
                    </a>
                </div>
                <div class="applications-grid">
                    <?php foreach ($interns as $app): ?>
                        <div class="app-card">
                            <h3><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></h3>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($app['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($app['phone']); ?></p>
                            <p><strong>University:</strong> <?php echo htmlspecialchars($app['university']); ?></p>
                            <p><strong>Track:</strong> <?php echo htmlspecialchars($app['internship_track']); ?></p>
                            <p><strong>Year:</strong> <?php echo htmlspecialchars($app['year_of_study']); ?></p>
                            <div class="app-date">Submitted: <?php echo htmlspecialchars($app['submitted_at']); ?></div>
                            <button class="details-btn" onclick="showInternDetails(<?php echo htmlspecialchars(json_encode($app)); ?>)">View Full Details</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-message">No internship applications yet.</div>
            <?php endif; ?>
        </div>

        <!-- Job Applications Section -->
        <div class="section">
            <h2><i class="fas fa-briefcase"></i> Job Applications (<?php echo count($jobs); ?>)</h2>
            <?php if (count($jobs) > 0): ?>
                <div class="sort-controls">
                    <span class="sort-label">Sort by:</span>
                    <a href="?job_sort=name_asc" class="sort-btn <?php echo $job_sort === 'name_asc' ? 'active' : ''; ?>">
                        <i class="fas fa-arrow-up"></i> Name (A-Z)
                    </a>
                    <a href="?job_sort=name_desc" class="sort-btn <?php echo $job_sort === 'name_desc' ? 'active' : ''; ?>">
                        <i class="fas fa-arrow-down"></i> Name (Z-A)
                    </a>
                    <a href="?job_sort=newest" class="sort-btn <?php echo $job_sort === 'newest' ? 'active' : ''; ?>">
                        <i class="fas fa-clock"></i> Newest First
                    </a>
                    <a href="?job_sort=oldest" class="sort-btn <?php echo $job_sort === 'oldest' ? 'active' : ''; ?>">
                        <i class="fas fa-hourglass-end"></i> Oldest First
                    </a>
                </div>
                <div class="applications-grid">
                    <?php foreach ($jobs as $app): ?>
                        <div class="app-card">
                            <h3><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></h3>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($app['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($app['phone']); ?></p>
                            <p><strong>Position:</strong> <?php echo htmlspecialchars($app['desired_position']); ?></p>
                            <p><strong>Experience:</strong> <?php echo htmlspecialchars($app['years_experience']); ?></p>
                            <p><strong>Education:</strong> <?php echo htmlspecialchars($app['education']); ?></p>
                            <div class="app-date">Submitted: <?php echo htmlspecialchars($app['submitted_at']); ?></div>
                            <button class="details-btn" onclick="showJobDetails(<?php echo htmlspecialchars(json_encode($app)); ?>)">View Full Details</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-message">No job applications yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Intern Details Modal -->
    <div id="internModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Internship Application Details</h2>
                <span class="close" onclick="closeModal('internModal')">&times;</span>
            </div>
            <div class="modal-body" id="internModalBody"></div>
        </div>
    </div>

    <!-- Job Details Modal -->
    <div id="jobModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Job Application Details</h2>
                <span class="close" onclick="closeModal('jobModal')">&times;</span>
            </div>
            <div class="modal-body" id="jobModalBody"></div>
        </div>
    </div>

    <script>
        function showInternDetails(data) {
            const body = document.getElementById('internModalBody');
            body.innerHTML = `
                <p><strong>Name:</strong> ${data.first_name} ${data.last_name}</p>
                <p><strong>Email:</strong> ${data.email}</p>
                <p><strong>Phone:</strong> ${data.phone}</p>
                <p><strong>University:</strong> ${data.university}</p>
                <p><strong>Major:</strong> ${data.major}</p>
                <p><strong>Year of Study:</strong> ${data.year_of_study}</p>
                <p><strong>Internship Track:</strong> ${data.internship_track}</p>
                <p><strong>Technical Skills:</strong> ${data.technical_skills}</p>
                <p><strong>Motivation:</strong> ${data.motivation}</p>
                <p><strong>Submitted:</strong> ${data.submitted_at}</p>
            `;
            document.getElementById('internModal').style.display = 'block';
        }

        function showJobDetails(data) {
            const body = document.getElementById('jobModalBody');
            body.innerHTML = `
                <p><strong>Name:</strong> ${data.first_name} ${data.last_name}</p>
                <p><strong>Email:</strong> ${data.email}</p>
                <p><strong>Phone:</strong> ${data.phone}</p>
                <p><strong>Desired Position:</strong> ${data.desired_position}</p>
                <p><strong>Years of Experience:</strong> ${data.years_experience}</p>
                <p><strong>Education:</strong> ${data.education}</p>
                <p><strong>Technical Skills:</strong> ${data.technical_skills}</p>
                <p><strong>Professional Experience:</strong> ${data.experience_description}</p>
                <p><strong>Salary Expectation:</strong> ${data.salary_expectation}</p>
                <p><strong>Submitted:</strong> ${data.submitted_at}</p>
            `;
            document.getElementById('jobModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        window.onclick = function(event) {
            const internModal = document.getElementById('internModal');
            const jobModal = document.getElementById('jobModal');
            if (event.target === internModal) {
                internModal.style.display = 'none';
            }
            if (event.target === jobModal) {
                jobModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
