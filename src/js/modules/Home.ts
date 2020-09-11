
export class Home {

    public static build(): void {

        for (const elem of $(".counter").get()) {
            const demo = new window["countUp"].CountUp(elem, $(elem).attr("count"));
            if (!demo.error) {
                demo.start();
            } else {
                console.error(demo.error);
            }
        }
    }

}
