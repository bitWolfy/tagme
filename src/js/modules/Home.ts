
export class Home {

    public static build(): void {

        for (const elem of $(".counter").get()) {
            const changeCount = parseInt($(elem).attr("count") || "1");
            const demo = new window["countUp"].CountUp(elem, $(elem).attr("count"), {
                startVal: Math.round(changeCount * 0.5),
            });
            if (!demo.error) {
                demo.start();
            } else {
                console.error(demo.error);
            }
        }
    }

}
