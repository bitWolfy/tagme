<section id="userauth-login">
    <h1>Login</h1>
    <form id="userauth-form" action="/auth/login.json">
        <input name="username" placeholder="Login" />
        <input name="password" placeholder="API Key" />
        <button type="submit" class="loading-button">Submit</button>
        <span class="userauth-remember">
            <input type="checkbox" name="remember_me" id="remember_me" checked>
            <label for="remember_me">Remember Me</label>
        </span>
    </form>
    <div id="userauth-status"></div>
    <div id="userauth-text">
        The usage of TagMe! utility requires proper authorization in order to interact with e621's systems.<br />
        All changes made to the posts are applied with <b>your username</b> and are thus your responsibility.
    </div>
</section>

<script src="https://www.google.com/recaptcha/api.js?render=<?php echo \TagMe\Configuration :: $recaptcha_key; ?>"></script>

<?php return [ "title" => "Log In - TagMe!" ]; ?>
