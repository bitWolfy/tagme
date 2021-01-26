import { Debug } from "../Debug";
import { APIPost, PostFlag, PostRating } from "../responses/APIPost";
import { Util } from "../Util";
import { Blacklist } from "./Blacklist";
import { Tag } from "./Tag";

export class Post implements PostData {

    public $ref: JQuery<HTMLElement>;       // reference to the post's DOM object

    public id: number;
    public flags: Set<PostFlag>;
    public score: number;                   // post total score
    public user_score: number;              // user's current vote. might be undefined if no vote has been registered this session
    public favorites: number;               // total number of favorites
    public is_favorited: boolean;           // true if the post is in the user's favorites
    public comments: number;                // total number of comments
    public rating: PostRating;              // rating in the one-letter lowercase format (s, q, e)
    public uploader: number;                // uploader ID
    public approver: number;                // approver ID, or -1 if there isn't one
    public page: string;                    // search page. can either be numeric, or in a- / b- format

    public date: {
        raw: string;                        // upload time, in `Fri Aug 21 2020 12:32:52 GMT-0700` format
        ago: string;                        // relative time, aka `5 minutes ago`
    };

    public tagString: string;               // string with space-separated tags. Makes outputting tags easier
    public tags: {
        all: Set<string>;
        artist: Set<string>;
        real_artist: Set<string>;           // same as artist, minus tags like `conditional_dnp` or `sound_warning`. See `Tag.isArtist()` for more info.
        copyright: Set<string>;
        species: Set<string>;
        character: Set<string>;
        general: Set<string>;
        invalid: Set<string>;               // usually empty, not sure why it even exists
        meta: Set<string>;
        lore: Set<string>;

        locked: Set<string>;
    };

    public file: {
        ext: string;                        // file extension
        md5: string;
        original: string;                   // full-resolution image. `null` if the post is deleted
        sample: string;                     // sampled (~850px) image. for WEBM, same as original. for SWF, null or undefined
        preview: string;                    // thumbnail (150px). for SWF, null or undefined
        size: number;
    };
    public loaded: LoadedFileType;          // currently loaded file size. used in hover loading mode

    public img: {
        width: number;
        height: number;
        ratio: number;                      // height divided by width. used to size thumbnails properly
    };

    public sampleImg: {                     // same as above, but based on the sample's file dimensions
        width: number;
        height: number;
        ratio: number;                      // height divided by width. used to size thumbnails properly
    };

    public has: {
        file: boolean;                      // true if the post wasn't deleted, and is not on the anon blacklist
        children: boolean;                  // whether the post has any children
        parent: boolean;                    // whether the post has a parent
    };

    public rel: {
        children: Set<number>;              // IDs of child posts
        parent: number;                     // ID of the parent post
    }

    public meta: {
        duration: number;                   // in seconds - for webm only, null for everything else
        animated: boolean;                  // file is animated in any way (gif, webm, swf, etc)
        sound: boolean;                     // file has sound effects of any kind
        interactive: boolean;               // file has interactive elements (webm / swf)
    }

    public warning: {
        sound: boolean;                     // file is marked with a sound warning
        epilepsy: boolean;                  // file is marked with epilepsy warning
    }

    private constructor(data: PostData, $ref: JQuery<HTMLElement>) {
        for (const [key, value] of Object.entries(data)) this[key] = value;
        this.$ref = $ref;
        this.$ref.data("wfpost", this);

        this.updateFilters();
    }

    /** Updates the post's data from the API response */
    public update(data: APIPost): Post {
        for (const [key, value] of Object.entries(PostData.fromAPI(data)))
            this[key] = value;

        this.updateFilters();

        return this;
    }

    /** Returns true if the post has been rendered, false otherwise */
    public isRendered(): boolean {
        return this.$ref.attr("rendered") == "true";
    }

    /** Returns true if the post is currently filtered out by the blacklist */
    public isBlacklisted(): boolean {
        return this.$ref.attr("blacklisted") == "true";
    }

    /** Refreshes the blacklist and custom flagger filters */
    public updateFilters(): Post {
        Blacklist.addPost(this);
        return this;
    }

    /**
     * Refreshes the post's blacklist status.  
     * Should be executed every time a blacklist filter is toggled.
     */
    public updateVisibility(): Post {
        const state = Blacklist.checkPostAlt(this.id);
        if (state) {
            if (state == 1) this.$ref.attr("blacklisted", "true");
            else this.$ref.attr("blacklisted", "maybe");

            const filterList = Blacklist.getActiveFilters();
            const container = $("#blacklist-container")
                .attr("data-disabled", state == 1 ? "false" : "true")
                .html("BLACKLISTED");
            const filterEl = $("<div>").appendTo(container);

            for (const filter of filterList.keys())
                $("<span>").html(filter).appendTo(filterEl);

        } else {
            this.$ref.removeAttr("blacklisted");

            $("#blacklist-container").attr("data-disabled", "true");
        }

        return this;
    }

    /**
     * Creates a Post element with the specified parameters
     * @param data API response with post data
     */
    public static make(data: APIPost): Post {

        const post = PostData.fromAPI(data);

        // Fallback for a rare error where post data fails to load
        // In that case, the post gets sent into the shadow realm
        if (!post.file.original && !post.flags.has(PostFlag.Deleted)) {
            Debug.log(`Post #${post.id} skipped: no file`);
            return null;
        }

        // Image container and post data store
        const result = new Post(post, $("#image-container"));

        // Register for blacklist and custom flagger
        result.updateFilters();
        result.updateVisibility();

        return result;
    }

}

/**
 * Generalized post data that is not attached to a specific element.  
 * Generated either from an API result, or from a DOM element.
 */
export interface PostData {

