<?php

use TagMe\Auth\User;
use Tagme\Auth\UserRank;

?>

<header>
    <div class="header-logo">
        <a href="/" class="header-logo-link">TagMe!</a>
    </div>
    <div class="header-search">
<?php include "search.partial.php"; ?>
    </div>

    <nav>
        <div class="nav-main">
            <a href="/projects/" class="header-link">All Projects</a>
            <?php if(User :: rankMatches(UserRank :: PRIVILEGED)) { ?>
            <a href="/projects/new" class="header-link">New Project</a>
            <?php } ?>
            <a href="/users/" class="header-link">Users</a>
            <a href="/comments/" class="header-link">Comments</a>
        </div>
        <div class="nav-auth">
<?php if(User :: isLoggedIn()) { ?>
            <a href="/users/<?php echo User :: getUserID(); ?>" class="header-link"><?php echo User :: getUsername(); ?></a>
            <a href="/auth/logout" class="header-link" id="logout-link">Logout</a>
<?php } else { ?>
            <a href="/auth/login" class="header-link header-right">Login</a>
<?php } ?>
        </div>
    </nav>

    <div class="side-links">
        <a id="theme-switch" title="Theme: DUSK"></a>
        <a id="random-switch" title="Mode: SHUFFLE"></a>
    </div>

</header>

