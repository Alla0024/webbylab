<header class="header">
    <div class="container">
        <div class="header_inner">
            <div class="header_main"></div>
            <nav class="nav">
                <a class="nav__link" href="index.php?action=movies">Movies</a>
                <a class="nav__link" href="index.php?action=actors">Actors</a>
                <?php if (!isset($_SESSION["Logged"])) { ?>
                    <a class="nav__link" href="index.php?action=registration">Registration</a>
                    <a class="nav__link" href="index.php?action=login">Login</a>
                <?php } else { ?>
                    <a class="nav__link" href="index.php?action=logout">Log out</a>
                <?php } ?>
            </nav>
        </div>
    </div>
</header>
