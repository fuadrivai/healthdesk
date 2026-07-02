<!-- ============================================
        TOP NAVBAR
        ============================================ -->
<nav class="top-navbar" id="topNavbar">
    <div class="navbar-left">
        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        <div class="nav-brand-text d-none d-sm-flex">
            <i class="bi bi-heart-pulse-fill text-primary-custom"></i>
            HealthDesk
            <span class="dot">·</span>
            <small>School Nurse</small>
        </div>
        <div class="nav-brand-text d-sm-none">
            <i class="bi bi-heart-pulse-fill text-primary-custom"></i>
            HD
        </div>
    </div>

    <div class="navbar-right">
        <button class="nav-icon-btn" id="notifBtn" data-bs-toggle="tooltip" title="Notifications">
            <i class="bi bi-bell-fill"></i>
            <span class="badge-dot">3</span>
        </button>

        <div class="user-profile" id="userProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="avatar">SN</div>
            <div>
                <div class="user-name">Sarah Nurse</div>
                <div class="user-role">School Nurse</div>
            </div>
            <i class="bi bi-chevron-down dropdown-toggle-icon"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userProfileDropdown"
            style="border:none; border-radius:var(--radius-sm); box-shadow:var(--shadow-hover); padding:0.5rem; min-width:180px;">
            <li><a class="dropdown-item" href="javascript:void(0)"><i class="bi bi-person me-2"></i>Profile</a>
            </li>
            <li><a class="dropdown-item" href="javascript:void(0)"><i class="bi bi-gear me-2"></i>Settings</a>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="javascript:void(0)"><i
                        class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
    </div>
</nav>