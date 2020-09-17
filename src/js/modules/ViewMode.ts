import { Util } from "../components/Util";

export class ViewMode {

    public static init(): void {
        let modeName = Util.LS.getItem("tagme.mode") || "narrow";
        const toggle = $("#mode-switch")
            .html(ViewMode.getModeIcon(modeName))
            .attr("title", "Theme: " + modeName.toUpperCase())
            .on("click", (event) => {
                event.preventDefault();

                if (modeName == "narrow") modeName = "wide";
                else modeName = "narrow";
                toggle
                    .html(ViewMode.getModeIcon(modeName))
                    .attr("title", "Theme: " + modeName.toUpperCase());

                Util.LS.setItem("tagme.mode", modeName);
                ViewMode.patch(modeName);

                return false;
            });

        ViewMode.patch(modeName);
    }

    private static patch(modeName: string): void {
        $("body").attr("viewmode", modeName);
    }

    private static getModeIcon(name: string): string {
        return name == "narrow" ? `<i class="fas fa-compress-alt"></i>` : `<i class="fas fa-expand-alt"></i>`;
    }

}
