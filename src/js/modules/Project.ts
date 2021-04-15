import { Debug } from "../components/Debug";
import { E621 } from "../components/E621";
import { Page } from "../components/Page";
import { Blacklist } from "../components/post/Blacklist";
import { Post } from "../components/post/Post";
import { APIPost } from "../components/responses/APIPost";
import { Sequence } from "../components/Sequence";
import { Util } from "../components/Util";

export class Project {

    public static async build(): Promise<void> {

        const imageContainer = $("#image-container");
        if (imageContainer.length == 0) return;

        const projectID = imageContainer.data("project"),
            projectName = imageContainer.data("project-name"),
            projectContags = (imageContainer.data("project-contags").split(" ") as string[]),
            query = imageContainer.data("query").split(" "),
            unrandom = imageContainer.data("static");

        $("#source-image").on("load reload", () => {

            const top = $("#image-data").offset().top,
                bottomEl = $("#proceed, #proceed-unauthorized"),
                bottom = bottomEl.offset().top + bottomEl.innerHeight(),
                descHeight = bottom - top;

            const height = $(window).height() - $("#image-container").offset().top - descHeight;
            imageContainer.css("height", height);
        });

        // Get query parameters
        let sequence = Sequence.get(projectID);
        Debug.log({
            page: sequence.page,
            seed: sequence.seed,
        });

        Page.removeQueryParameter("page", "seed");

        // Load image data
        let imgData: APIPost[];
        let error = false;
        if (unrandom) imgData = await E621.Posts.get<APIPost>({ "tags": query, limit: 1, page: 1 });
        else {
            query.push("randseed:" + sequence.seed);
            try { imgData = await E621.Posts.get<APIPost>({ "tags": query, limit: 1, page: sequence.page }); }
            catch (err) { error = true; }
        }

        // Number of pages has exceeded number of posts to display
        if (!error && (imgData[0] == undefined || imgData[0]["sample"]["url"] == null) && sequence.page > 1) {
            sequence = Sequence.reset(projectID);
            try { imgData = await E621.Posts.get<APIPost>({ "tags": query, limit: 1, randseed: sequence.seed, page: sequence.page }); }
            catch (err) { error = true; }
        }

        // Search is empty
        if (error || imgData[0] == undefined || imgData[0]["sample"]["url"] == null) {
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
        const postData = imgData[~~(imgData.length * Math.random())];
        const post = Post.make(postData);


        // Fill in the page elements
        imageContainer.attr("data-id", post.id);

        if (post.file.ext == "webm") {
            $("#source-image").remove();
            const video = $("#source-video")
                .removeClass("display-none")
                .attr({
                    "src": post.file.original,
                    "poster": post.file.sample,
                });
            imageContainer.removeClass("loading");

            if (post.isBlacklisted()) {
                video.one("click", () => {
                    Blacklist.disableAll();
                    post.updateVisibility();
                });
            }
        } else {
            const image = $("#source-image")
                .attr("src", post.file.sample)
                .one("load", () => {
                    rebuildZoom();

                    // Replace the sampled image with the high-res one
                    image
                        .attr("src", post.file.original)
                        .one("load", () => {
                            rebuildZoom(false);
                            imageContainer.addClass("loaded");
                        });
                    // imageContainer.find("img[role='presentation']").attr("src", post.file.url);

                    imageContainer.removeClass("loading");
                })
                .one("error", () => {

                    // Fallback for images that are missing a sample version
                    image
                        .attr("src", post.file.original)
                        .one("load", () => {
                            rebuildZoom(false);
                            imageContainer
                                .removeClass("loading")
                                .addClass("loaded");
                        });
                })
                .on("reload", () => {
                    rebuildZoom(!imageContainer.hasClass("loaded"));
                });
            $("#source-video").remove();

            if (post.isBlacklisted()) {
                imageContainer.one("click", () => {
                    Blacklist.disableAll();
                    post.updateVisibility();
                    rebuildZoom();
                });
            }

            function rebuildZoom(sample = true): void {
                image.removeClass("zoom");
                imageContainer.trigger("zoom.destroy");

                if (post.isBlacklisted()) return;

                const ratio = Util.Math.round((sample ? post.sampleImg.height : post.img.height) / image.height());
                const unratio = Util.Math.round(image.height() / (sample ? post.sampleImg.height : post.img.height));

                Debug.log(
                    "zoom",
                    (sample ? post.sampleImg.height : post.img.height),
                    image.height(),
                    ratio,
                    unratio,
                    ratio > 1 ? 1.25 : (3 - ratio)
                );

                ($("#image-container") as any)
                    .zoom({
                        url: image.attr("src"),
                        on: "click",
                        magnify: ratio > 1 ? 1.25 : (3 - ratio),
                        onZoomIn: () => { image.addClass("zoom"); },
                        onZoomOut: () => { image.removeClass("zoom"); },
                    });
            }
        }

        $("#source-link")
            .attr("href", "https://e621.net/posts/" + post.id)
            .html("#" + post.id);
        $("#source-date").html(new Date(post.date.raw).toISOString());
        $("#source-history").attr("href", "https://e621.net/post_versions?search[post_id]=" + post.id);
        $("#tags-old, #tags-new").val(post.tagString);


        // Check for DNP status
        if (post.tags.artist.has("avoid_posting") || post.tags.artist.has("conditional_dnp")) {
            $("#dnp-notice").removeAttr("style");
        }

        // Check for locked tags
        const locked = new Set<string>();
        for (const tag of post.tags.locked)
            locked.add(tag.startsWith("-") ? tag.substr(1) : ("-" + tag));

        for (const taglist of $(".taglist")) {
            for (const tag of $(taglist).find("a").get()) {
                const $tag = $(tag);
                if (!locked.has($tag.text())) continue;
                $tag.addClass("locked");
            }
        }

        let maxTextareaHeight = 0;
        for (const textarea of $("#tags textarea").get()) {
            const $elem = $(textarea);
            $elem.css("height", 0); // Believe it or not, this is necessary
            const height = Math.ceil($elem[0].scrollHeight / 16);
            if (height > maxTextareaHeight) maxTextareaHeight = height;
        }
        $("#tags textarea").css("height", maxTextareaHeight + "rem");

        // Correct the page title and URL
        const title = $("title");
        title.html("#" + post.id + " - " + projectName + " - TagMe!");

        window.history.replaceState("Object", "Title", "/projects/" + projectID + "/resolve/" + post.id);

        // Prevent opening links when submitting
        $("a[target=_blank]").on("click", function () { this.blur(); });


        // Actions
        const actions = $("#actions").on("click", "input", () => {

            let counter = 0;

            // Compile a list of changes based on the active inputs
            const changes: Set<string> = new Set();
            for (const input of actions.find("input:checked")) {
                const $parent = $(input).parent();
                for (const tag of ($parent.attr("data-added") || "").split(" ").filter((el) => el !== ""))
                    changes.add(tag);
                for (const tag of ($parent.attr("data-removed") || "").split(" ").filter((el) => el !== ""))
                    changes.add("-" + tag);

                counter++;
            }

            if (counter > 1) {
                for (const tag of projectContags) {
                    if (tag.startsWith("-")) changes.delete(tag.substr(1));
                    else changes.add(tag);
                }
            }

            $("#tags-changes").val([...changes].join(" "));
        });


        // Skip / Submit
        $("#page-skip").on("click", (event) => {
            event.preventDefault();
            if (!unrandom) Sequence.increment(projectID);
            location.href = `/projects/${projectID}/resolve`;
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
            const oldTags = post.tagString,
                oldTagSet = new Set(post.tagString.split(" ")),
                changesList = Util.getCleanInputValue($("#tags-changes")),
                mergedChanges = TagCache.mergeChanges(oldTagSet, changesList);

            if ((mergedChanges.size == 0) ||                    // New tags should not be empty
                (mergedChanges.size < oldTagSet.size / 2)) {    // New tags should not have shrunk by more than 50%

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
                        new_tags: changesList,
                    }),
                });

                console.log(await beetlejuice.text());

                Sequence.increment(projectID);
                location.href = `/projects/${projectID}/resolve/`;
                working = false;
                submitbutton.removeAttr("loading");
                return false;
            }

