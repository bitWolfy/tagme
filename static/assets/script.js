(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Debug = void 0;
class Debug {
    static log(...data) {
        if (Debug.enabled)
            console.log(...data);
    }
    static connectLog(...data) {
        if (Debug.connect)
            console.log("CONNECT", ...data);
    }
}
exports.Debug = Debug;
Debug.enabled = true;
Debug.connect = true;

},{}],2:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.E621 = void 0;
const Debug_1 = require("./Debug");
const Util_1 = require("./Util");
const ENDPOINT_DEFS = [
    { name: "posts", path: "posts.json", node: "posts" },
    { name: "post", path: "posts/%ID%.json", node: "post" },
    { name: "post_votes", path: "posts/%ID%/votes.json" },
    { name: "tags", path: "tags.json" },
    { name: "tag", path: "tags/%ID%.json" },
    { name: "tag_aliases", path: "tag_aliases.json" },
    { name: "tag_implications", path: "tag_implications.json" },
    { name: "notes", path: "notes.json" },
    { name: "favorites", path: "favorites.json", node: "posts" },
    { name: "favorite", path: "favorites/%ID%.json" },
    { name: "pools", path: "pools.json" },
    { name: "pool", path: "pools/%ID%.json" },
    { name: "sets", path: "post_sets.json" },
    { name: "set", path: "post_sets/%ID%.json" },
    { name: "set_add_post", path: "post_sets/%ID%/add_posts.json" },
    { name: "set_remove_post", path: "post_sets/%ID%/remove_posts.json" },
    { name: "users", path: "users.json" },
    { name: "user", path: "users/%ID%.json" },
    { name: "blips", path: "blips.json" },
    { name: "wiki_pages", path: "wiki_pages.json" },
    { name: "comments", path: "comments.json" },
    { name: "comment", path: "comments/%ID%.json" },
    { name: "forum_posts", path: "forum_posts.json" },
    { name: "forum_post", path: "forum_posts/%ID%.json" },
    { name: "forum_topics", path: "forum_topics.json" },
    { name: "forum_topic", path: "forum_topics/%ID%.json" },
    { name: "dtext_preview", path: "dtext_preview" },
    { name: "iqdb_queries", path: "iqdb_queries.json" },
];
class APIEndpoint {
    constructor(queue, endpoint) {
        this.queue = queue;
        this.path = endpoint.path;
        this.name = endpoint.name;
        this.node = endpoint.node;
    }
    id(param) {
        this.param = param + "";
        return this;
    }
    async get(query, delay) {
        return this.queue.createRequest(this.getParsedPath(), this.queryToString(query), "GET", "", this.name, this.node, delay).then((response) => {
            const result = this.formatData(response[0], response[2]);
            return Promise.resolve(result);
        }, (response) => {
            return Promise.reject(response[0]);
        });
    }
    async first(query, delay) {
        return this.get(query, delay).then((response) => {
            if (response.length > 0)
                return Promise.resolve(response[0]);
            else
                return Promise.resolve(null);
        });
    }
    async post(data, delay) {
        return this.queue.createRequest(this.getParsedPath(), "", "POST", this.queryToString(data, true), this.name, this.node, delay).then((data) => {
            return Promise.resolve(data);
        }, (error) => { return Promise.reject(error); });
    }
    async delete(data, delay) {
        return this.queue.createRequest(this.getParsedPath(), "", "DELETE", this.queryToString(data, true), this.name, this.node, delay).then((data) => {
            return Promise.resolve(data);
        }, (error) => { return Promise.reject(error); });
    }
    async put(data, delay) {
        return this.queue.createRequest(this.getParsedPath(), "", "PUT", this.queryToString(data, true), this.name, this.node, delay).then((data) => {
            return Promise.resolve(data);
        }, (error) => { return Promise.reject(error); });
    }
    getParsedPath() {
        if (this.param) {
            const output = this.path.replace(/%ID%/g, this.param);
            this.param = undefined;
            return output;
        }
        return this.path;
    }
    queryToString(query, post = false) {
        if (query === undefined)
            return "";
        if (typeof query === "string")
            return query;
        const keys = Object.keys(query);
        if (keys.length === 0)
            return "";
        const queryString = [];
        keys.forEach((key) => {
            let value = query[key];
            if (value === undefined)
                return;
            if (Array.isArray(value))
                value = value.join("+");
            if (typeof value == "object") {
                for (const [subkey, subvalue] of Object.entries(value)) {
                    if (post)
                        queryString.push(key + "[" + encodeURIComponent(subkey) + "]=" + encodeURIComponent(subvalue + ""));
                    else
                        queryString.push(key + "[" + encodeURIComponent(subkey) + "]=" + encodeURIComponent(subvalue + "").replace(/%2B/g, "+"));
                }
            }
            else {
                if (post)
                    queryString.push(encodeURIComponent(key) + "=" + encodeURIComponent(value));
                else
                    queryString.push(encodeURIComponent(key) + "=" + encodeURIComponent(value).replace(/%2B/g, "+"));
            }
        });
        return queryString.join("&");
    }
    formatData(data, node) {
        if (node !== undefined)
            data = data[node];
        if (Array.isArray(data))
            return data;
        else
            return [data];
    }
}
class E621 {
    constructor() {
        this.emitter = $({});
        this.processing = false;
        this.requestIndex = 0;
        this.endpoints = {};
        this.authToken = $("head meta[name=csrf-token]").attr("content");
        this.queue = [];
        ENDPOINT_DEFS.forEach((definition) => {
            this.endpoints[definition.name] = new APIEndpoint(this, definition);
        });
    }
    static getEndpoint(name) {
        if (this.instance === undefined)
            this.instance = new E621();
        return this.instance.endpoints[name];
    }
    async createRequest(path, query, method, requestBody, endpoint, node, delay) {
        if (delay === undefined)
            delay = E621.requestRateLimit;
        else if (delay < 500)
            delay = 500;
        if (method == "PUT")
            method = "PATCH";
        const requestInfo = {
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            method: method,
            mode: "cors",
        };
        query = query
            + (query.length > 0 ? "&" : "")
            + "_client=" + encodeURIComponent(window["tagme"]["useragent"])
            + "&login=" + $("meta[name=current-user-name]").attr("content")
            + "&api_key=" + Util_1.Util.readCookie("api_key");
        const entry = new Request("https://e621.net/" + path + "?" + query, requestInfo);
        const index = this.requestIndex++;
        const final = new Promise((resolve, reject) => {
            this.emitter.one("api.re621.result-" + index, (e, data, status, endpoint, node) => {
                if (data === null)
                    data = [];
                if (data[endpoint] !== undefined && !["posts", "post"].includes(endpoint))
                    data = [];
                if (data["error"] === undefined)
                    resolve([data, status, node]);
                else
                    reject([data, status, node]);
            });
        });
        this.add({ request: entry, index: index, delay: delay, endpoint: endpoint, node: node, });
        return final;
    }
    async add(newItem) {
        this.queue.push(newItem);
        if (this.processing)
            return;
        this.processing = true;
        while (this.queue.length > 0) {
            const item = this.queue.shift();
            Debug_1.Debug.connectLog(item.request.url);
            await new Promise(async (resolve) => {
                fetch(item.request).then(async (response) => {
                    if (response.ok) {
                        let responseText = await response.text();
                        if (!responseText)
                            responseText = "[]";
                        this.emitter.trigger("api.re621.result-" + item.index, [
                            JSON.parse(responseText),
                            response.status,
                            item.endpoint,
                            item.node,
                        ]);
                    }
                    else {
                        this.emitter.trigger("api.re621.result-" + item.index, [
                            { error: response.status + " " + response.statusText },
                            response.status,
                            item.endpoint,
                            item.node,
                        ]);
                    }
                    resolve();
                }, (error) => {
                    this.emitter.trigger("api.re621.result-" + item.index, [
                        { error: error[1] + " " + error[0].error },
                        error[1],
                        item.endpoint,
                        item.node,
                    ]);
                    resolve();
                });
            });
            await Util_1.Util.sleep(item.delay);
        }
        this.processing = false;
    }
}
exports.E621 = E621;
E621.requestRateLimit = 1000;
E621.Posts = E621.getEndpoint("posts");
E621.Post = E621.getEndpoint("post");
E621.PostVotes = E621.getEndpoint("post_votes");
E621.Tags = E621.getEndpoint("tags");
E621.Tag = E621.getEndpoint("tag");
E621.TagAliases = E621.getEndpoint("tag_aliases");
E621.TagImplications = E621.getEndpoint("tag_implications");
E621.Notes = E621.getEndpoint("notes");
E621.Favorites = E621.getEndpoint("favorites");
E621.Favorite = E621.getEndpoint("favorite");
E621.Pools = E621.getEndpoint("pools");
E621.Pool = E621.getEndpoint("pool");
E621.Sets = E621.getEndpoint("sets");
E621.Set = E621.getEndpoint("set");
E621.SetAddPost = E621.getEndpoint("set_add_post");
E621.SetRemovePost = E621.getEndpoint("set_remove_post");
E621.Users = E621.getEndpoint("users");
E621.User = E621.getEndpoint("user");
E621.Blips = E621.getEndpoint("blips");
E621.Wiki = E621.getEndpoint("wiki_pages");
E621.Comments = E621.getEndpoint("comments");
E621.Comment = E621.getEndpoint("comment");
E621.ForumPosts = E621.getEndpoint("forum_posts");
E621.ForumPost = E621.getEndpoint("forum_post");
E621.ForumTopics = E621.getEndpoint("forum_topics");
E621.ForumTopic = E621.getEndpoint("forum_topic");
E621.DTextPreview = E621.getEndpoint("dtext_preview");
E621.IQDBQueries = E621.getEndpoint("iqdb_queries");

},{"./Debug":1,"./Util":4}],3:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.PageDefintion = exports.Page = void 0;
class Page {
    constructor() {
        this.url = new URL(window.location.toString());
    }
    static matches(filter) {
        if (filter instanceof RegExp)
            filter = [filter];
        const pathname = this.getInstance().url.pathname.replace(/[\/?]$/g, "");
        let result = false;
        filter.forEach(function (constraint) {
            result = result || constraint.test(pathname);
        });
        return result;
    }
    static getURL() {
        return this.getInstance().url;
    }
    static getQueryParameter(key) {
        return this.getInstance().url.searchParams.get(key);
    }
    static setQueryParameter(key, value) {
        this.getInstance().url.searchParams.set(key, value);
        this.refreshCurrentUrl();
    }
    static removeQueryParameter(key) {
        this.getInstance().url.searchParams.delete(key);
        this.refreshCurrentUrl();
    }
    static refreshCurrentUrl() {
        const url = this.getInstance().url;
        const searchPrefix = url.searchParams.toString().length === 0 ? "" : "?";
        history.replaceState({}, "", url.origin + url.pathname + searchPrefix + url.searchParams.toString());
    }
    static getSiteName() {
        return this.getInstance().url.hostname.replace(/\.net/g, "");
        ;
    }
    static getPageID() {
        return this.getInstance().url.pathname.split("/")[2];
    }
    static getInstance() {
        if (this.instance === undefined)
            this.instance = new Page();
        return this.instance;
    }
}
exports.Page = Page;
exports.PageDefintion = {
    home: /^(\/)?$/,
    projects_resolve: /^\/projects\/[\d\w_]+\/resolve(\/\d{1,10})?\/?$/,
    projects_edit: /^\/projects\/[\d\w_]+\/edit\/?$/,
    projects_new: /^\/projects\/new\/?$/,
    projects_view: /^\/projects\/[\d\w_]+\/?/,
    projects_list: /^\/projects\/?$/,
    auth_login: /^\/auth\/login\/?$/,
    auth_logout: /^\/auth\/logout\/?$/,
};

},{}],4:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Util = void 0;
const UtilID_1 = require("./UtilID");
class Util {
    static downloadAsJSON(data, file) {
        $("<a>")
            .attr({
            "download": file + ".json",
            "href": "data:application/json," + encodeURIComponent(JSON.stringify(data, null, 4)),
        })
            .appendTo("body")
            .click(function () { $(this).remove(); })[0].click();
    }
    static async sleep(time) {
        return new Promise((resolve) => { setTimeout(() => { resolve(); }, time); });
    }
    static chunkArray(input, size, altMode = false) {
        if (!Array.isArray(input))
            input = Array.from(input);
        const result = [];
        if (altMode) {
            result[0] = input.slice(0, size);
            result[1] = input.slice(size);
        }
        else {
            for (let i = 0; i < input.length; i += size)
                result.push(input.slice(i, i + size));
        }
        return result;
    }
    static getArrayIndexes(input, value) {
        const indexes = [];
        let i = 0;
        for (; i < input.length; i++) {
            if (input[i] === value)
                indexes.push(i);
        }
        return indexes;
    }
    static quickParseMarkdown(input) {
        if (input === undefined)
            return "";
        return input
            .replace(/\*\*(.*?)\*\*/gm, "<strong>$1</strong>")
            .replace(/^[-]+(.*)?/gmi, "<ul><li>$1</li></ul>")
            .replace(/\<\/ul\>\r\n\<ul\>/gm, "")
            .replace(/\n(?!<)/gm, "<br />");
    }
    static parseDText(input, removeSections = true) {
        if (removeSections) {
            input = input.replace(/\[quote\][\s\S]*\[\/quote\]/g, "")
                .replace(/\[code\][\s\S]*\[\/code\]/g, "")
                .replace(/\\[section[\s\S]*\[\/section\]/g, "");
        }
        input = input
            .replace(/\[b\]([\s\S]*)\[\/b\]/g, "<b>$1</b>")
            .replace(/\[i\]([\s\S]*)\[\/i\]/g, "<i>$1</i>")
            .replace(/\[u\]([\s\S]*)\[\/u\]/g, "<u>$1</u>")
            .replace(/\[o\]([\s\S]*)\[\/o\]/g, "<o>$1</o>")
            .replace(/\[s\]([\s\S]*)\[\/s\]/g, "<s>$1</s>")
            .replace(/\[sup\]([\s\S]*)\[\/sup\]/g, "<sup>$1</sup>")
            .replace(/\[sub\]([\s\S]*)\[\/sub\]/g, "<sub>$1</sub>")
            .replace(/\[spoiler\]([\s\S]*)\[\/spoiler\]/g, "<span>$1</span>")
            .replace(/\[color\]([\s\S]*)\[\/color\]/g, "<span>$1</span>");
        return input;
    }
    static formatBytes(bytes, decimals = 2) {
        if (bytes === 0)
            return "0 B";
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + sizes[i];
    }
    static formatK(num) {
        return Math.abs(num) > 999 ? (Math.sign(num) * (Math.abs(num) / 1000)).toFixed(1) + "k" : Math.sign(num) * Math.abs(num) + "";
    }
    static formatRatio(width, height) {
        const d = gcd(width, height);
        return [width / d, height / d];
        function gcd(u, v) {
            if (u === v)
                return u;
            if (u === 0)
                return v;
            if (v === 0)
                return u;
            if (~u & 1)
                if (v & 1)
                    return gcd(u >> 1, v);
                else
                    return gcd(u >> 1, v >> 1) << 1;
            if (~v & 1)
                return gcd(u, v >> 1);
            if (u > v)
                return gcd((u - v) >> 1, v);
            return gcd((v - u) >> 1, u);
        }
    }
    static getTagString(input) {
        return input.val().toString().trim()
            .toLowerCase()
            .replace(/\r?\n|\r/g, " ")
            .replace(/(?:\s){2,}/g, " ");
    }
    static getCleanInputValue(input) {
        if (input.length == 0)
            return "";
        return input.val().toString().trim();
    }
    static getTags(input) {
        return (typeof input === "string" ? input : Util.getTagString(input))
            .split(" ")
            .filter((el) => { return el != null && el != ""; });
    }
    static getUniqueTags(input) {
        if (typeof input === "string")
            return Util.trimArrayDuplicates(Util.getTags(input));
        return Util.trimArrayDuplicates(Util.getTags(input));
    }
    static trimArrayDuplicates(input) {
        return [...new Set(input)];
    }
    static readCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ')
                c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0)
                return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
}
exports.Util = Util;
Util.ID = UtilID_1.UtilID;
Util.LS = window.localStorage;
Util.SS = window.sessionStorage;

},{"./UtilID":5}],5:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.UtilID = void 0;
class UtilID {
    static make(length = 8, unique = true) {
        if (!unique)
            return getRandomString(length);
        let uniqueID;
        do {
            uniqueID = getRandomString(length);
        } while (UtilID.uniqueIDs.has(uniqueID));
        UtilID.uniqueIDs.add(uniqueID);
        return uniqueID;
        function getRandomString(length) {
            let result = '';
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', charLength = chars.length;
            for (let i = 0; i < length; i++) {
                result += chars.charAt(Math.floor(Math.random() * charLength));
            }
            return result;
        }
    }
    static has(id) {
        return UtilID.uniqueIDs.has(id);
    }
    static remove(id) {
        if (!UtilID.has(id))
            return false;
        UtilID.uniqueIDs.delete(id);
        return true;
    }
}
exports.UtilID = UtilID;
UtilID.uniqueIDs = new Set();

},{}],6:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.APIPost = exports.PostFlag = exports.PostRating = void 0;
var PostRating;
(function (PostRating) {
    PostRating["Safe"] = "s";
    PostRating["Questionable"] = "q";
    PostRating["Explicit"] = "e";
})(PostRating = exports.PostRating || (exports.PostRating = {}));
var PostRatingAliases;
(function (PostRatingAliases) {
    PostRatingAliases["s"] = "s";
    PostRatingAliases["safe"] = "s";
    PostRatingAliases["q"] = "q";
    PostRatingAliases["questionable"] = "q";
    PostRatingAliases["e"] = "e";
    PostRatingAliases["explicit"] = "e";
})(PostRatingAliases || (PostRatingAliases = {}));
var PostFlag;
(function (PostFlag) {
    PostFlag["Pending"] = "pending";
    PostFlag["Flagged"] = "flagged";
    PostFlag["Deleted"] = "deleted";
})(PostFlag = exports.PostFlag || (exports.PostFlag = {}));
(function (PostFlag) {
    function get(post) {
        const flags = new Set();
        if (post.flags.deleted)
            flags.add(PostFlag.Deleted);
        if (post.flags.flagged)
            flags.add(PostFlag.Flagged);
        if (post.flags.pending)
            flags.add(PostFlag.Pending);
        return flags;
    }
    PostFlag.get = get;
    function getString(post) {
        return [...PostFlag.get(post)].join(" ");
    }
    PostFlag.getString = getString;
    function fromSingle(input) {
        input = input.toLowerCase().trim();
        switch (input) {
            case "pending": return PostFlag.Pending;
            case "flagged": return PostFlag.Flagged;
            case "deleted": return PostFlag.Deleted;
        }
        return null;
    }
    PostFlag.fromSingle = fromSingle;
    function fromString(input) {
        const parts = new Set(input.split(" "));
        const flags = new Set();
        if (parts.has("deleted"))
            flags.add(PostFlag.Deleted);
        if (parts.has("flagged"))
            flags.add(PostFlag.Flagged);
        if (parts.has("pending"))
            flags.add(PostFlag.Pending);
        return flags;
    }
    PostFlag.fromString = fromString;
})(PostFlag = exports.PostFlag || (exports.PostFlag = {}));
(function (PostRating) {
    function fromValue(value) {
        return PostRatingAliases[value];
    }
    PostRating.fromValue = fromValue;
    function toString(postRating) {
        for (const key of Object.keys(PostRating)) {
            if (PostRating[key] === postRating) {
                return key;
            }
        }
        return undefined;
    }
    PostRating.toString = toString;
    function toFullString(postRating) {
        switch (postRating.toLowerCase()) {
            case "s": return "safe";
            case "q": return "questionable";
            case "e": return "explicit";
        }
        return null;
    }
    PostRating.toFullString = toFullString;
})(PostRating = exports.PostRating || (exports.PostRating = {}));
var APIPost;
(function (APIPost) {
    function getTags(post) {
        return [
            ...post.tags.artist,
            ...post.tags.character,
            ...post.tags.copyright,
            ...post.tags.general,
            ...post.tags.invalid,
            ...post.tags.lore,
            ...post.tags.meta,
            ...post.tags.species
        ].sort();
    }
    APIPost.getTags = getTags;
    function getTagString(post) {
        return APIPost.getTags(post).join(" ");
    }
    APIPost.getTagString = getTagString;
    function getTagSet(post) {
        return new Set(APIPost.getTags(post));
    }
    APIPost.getTagSet = getTagSet;
    function fromDomElement($element) {
        let md5;
        if ($element.attr("data-md5"))
            md5 = $element.attr("data-md5");
        else if ($element.attr("data-file-url"))
            md5 = $element.attr("data-file-url").substring(36, 68);
        const ext = $element.attr("data-file-ext");
        let score;
        if ($element.attr("data-score"))
            score = parseInt($element.attr("data-score"));
        else if ($element.find(".post-score-score").length !== 0)
            score = parseInt($element.find(".post-score-score").first().html().substring(1));
        const flagString = $element.attr("data-flags");
        return {
            error: "",
            id: parseInt($element.attr("data-id")),
            change_seq: -1,
            comment_count: -1,
            created_at: "",
            description: "",
            fav_count: -1,
            file: {
                ext: ext,
                height: -1,
                width: -1,
                md5: md5,
                size: -1,
                url: $element.attr("data-file-url") ? $element.attr("data-file-url") : getFileName(md5),
            },
            flags: {
                deleted: flagString.includes("deleted"),
                flagged: flagString.includes("flagged"),
                note_locked: false,
                pending: flagString.includes("pending"),
                rating_locked: false,
                status_locked: false,
            },
            locked_tags: [],
            pools: [],
            preview: {
                height: -1,
                width: -1,
                url: $element.attr("data-preview-file-url") ? $element.attr("data-preview-file-url") : getFileName(md5, "preview"),
            },
            rating: PostRating.fromValue($element.attr("data-rating")),
            relationships: {
                children: [],
                has_active_children: false,
                has_children: false,
            },
            sample: {
                has: true,
                height: -1,
                width: -1,
                url: $element.attr("data-large-file-url") ? $element.attr("data-large-file-url") : getFileName(md5, "sample"),
            },
            score: {
                down: 0,
                total: score,
                up: 0,
            },
            sources: [],
            tags: {
                artist: [],
                character: [],
                copyright: [],
                general: $element.attr("data-tags").split(" "),
                invalid: [],
                lore: [],
                meta: [],
                species: [],
            },
            updated_at: "",
            uploader_id: parseInt($element.attr("data-uploader-id")),
        };
        function getFileName(md5, prefix) {
            if (md5 === undefined)
                return "/images/deleted-preview.png";
            if (prefix)
                return `https://static1.e621.net/data/${prefix}/${md5.substring(0, 2)}/${md5.substring(2, 4)}/${md5}.jpg`;
            return `https://static1.e621.net/data/${md5.substring(0, 2)}/${md5.substring(2, 4)}/${md5}.jpg`;
        }
    }
    APIPost.fromDomElement = fromDomElement;
})(APIPost = exports.APIPost || (exports.APIPost = {}));

},{}],7:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const Page_1 = require("./components/Page");
const Background_1 = require("./modules/Background");
const Comment_1 = require("./modules/Comment");
const Hotkeys_1 = require("./modules/Hotkeys");
const Project_1 = require("./modules/Project");
const ProjectEdit_1 = require("./modules/ProjectEdit");
const User_1 = require("./modules/User");
window["tagme"] = {
    "useragent": "com.bitwolfy.tagme/resolver/0.1",
};
Background_1.Background.init();
User_1.User.init();
Hotkeys_1.Hotkeys.init();
if (Page_1.Page.matches(Page_1.PageDefintion.projects_resolve)) {
    Project_1.Project.build();
}
if (Page_1.Page.matches([Page_1.PageDefintion.projects_new, Page_1.PageDefintion.projects_edit])) {
    ProjectEdit_1.ProjectEdit.build();
}
if (Page_1.Page.matches(Page_1.PageDefintion.projects_view)) {
    Comment_1.Comment.build();
}

},{"./components/Page":3,"./modules/Background":8,"./modules/Comment":9,"./modules/Hotkeys":10,"./modules/Project":11,"./modules/ProjectEdit":12,"./modules/User":13}],8:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Background = void 0;
const Util_1 = require("../components/Util");
class Background {
    static init() {
        const seed = Math.random();
        let paletteName = Util_1.Util.LS.getItem("tagme.theme") || "dusk";
        const toggle = $("#theme-swtich")
            .html(Background.getPaletteIcon(paletteName))
            .on("click", (event) => {
            event.preventDefault();
            if (paletteName == "dawn")
                paletteName = "dusk";
            else
                paletteName = "dawn";
            toggle.html(Background.getPaletteIcon(paletteName));
            Util_1.Util.LS.setItem("tagme.theme", paletteName);
            Background.patch(seed, paletteName);
            return false;
        });
        Background.patch(seed, paletteName);
        $(window).resize(() => {
            Background.patch(seed, paletteName);
        });
    }
    static patch(seed, paletteName) {
        const palette = Background.getPalette(paletteName);
        $("#background-style").html(`body { background: ${palette[0]}; }`);
        const pattern = Trianglify({
            width: window.innerWidth,
            height: window.innerHeight,
            variance: 1,
            cell_size: 75,
            seed: seed,
            x_colors: false,
            y_colors: palette,
        });
        $("#background-style").html(`body { background: ${palette[0]} url(` + pattern.png() + `) fixed; }`);
    }
    static getPalette(name) {
        return Background.palettes[name] == undefined ? Background.palettes["dawn"] : Background.palettes[name];
    }
    static getPaletteIcon(name) {
        return name == "dawn" ? `<i class="fas fa-sun"></i>` : `<i class="fas fa-moon"></i>`;
    }
}
exports.Background = Background;
Background.palettes = {
    "dawn": ["#D89840", "#7D4735"],
    "dusk": ["#0c2032", "#2c3e50"],
};

},{"../components/Util":4}],9:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Comment = void 0;
const Debug_1 = require("../components/Debug");
const Util_1 = require("../components/Util");
class Comment {
    static async build() {
        const $newCommentInput = $("#comment-new-content");
        const $commentAddForm = $("#comment-new-form"), $commentEditForm = $("form.comment-edit-form");
        let working = false;
        $("comment a.comment-edit").on("click", (event) => {
            event.preventDefault();
            const $comment = $(event.currentTarget).parents("comment"), $toggle = $comment.find("comment-body, comment-edit");
            $toggle.toggleClass("display-none");
            return false;
        });
        $("comment a.comment-hide").on("click", async (event) => {
            event.preventDefault();
            const $button = $(event.currentTarget), $comment = $button.parents("comment");
            const isHidden = $comment.data("hidden");
            const response = await fetch(`/comments/${$comment.data("id")}/hide.json`, {
                method: "POST",
                body: JSON.stringify({
                    action_hide: !isHidden,
                }),
            });
            const text = await response.text();
            console.log(text);
            Debug_1.Debug.log(JSON.parse(text));
            if (isHidden)
                $button.text("Hide");
            else
                $button.text("Restore");
            $comment
                .attr("data-hidden", !isHidden + "")
                .data("hidden", !isHidden);
            return false;
        });
        $("comment a.comment-respond").on("click", (event) => {
            event.preventDefault();
            const $comment = $(event.currentTarget).parents("comment"), text = $comment.find("textarea.comment-content").text();
            const quotedText = [];
            for (const line of text.split("\n"))
                quotedText.push("> " + line);
            $newCommentInput.val((index, value) => {
                const newValue = `> ${$comment.data("username")} said:  \n` + quotedText.join("\n");
                if (value.length == 0)
                    return newValue;
                else
                    return value + "\n" + newValue;
            });
            return false;
        });
        $commentEditForm.on("submit", async (event) => {
            event.preventDefault();
            if (working)
                return;
            working = true;
            const $comment = $(event.currentTarget).parents("comment"), $input = $comment.find("textarea[name=content]").first();
            const response = await fetch(`/comments/${$comment.data("id")}/edit.json`, {
                method: "POST",
                body: JSON.stringify({
                    "content": Util_1.Util.getCleanInputValue($input),
                }),
            });
            const text = await response.text();
            Debug_1.Debug.log(JSON.parse(text));
            location.reload();
            working = false;
            return false;
        });
        $commentAddForm.on("submit", async (event) => {
            event.preventDefault();
            if (working)
                return;
            working = true;
            console.log(Util_1.Util.getCleanInputValue($newCommentInput), $commentAddForm.data("project"));
            const response = await fetch("/comments/new.json", {
                method: "POST",
                body: JSON.stringify({
                    "project_id": $commentAddForm.data("project"),
                    "content": Util_1.Util.getCleanInputValue($newCommentInput),
                }),
            });
            const text = await response.text();
            console.log(text);
            Debug_1.Debug.log(JSON.parse(text));
            working = false;
            return false;
        });
    }
}
exports.Comment = Comment;

},{"../components/Debug":1,"../components/Util":4}],10:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Hotkeys = void 0;
class Hotkeys {
    static async init() {
        for (const element of $("[data-hotkey]").get()) {
            const $elem = $(element);
            Mousetrap.bind($elem.data("hotkey") + "", () => { $elem[0].click(); });
        }
    }
}
exports.Hotkeys = Hotkeys;

},{}],11:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Project = void 0;
const E621_1 = require("../components/E621");
const APIPost_1 = require("../components/responses/APIPost");
const Util_1 = require("../components/Util");
class Project {
    static async build() {
        const imageContainer = $("#image-container"), projectID = imageContainer.data("project"), query = imageContainer.data("query");
        const imgData = await E621_1.E621.Posts.get({ "tags": query, limit: 1, });
        if (imgData[0] == undefined || imgData[0]["sample"]["url"] == null) {
            $("page-container").html(`
                <section class="project-error">
                    <h2>Error - No Image Found</h2>
                    <p>The API query returned empty. Either there are no more posts to sort through, or an error has occurred.</p>
                    <p>Either way, there is nothing more to be done here. <a href="/projects/<?php echo $project -> id; ?>">Return to the project page</a>.</p>
                </section>
            `);
            return;
        }
        const post = imgData[0];
        imageContainer.attr("data-id", post.id);
        $("#source-image")
            .attr("src", post.sample.url)
            .one("load", () => {
            imageContainer.removeClass("loading");
        });
        $("#source-link")
            .attr("href", "https://e621.net/posts/" + post.id)
            .html("#" + post.id);
        $("#source-date").html(new Date(post.created_at).toISOString());
        $("#source-history").attr("href", "https://e621.net/post_versions?search[post_id]=" + post.id);
        $("#tags-old, #tags-new").val(APIPost_1.APIPost.getTagString(post));
        $("textarea").height($("textarea")[0].scrollHeight);
        const title = $("title");
        title.html("#" + post.id + " - Character Count Tags - TagMe!");
        window.history.replaceState("Object", "Title", "/projects/" + projectID + "/resolve/" + post.id);
        $("#image-container").zoom({
            url: $("#source-image").attr("src"),
            on: "click",
            magnify: 0.9,
        });
        const actions = $("#actions").on("click", "input", () => {
            const addedTags = new Set();
            const removedTags = new Set();
            for (const input of actions.find("input:checked")) {
                const $parent = $(input).parent();
                for (const tag of $parent.attr("data-added").split(" "))
                    addedTags.add(tag);
                for (const tag of $parent.attr("data-removed").split(" "))
                    removedTags.add(tag);
            }
            const allTags = new Set(Util_1.Util.getTags($("#tags-old")));
            removedTags.forEach((tag) => { allTags.delete(tag); });
            addedTags.forEach((tag) => { allTags.add(tag); });
            $("#tags-new").val([...allTags].join(" "));
        });
        $("#page-skip").on("click", (event) => {
            event.preventDefault();
            location.href = `/projects/${projectID}/resolve/`;
        });
        let working = false;
        const submitbutton = $("#page-submit").on("click", async (event) => {
            event.preventDefault();
            if (imageContainer.hasClass("loading"))
                return;
            if (working)
                return;
            working = true;
            submitbutton.attr("loading", "true");
            const oldTags = APIPost_1.APIPost.getTagString(post), newTags = Util_1.Util.getCleanInputValue($("#tags-new"));
            if ((newTags.length == 0) ||
                (newTags.length < oldTags.length / 2)) {
                const beetlejuice = await fetch("/admin/betelgeuse.json", {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        summoner: $("meta[name=current-user-id]").attr("content"),
                    }),
                });
                console.log(await beetlejuice.text());
                location.href = `/projects/${projectID}/resolve/`;
                working = false;
                submitbutton.removeAttr("loading");
                return false;
            }
            if (oldTags == newTags) {
                location.href = `/projects/${projectID}/resolve/`;
                working = false;
                submitbutton.removeAttr("loading");
                return false;
            }
            const response = await fetch(`/projects/${projectID}/resolve/${post.id}.json`, {
                method: "POST",
                body: JSON.stringify({
                    postID: post.id,
                    tags: $("#tags-new").val() + "",
                }),
            });
            const responseText = await response.text();
            const data = JSON.parse(responseText);
            if (data["success"])
                location.href = `/projects/${projectID}/resolve/`;
            else
                $("#resolve-error").removeClass("display-none");
            submitbutton.removeAttr("loading");
            working = false;
            return false;
        });
    }
}
exports.Project = Project;

},{"../components/E621":2,"../components/Util":4,"../components/responses/APIPost":6}],12:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.ProjectEdit = void 0;
const Util_1 = require("../components/Util");
class ProjectEdit {
    static async build() {
        const optionsContainer = $("#options-gen");
        $("#options-add-btn").on("click", (event) => {
            event.preventDefault();
            if ($("div.option").length >= 10)
                return;
            const id = Util_1.Util.ID.make();
            const container = $("<div>")
                .addClass("option")
                .appendTo(optionsContainer);
            $("<div>")
                .addClass("option-name")
                .html(`
                    <label for="option-name-${id}">Title</label>
                    <input id="option-name-${id}" name="opt-name" placeholder="Display name of the option" required pattern="[\\S ]{3,32}">
                `)
                .appendTo(container);
            $("<div>")
                .addClass("option-controls")
                .html(`<button class="options-remove-btn">Remove</button>`)
                .appendTo(container);
            $("<div>")
                .addClass("option-addtags")
                .html(`
                    <label for="option-addtags-${id}">Added Tags</label>
                    <textarea id="option-addtags-${id}" name="opt-tadd" placeholder="Tags to add if this option is selected"></textarea>
                `)
                .appendTo(container);
            $("<div>")
                .addClass("option-remtags")
                .html(`
                    <label for="option-remtags-${id}">Removed Tags</label>
                    <textarea id="option-remtags-${id}" name="opt-trem" placeholder="Tags to remove if this option is selected"></textarea>
                `)
                .appendTo(container);
            return false;
        });
        optionsContainer.on("click", "button.options-remove-btn", (event) => {
            event.preventDefault();
            if ($("div.option").length < 3)
                return;
            $(event.currentTarget).parents("div.option").remove();
            return false;
        });
        $("#project-new").on("input change focus", "input, textarea", (event) => {
            const $input = $(event.currentTarget);
            $input.toggleClass("invalid", !$input.get()[0].checkValidity());
        });
        let timer;
        const $metaInputError = $("#input-meta-invalid");
        const originalName = $("form#project-new").attr("data-meta");
        $("input[name=meta]").on("input change", (event) => {
            const $input = $(event.currentTarget);
            window.clearTimeout(timer);
            $metaInputError.html("");
            const newVal = ($input.val() + "").toLowerCase();
            if (newVal.length < 3 || newVal == originalName)
                return;
            timer = window.setTimeout(async () => {
                const serverResponse = await fetch("/projects/" + ($input.val() + "") + ".json");
                const response = await serverResponse.json();
                if (response.data !== null)
                    $metaInputError.html("Already Taken");
            }, 400);
        });
        const form = $("#project-new"), inputName = form.find("[name=name]"), inputMeta = form.find("[name=meta]"), inputDesc = form.find("[name=desc]"), inputText = form.find("[name=text]"), inputTags = form.find("[name=tags]"), inputOptions = $("#options-gen"), response = $("#submit-response");
        let working = false;
        form.on("submit", async (event) => {
            event.preventDefault();
            response.html("");
            if (working)
                return;
            working = true;
            for (const element of form.find("input, textarea").get())
                element.checkValidity();
            if (form.find("input.invalid, textarea.invalid").length != 0) {
                response.html("Invalid data in form");
                return false;
            }
            const optData = inputOptions.children("div.option");
            if (optData.length < 2) {
                response.html("At least two options are required");
                return false;
            }
            const dataPackage = {
                name: Util_1.Util.getCleanInputValue(inputName),
                meta: Util_1.Util.getCleanInputValue(inputMeta).toLowerCase(),
                desc: Util_1.Util.getCleanInputValue(inputDesc),
                text: Util_1.Util.getCleanInputValue(inputText),
                tags: Util_1.Util.getUniqueTags(inputTags),
                optmode: form.find("[name=optmode]:checked").val() == "1" ? 1 : 0,
                options: [],
            };
            for (const optEntry of optData.get()) {
                const $entry = $(optEntry);
                dataPackage.options.push({
                    name: $entry.find("[name=opt-name]").val() + "",
                    tadd: Util_1.Util.getUniqueTags($entry.find("[name=opt-tadd]")),
                    trem: Util_1.Util.getUniqueTags($entry.find("[name=opt-trem]")),
                });
            }
            const serverResponse = await fetch(form.attr("action"), {
                method: "POST",
                body: JSON.stringify(dataPackage),
            });
            const responseText = await serverResponse.text();
            const data = JSON.parse(responseText);
            console.log(data);
            if (data.success) {
                location.href = "/projects/" + data.data + "/";
            }
            else {
                let text = "";
                switch (data.error) {
                    case "error.notfound": {
                        text = "Unknown project ID";
                        break;
                    }
                    case "error.format": {
                        text = "Wrong input format";
                        break;
                    }
                    case "error.duplicate": {
                        text = "Duplicate project ID";
                        break;
                    }
                    default: {
                        text = data.error;
                    }
                }
                response.html("Error: " + text);
            }
            working = false;
            return false;
        });
    }
}
exports.ProjectEdit = ProjectEdit;

},{"../components/Util":4}],13:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.User = void 0;
const Page_1 = require("../components/Page");
class User {
    static init() {
        const username = $("#userauth-form input[name=username]"), apikey = $("#userauth-form input[name=password]"), submit = $("#userauth-form button[type=submit]"), status = $("#userauth-status");
        $("#userauth-form").on("submit", async (event) => {
            event.preventDefault();
            status.html("");
            submit.attr("loading", "true");
            if (username.val() == "" || apikey.val() == "") {
                status.html("Authentication Failed");
                submit.removeAttr("loading");
                return;
            }
            const recaptcha = await grecaptcha.execute("6LeafMgUAAAAALOtMnoNHRCBSu48k0NGKPqllHnh", { action: "submit" });
            console.log(recaptcha);
            const captchaResponse = await fetch(`/auth/captcha.json`, {
                method: "POST",
                body: JSON.stringify({
                    captcha: recaptcha,
                }),
            });
            const captchaText = await captchaResponse.text();
            console.log(captchaText);
            if (!JSON.parse(captchaText)["success"]) {
                status.html("Authentication Failed");
                return false;
            }
            const response = await fetch(`/auth/login.json`, {
                method: "POST",
                body: JSON.stringify({
                    username: username.val() + "",
                    password: apikey.val() + "",
                }),
            });
            const responseText = await response.text();
            console.log(responseText);
            const data = JSON.parse(responseText);
            if (data["success"]) {
                if (Page_1.Page.matches(Page_1.PageDefintion.auth_login))
                    location.href = "/";
                else
                    location.reload();
            }
            else
                status.html("Authentication Failed");
            submit.removeAttr("loading");
            return false;
        });
        $("#logout-link").on("click", async (event) => {
            event.preventDefault();
            await fetch("/auth/logout.json");
            location.reload();
            return false;
        });
        const $banButton = $("#action-user-ban");
        $banButton.on("click", async (event) => {
            event.preventDefault();
            const unban = $banButton.text() == "Unban";
            const banAction = await fetch($banButton.attr("href") + "?unban=" + unban);
            $banButton.text(unban ? "Ban" : "Unban");
            console.log(await banAction.text());
            return false;
        });
    }
}
exports.User = User;

},{"../components/Page":3}]},{},[7]);
