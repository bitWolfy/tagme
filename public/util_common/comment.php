<?php
use TagMe\Util;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;
?>
<comment
    id="comment-<?php outprint($comment["id"]); ?>"

    data-id="<?php outprint($comment["id"]); ?>"
    data-user-id="<?php outprint($comment["user_id"]);?>"
    data-username="<?php outprint($comment["username"]); ?>"
    data-added-on="<?php outprint($comment["added_on"]); ?>"
    data-edited-on="<?php outprint($comment["edited_on"]); ?>"
    data-project="<?php outprint($project["project_id"]); ?>"
    data-hidden="<?php echo $comment["is_hidden"] == 1 ? "true" : "false"; ?>"
>
    <comment-header>
        <a href="/users/<?php outprint($comment["user_id"]); ?>"><?php outprint($comment["username"]); ?></a>
        said
        <span class="comment-date" title="<?php outprint(date("y-m-d h:i", $comment["added_on"])); ?>">
            <?php outprint(Util :: to_time_ago($comment["added_on"])); ?>
        </span>
        <?php if($comment["edited_on"] > $comment["added_on"]) { ?>
            <span class="text-muted">
                (edited 
                <span class="comment-date" title="<?php outprint(date("y-m-d h:i", $comment["edited_on"])); ?>">
                    <?php outprint(Util :: to_time_ago($comment["edited_on"])); ?>
                </span>
                )
            </span>
        <?php } ?>
        <comment-actions>
            <a href="/projects/<?php outprint($projectID . "#comment-" . $comment["id"]); ?>">#<?php outprint($comment["id"]); ?></a>
            <?php if(User :: isLoggedIn()) { ?>
                <?php if(User :: idMatches($comment["user_id"]) || User :: rankMatches(UserRank :: JANITOR)) { ?>
                    <a href="javascript:void(0);" class="comment-edit">Edit</a>
                    <a href="javascript:void(0);" class="comment-hide"><?php echo $comment["is_hidden"] == 1 ? "Restore" : "Hide"; ?></a>
                <?php } ?>
                <a href="javascript:void(0);" class="comment-respond">Respond</a>
            <?php } ?>
        </comment-actions>
    </comment-header>
    
    <comment-body class="markdown">
        <?php echo $Parsedown -> text($comment["content"]); ?>
    </comment-body>

    <?php if(User :: getUserID() == $comment["user_id"]) { ?>
        <comment-edit class="display-none">
            <form class="comment-edit-form">
                <textarea name="content" class="comment-content" required pattern="^.{3,10000}$"><?php echo outprint($comment["content"]); ?></textarea>
                <button type="submit">Submit</button>
                <span class="comment-error"></span>
                <span class="comment-help"><a href="https://www.markdownguide.org/basic-syntax/">Markdown syntax</a> is supported.</span>
            </form>
        </comment-edit>
    <?php } else { ?>
        <textarea class="comment-content display-none"><?php echo outprint($comment["content"]); ?></textarea>
    <?php } ?>

</comment>
