<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-handshake me-2"></i>HR Connect
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i>Басты бет</a>
                </li>
                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'job_seeker'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="jobseeker/browse_vacancies.php"><i class="fas fa-search me-2"></i>Вакансиялар</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile_jobseeker.php"><i class="fas fa-user me-2"></i>Профиль</a>
                    </li>
                <?php elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'hr'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="hr/my_vacancies.php"><i class="fas fa-briefcase me-2"></i>Вакансиялар</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Шығу</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
