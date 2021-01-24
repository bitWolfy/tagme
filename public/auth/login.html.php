<section id="userauth-login">
    <h1>Login</h1>
    <form id="userauth-form" action="/auth/login.json">
        <input name="username" type="text" placeholder="Login" />
        <input name="password" type="password" placeholder="API Key" />
        <button type="submit" class="loading-button">Submit</button>
        <span class="userauth-remember">
            <input type="checkbox" name="remember_me" id="remember_me" checked>
            <label for="remember_me">Remember Me</label>
        </span>
    </form>
    <div id="userauth-status"></div>
    <div id="userauth-text">
        <p>
            The usage of TagMe! utility requires authorization in order to interact with e621's systems.
            The API key can be accessed from the <a href="https://e621.net/users/home">account page</a>.
        </p>
        <p>
            All tag changes are made with <b>your username</b>.
            By using TagMe, you accept full responsibility for any post edits that you make, as well as the consequences of tag abuse.
        </p>
        <p>
            TagMe usese cookies to store your API key. This information is not accessible to the site's administrators in any way.
            If you believe that your account has been compromised, regenerate the API key immediately and contact e621's administrators.
        </p>
        <p>
            E621 <a href="https://e621.net/wiki_pages/1638">rules</a> and <a href="https://e621.net/static/terms_of_service">terms of service</a> fully apply.
            Misuse of this utility may lead to your suspension from the service.
        </p>
    </div>
</section>

<script src="https://www.google.com/recaptcha/api.js?render=<?php echo \TagMe\Configuration :: $recaptcha_key; ?>"></script>

<?php return [ "title" => "Log In - TagMe!" ]; ?>
