
declare const Mousetrap;

export class Hotkeys {

    public static async init(): Promise<void> {

        for (const element of $("[data-hotkey]").get()) {
            const $elem = $(element);
            const keys = $elem.attr("data-hotkey").split("|");

            $elem.attr("title", keys.length == 1 ? `Hotkey: ${keys[0]}` : `Hotkeys: ${keys.join(" / ")}`);

            for (const key of keys)
                Mousetrap.bind(key, () => { $elem[0].click(); });
        }
    }

}
