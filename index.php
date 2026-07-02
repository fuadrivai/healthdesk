<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HealthDesk - School Nurse System</title>

    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <!-- Bootstrap 5.x -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <link rel="stylesheet" href="assets/css/app.css" />
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-wrapper" id="mainWrapper">

        <?php include 'includes/navbar.php'; ?>

        <div class="page-content">

            <!-- ==========================================
            DASHBOARD
            ========================================== -->
            <section class="page-section active" id="page-dashboard">

                <!-- Page Header -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-modern">
                                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Dashboard</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <span class="badge bg-soft-primary text-primary-custom px-3 py-2 rounded-pill"
                            id="dashboardTodayBadge">
                            <i class="bi bi-calendar3 me-1"></i> Today
                        </span>
                    </div>
                </div>

                <!-- 4 Summary Cards -->
                <div class="row g-3 g-md-4 mb-4">
                    <div class="col-6 col-xl-3">
                        <div class="stat-card d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:linear-gradient(135deg,#4F8EF7,#7AA6F8);">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <div>
                                <div class="stat-number" id="dashboardTodayVisitors">0</div>
                                <div class="stat-label">Today's Visitors</div>
                                <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> +12%</div>
                            </div>
                            <div class="stat-chart-deco"><i class="bi bi-graph-up"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="stat-card d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:linear-gradient(135deg,#E57373,#f08a8a);">
                                <i class="bi bi-heart-pulse"></i>
                            </div>
                            <div>
                                <div class="stat-number" id="dashboardSickToday">0</div>
                                <div class="stat-label">Students Sick Today</div>
                                <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> +5%</div>
                            </div>
                            <div class="stat-chart-deco"><i class="bi bi-activity"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="stat-card d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:linear-gradient(135deg,#FFB74D,#ffcc80);">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <div class="stat-number" id="dashboardWaitingToday">0</div>
                                <div class="stat-label">Waiting</div>
                                <div class="stat-trend down"><i class="bi bi-arrow-down-short"></i> -3%</div>
                            </div>
                            <div class="stat-chart-deco"><i class="bi bi-hourglass"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="stat-card d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:linear-gradient(135deg,#5CB85C,#7dcc7d);">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div>
                                <div class="stat-number" id="dashboardRecoveredToday">0</div>
                                <div class="stat-label">Recovered</div>
                                <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> +8%</div>
                            </div>
                            <div class="stat-chart-deco"><i class="bi bi-emoji-smile"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Row -->
                <div class="row g-3 g-md-4 mb-4">
                    <!-- Visitor Chart -->
                    <div class="col-lg-7">
                        <div class="card-modern">
                            <div class="card-header">
                                <i class="bi bi-bar-chart-fill me-2 text-primary-custom"></i>
                                Visitor Statistics
                            </div>
                            <div class="card-body">
                                <div class="chart-placeholder">
                                    <canvas id="visitorChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Common Illness -->
                    <div class="col-lg-5">
                        <div class="card-modern h-100">
                            <div class="card-header">
                                <i class="bi bi-activity me-2 text-primary-custom"></i>
                                Common Illness Categories
                            </div>
                            <div class="card-body">
                                <div class="progress-illness" id="dashboardIllnessList">
                                    <div class="item">
                                        <span class="label">Loading...</span>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width:0%;"></div>
                                        </div>
                                        <span class="value">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Visitors Table & Right Panel -->
                <div class="row g-3 g-md-4">
                    <div class="col-lg-8">
                        <div class="card-modern">
                            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                                <span><i class="bi bi-clock-history me-2 text-primary-custom"></i>
                                    Recent
                                    Visitors</span>
                                <a href="javascript:void(0)"
                                    class="text-primary-custom text-decoration-none small fw-600"
                                    data-page="list-visitor">View All <i class="bi bi-arrow-right"></i></a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive-modern">
                                    <table class="table table-modern mb-0">
                                        <thead>
                                            <tr>
                                                <th>Visit Time</th>
                                                <th>Student Name</th>
                                                <th>Division</th>
                                                <th>Complaint</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dashboardRecentVisitorsBody">
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">Loading recent
                                                    visitors...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel -->
                    <div class="col-lg-4">
                        <!-- Today's Schedule -->
                        <div class="card-modern mb-3">
                            <div class="card-header">
                                <i class="bi bi-calendar-event me-2 text-primary-custom"></i>
                                Today's Schedule
                            </div>
                            <div class="card-body">
                                <div class="schedule-timeline">
                                    <div class="item">
                                        <span class="time">09:00</span>
                                        <div class="content">
                                            <div class="title">Morning Check-up</div>
                                            <div class="desc">Routine health screening
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item">
                                        <span class="time">10:30</span>
                                        <div class="content">
                                            <div class="title">Vaccination Session</div>
                                            <div class="desc">Flu vaccines for grade 10
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item">
                                        <span class="time">13:00</span>
                                        <div class="content">
                                            <div class="title">Follow-up Visits</div>
                                            <div class="desc">Check recovery progress
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item">
                                        <span class="time">15:00</span>
                                        <div class="content">
                                            <div class="title">Health Talk</div>
                                            <div class="desc">Nutrition awareness</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Medicine Stock -->
                        <div class="card-modern mb-3">
                            <div class="card-header">
                                <i class="bi bi-capsule me-2 text-primary-custom"></i>
                                Medicine Stock
                            </div>
                            <div class="card-body">
                                <div class="stock-item">
                                    <span class="stock-name">Paracetamol</span>
                                    <div class="stock-bar">
                                        <div class="stock-fill" style="width:78%;background:var(--primary);">
                                        </div>
                                    </div>
                                    <span class="stock-qty">78%</span>
                                </div>
                                <div class="stock-item">
                                    <span class="stock-name">Ibuprofen</span>
                                    <div class="stock-bar">
                                        <div class="stock-fill" style="width:45%;background:var(--warning);">
                                        </div>
                                    </div>
                                    <span class="stock-qty">45%</span>
                                </div>
                                <div class="stock-item">
                                    <span class="stock-name">Antihistamine</span>
                                    <div class="stock-bar">
                                        <div class="stock-fill" style="width:62%;background:var(--secondary);">
                                        </div>
                                    </div>
                                    <span class="stock-qty">62%</span>
                                </div>
                                <div class="stock-item">
                                    <span class="stock-name">Cough Syrup</span>
                                    <div class="stock-bar">
                                        <div class="stock-fill" style="width:30%;background:var(--danger);">
                                        </div>
                                    </div>
                                    <span class="stock-qty">30%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card-modern">
                            <div class="card-header">
                                <i class="bi bi-lightning-charge me-2 text-primary-custom"></i>
                                Quick Actions
                            </div>
                            <div class="card-body">
                                <div class="quick-actions">
                                    <button class="btn" data-page="input-visitor"><i class="bi bi-plus-circle"></i>
                                        New
                                        Visitor</button>
                                    <button class="btn" data-page="report"><i class="bi bi-printer"></i> Print
                                        Report</button>
                                    <button class="btn" data-page="master-student"><i class="bi bi-search"></i>
                                        Student
                                        Search</button>
                                    <button class="btn" id="showToastBtn"><i class="bi bi-bell"></i> Notify</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>

            <!-- ==========================================
            INPUT VISITOR
            ========================================== -->
            <section class="page-section" id="page-input-visitor">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="page-title">Input Visitor</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-modern">
                                <li class="breadcrumb-item"><a href="javascript:void(0)" data-page="dashboard">Home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Input
                                    Visitor</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="card-modern">
                    <div class="card-body">
                        <form id="visitorForm" enctype="multipart/form-data">
                            <input type="hidden" id="visitorId" name="id" />
                            <input type="hidden" id="visitorStudentId" name="student_id" />

                            <div class="mb-4">
                                <div class="form-section-title"><i class="bi bi-person-badge"></i> Student Selection
                                </div>
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label-modern">Student
                                            Name</label>
                                        <input type="text" class="form-control form-control-modern" id="visitorName"
                                            name="name" placeholder="Select student from list" readonly required />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label-modern">Level</label>
                                        <input type="text" class="form-control form-control-modern" id="visitorLevel"
                                            name="level" readonly required />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label-modern">Grade</label>
                                        <input type="text" class="form-control form-control-modern" id="visitorGrade"
                                            name="grade" readonly />
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button type="button" class="btn btn-modern btn-modern-primary"
                                            id="openStudentPickerBtn">
                                            <i class="bi bi-search me-1"></i>Select
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-section-title"><i class="bi bi-clipboard2-pulse"></i> Visit Details
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label-modern">Date</label>
                                        <input type="date" class="form-control form-control-modern" id="visitorDate"
                                            name="date" required />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label-modern">Time</label>
                                        <input type="time" class="form-control form-control-modern" id="visitorTime"
                                            name="time" required />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label-modern">Sick
                                            Category</label>
                                        <select class="form-select form-select-modern" id="visitorSickCategory"
                                            name="sick_category_id">
                                            <option value="">Select Category</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-modern">Intervention</label>
                                        <textarea class="form-control form-control-modern" id="visitorIntervention"
                                            name="intervention" rows="2" placeholder="Intervention"></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-modern">Accidental</label>
                                        <select class="form-select form-select-modern" id="visitorAccidental"
                                            name="accidental">
                                            <option value="">Select accidental type</option>
                                            <option value="Accidental">Accidental</option>
                                            <option value="Illness">Illness</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-modern">Result</label>
                                        <select class="form-select form-select-modern" id="visitorResult" name="result">
                                            <option value="">Select result</option>
                                            <option value="Go back to class">Go back to
                                                class</option>
                                            <option value="Go home">Go home</option>
                                            <option value="Go back to office">Go back to
                                                office</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-modern">Item Used</label>
                                        <textarea class="form-control form-control-modern" id="visitorItemUsed"
                                            name="item_used" rows="2" placeholder="Item used"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-modern">Note</label>
                                        <textarea class="form-control form-control-modern" id="visitorNote" name="note"
                                            rows="2" placeholder="Note"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-modern">Image</label>
                                        <input type="file" class="form-control form-control-modern" id="visitorImage"
                                            name="image" accept="image/*" />
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-modern btn-modern-primary" id="submitVisitorBtn">
                                    <i class="bi bi-check2-circle me-1"></i> <span id="visitorSubmitText">Save</span>
                                </button>
                                <button type="button" class="btn btn-modern btn-modern-outline-primary"
                                    id="resetVisitorFormBtn">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                </button>
                                <button type="button" class="btn btn-modern btn-modern-danger" data-page="list-visitor">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <!-- ==========================================
            LIST VISITOR
            ========================================== -->
            <section class="page-section" id="page-list-visitor">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="page-title">List Visitor</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-modern">
                                <li class="breadcrumb-item"><a href="javascript:void(0)" data-page="dashboard">Home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">List
                                    Visitor</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <button class="btn btn-modern btn-modern-primary" data-page="input-visitor">
                            <i class="bi bi-plus-circle me-1"></i> New Visitor
                        </button>
                    </div>
                </div>

                <div class="card-modern mb-4">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <div class="search-box-modern">
                                    <i class="bi bi-search search-icon"></i>
                                    <input type="text" class="form-control" id="visitorSearch"
                                        placeholder="Search by name or ID..." />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label-modern">Division</label>
                                <select class="form-select form-select-modern" id="visitorDivisionFilter">
                                    <option value="">All Divisions</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label-modern">Start Date</label>
                                <input type="date" class="form-control form-control-modern" id="visitorStartDate" />
                            </div>
                            <div class="col-md-2">
                                <label class="form-label-modern">End Date</label>
                                <input type="date" class="form-control form-control-modern" id="visitorEndDate" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-modern">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <span><i class="bi bi-table me-2 text-primary-custom"></i> Visitor
                            Records</span>
                        <button class="btn btn-sm btn-modern btn-modern-primary" type="button" id="exportVisitorBtn">
                            <i class="bi bi-download me-1"></i> Export
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive-modern">
                            <table class="table table-modern mb-0" id="visitorTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Date Time</th>
                                        <th>level / Grade</th>
                                        <th>Category</th>
                                        <th>Accidental</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="visitorTableBody">

                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-wrap align-items-center justify-content-between p-3 border-top">
                            <span class="text-muted small" id="visitorSummary">Showing 0
                                entries</span>
                            <nav>
                                <ul class="pagination pagination-modern mb-0" id="visitorPagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==========================================
            REPORT
            ========================================== -->
            <section class="page-section" id="page-report">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="page-title">Report</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-modern">
                                <li class="breadcrumb-item"><a href="javascript:void(0)" data-page="dashboard">Home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Report</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="mt-2 mt-sm-0 d-flex gap-2 flex-wrap">
                        <button class="btn btn-modern btn-modern-primary"><i class="bi bi-printer me-1"></i>
                            Print</button>
                        <button class="btn btn-modern btn-modern-success"><i class="bi bi-file-excel me-1"></i>
                            Excel</button>
                        <button class="btn btn-modern btn-modern-danger"><i class="bi bi-file-pdf me-1"></i>
                            PDF</button>
                    </div>
                </div>

                <!-- Report Summary Cards -->
                <div class="row g-3 g-md-4 mb-4">
                    <div class="col-md-4">
                        <div class="report-stat-card">
                            <div class="number">124</div>
                            <div class="label">Today's Visitors</div>
                            <span class="badge bg-soft-success text-success mt-2">+18%</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="report-stat-card">
                            <div class="number">847</div>
                            <div class="label">This Week</div>
                            <span class="badge bg-soft-primary text-primary-custom mt-2">+7%</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="report-stat-card">
                            <div class="number">3,241</div>
                            <div class="label">This Month</div>
                            <span class="badge bg-soft-warning text-warning mt-2">+12%</span>
                        </div>
                    </div>
                </div>

                <!-- Chart Placeholder -->
                <div class="row g-3 g-md-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card-modern">
                            <div class="card-header">
                                <i class="bi bi-bar-chart-line me-2 text-primary-custom"></i>
                                Weekly Visitor Trend
                            </div>
                            <div class="card-body">
                                <div class="chart-placeholder">
                                    <canvas id="reportChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card-modern h-100">
                            <div class="card-header">
                                <i class="bi bi-pie-chart me-2 text-primary-custom"></i>
                                Illness Distribution
                            </div>
                            <div class="card-body">
                                <div class="progress-illness">
                                    <div class="item">
                                        <span class="label">Fever</span>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width:65%;"></div>
                                        </div>
                                        <span class="value">65%</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Cough</span>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width:52%;"></div>
                                        </div>
                                        <span class="value">52%</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Headache</span>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width:42%;"></div>
                                        </div>
                                        <span class="value">42%</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Stomachache</span>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width:28%;"></div>
                                        </div>
                                        <span class="value">28%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Table -->
                <div class="card-modern">
                    <div class="card-header">
                        <i class="bi bi-table me-2 text-primary-custom"></i> Detailed Report
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive-modern">
                            <table class="table table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Total Visitors</th>
                                        <th>Sick</th>
                                        <th>Recovered</th>
                                        <th>Waiting</th>
                                        <th>Division</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>2026-06-28</td>
                                        <td>18</td>
                                        <td>6</td>
                                        <td>8</td>
                                        <td>4</td>
                                        <td>10A</td>
                                    </tr>
                                    <tr>
                                        <td>2026-06-29</td>
                                        <td>22</td>
                                        <td>7</td>
                                        <td>10</td>
                                        <td>5</td>
                                        <td>11B</td>
                                    </tr>
                                    <tr>
                                        <td>2026-06-30</td>
                                        <td>20</td>
                                        <td>5</td>
                                        <td>9</td>
                                        <td>6</td>
                                        <td>12A</td>
                                    </tr>
                                    <tr>
                                        <td>2026-07-01</td>
                                        <td>24</td>
                                        <td>8</td>
                                        <td>11</td>
                                        <td>5</td>
                                        <td>10B</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==========================================
            MASTER STUDENT
            ========================================== -->
            <section class="page-section" id="page-master-student">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="page-title">Master Student</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-modern">
                                <li class="breadcrumb-item"><a href="javascript:void(0)" data-page="dashboard">Home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Master Student</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="card-modern mb-4">
                    <div class="card-body">
                        <div class="search-box-modern">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" class="form-control" id="studentSearch"
                                placeholder="Search student by name or ID..." />
                        </div>
                    </div>
                </div>

                <div class="card-modern">
                    <div class="card-body p-0">
                        <div class="table-responsive-modern">
                            <table class="table table-modern mb-0" id="studentTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Grade</th>
                                        <th>level</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="studentTableBody">

                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-wrap align-items-center justify-content-between p-3 border-top">
                            <span class="text-muted small" id="studentSummary">Showing 0
                                students</span>
                            <nav>
                                <ul class="pagination pagination-modern mb-0" id="studentPagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==========================================
            MASTER SICK CATEGORY
            ========================================== -->
            <section class="page-section" id="page-master-sick">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="page-title">Master Sick Category</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-modern">
                                <li class="breadcrumb-item"><a href="javascript:void(0)" data-page="dashboard">Home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Master Sick Category</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="mt-2 mt-sm-0">
                        <button class="btn btn-modern btn-modern-primary" data-bs-toggle="modal"
                            data-action="open-category-create">
                            <i class="bi bi-plus-circle me-1"></i> Add Category
                        </button>
                    </div>
                </div>

                <div class="card-modern">
                    <div class="card-body p-0">
                        <div class="table-responsive-modern">
                            <table class="table table-modern mb-0" id="categoryTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Severity</th>
                                        <th>Color Badge</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="categoryTableBody">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==========================================
            MASTER DIVISION
            ========================================== -->
            <section class="page-section" id="page-master-division">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="page-title">Master Division</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-modern">
                                <li class="breadcrumb-item"><a href="javascript:void(0)" data-page="dashboard">Home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Master Division</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="card-modern">
                    <div class="card-body p-0">
                        <div class="table-responsive-modern">
                            <table class="table table-modern mb-0" id="divisionTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Division Name</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

        </div><!-- /page-content -->

        <!-- ============================================
        FOOTER
        ============================================ -->
        <footer class="footer-modern">
            <span>&copy; 2026 <strong>HealthDesk</strong> School Nurse System</span>
            <span>Version <span class="version">1.0</span></span>
        </footer>

    </div><!-- /main-wrapper -->

    <!-- ============================================
    TOAST CONTAINER
    ============================================ -->
    <div class="toast-container-modern" id="toastContainer"></div>

    <!-- ============================================
    MODALS
    ============================================ -->

    <!-- Add Student Modal -->
    <div class="modal fade modal-modern" id="addStudentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-600"><i class="bi bi-person-plus text-primary-custom me-2"></i>Add
                        Student
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-modern">Student Name</label>
                        <input type="text" class="form-control form-control-modern" placeholder="Full name" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label-modern">Student ID</label>
                        <input type="text" class="form-control form-control-modern" placeholder="e.g. S-2024-001" />
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-modern">Gender</label>
                            <select class="form-select form-select-modern">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-modern">Class</label>
                            <input type="text" class="form-control form-control-modern" placeholder="e.g. 10" />
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label-modern">Division</label>
                        <select class="form-select form-select-modern">
                            <option value="10A">10A</option>
                            <option value="10B">10B</option>
                            <option value="11A">11A</option>
                            <option value="11B">11B</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-modern">Phone</label>
                        <input type="text" class="form-control form-control-modern" placeholder="Phone number" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modern btn-modern-outline-primary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-modern btn-modern-primary" data-bs-dismiss="modal">Save
                        Student</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade modal-modern" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-600" id="categoryModalTitle"><i
                            class="bi bi-tag text-primary-custom me-2"></i>Add Sick Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm">
                        <input type="hidden" id="categoryId" />
                        <div class="mb-3">
                            <label class="form-label-modern">Name</label>
                            <input type="text" class="form-control form-control-modern" id="categoryName"
                                placeholder="e.g. Fever" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Severity</label>
                            <select class="form-select form-select-modern" id="categorySeverity" required>
                                <option value="">Select severity</option>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-modern">Color Badge</label>
                            <input type="color" class="form-control form-control-modern" id="categoryColorBadge"
                                value="#4F8EF7" />
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modern btn-modern-outline-primary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-modern btn-modern-primary" form="categoryForm"
                        id="saveCategoryBtn">Save
                        Category</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Division Modal -->
    <div class="modal fade modal-modern" id="addDivisionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-600"><i class="bi bi-diagram-3 text-primary-custom me-2"></i>Add
                        Division
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-modern">Division Code</label>
                        <input type="text" class="form-control form-control-modern" placeholder="e.g. 10A" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label-modern">Division Name</label>
                        <input type="text" class="form-control form-control-modern" placeholder="e.g. Class 10 - A" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label-modern">Description</label>
                        <input type="text" class="form-control form-control-modern" placeholder="Description" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modern btn-modern-outline-primary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-modern btn-modern-primary" data-bs-dismiss="modal">Save
                        Division</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Picker Modal -->
    <div class="modal fade modal-modern" id="studentPickerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-600"><i class="bi bi-people text-primary-custom me-2"></i>Select Student
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="search-box-modern mb-3">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control" id="studentPickerSearch"
                            placeholder="Search student..." />
                    </div>
                    <div class="table-responsive-modern">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Grade</th>
                                    <th>Level</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="studentPickerTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <span class="text-muted small" id="studentPickerSummary">Showing 0
                        students</span>
                    <ul class="pagination pagination-modern mb-0" id="studentPickerPagination"></ul>
                </div>
            </div>
        </div>
    </div>


    <!-- ============================================
    SCRIPTS
    ============================================ -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js">
    </script>

    <!-- Bootstrap 5 Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
    </script>

    <script src="assets/js/app.js"></script>
    <script src="assets/js/jquery.blockUI.js"></script>
    <script src="assets/js/script.js?v=1.0.0"></script>
    <script src="assets/js/student.js?v=1.0.0"></script>
    <script src="assets/js/division.js?v=1.0.0"></script>
    <script src="assets/js/category.js?v=1.0.0"></script>
    <script src="assets/js/healthdesk.js?v=1.0.0"></script>

</body>

</html>