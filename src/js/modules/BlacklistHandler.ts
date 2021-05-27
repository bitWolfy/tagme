import { E621 } from "../components/E621";
import { Blacklist } from "../components/post/Blacklist";
import { APICurrentUser } from "../components/responses/APIUser";
import { Util } from "../components/Util";

export class BlacklistHandler {

    private static blacklist: Blacklist;

    public static async build(): Promise<void> {

        const userMetaEl = $("meta[name=current-user-id]");
        if (userMetaEl.length == 0) return;
        const userID = userMetaEl.attr("content");

        let blacklistTime = parseInt(Util.LS.getItem("blacklist.time") || "0");
        let blacklistData = JSON.parse(Util.LS.getItem("blacklist.data") || "[]");
        let blacklistState = Util.LS.getItem("blacklist.state") !== "false";

        if (blacklistTime < (Util.Time.now() - Util.Time.DAY))
            await reloadBlacklist();

        $("<meta>")
            .attr({
                "name": "blacklisted-state",
                "content": blacklistState,
            })
            .insertAfter("meta[name=current-user-id]");

        $("<meta>")
            .attr({
                "name": "blacklisted-tags",
                "content": JSON.stringify(blacklistData),
            })
            .insertAfter("meta[name=current-user-id]");

        const stateSwitch = $("#blacklist-switch")
            .removeAttr("style")
            .html(`<i class="fas fa-bold"></i>`)
            .attr("enabled", blacklistState ? "true" : "false")
            .on("click", async (event) => {
                event.preventDefault();
                blacklistState = !blacklistState;

                stateSwitch
                    .attr("enabled", blacklistState ? "true" : "false")
                    .attr("title", `Blacklist: ${blacklistState ? "ON" : "OFF"}`);

                await reloadBlacklist();
            });

        async function reloadBlacklist(): Promise<void> {
            try {
                if (blacklistState) {
                    const userData = await E621.User.id(userID).first<APICurrentUser>();
                    if (userData !== null)
                        blacklistData = userData.blacklisted_tags.split("\n").filter(n => n) || [];
                } else blacklistData = [];
            } catch (e) { blacklistData = []; }

            blacklistTime = Util.Time.now();

            Util.LS.setItem("blacklist.time", blacklistTime + "");
            Util.LS.setItem("blacklist.data", JSON.stringify(blacklistData));
            Util.LS.setItem("blacklist.state", blacklistState + "");
        }
    }

}
