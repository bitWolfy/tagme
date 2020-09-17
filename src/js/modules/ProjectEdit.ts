import { Util } from "../components/Util";

export class ProjectEdit {

    public static async build(): Promise<void> {

        const optionsContainer = $("#options-gen");

        // Create a new option input
        $("#options-add-btn").on("click", (event) => {
            event.preventDefault();

            if ($("div.option").length >= 10) return;

            const id = Util.ID.make();

            const container = $("<div>")
                .addClass("option")
                .appendTo(optionsContainer);

            $("<div>")
                .addClass("option-name")
                .html(`
                    <label for="option-name-${id}">Title</label>
                    <input id="option-name-${id}" name="opt-name" placeholder="Display name of the option" required pattern="[\\S ]{3,32}">
                `)
                .appendTo(container);

            $("<div>")
                .addClass("option-controls")
                .html(`<button class="options-remove-btn">Remove</button>`)
                .appendTo(container);

            $("<div>")
                .addClass("option-addtags")
                .html(`
                    <label for="option-addtags-${id}">Added Tags</label>
                    <textarea id="option-addtags-${id}" name="opt-tadd" placeholder="Tags to add if this option is selected"></textarea>
                `)
                .appendTo(container);

            $("<div>")
                .addClass("option-remtags")
                .html(`
                    <label for="option-remtags-${id}">Removed Tags</label>
                    <textarea id="option-remtags-${id}" name="opt-trem" placeholder="Tags to remove if this option is selected"></textarea>
                `)
                .appendTo(container);

            return false;
        });


        // Remove option input
        optionsContainer.on("click", "button.options-remove-btn", (event) => {
            event.preventDefault();
            if ($("div.option").length < 3) return;
            $(event.currentTarget).parents("div.option").remove();
            return false;
        });


        // Validation
        $("#project-new").on("input change focus", "input, textarea", (event) => {
            const $input = $(event.currentTarget);
            $input.toggleClass("invalid", !($input.get()[0] as HTMLInputElement).checkValidity());
            // console.log($input.val());
        });

        let timer;
        const $metaInputError = $("#input-meta-invalid");
        const originalName = $("form#project-new").attr("data-meta");
        $("input[name=meta]").on("input change", (event) => {
            const $input = $(event.currentTarget);
            window.clearTimeout(timer);

            $metaInputError.html("");
            const newVal = ($input.val() + "").toLowerCase();
            if (newVal.length < 3 || newVal == originalName) return;

            timer = window.setTimeout(async () => {
                const serverResponse = await fetch("/projects/" + ($input.val() + "") + ".json");
                const response = await serverResponse.json();
                if (response.data !== null) $metaInputError.html("Already Taken");
            }, 400);
        });

        // Form Submission
        const form = $("#project-new"),
            inputName = form.find("[name=name]"),
            inputMeta = form.find("[name=meta]"),
            inputDesc = form.find("[name=desc]"),
            inputText = form.find("[name=text]"),
            inputTags = form.find("[name=tags]"),
            inputOptions = $("#options-gen"),
            response = $("#submit-response");

        let working = false;
        form.on("submit", async (event) => {
            event.preventDefault();
            response.html("")

            if (working) return;
            working = true;

            for (const element of form.find("input, textarea").get())
                (element as HTMLInputElement).checkValidity();
            if (form.find("input.invalid, textarea.invalid").length != 0) {
                response.html("Invalid data in form")
                return false;
            }

            const optData = inputOptions.children("div.option");
            if (optData.length < 2) {
                response.html("At least two options are required");
                return false;
            }

            const dataPackage = {
                name: Util.getCleanInputValue(inputName),
                meta: Util.getCleanInputValue(inputMeta).toLowerCase(),
                desc: Util.getCleanInputValue(inputDesc),
                text: Util.getCleanInputValue(inputText),
                tags: Util.getUniqueTags(inputTags),
                optmode: form.find("[name=optmode]:checked").val() == "1" ? 1 : 0,
                options: [],
                private: form.find("[name=private]:checked").val() == "1" ? 1 : 0,
            };

            for (const optEntry of optData.get()) {
                const $entry = $(optEntry);

                dataPackage.options.push({
                    name: $entry.find("[name=opt-name]").val() + "",
                    tadd: Util.getUniqueTags($entry.find("[name=opt-tadd]")),
                    trem: Util.getUniqueTags($entry.find("[name=opt-trem]")),
                });
            }

            // console.log(dataPackage);

            // Query the API to validate the username:key
            const serverResponse = await fetch(form.attr("action"), {
                method: "POST",
                body: JSON.stringify(dataPackage),
            });

            const responseText = await serverResponse.text();
            // console.log(responseText);
            const data = JSON.parse(responseText);
            console.log(data);

            if (data.success) {
                location.href = "/projects/" + data.data + "/";
            } else {
                let text = "";
                switch (data.error) {
                    case "error.notfound": { text = "Unknown project ID"; break; }
                    case "error.format": { text = "Wrong input format"; break; }
                    case "error.duplicate": { text = "Duplicate project ID"; break; }
                    default: { text = data.error; }
                }
                response.html("Error: " + text);
            }

            working = false;
            return false;
        })
    }

}
