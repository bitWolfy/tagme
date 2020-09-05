<?php

require_once ROOT . "/public/projects/_data.php";
require_once ROOT . "/public/users/_data.php";

$projects = getProjectList([ "is_deleted" => "false", ]);
$changes = getUserList([ "order" => "changes",]);

?>

<!--
<section>
<section-header>E621 Tagging Project Resolver</section-header>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam volutpat nisi turpis, sit amet elementum erat ultricies eu. Aenean hendrerit tortor a lacus dapibus laoreet. Suspendisse ligula lorem, faucibus vel pharetra dignissim, pellentesque sit amet nisi. Aliquam pulvinar convallis nulla, eu faucibus nisl. Suspendisse potenti. Donec rutrum, elit sit amet molestie porta, velit velit tempus sapien, vel volutpat purus erat quis urna. Aliquam feugiat vestibulum sapien, vel accumsan purus tempus eu. Nam mollis mauris vitae dignissim hendrerit. Nunc eleifend vel ligula venenatis rutrum. Maecenas at tempus nunc.</p>
<p>Phasellus sagittis est tellus, vel vestibulum nibh mollis vel. Aenean a bibendum massa. Morbi purus tellus, ultrices eu iaculis eu, tincidunt id turpis. Nunc placerat egestas gravida. Nullam egestas faucibus molestie. Quisque a nunc ut mi rhoncus imperdiet. Fusce nec sagittis urna, in faucibus leo. Vivamus nec venenatis lectus, ac rhoncus neque. Morbi iaculis aliquet posuere. Maecenas egestas libero ac lobortis euismod. Praesent orci ex, suscipit ut lorem nec, ornare accumsan odio.</p>
</section>
-->

<section class="home-display">

    <section-header>Latest Projects</section-header>
    <section-header>Top Contributors</section-header>

    <section class="home-projects">
    <?php foreach($projects["data"] as $entry) { ?>

        <div><a href="/projects/<?php outprint($entry["meta"]); ?>"><?php outprint($entry["name"]); ?></a></div>
        <div><?php outprint($entry["desc"]); ?></div>

    <?php } ?>
    </section>

    <section class="home-contributions">
    <?php foreach($changes["data"] as $entry) { ?>

        <div><a href="/users/<?php outprint($entry["user_id"]); ?>"><?php outprint($entry["username"]); ?></a></div>
        <div><?php outprint($entry["changes"]); ?></div>

    <?php } ?>
    </section>

</section>

<?php return []; ?>
