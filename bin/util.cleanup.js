const fs = require("fs");

fs.rmdir("./build/", { recursive: true }, () => { console.log("done"); });
