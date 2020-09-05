import { Page, PageDefintion } from "../components/Page";

declare const grecaptcha: any;

export class User {

    public static init(): void {

        // Login Form
        const username = $("#userauth-form input[name=username]"),
            apikey = $("#userauth-form input[name=password]"),
            remember = $("#remember_me"),
            submit = $("#userauth-form button[type=submit]"),
            status = $("#userauth-status");

        $("#userauth-form").on("submit", async (event) => {
            event.preventDefault();
            status.html("");
            submit.attr("loading", "true");

            // Empty fields are always incorrect
            if (username.val() == "" || apikey.val() == "") {
                status.html("Authentication Failed");
                submit.removeAttr("loading");
                return;
            }

            // Validate the captcha
            const recaptcha = await grecaptcha.execute($("meta[name=recaptcha]").attr("content"), { action: "submit" });
            console.log(recaptcha);
            const captchaResponse = await fetch(`/auth/captcha.json`, {
                method: "POST",
                body: JSON.stringify({
                    captcha: recaptcha,
                }),
            });

            const captchaText = await captchaResponse.text();
            console.log(captchaText);
            if (!JSON.parse(captchaText)["success"]) {
                status.html("Authentication Failed");
                return false;
            }

            // Query the API to validate the username:key
            const response = await fetch(`/auth/login.json`, {
                method: "POST",
                body: JSON.stringify({
                    username: username.val() + "",
                    password: apikey.val() + "",
                    remember: remember.is(":checked"),
                }),
            });

            const responseText = await response.text();
            console.log(responseText);
            const data = JSON.parse(responseText);

            // Response evaluation
            if (data["success"]) {
                if (Page.matches(PageDefintion.auth_login)) location.href = "/";
                else location.reload();
            } else status.html("Authentication Failed");

            submit.removeAttr("loading");
            return false;
        });


        // Logout Link
        $("#logout-link").on("click", async (event) => {
            event.preventDefault();

            await fetch("/auth/logout.json");
            location.reload();

            return false;
        });


        // Ban User
        const $banButton = $("#action-user-ban");
        $banButton.on("click", async (event) => {
            event.preventDefault();

            const unban = $banButton.text() == "Unban";
            const banAction = await fetch($banButton.attr("href") + "?unban=" + unban);

            $banButton.text(unban ? "Ban" : "Unban");

            console.log(await banAction.text());

            return false;
        });

    }


}
