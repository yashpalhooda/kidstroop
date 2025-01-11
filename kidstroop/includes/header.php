<?php
require_once 'config.php';
if (!isset($pageTitle)) {
    $pageTitle = SITE_NAME;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js" defer></script>
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">
    <style>
        :root {
            --transition-speed: 0.3s;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background-color var(--transition-speed), color var(--transition-speed);
        }

        .navbar,
        .footer {
            transition: background-color var(--transition-speed), color var(--transition-speed);
        }

        main {
            flex: 1;
            padding: 2rem 0;
        }

        .icon-button {
            position: relative;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color var(--transition-speed);
            border: none;
            background: none;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 5%;
            display: none;
            flex-direction: column;
            background-color: white;
            box-shadow: 0 0.5rem 1rem rgba(7, 7, 7, 0.15);
            padding: 0.5rem;
            border-radius: 0.25rem;
            z-index: 1000;
        }

        .dropdown-menu.show {
            display: flex;
        }

        .icon-button:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }

        [data-bs-theme="dark"] .icon-button:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .search-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height var(--transition-speed);
        }

        .search-container.show {
            max-height: 100px;
        }

        .sun-icon,
        .moon-icon {
            width: 24px;
            height: 24px;
        }

        [data-bs-theme="light"] .moon-icon,
        [data-bs-theme="dark"] .sun-icon {
            display: none;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .navbar-brand img {
            max-height: 80px;
            display: inline-block;
        }

        .navbar-brand span {
            display: none;
        }

        .navbar-nav {
            margin-left: auto;
            margin-right: auto;
        }

        .nav-link {
            font-size: 1.3rem;
            font-family: 'Comic Sans MS', cursive, sans-serif;
        }

        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #212529;
            border-color: #495057;
        }

        [data-bs-theme="dark"] .dropdown-menu .dropdown-item {
            color: #fff;
        }

        [data-bs-theme="dark"] .dropdown-menu .dropdown-item:hover {
            background-color: #495057;
        }

        [data-bs-theme="dark"] .dropdown-header {
            color: #adb5bd;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <img src="logo.png" alt="<?php echo SITE_NAME; ?>">
                <span><?php echo SITE_NAME; ?></span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <button class="icon-button d-lg-none" onclick="toggleSearch()">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActiveMenu('index.php'); ?>" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActiveMenu('about.php'); ?>" href="/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActiveMenu('services.php'); ?>" href="/services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActiveMenu('contact.php'); ?>" href="/contact">Contact</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <button class="icon-button" onclick="toggleTheme()">
                        <svg class="sun-icon" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0 .39-.39.39-1.03 0-1.41L5.99 4.58zm12.37 12.37c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0 .39-.39.39-1.03 0-1.41l-1.06-1.06zm1.06-10.96c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06zM7.05 18.36c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06z" />
                        </svg>
                        <svg class="moon-icon" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9c0-.46-.04-.92-.1-1.36-.98 1.37-2.58 2.26-4.4 2.26-3.03 0-5.5-2.47-5.5-5.5 0-1.82.89-3.42 2.26-4.4-.44-.06-.9-.1-1.36-.1z" />
                        </svg>
                    </button>
                    <button id="userButton" class="icon-button" onclick="toggleUserMenu()">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </button>
                </div>
                <!-- User Menu Dropdown -->
                <div id="userMenu" class="dropdown-menu dropdown-menu-end">
                    <h6 class="dropdown-header">User Menu</h6>
                    <a class="dropdown-item" href="/includes/login.php">Sign In</a>
                    <a class="dropdown-item" href="/includes/login.php?form=signup">Sign Up</a>
                </div>
            </div>
    </nav>
<!-- / -->
    <!-- Search Container -->
    <div id="searchContainer" class="search-container bg-body-tertiary">
        <div class="container py-3">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="button" onclick="toggleSearch()">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/scripts.js" defer></script>
</body>
</html>