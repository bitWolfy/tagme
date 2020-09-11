const fs = require("fs");

const package = JSON.parse(fs.readFileSync("./package.json"));

fs.writeFileSync(
    "./config/build.json",
    JSON.stringify({
        "version": package.version,
        "build": getBuildTime(),
    }, null, 4)
);


function getBuildTime() {
    function twoDigit(n) { return (n < 10 ? '0' : '') + n; }

    const date = new Date();
    return (date.getFullYear() + "").substring(2) + twoDigit(date.getMonth() + 1) + twoDigit(date.getDate()) + "" + twoDigit(date.getHours()) + twoDigit(date.getMinutes());
};
