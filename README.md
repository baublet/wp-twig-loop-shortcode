# Loop Shortcodes
This WordPress plugin gives you the ability to call the Loop from shortcodes and style them using the [Twig template engine](http://twig.sensiolabs.org/). It allows you to use WordPress in a very frankenstein-like manner as a fairly powerful CMS, using inline templates in pages and posts which query other posts, users, and taxonomies on the fly.

**Note:** This is *not* intended to be used in production without *significant* caching. Running WordPress and Twig, compiling Twig templates, making multiple WordPress WP_Query objects, and parsing dozens of posts is memory and processor-intensive.

# Installation

SSH into your WordPress plugins directory. It's typically ```wordpress/wp-content/plugins```. Then, clone this repo into that directory via:

```
git clone https://github.com/baublet/wp-twig-loop-shortcode.git
```

It will download into ```plugins/wp-twig-loop-shortcode```. Once that is done, you have to download the Twig submodule. To do that:

```
cd wp-twig-loop-shortcode
git submodule init
git submodule update
```

These commands navigate the SSH shell to the plugin directory and initiate the submodules. Then, git will download Twig from the Twig repo.

Once all that is done, you can then turn on the plugin in your WordPress plugin directory.

## Updates

To update the plugin to the latest version, ssh into your ```wp-content/plugins/wp-twig-loop-shortcode/``` directory and simply type

```
git pull
```

It will update the plugin to the latest version!

# Usage

Once the plugin is enabled, you will be able to call it with the shortcode:

```
[loop query="your query"]
    My template goes here!
[/loop]
```

**Note:** Because of the way WordPress works, you need to close your loop tag whether or not you use a custom template.

## Examples

### Post Loops

```html
<ul class="news-feed">

[loop query="posts_per_page=3&cat=5&date_query{}{after}=1 month ago" environment=sticky]
    {# ############################################## #}
    {# Action Items! And yes, Twig comments work here #}
    {# ############################################## #}

    <li class="action">
        <h4><a href="{{ link }}" title="{{ title|titlesafe }}">{{ title|raw }}</a></h4>
        <div class="description">
            {{ excerpt|raw }} <a href="{{ link }}" title="Read more about {{ title|titlesafe }}">Read more...</a>
        </div>
        <div class="meta">
            Last update: {{ modified_ago }} ago
        </div>
    </li>
[/loop]

[loop query="" sticky=1 environment="sticky"]
    {# ######################### #}
    {# Loads up our sticky posts #}
    {# ######################### #}

    <li class="sticky">
        <h4>
            <a href="{{ link }}" title="{{ excerpt|words(50)|titlesafe }}">{{ title|title|raw }}{{ title|subtitle(' <span class="subtitle"><span class="screen-reader-text">:</span>','</span>')|raw }}</a>
        </h4>
    </li>

[/loop]

[loop query="posts_per_page=10&cat=4,25,20,-410" content=0 environment=main-news recall_environment="sticky" recall_environment_type="post__not_in"]
    {# ############### #}
    {# Main News Items #}
    {# ############### #}

    {# This calls our regular posts without the items we loaded as sticky posts or action items. #}

    {# Give it an "new" class if it was posted within the last two weeks. #}
    <li{% if age < 1204800  %} class="new"{% endif %}>
        <h4>
            <a href="{{ link }}" title="{{ excerpt|words(50)|titlesafe }}">{{ title|title|raw }}{{ title|subtitle(' <span class="subtitle"><span class="screen-reader-text">:</span>','</span>')|raw }}</a>
        </h4>
    <div class="meta">
    {% if 27 in category_ids or 405 in category_ids %}
        {# In this example, we only include the author in certain categories #}
        <span class="author a{{ author.id }}">
            {# Include the first, primary author always. #}
            by <a href="{{ author.page }}" title="View all posts by {{ author.display_name }}" class="name">{{ author.display_name|trim }}</a>

            {# In this snippet, we're including the other authors if there are any #}
            {% if custom.OtherAuthor is not iterable and custom.OtherAuthor > 0 %}
                {# This is run if there is only a single other author. #}
                and
                <a href="{{ custom.OtherAuthor|wpgetauthorpage }}" title="View all posts by {{ custom.OtherAuthor|wpauthormeta('display_name') }}" class="name">
                    {{ custom.OtherAuthor|wpauthormeta('display_name') }}
                </a>
            {% else %}
                {# Now, let's loop through the custom keys called OtherAuthor if there are multiple #}
                {% for otherid in custom.OtherAuthor %}
                    {# The below snippet outputs ", and" if this is the last item in this loop, otherwise it outputs ", " #}
                    {{ loop.last ? ', and ' : ', ' }}
                    <a href="{{ otherid|wpgetauthorpage }}" title="View all posts by {{ otherid|wpauthormeta('display_name') }}" class="name">
                      {{ otherid|wpauthormeta('display_name') }}
                    </a>
                {% endfor %}
            {% endif %}
        </span>
    {% endif %}
    <span class="date" title="{{ date }}">{{ ago }} ago</span>,
    {# Now, let's output the categories #}
    <span class="categories">
        {% if categories > 1 %}
            {% for category in categories %}
                <a href="{{ category.link }}">{{ category.name }}</a>
            {% endfor %}
        {% elseif categories == 1 %}
            <a href="{{ category.0.link }}">{{ category.0.name }}</a>
        {% endif %}
    </span>
    </div>
</li>
[/loop]

</ul>
```

This is a very sophisticated example, but it should give you an idea of most of this plugin's functions.

## Arguments

Required arguments:
* `query` Your custom query to grab posts. (More detail below.)

Optional arguments:
* `thumbnail_size` String. The size of the thumbnail you want to associate with the post. Default is `'thumbnail'`.
* `content` Boolean (1 or 0). If 1, you can use the full post content via `{{ content }}`. Default is 0.
* `nl2br` Boolean (1 or 0). If 1, it converts your line breaks to `<br>` tags in the template. Default is false.
* `texturize` Boolean (1 or 0). If 1, this will texturize the output of your post through WordPress' `wptexturize()` function. Default is 1.
* `sticky` Boolean (1 or 0). If 1, this function will include sticky posts. Default is 0. **Note:** it's a good idea to leave this off, as sticky posts will *always* be included when this is true, even if, for example, they aren't in the category you specify. One strategy for avoiding this is to include sticky posts in a separate loop above the items you want to regularly include.
* `environment` String. The name of this environment. (More information below.) Default: `'loop_shortcode'`.
* `recall_environment` Boolean (1 or 0). If 1, this will include the posts listed in the `environment` variable into this query using the `recall_environment_type` comparator. Default is 0
* `recall_environment_type` String. Comparator to use in your queries to either include or exclude (or some other type of comparison I'm not aware of) with post IDs in certain environments. Default: `post__not_in`.

### Environments

Environments are optional features that help you reduce post duplication on pages with multiple query strings. Every environment is defined in loop shortcode you use. For example:

```
[loop query="my query" environment="main-posts"]
    template
[/loop]
```

This query parses `my query` (whatever you prefer here), and adds all of the post IDs it finds to an environment called `main-posts`. In the same page in another loop, you can recall this environment and exclude all posts already called up in that environment so that the posts you used before aren't duplicated elsewhere. To do so, you would use:

```
[loop query="similar or same query to the above" environment="main-posts" recall_environment=1 recall_environment_type="post__not_in"]
    template 2
[/loop]
```

This way, the posts you render in template 2 aren't the same ones you rendered in template 1.

In the backend, this means that each ID is added to a PHP associative array:
```php
environments['environment-name'] = array(post, ids, are, here, as_integers);
```

Environments are created linearly (e.g., the first shortcode in the post code has first dibs on posts). You can also build environments without necessarily using them, as all posts are stored on a default environment called `loop_shortcode`. When you want to recall an environment, the plugin simply appends the following string to your query:

```php
$query .= "&{recall_environment_type}[]={environment_id}"
```

It does this for each post in the environment. So, for example, if you want to exclude all the posts, you would use `recall_environment_type="post__not_in"` in your shortcode. Then, this query simply appends a bunch of posts for the WP_Query object to explicitly exclude from the current query.

## Queries

For the basics of WordPress queries using the WP Query object, see [the WordPress manual on WP_Query](https://codex.wordpress.org/Class_Reference/WP_Query). The object basically takes both arrays and query strings as a valid query on the database. A query string is basically a URI resource string.

Thus, `posts_per_page=10&cat=4,25,20,-410` is the PHP equivalent of:

```php
    $posts_per_page = 10;
    $cat = '4,25,20,-410';
```

Similarly, you can use arrays for more advanced queries. To get around the fact that wordpress doesn't play nice when you use brackets ([ and ]) inside shortcodes, even if in quotations (I know, right?), you can use curly-brackets ({ and }) instead. For example:

```
posts_per_page=5&post_type=event&order=ASC&orderby=meta_value_num&meta_key=date&meta_query{0}{key}=date&meta_query{0}{value}={{lastweek}}&meta_query{0}{compare}={{>=}}&meta_query{0}{type}=numeric
```

This is the equivalent of passing the following arguments into the WP_Query object:

```php
    $args = array (
                    posts_per_page => 5,
                    post_type => "event",
                    order = "ASC",
                    orderby = "meta_value_num",
                    meta_key = "date",
                    meta_query = array(
                        array(
                            "key" => "date",
                            "value" => "{{lastweek}}",
                            "compare" => "{{>=}}",
                            "type" => "numeric"
                            )
                        )
    );
```

In simple terms, this query grabs five posts of the type "event" with the custom key named "date" (which in the backend, WordPress calls the meta key named "date") of between last week and forever into the future.

In real terms, this query loads five upcoming events, but keeps events for up to a week after they happen.

Getting your query right might take a lot of trial and error. Just play with it.

### Query Helpers

In the example above, you may have noticed that we used {{lastweek}} and {{>=}} in our query. We have to do things like this for a few reasons:

1. WordPress doesn't like you using brackets in your shortcodes at all. Even in quotations.
2. We need to strip out things that might mess up parsing your query string, like the `>=` symbol.
3. It's really useful to have a few date helpers to query things based on dates!

So this plugin provides a few helpers you can use in your query strings.

```html
{{now}}                 // Time helpers. Returns straight up UNIX time stamps
{{tomorrow}}            // Tomorrow
{{yesterday}}           // Yesterday
{{nextweek}}            // Next week
{{lastweek}}            // Last week
{{nextmonth}}           // Next month
{{lastmonth}}           // Last month

{{environments}}        // The custom environments string built by the plugin. This is not required for environments to work.

{{any odd characters}}  // Any characters in double curly braces will be encoded using PHPs urlencode function

{0}                     // And finally, in the last step, all single curly braces are converted into brackets ([ and ])
```

This allows this:

```
posts_per_page=5&post_type=event&order=ASC&orderby=meta_value_num&meta_key=date&meta_query{0}{key}=date&meta_query{0}{value}={{lastweek}}&meta_query{0}{compare}={{>=}}&meta_query{0}{type}=numeric
```

To be passed into a query object as:

```
posts_per_page=5&post_type=event&order=ASC&orderby=meta_value_num&meta_key=date&meta_query[0][key]=date&meta_query[0][value]=1456442898&meta_query[0][compare]=%3E%3D&meta_query[0][type]=numeric
```

## Templates

You can put anything inside the shortcode as a template. But without helpers, that means nothing! So this plugin provides you with a host of assorted helpers to help you build posts from the loop output.

The default template is:

```html
<article class="post {{ post_class }}">
    <h4><a href="{{ link }}" class="title" title="{{ excerpt|striptags|words(50) }}">{{ title|raw }}</a></h4>
    <p>{{ excerpt|raw }}</p>
    <div class="meta">
        <span class="author">By <a href="{{ author.page }}">{{ author.display_name }}</a><span>
        <span class="date"><span title="{{ date }}">{{ ago }}</span> ago</span>
        <span class="comments"><span>{{ comments }}</span> {{ comments == 1 ? \'comment\' : \'comments\' }}</span>
    </div>
</article>
```

### Posts

```php
{{ query }}         // The query you used!

{{ id }}            // The post's ID
{{ title }}         // Post title
{{ link }}          // Post link (just the URL) using get_permalink()

{{ date }}          // Default output of the postdate
{{ time }}          // Time of the post
{{ ago }}           // Human-readable ago (e.g., 2 hours ago) using WordPress's human_time_diff() function
{{ modified }}      // The date that the post was last modified
{{ modified_time }} // The time that the post was last modified
{{ modified_ago }}  // Human-readable ago for the post's last modified date

{{ content }}       // The post's content, if you chose to include it (otherwise, it's blank)
{{ excerpt }}       // The post's excerpt

{{ post_type }}     // The human-readable slug for the post type
{{ comments }}      // The number of comments
{{ comments_s }}    // Is blank if there if there is one comment, or with and s if there are multiple
                    // This allows you to use {{ comments }} comment{{ comments_s }} to output, e.g., 1 comment, or 3 comments

{{ author.id }}             // The author's ID
{{ author.username }}       // The author's username
{{ author.display_name }}   // Display name of the author
{{ author.page }}           // The link to the author's page using WordPress's get_author_posts_url()
{{ author.link }}           // Gets the full link of the author's page using get_the_author_meta('user_url')
{{ author.email }}          // Gets the author's email

{{ thumb }}         // The array of the thumbnail provided by wp_get_attachment_image_src()
    {{ thumb.1 }}   // The source of the image
    {{ thumb.2 }}   // The width of the image
    {{ thumb.3 }}   // The height of the image
{{ thumbnail }}     // The url of the post's thumbnail/featured image using using get_the_post_thumbnail()

{{ post_class }}    // The classes of the post provided by get_post_class()
                    // e.g., <div class="post {{ post_class }}"> ...

{{ categories }}            // The array of the categories
    {{ categories.n.link }} // The link to the category
    {{ categories.n.name }} // Category name
    {{ categories.n.id }}   // Category ID
{{ category_ids }}          // An array of category IDs that this post belongs to
                            // Very useful for seeing if the post belongs to a certain category,
                            // e.g. {% if 23 in category_ids %} do some special styling {% endif %}

{{ custom }}                // If you have chosen to include custom fields, here's how you access them!
    {{ custom.myField }}    // If there'sa single value, this won't be an array, otherwise it will be
```

## Custom Twig Helpers

Twig gives developers the ability to add powerful custom filters and functions to its variables. I have supplied many for you in twig-setup.php.

```php
{{ excerpt|words(50, '...') }}
```

Concatenates the number words (50 here), truncating the rest, and appending '...' if there were words concatenated.

```php
{{ title|title('<span class="maintitle">','</span>') }}     // Title: Subtitle => <span class="maintitle">Title</span>
```

This function returns everything before the first colon of the string, surrounded by the first and second arguments of the function. This allows you to style titles and subtitles differently.

If there is no colon, it just returns the title.

```php
{{ title|subtitle('<span class="subtitle">','</span>') }}     // Title: Subtitle => <span class="subtitle">Subtitle</span>
```

This is identical to the above, but instead returns everything after the colon with the specified tags surrounding it. This allows you to use them in conjunction to build separate title and subtitles in tags.

```php
{{ title|titlesafe }}
```

Returns a version of the variable that is safe to be placed in an HTML title tag.

```php
{{ otherauthor.id|wpauthormeta('display_name') }}
```

Allows you to call the `get_the_author_meta()` function in WordPress so that you can use custom fields in fancy ways to attach multiple authors to posts.

```php
{{ otherauthor.id|wpgetauthorpage }}
```

Looks for an author with the id specified in the variable (in this instance, `otherauthor.id`), and returns the URL of their author page using WordPress's function, `get_author_posts_url()`.

# Todo

- [ ] Installation instructions
- [ ] Add some examples
  - [X] Post loops
  - [ ] User loops
  - [ ] Taxonomy loops
- [x] Fix the bug with WordPress' notoriously annoying texturizing and prettying of my damned query strings...
  - I tried to reproduce this and failed...
- [x] Add the ability to save templates in the tag to reduce code duplication
  - [x] Basic functionality via a backend menu (Appearance -> Loop Shortcode)
  - [x] Give it ability to save the full shortcode, including the query,  environment, etc.
  - [x] Use WordPress' [built-in options functions](https://codex.wordpress.org/Creating_Options_Pages) to keep track of these