            // If the changes are empty, simply skip the next post
            if (changesList.length == 0) {
                Sequence.increment(projectID);
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
                    changes: changesList,
                    oldTags: oldTags,
                }),
            });
            const responseText = await response.text();
            // console.log(responseText);
            const data = JSON.parse(responseText);
            // console.log(data);

            if (data["success"]) {
                // tagCache.add(post.id, post.tagString);
                Sequence.increment(projectID);
                location.href = `/projects/${projectID}/resolve`;
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

class TagCache {

    private ids: Set<number>;
    private cache: TagCacheData;

    public constructor() {
        const data = JSON.parse(sessionStorage.getItem("tagcache") || "{}");

        this.ids = new Set();
        this.cache = {};

        let index = 0;
        for (const key of Object.keys(data).reverse()) {
            const keyVal = parseInt(key);
            if (!keyVal) continue;

            this.ids.add(keyVal);
            this.cache[keyVal] = data[key];

            if (index >= 100) break;
            index++;
        }

        sessionStorage.setItem("tagcache", JSON.stringify(this.cache));
    }

    public has(id: number): boolean {
        return this.ids.has(id);
    }

    public add(id: number, tags: string): void {
        this.cache[id] = tags;
        this.ids.add(id);
        sessionStorage.setItem("tagcache", JSON.stringify(this.cache));
    }

    public get(id: number): string {
        return this.cache[id];
    }

    public static getRestorationString(oldString: string, newString: string): string {
        const oldTags = Util.getTags(oldString),
            newTags = Util.getTags(newString);

        const removed = oldTags.filter((tag) => !newTags.includes(tag));
        const added = newTags.filter((tag) => !oldTags.includes(tag));
        added.forEach((part, index) => {
            added[index] = "-" + part;
        });

        return removed.join(" ") + " " + added.join(" ");
    }

    public static mergeChanges(tags: Set<string>, changeString: string): Set<string> {
        const changes = Util.getTags(changeString);

        for (const change of changes) {
            if (change.startsWith("-")) tags.delete(change.substr(1));
            else tags.add(change);
        }

        return tags
    }

}

interface TagCacheData {
    [id: number]: string;
}
