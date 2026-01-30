<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
    </ul>
</nav>
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<?php
$currentPage = basename($_SERVER['PHP_SELF']);

$now = date('Y-m-d H:i:s');
// Application counts
$query = "
    SELECT
        SUM(status = 'new') AS newApplications,
        SUM(status = 'processed' AND app_dt > '$now') AS processedApplications,
        SUM((status = 'processed' OR status = 'rescheduled') AND app_dt < '$now') AS respondApplications,
        SUM(status = 'rescheduled' AND app_dt > '$now') AS rescheduledApplications,
        SUM(status = 'pending') AS processingApplications,
        SUM(status = 'completed') AS completedApplications
    FROM applications
";

$applicationCounts = $conn->query($query)->fetch_assoc();
$newApplications = $applicationCounts['newApplications'];
$processedApplications = $applicationCounts['processedApplications'];
$respondApplications = $applicationCounts['respondApplications'];
$rescheduledApplications = $applicationCounts['rescheduledApplications'];
$processingApplications = $applicationCounts['processingApplications'];
$completedApplications = $applicationCounts['completedApplications'];
?>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link text-center">
        <span class="brand-text font-weight-light">Admin Dashboard</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?= ($currentPage == 'index.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item has-treeview <?= ($currentPage == 'application_types.php' || $currentPage == 'manage-staff.php' || $currentPage == 'office_enquiry_numbers.php' || $currentPage == 'rpo_offices.php' || $currentPage == 'call_status.php' || $currentPage == 'annexure_types.php' || $currentPage == 'document_types.php' || $currentPage == 'locations.php' || $currentPage == 'user_accounts.php') ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= ($currentPage == 'application_types.php' || $currentPage == 'manage-staff.php' || $currentPage == 'office_enquiry_numbers.php' || $currentPage == 'rpo_offices.php' || $currentPage == 'call_status.php' || $currentPage == 'annexure_types.php' || $currentPage == 'document_types.php' || $currentPage == 'locations.php' || $currentPage == 'user_accounts.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-list"></i>
                        <p>
                            Master
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="application_types.php" class="nav-link <?= ($currentPage == 'application_types.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Application Types</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="office_enquiry_numbers.php" class="nav-link <?= ($currentPage == 'office_enquiry_numbers.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Office Enquiry Numbers</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="rpo_offices.php" class="nav-link <?= ($currentPage == 'rpo_offices.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>RPO Offices</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage-staff.php" class="nav-link <?= ($currentPage == 'manage-staff.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Staff</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="locations.php" class="nav-link <?= ($currentPage == 'locations.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Locations</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="user_accounts.php" class="nav-link <?= ($currentPage == 'user_accounts.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>User IDs</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="document_types.php" class="nav-link <?= ($currentPage == 'document_types.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Document Types</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="annexure_types.php" class="nav-link <?= ($currentPage == 'annexure_types.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Annexure Types</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="call_status.php" class="nav-link <?= ($currentPage == 'call_status.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Call Status</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview menu-open">
                    <a href="#" class="nav-link <?= ($currentPage == 'applications.php' || $currentPage == 'p_applications.php' || $currentPage == 'r_applications.php' || $currentPage == 'res_applications.php' || $currentPage == 'pro_applications.php' || $currentPage == 'com_applications.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>
                            Applications
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="applications.php" class="nav-link <?= ($currentPage == 'applications.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>New <span class="badge badge-primary right"><?= $newApplications ?></span></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="p_applications.php" class="nav-link <?= ($currentPage == 'p_applications.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Appointments <span class="badge badge-secondary right"><?= $processedApplications ?></span></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="r_applications.php" class="nav-link <?= ($currentPage == 'r_applications.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Respond <span class="badge badge-danger right"><?= $respondApplications ?></span></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="res_applications.php" class="nav-link <?= ($currentPage == 'res_applications.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Rescheduled <span class="badge badge-info right"><?= $rescheduledApplications ?></span></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pro_applications.php" class="nav-link <?= ($currentPage == 'pro_applications.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Status Check <span class="badge badge-warning right"><?= $processingApplications ?></span></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="com_applications.php" class="nav-link <?= ($currentPage == 'com_applications.php') ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Completed <span class="badge badge-success right"><?= $completedApplications ?></span></p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="enquiries.php" class="nav-link <?= ($currentPage == 'enquiries.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-comment-alt"></i>
                        <p>Manage Enquiries</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link <?= ($currentPage == 'settings.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>Settings</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link logout-btn <?= ($currentPage == 'logout.php') ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>