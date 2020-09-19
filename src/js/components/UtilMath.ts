export class UtilMath {

    public static clamp(value: number, min: number, max: number): number {
        return Math.min(Math.max(value, min), max);
    }

    public static between(value: number, min: number, max: number): boolean {
        return min <= value && max >= value;
    }

    public static random(length: number): number {
        return parseInt((new Date().getTime() + "").substr(-length));
    }

}
