import { UtilID } from "./UtilID";
import { UtilMath } from "./UtilMath";
import { UtilSize } from "./UtilSize";
import { UtilTime } from "./UtilTime";

/** Common utilities used in other modules */
export class Util {

    public static ID = UtilID;
    public static Math = UtilMath;
    public static Size = UtilSize;
    public static Time = UtilTime;

    public static LS = window.localStorage;
    public static SS = window.sessionStorage;

    /**
     * Downloads the provided object as a JSON file
     * @param data Object to download
     * @param file File name
     */
    public static downloadAsJSON(data: any, file: string): void {
        $("<a>")
            .attr({
                "download": file + ".json",
                "href": "data:application/json," + encodeURIComponent(JSON.stringify(data, null, 4)),
            })
            .appendTo("body")
            .click(function () { $(this).remove(); })
        [0].click();
    }

    /**
     * Returns a promise that is fulfilled after the specified time period elapses
     * @param time Time period, in milliseconds
     */
    public static async sleep(time: number): Promise<void> {
        return new Promise((resolve) => { setTimeout(() => { resolve(); }, time) });
    }

    /**
     * Split the array into chunks of specified size.  
     * If `altMode` is set to true, splits array into two parts.  
     * - [0] is the size specified by the `size` argument  
     * - [1] is the remainder.  
     * Otherwise, splits the array normally.
     * @param input Original array
     * @param size Size of the resulting chunks
     * @param altMode Alternative mode
     * @returns Array of smaller arrays of specified size
     */
    public static chunkArray<T>(input: T[] | Set<T>, size: number, altMode = false): T[][] {
        if (!Array.isArray(input)) input = Array.from(input);
        const result = [];
        if (altMode) {
            result[0] = input.slice(0, size);
            result[1] = input.slice(size);
        } else {
            for (let i = 0; i < input.length; i += size)
                result.push(input.slice(i, i + size));
        }
        return result;
    }

    /**
     * Returns the indexes of all instances of the specified value in an array
     * @param input Array to search
     * @param value Value to look for
     * @returns Array of number indexes
     */
    public static getArrayIndexes<T>(input: T[], value: T): number[] {
        const indexes: number[] = [];
        let i = 0;
        for (; i < input.length; i++) {
            if (input[i] === value) indexes.push(i);
        }
        return indexes;
    }

    /**
     * Limited markdown parser. Don't rely on this thing to be any good, replace with an actual library if really necessary.
     * @param input Markdown input
     * @returns HTML output
     */
    public static quickParseMarkdown(input: string): string {
        if (input === undefined) return "";
        return input
            .replace(/\*\*(.*?)\*\*/gm, "<strong>$1</strong>")
            .replace(/^[-]+(.*)?/gmi, "<ul><li>$1</li></ul>")
            .replace(/\<\/ul\>\r\n\<ul\>/gm, "")
            .replace(/\n(?!<)/gm, "<br />");
    }

    /**
     * Parses the provided DText string, returning it as plain text
     * @param input Input to process
     * @param removeSections If true, removes `quote`, `code`, and `sections` blocks altogether
     */
    public static parseDText(input: string, removeSections = true): string {
        if (removeSections) {
            input = input.replace(/\[quote\][\s\S]*\[\/quote\]/g, "")
                .replace(/\[code\][\s\S]*\[\/code\]/g, "")
                .replace(/\\[section[\s\S]*\[\/section\]/g, "");
        }

        input = input
            .replace(/\[b\]([\s\S]*)\[\/b\]/g, "<b>$1</b>")                     // bold
            .replace(/\[i\]([\s\S]*)\[\/i\]/g, "<i>$1</i>")                     // italicts
            .replace(/\[u\]([\s\S]*)\[\/u\]/g, "<u>$1</u>")                     // Underline
            .replace(/\[o\]([\s\S]*)\[\/o\]/g, "<o>$1</o>")                     // Overline
            .replace(/\[s\]([\s\S]*)\[\/s\]/g, "<s>$1</s>")                     // Strikeout
            .replace(/\[sup\]([\s\S]*)\[\/sup\]/g, "<sup>$1</sup>")             // Superscript
            .replace(/\[sub\]([\s\S]*)\[\/sub\]/g, "<sub>$1</sub>")             // Subscript
            .replace(/\[spoiler\]([\s\S]*)\[\/spoiler\]/g, "<span>$1</span>")   // Spoiler
            .replace(/\[color\]([\s\S]*)\[\/color\]/g, "<span>$1</span>")       // Color

        return input;
    }

    /**
     * Converts a byte number into a formatted string
     * @param bytes Number
     * @param decimals Decimal places
     */
    public static formatBytes(bytes: number, decimals = 2): string {
        if (bytes === 0) return "0 B";

        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];

        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + sizes[i];
    }

    /**
     * Trims the thousands off a number and replaced them with a K.  
     * ex. 54321 -> 54.3k
     * @param num Number to trim
     */
    public static formatK(num: number): string {
        return Math.abs(num) > 999 ? (Math.sign(num) * (Math.abs(num) / 1000)).toFixed(1) + "k" : Math.sign(num) * Math.abs(num) + "";
    }


    /* returns an array with the ratio */
    public static formatRatio(width: number, height: number): [number, number] {
        const d = gcd(width, height);
        return [width / d, height / d];

        function gcd(u: number, v: number): number {
            if (u === v) return u;
            if (u === 0) return v;
            if (v === 0) return u;

            if (~u & 1)
                if (v & 1)
                    return gcd(u >> 1, v);
                else
                    return gcd(u >> 1, v >> 1) << 1;

            if (~v & 1) return gcd(u, v >> 1);

            if (u > v) return gcd((u - v) >> 1, v);

            return gcd((v - u) >> 1, u);
        }
    }

    /**
     * Parses the textare input specified in the parameter and returns a list of space-separated tags
     * @deprecated Renamed to `Util.getInputValue()` for clarity
     * @param input Textarea to parse
     */
    public static getTagString(input: JQuery<HTMLElement>): string {
        return input.val().toString().trim()
            .toLowerCase()
            .replace(/\r?\n|\r/g, " ")      // strip newlines
            .replace(/(?:\s){2,}/g, " ");   // strip multiple spaces
    }

    public static getCleanInputValue(input: JQuery<HTMLElement>): string {
        if (input.length == 0) return "";
        return input.val().toString().trim();
    }

    public static getTags(input: string): string[];
    public static getTags(input: JQuery<HTMLElement>): string[];
    public static getTags(input: string | JQuery<HTMLElement>): string[] {
        return (typeof input === "string" ? input : Util.getTagString(input))
            .split(" ")
            .filter((el) => { return el != null && el != ""; });
    }

    public static getUniqueTags(input: string): string[];
    public static getUniqueTags(input: JQuery<HTMLElement>): string[];
    public static getUniqueTags(input: string | JQuery<HTMLElement>): string[] {
        if (typeof input === "string") return Util.trimArrayDuplicates(Util.getTags(input));
        return Util.trimArrayDuplicates(Util.getTags(input));
    }

    public static trimArrayDuplicates<T>(input: T[]): T[] {
        return [...new Set(input)];
    }

    public static readCookie(name: string): any {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

}
