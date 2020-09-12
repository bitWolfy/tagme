import { Debug } from "../components/Debug";
import { Util } from "../components/Util";

export class Comment {

    public static async build(): Promise<void> {

        const $newCommentInput = $("#comment-new-content");
        const $commentAddForm = $("#comment-new-form"),
            $commentEditForm = $("form.comment-edit-form");

        let working = false;

        // Edit button
        $("comment a.comment-edit").on("click", (event) => {
            event.preventDefault();

            const $comment = $(event.currentTarget).parents("comment"),
                $toggle = $comment.find("comment-body, comment-edit");

            $toggle.toggleClass("display-none");

            return false;
        });

        // Hide button
        $("comment a.comment-hide").on("click", async (event) => {
            event.preventDefault();

            const $button = $(event.currentTarget),
                $comment = $button.parents("comment");

            const isHidden = $comment.data("hidden");

            const response = await fetch(`/comments/${$comment.data("id")}/hide.json`, {
                method: "POST",
                body: JSON.stringify({
                    action_hide: !isHidden,
                }),
            });

            const text = await response.text();
            console.log(text);
            Debug.log(JSON.parse(text));

            if (isHidden) $button.text("Hide");
            else $button.text("Restore");

            $comment
                .attr("data-hidden", !isHidden + "")
                .data("hidden", !isHidden);

            return false;
        });

        // Respond button
        $("comment a.comment-respond").on("click", (event) => {
            event.preventDefault();

            const $comment = $(event.currentTarget).parents("comment"),
                text = $comment.find("textarea.comment-content").text();

            const quotedText = [];
            for (const line of text.split("\n"))
                quotedText.push("> " + line);

            $newCommentInput.val((index, value) => {
                const newValue = `> ${$comment.data("username")} said:  \n` + quotedText.join("\n");
                if (value.length == 0) return newValue;
                else return value + "\n" + newValue;
            });

            return false;
        });


        // Edit Comment Form
        $commentEditForm.on("submit", async (event) => {
            event.preventDefault();

            if (working) return;
            working = true;

            const $comment = $(event.currentTarget).parents("comment"),
                $input = $comment.find("textarea[name=content]").first();

            const response = await fetch(`/comments/${$comment.data("id")}/edit.json`, {
                method: "POST",
                body: JSON.stringify({
                    "content": Util.getCleanInputValue($input),
                }),
            })

            const text = await response.text();
            // console.log(text);
            Debug.log(JSON.parse(text));

            location.reload();

            working = false;
            return false;
        });


        // New Comment Form
        $commentAddForm.on("submit", async (event) => {
            event.preventDefault();

            if (working) return;
            working = true;

            console.log(Util.getCleanInputValue($newCommentInput), $commentAddForm.data("project"));

            const response = await fetch("/comments/new.json", {
                method: "POST",
                body: JSON.stringify({
                    "project_id": $commentAddForm.data("project"),
                    "content": Util.getCleanInputValue($newCommentInput),
                }),
            })

            const text = await response.text();
            console.log(text);
            Debug.log(JSON.parse(text));

            location.reload();

            working = false;
            return false;
        });
    }

}
