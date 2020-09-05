<form class="search-container" action="/projects">
    <input name="search" class="search-input" value="<?php outprint(isset($_GET["search"]) ? $_GET["search"] : ""); ?>">
    <button type="submit" class="search-button" title="Find"></button>
</form>
