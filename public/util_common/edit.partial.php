<form id="project-new" action="<?php printInputValue($edit, "action"); ?>" data-meta="<?php printInputValue($edit, "meta"); ?>">
    <input-group>
        <input-label>Project Name</input-label>
        <input type="text" name="name" placeholder="" required pattern="^[\S ]{3,64}$" value="<?php printInputValue($edit, "name"); ?>">
    </input-group>
    <input-group class="input-descr">
        Title of the tagging project. Displayed on in the project list and on the resolution page.
    </input-group>

    
    <input-group>
        <input-label id="input-meta-label">
            Meta Name
            <span id="input-meta-invalid"></span>
        </input-label>
        <input
            type="text"
            name="meta"
            placeholder=""
            required
            pattern="^[\d\w_]{3,16}$"
            value="<?php printInputValue($edit, "meta"); ?>"
            <?php if(isset($edit) && isset($edit["hide_meta"]) && $edit["hide_meta"]) { echo "disabled"; } ?>
        >
    </input-group>
    <input-group class="input-descr">
        Short name, used primarily in the page URL. Should (roughly) match the project title.<br />
        Can only contain letters, numbers, and underscores.
    </input-group>

    <input-group>
        <input-label>Description</input-label>
        <textarea name="desc" placeholder="" required pattern="^.{3,255}$"><?php printInputValue($edit, "desc"); ?></textarea>
    </input-group>
    <input-group class="input-descr">
        Project description, in the form of a question that the options below can answer.
    </input-group>

    <input-group>
        <input-label>Tagging Guidelines</input-label>
        <textarea name="text" placeholder="" required pattern="^.{3,10000}$"><?php printInputValue($edit, "text"); ?></textarea>
    </input-group>
    <input-group class="input-descr">
        The appropriate section of the tagging rules that applies to the options below.<br />
        <a href="https://www.markdownguide.org/basic-syntax/">Markdown syntax</a> is supported.
    </input-group>

    <input-group>
        <input-label>Tag String</input-label>
        <input type="text" name="tags" placeholder="ex. -solo -duo -group -zero_pictured" required pattern="^[\S ]{3,10000}$" value="<?php outprint(isset($edit["tags"]) ? implode(" ", $edit["tags"]) : ""); ?>">
    </input-group>
    <input-group class="input-descr">
        Posts that match this space-separated list of tags will be displayed when resolving the project.<br />
        Treat this like E621's searchbar - the same rules apply, but the maximum is 35 tags.<br />
        Flash and WEBM formats are currently not supported.
    </input-group>

    <input-group>
        <input-label>Options Mode</input-label>
        <input-section>
            <input type="radio" id="optmode-0" name="optmode" value="0" <?php outprint((!isset($edit["optmode"]) || $edit["optmode"] == "0") ? "checked" : ""); ?>>
            <label for="optmode-0">Select one</label>

            <input type="radio" id="optmode-1" name="optmode" value="1" <?php outprint((isset($edit["optmode"]) && $edit["optmode"] == "1") ? "checked" : ""); ?>>
            <label for="optmode-1">Select all that apply</label>
        </input-section>
    </input-group>
    <input-group class="input-descr">
        In "select one" mode, the user can only pick one of the available options.<br />
        In "select all that apply", any combination of options is permitted.
    </input-group>

    <input-group>
        <input-label>Options</input-label>
        <div id="options-gen">
<?php
if(isset($edit["options"])) { 
    foreach($edit["options"] as $option) {
        printOptionHTML($option["name"], implode(" ", $option["tadd"]), implode(" ", $option["trem"]));
    }
} else {
    for($i = 0; $i < 2; $i++) {
        printOptionHTML();
    }
} ?>
        </div>
        <div id="options-add">
            <button id="options-add-btn">Add Option</button>
            <div id="options-add-descr">
                Options to choose from. At least 2 are required, but up to 10 are allowed.<br />
                "Added" and "Removed tags" fields must contain valid tags, separated by spaces.
            </div>
        </div>
    </input-group>
    <input-group class="input-descr">
    </input-group>

    <input-group>
        <input-label>Conditional Tags</input-label>
        <input type="text" name="contags" placeholder="ex. multiple_images" pattern="^[\S ]{0,10000}$" value="<?php outprint(isset($edit["contags"]) ? implode(" ", $edit["contags"]) : ""); ?>">
    </input-group>
    <input-group class="input-descr">
        These tags are added if more than one option is selected.<br />
        Add a minus (-) before the tag to remove it instead.
    </input-group>

    <input-group>
        <input-label>Project Visibility</input-label>
        <input-section>
            <input type="radio" id="private-0" name="private" value="0" <?php outprint((!isset($edit["is_private"]) || $edit["is_private"] == "0") ? "checked" : ""); ?>>
            <label for="private-0">Public</label>

            <input type="radio" id="private-1" name="private" value="1" <?php outprint((isset($edit["is_private"]) && $edit["is_private"] == "1") ? "checked" : ""); ?>>
            <label for="private-1">Unlisted</label>
        </input-section>
    </input-group>
    <input-group class="input-descr">
        Unlisted projects are still available via a direct link, but not visible in the project list.
    </input-group>

    <div class="submit-group">
        <div></div>
        <div id="submit-response"></div>
        <button type="submit">Submit</button>
    </div>
</form>

<?php

function printInputValue($entries, $index) {
    return outprint(isset($entries[$index]) ? $entries[$index] : "");
}

function printOptionHTML($title = "", $tadd = "", $trem = "") {
    $elementID = bin2hex(random_bytes(4));
    ?>
    <div class="option">
        <div class="option-name">
            <label for="option-name-<?php outprint($elementID); ?>">Title</label>
            <input id="option-name-<?php outprint($elementID); ?>" name="opt-name" placeholder="Display name of the option" required="" pattern="[\S ]{3,32}" class="" value="<?php outprint($title); ?>">
        </div>
        <div class="option-addtags">
            <label for="option-addtags-<?php outprint($elementID); ?>">Added Tags</label>
            <textarea id="option-addtags-<?php outprint($elementID); ?>" name="opt-tadd" placeholder="Tags to add if this option is selected"><?php outprint($tadd); ?></textarea>
        </div>
        <div class="option-remtags">
            <label for="option-remtags-<?php outprint($elementID); ?>">Removed Tags</label>
            <textarea id="option-remtags-<?php outprint($elementID); ?>" name="opt-trem" placeholder="Tags to remove if this option is selected"><?php outprint($trem); ?></textarea>
        </div>
        <div class="option-move">
            <button class="options-move-up">&uarr;</button>
            <button class="options-move-down">&darr;</button>
        </div>
        <div class="option-controls">
            <button class="options-remove-btn">Remove</button>
        </div>
    </div>
<?php
}

?>
