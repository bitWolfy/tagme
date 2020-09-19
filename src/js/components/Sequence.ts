import { Util } from "./Util";

export class Sequence {

    private static instance: Sequence;

    private sequences: SequenceList;

    private constructor() {
        this.sequences = JSON.parse(Util.LS.getItem("tagme.sequence") || "{}")
        console.log("loaded sequences", this.sequences);

        const now = Util.Time.now();
        for (const [name, entry] of Object.entries(this.sequences))
            if (entry.time < now) delete this.sequences[name];
    }

    private static getInstance(): Sequence {
        if (this.instance == undefined) this.instance = new Sequence();
        return this.instance;
    }

    private static save(): void {
        Util.LS.setItem("tagme.sequence", JSON.stringify(Sequence.getInstance().sequences) || "{}");
    }

    public static get(name: string): SequenceEntry {
        const instance = Sequence.getInstance();

        // Fetch data from storage, creating if not present
        let entry = instance.sequences[name];
        if (entry == undefined)
            entry = { seed: Util.Math.random(6), page: 1, time: 0 }

        // Set the expiration date
        entry.time = Util.Time.now() + Util.Time.WEEK;

        // Save and return
        instance.sequences[name] = entry;
        Sequence.save();
        return entry;
    }

    public static reset(name: string): SequenceEntry {
        const entry = { seed: Util.Math.random(6), page: 1, time: 0 };
        Sequence.getInstance().sequences[name] = entry;
        Sequence.save();
        return entry;
    }

    public static increment(name: string): SequenceEntry {
        const instance = Sequence.getInstance();

        let entry = instance.sequences[name];
        if (entry == undefined) return null;

        // Increment the page number, reset if too large
        entry.page++;
        if (entry.page >= 700)
            entry = { seed: Util.Math.random(6), page: 1, time: 0 };

        // Save and return
        instance.sequences[name] = entry;
        Sequence.save();
        return entry;
    }

}

type SequenceList = {
    [name: string]: SequenceEntry;
}

interface SequenceEntry {
    seed: number;
    page: number;
    time: number;
}
