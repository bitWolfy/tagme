<?php

\TagMe\Auth\Session :: clearSessionCookies();
\TagMe\Auth\User :: reset();
?>

<section id="userauth-logout">
    There is nothing here. <a href="/">Turn back</a>.<br />
    Or don't. I'm a sign, not a cop.
</section>

<?php return [ "title" => "Log Out - TagMe!" ]; ?>
