<?php

require_once ROOT . "/public/betelgeuse/_data.php";

use TagMe\Util;

$strikes = getStrikeList($_GET);

?>

<section class="strike-list">
    <table>
        <thead><tr>
            <th>ID</th>
            <th>User</th>
            <th>Project</th>
            <th>Post</th>
            <th>Timestamp</th>
            <th>Old Tags</th>
            <th>New Tags</th>
        </tr></thead>
        <tbody>
        <?php foreach($strikes["data"] as $strike) { ?>
            <tr>
                <td><?php outprint($strike["id"]); ?></td>
                <td><a href="/users/<?php outprint($strike["user_id"]); ?>"><?php outprint($strike["username"]); ?></a></td>
                <td><a href="/projects/<?php outprint($strike["meta"]); ?>"><?php outprint($strike["meta"]); ?></a></td>
                <td><a href="https://e621.net/post_versions?search[post_id]=<?php outprint($strike["post_id"]); ?>"><?php outprint($strike["post_id"]); ?></a></td>
                <td><?php outprint(Util :: to_time_ago($strike["timestamp"])); ?></td>
                <td><textarea><?php outprint($strike["old_tags"]); ?></textarea></td>
                <td><textarea><?php outprint($strike["new_tags"]); ?></textarea></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</section>
