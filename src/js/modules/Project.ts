import { Debug } from "../components/Debug";
import { E621 } from "../components/E621";
import { Page } from "../components/Page";
import { APIPost } from "../components/responses/APIPost";
import { Util } from "../components/Util";

export class Project {

    public static async build(): Promise<void> {

        const imageContainer = $("#image-container");
        if (imageContainer.length == 0) return;

        const projectID = imageContainer.data("project"),
            query = imageContainer.data("query").split(" ");

        const height = $(window).height() - $("#guidelines").offset().top + $("#source-image").height();
        imageContainer.css("height", height);

        // Get query parameters
        let paramPage = (parseInt(Page.getQueryParameter("page")) || 1);
        if (paramPage < 1 || paramPage > 700) paramPage = 1;

        let paramSeed = Page.getQueryParameter("seed");
        if (paramSeed == null) {
            paramSeed = (new Date().getTime() + "").substr(-6);
            paramPage = 1;
        }

        Debug.log({
            page: paramPage,
            seed: paramSeed,
        });

        Page.removeQueryParameter("page", "seed");

        // Load image data
        query.push("randseed:" + paramSeed);
        let imgData = await E621.Posts.get<APIPost>({ "tags": query, limit: 1, page: paramPage });

        // Number of pages has exceeded number of posts to display
        if ((imgData[0] == undefined || imgData[0]["sample"]["url"] == null) && paramPage > 1) {
            paramSeed = (new Date().getTime() + "").substr(-8);
            paramPage = 1;
            imgData = await E621.Posts.get<APIPost>({ "tags": query, limit: 1, randseed: paramSeed, page: paramPage });
        }

        // Search is empty
        if (imgData[0] == undefined || imgData[0]["sample"]["url"] == null) {
            $("page-container").html(`
                <section class="project-error">
                    <h2>Error - No Image Found</h2>
                    <p>The API query returned empty. Either there are no more posts to sort through, or an error has occurred.</p>
                    <p>Either way, there is nothing more to be done here. <a href="/projects/${projectID}">Return to the project page</a>.</p>
                </section>
            `);
            return;
        }

        // console.log(imgData);
        const post = imgData[~~(imgData.length * Math.random())];


        // Fill in the page elements
        imageContainer.attr("data-id", post.id);

        if (post.file.ext == "webm") {
            $("#source-image").remove();
            $("#source-video")
                .removeClass("display-none")
                .attr({
                    "src": post.file.url,
                    "poster": post.sample.url,
                });
            imageContainer.removeClass("loading");
        } else {
            $("#source-image")
                .attr("src", $("body").attr("altmode") ? post.file.url : post.sample.url)
                .one("load", () => {
                    imageContainer.removeClass("loading");
                })
                .one("error", () => {

                    // Fallback for images that are missing a sample version
                    $("#source-image")
                        .attr("src", post.file.url)
                        .one("load", () => {
                            imageContainer.removeClass("loading");
                        });
                });
            $("#source-video").remove();

            // Initialize the zoom box
            ($("#image-container") as any).zoom({
                url: $("#source-image").attr("src"),
                on: "click",
                magnify: 1.1,
            });
        }

        $("#source-link")
            .attr("href", "https://e621.net/posts/" + post.id)
            .html("#" + post.id);
        $("#source-date").html(new Date(post.created_at).toISOString());
        $("#source-history").attr("href", "https://e621.net/post_versions?search[post_id]=" + post.id);
        $("#tags-old, #tags-new").val(APIPost.getTagString(post));

        $("textarea").height($("textarea")[0].scrollHeight);

        // Correct the page title and URL
        const title = $("title");
        title.html("#" + post.id + " - Character Count Tags - TagMe!");

        window.history.replaceState("Object", "Title", "/projects/" + projectID + "/resolve/" + post.id);

        // Prevent opening links when submitting
        $("a[target=_blank]").on("click", function () { this.blur(); });


        // Actions
        const actions = $("#actions").on("click", "input", () => {
            const addedTags: Set<string> = new Set();
            const removedTags: Set<string> = new Set();
            for (const input of actions.find("input:checked")) {
                const $parent = $(input).parent();
                for (const tag of ($parent.attr("data-added") || "").split(" ").filter((el) => el !== ""))
                    addedTags.add(tag);
                for (const tag of ($parent.attr("data-removed") || "").split(" ").filter((el) => el !== ""))
                    removedTags.add(tag);
            }

            const allTags = new Set(Util.getTags($("#tags-old")));
            removedTags.forEach((tag) => { allTags.delete(tag); })
            addedTags.forEach((tag) => { allTags.add(tag); })

            $("#tags-new").val([...allTags].join(" "));
        });


        // Skip / Submit
        $("#page-skip").on("click", (event) => {
            event.preventDefault();
            location.href = `/projects/${projectID}/resolve?seed=${paramSeed}&page=${paramPage + 1}`;
        });

        let working = false;
        const submitbutton = $("#page-submit").on("click", async (event) => {
            event.preventDefault();

            // Prevent submission while the post is still loading
            if (imageContainer.hasClass("loading")) return;

            // Prevent multiple form submission
            if (working) return;
            working = true;

            submitbutton.attr("loading", "true");

            // Validate inputs
            const oldTags = APIPost.getTagString(post),
                newTags = Util.getCleanInputValue($("#tags-new"));

            if ((newTags.length == 0) ||                    // New tags should not be empty
                (newTags.length < oldTags.length / 2)) {    // New tags should not have shrunk by more than 50%

                // Summon Beetlejuice
                const beetlejuice = await fetch("/betelgeuse/summon.json", {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        project_id: imageContainer.data("project-id"),
                        post_id: imageContainer.data("id"),
                        old_tags: oldTags,
                        new_tags: newTags,
                    }),
                });

                console.log(await beetlejuice.text());

                location.href = `/projects/${projectID}/resolve/`;
                working = false;
                submitbutton.removeAttr("loading");
                return false;
            }

            // If no changes have been made, simply skip to the next post
            if (oldTags == newTags) {
                location.href = `/projects/${projectID}/resolve/`;
                working = false;
                submitbutton.removeAttr("loading");
                return false;
            }

            // Submit changes to e621
            const response = await fetch(`/projects/${projectID}/resolve/${post.id}.json`, {
                method: "POST",
                body: JSON.stringify({
                    postID: post.id,
                    tags: $("#tags-new").val() + "",
                }),
            });
            const responseText = await response.text();
            // console.log(responseText);
            const data = JSON.parse(responseText);
            // console.log(data);

            if (data["success"]) {
                location.href = `/projects/${projectID}/resolve?seed=${paramSeed}&page=${paramPage + 1}`;
                await Util.sleep(500); // Throttle the requests slightly to give e621 time to apply tag changes
            } else $("#resolve-error").removeClass("display-none");

            submitbutton.removeAttr("loading");
            working = false;
            return false;
        });
    }

    private static getShuffleIcon(state: boolean): string {
        return state ? `<i class="fas fa-random"></i>` : `<i class="fas fa-repeat"></i>`;
    }

}

interface ProjectDefinition {
    name: string;
    author: string;
    descr: string;
    tags: string;
    options: ProjectOptions[];
}

interface ProjectOptions {
    name: string;
    removedTags: string;
    addedTags: string;
}
