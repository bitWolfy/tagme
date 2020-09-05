
export class Debug {

    private static enabled = true;
    private static connect = true;

    /** Logs the provided data into the console log if debug is enabled */
    public static log(...data: any[]): void {
        if (Debug.enabled) console.log(...data);
    }

    /** Logs the provided data into the console log if connections logging is enabled */
    public static connectLog(...data: any[]): void {
        if (Debug.connect) console.log("CONNECT", ...data);
    }

}
