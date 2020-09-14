import { Page, PageDefintion } from "./components/Page";
import { Background } from "./modules/Background";
import { Comment } from "./modules/Comment";
import { Home } from "./modules/Home";
import { Hotkeys } from "./modules/Hotkeys";
import { Project } from "./modules/Project";
import { ProjectEdit } from "./modules/ProjectEdit";
import { User } from "./modules/User";

window["tagme"] = {
    "useragent": "dev.tagme/resolver/0.1",
};

Background.init();
User.init();
Hotkeys.init();

if (Page.matches(PageDefintion.home)) {
    Home.build();
}

if (Page.matches(PageDefintion.projects_resolve)) {
    // console.log("project.resolve");
    Project.build();
}

if (Page.matches([PageDefintion.projects_new, PageDefintion.projects_edit])) {
    // console.log("project.edit");
    ProjectEdit.build();
}

if (Page.matches(PageDefintion.projects_view)) {
    // console.log("project.view");
    Comment.build();
}
