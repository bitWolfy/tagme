import { Util } from "../components/Util";

declare const Trianglify: any;

export class Background {

    private static palettes = {
        "dawn": ["#D89840", "#7D4735"],
        "dusk": ["#0c2032", "#2c3e50"],
    };

    public static init(): void {
        const seed = Math.random();

        let paletteName = Util.LS.getItem("tagme.theme") || "dusk";
        const toggle = $("#theme-switch")
            .html(Background.getPaletteIcon(paletteName))
            .attr("title", "Theme: " + paletteName.toUpperCase())
            .on("click", (event) => {
                event.preventDefault();

                if (paletteName == "dawn") paletteName = "dusk";
                else paletteName = "dawn";
                toggle
                    .html(Background.getPaletteIcon(paletteName))
                    .attr("title", "Theme: " + paletteName.toUpperCase());

                Util.LS.setItem("tagme.theme", paletteName);
                Background.patch(seed, paletteName);

                return false;
            });

        Background.patch(seed, paletteName);
        $(window).resize(() => {
            Background.patch(seed, paletteName);
        });
    }

    private static patch(seed: number, paletteName: string): void {
        const palette = Background.getPalette(paletteName);
        $("#background-style").html(`body { background: ${palette[0]}; }`);
        const pattern = Trianglify({
            width: window.innerWidth,
            height: window.innerHeight,

            variance: 1,
            cell_size: 75,
            seed: seed,

            x_colors: false,
            y_colors: palette,
        });
        $("#background-style").html(`body { background: ${palette[0]} url(` + pattern.png() + `) fixed; }`);
    }

    private static getPalette(name: string): [string, string] {
        return Background.palettes[name] == undefined ? Background.palettes["dawn"] : Background.palettes[name];
    }

    private static getPaletteIcon(name: string): string {
        return name == "dawn" ? `<i class="fas fa-sun"></i>` : `<i class="fas fa-moon"></i>`;
    }

}
