import { Page, PageDefintion } from "./components/Page";
import { Background } from "./modules/Background";
import { BlacklistHandler } from "./modules/BlacklistHandler";
import { Comment } from "./modules/Comment";
import { Home } from "./modules/Home";
import { Hotkeys } from "./modules/Hotkeys";
import { Project } from "./modules/Project";
import { ProjectEdit } from "./modules/ProjectEdit";
import { User } from "./modules/User";
import { ViewMode } from "./modules/ViewMode";

window["tagme"] = {
    "useragent": "dev.tagme/resolver/0.1",
};

async function run(): Promise<void> {

    Background.init();
    ViewMode.init();

    User.init();
    await Hotkeys.init();

    await BlacklistHandler.build();

    if (Page.matches(PageDefintion.home)) {
        Home.build();
    }

    if (Page.matches(PageDefintion.projects_resolve)) {
        // console.log("project.resolve");
        await Project.build();
    }

    if (Page.matches([PageDefintion.projects_new, PageDefintion.projects_edit])) {
        // console.log("project.edit");
        await ProjectEdit.build();
    }

    if (Page.matches(PageDefintion.projects_view)) {
        // console.log("project.view");
        await Comment.build();
    }
}

run();
