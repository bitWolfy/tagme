
declare const Mousetrap;

export class Hotkeys {

    public static async init(): Promise<void> {

        for (const element of $("[data-hotkey]").get()) {
            const $elem = $(element);
            Mousetrap.bind($elem.data("hotkey") + "", () => { $elem[0].click(); });
        }
    }

}