    id: number;
    flags: Set<PostFlag>;
    score: number;
    user_score: number;
    favorites: number;
    is_favorited: boolean;
    comments: number;
    rating: PostRating;
    uploader: number;
    approver: number;

    page: string;

    date: {
        raw: string;
        ago: string;
    };

    tagString: string;
    tags: {
        all: Set<string>;
        artist: Set<string>;
        real_artist: Set<string>;
        copyright: Set<string>;
        species: Set<string>;
        character: Set<string>;
        general: Set<string>;
        invalid: Set<string>;
        meta: Set<string>;
        lore: Set<string>;

        locked: Set<string>;
    };

    file: {
        ext: string;
        md5: string;
        original: string;
        sample: string;
        preview: string;
        size: number;
    };
    loaded: LoadedFileType;

    img: {
        width: number;
        height: number;
        ratio: number;
    };

    sampleImg: {
        width: number;
        height: number;
        ratio: number;
    };

    has: {
        file: boolean;
        children: boolean;
        parent: boolean;
    };

    rel: {
        children: Set<number>;
        parent: number;
    };

    meta: {
        duration: number;
        animated: boolean;
        sound: boolean;
        interactive: boolean;
    };

    warning: {
        sound: boolean;
        epilepsy: boolean;
    };

}

export namespace PostData {

    /**
     * Generates PostData from an API result
     * @param data API result
     * @param page Search page
     */
    export function fromAPI(data: APIPost, page?: string): PostData {

        const tags = APIPost.getTagSet(data),
            flags = PostFlag.get(data);

        return {
            id: data.id,
            flags: flags,
            score: data.score.total,
            user_score: undefined,
            favorites: data.fav_count,
            is_favorited: data.is_favorited == true,
            comments: data.comment_count,
            rating: PostRating.fromValue(data.rating),
            uploader: data.uploader_id,
            approver: data.approver_id ? data.approver_id : -1,

            page: page,

            date: {
                raw: data.created_at == null ? data.updated_at : data.created_at,
                ago: Util.Time.ago(data.created_at == null ? data.updated_at : data.created_at),
            },

            tagString: [...tags].sort().join(" "),
            tags: {
                all: tags,
                artist: new Set(data.tags.artist),
                real_artist: new Set(data.tags.artist.filter(tag => Tag.isArtist(tag))),
                copyright: new Set(data.tags.copyright),
                species: new Set(data.tags.species),
                character: new Set(data.tags.character),
                general: new Set(data.tags.general),
                invalid: new Set(data.tags.invalid),
                meta: new Set(data.tags.meta),
                lore: new Set(data.tags.lore),

                locked: new Set(data.locked_tags),
            },

            file: {
                ext: data.file.ext,
                md5: data.file.md5,
                original: data.file.url,
                sample: data.sample.has ? data.sample.url : data.file.url,
                preview: data.preview.url,
                size: data.file.size,
            },
            loaded: undefined,

            img: {
                width: data.file.width,
                height: data.file.height,
                ratio: Util.Math.round(data.file.height / data.file.width, 2),
            },

            sampleImg: {
                width: data.sample.width,
                height: data.sample.height,
                ratio: Util.Math.round(data.sample.height / data.sample.width, 2),
            },

            has: {
                file: data.file.url !== null,
                children: data.relationships.has_active_children,
                parent: data.relationships.parent_id !== undefined && data.relationships.parent_id !== null,
            },

            rel: {
                children: new Set(data.relationships.children),
                parent: data.relationships.parent_id,
            },

            meta: {
                duration: data.duration,
                animated: tags.has("animated") || data.file.ext == "webm" || data.file.ext == "gif" || data.file.ext == "swf",
                sound: tags.has("sound"),
                interactive: data.file.ext == "webm" || data.file.ext == "swf",
            },

            warning: {
                sound: tags.has("sound_warning"),
                epilepsy: tags.has("epilepsy_warning"),
            },

        };
    }

    /**
     * Generates PostData from a DOM element  
     * Only works on an individual post page (ex. `/posts/12345`)
     */
    export function fromDOM(): PostData {

        const $article = $("#image-container");
        const data: APIPost = JSON.parse($article.attr("data-post"));

        // Fetch tags - the existant ones are insufficient
        data["tags"] = {
            artist: getTags("artist"),
            character: getTags("character"),
            copyright: getTags("copyright"),
            general: getTags("general"),
            invalid: getTags("invalid"),
            lore: getTags("lore"),
            meta: getTags("meta"),
            species: getTags("species"),
        };

        // Restore the preview image. Not used anywhere, but avoids an error.
        const md5 = data["file"]["md5"],
            md52 = md5.substr(0, 2);
        data["preview"] = {
            "width": -1,
            "height": -1,
            "url": `https://static1.e621.net/data/preview/${md52}/${md52}/${md5}.jpg`
        };

        return PostData.fromAPI(data);

        function getTags(group: string): string[] {
            const result: string[] = [];
            for (const element of $(`#tag-list .${group}-tag-list`).children()) {
                result.push($(element).find(".search-tag").text().replace(/ /g, "_"));
            }
            return result;
        }
    }

    /**
     * Creates a thumbnail preview from an MD5 hash
     * @param md5 MD5 hash
     */
    export function createPreviewUrlFromMd5(md5: string): string {
        // Assume that the post is flash when no md5 is passed
        return md5 == ""
            ? "https://static1.e621.net/images/download-preview.png"
            : `https://static1.e621.net/data/preview/${md5.substring(0, 2)}/${md5.substring(2, 4)}/${md5}.jpg`;
    }
}

export enum LoadedFileType {
    PREVIEW = "preview",
    SAMPLE = "sample",
    ORIGINAL = "original",
}
