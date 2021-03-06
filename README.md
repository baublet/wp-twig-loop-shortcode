## Changes

### July 12, 2016

Updated code comments and separated the backend interface into its own folder from the main LSC  files.

### June 3, 2016

Added much in the way of documentation and improved the backend display a bit.

### TODO
* Separate the admin menu markup and CSS in a more elegant manner
* Update the way this plugin loads to use a global registry so that we don't pollute the global namespace, so we can load Twig only when we need it, and so we only have to load shortcodes we need, all while allowing us to continue sharing dependencies. This is a fairly large project that is unnecessary with the use of caching, but it would be useful to do it for shared hosting environments with low memory and/or no caching abilities.

# Loop Shortcodes
This WordPress plugin gives you the ability to call the Loop from shortcodes and style them using the [Twig template engine](http://twig.sensiolabs.org/). It allows you to use WordPress in a very frankenstein-like manner as a fairly powerful CMS, using inline templates in pages and posts which query other posts, users, and taxonomies on the fly.

**Note:** This is *not* intended to be used in production without some form of caching. Running WordPress and Twig, compiling Twig templates, making multiple WordPress WP_Query objects, and parsing dozens of posts is memory and processor-intensive.

# Installation

SSH into your WordPress plugins directory. It's typically ```wordpress/wp-content/plugins```. Then, clone this repo into that directory via:

```
git clone https://github.com/baublet/wp-twig-loop-shortcode.git
```

It will download into ```plugins/wp-twig-loop-shortcode```. Once that is done, you have to download the Twig submodule if you don't have Twig installed globally. To do that:

```
cd wp-twig-loop-shortcode
git submodule init
git submodule update
```

These commands navigate the SSH shell to the plugin directory and initiate the submodules. Then, git will download Twig from the Twig repo.

Once all that is done, you can then activate the plugin in your WordPress plugin directory. To validate that it's working, navigate to the Templates page in your WordPress admin menu, located in Appearance -> Loop Shortcode.

## Updates

To update this plugin to the latest version, ssh into your ```wp-content/plugins/wp-twig-loop-shortcode/``` directory and type:

```
git pull
```

It will update the plugin to the latest version. Also, don't forget to periodically update Twig via:

```
git pull --recurse-submodules
```

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

### User Loops

```html
<h3>Top Authors</h3>
[userloop query="number=25&offset=1&orderby=post_count&order=DESC" avatar_size=64]
  <div class="an-author-box">
    <a href="{{ authorpage }}" class="author" title="Posts by {{ display_name|titlesafe }} ({{ posts }})">{{ avatar|raw }}</a>
  </div>
[/userloop]
```

This displays the avatars of 25 authors of the blog with a link to their page, ordered by number of posts (the most active posters at the top).

### Taxonomy/Term Loops

```html
<h3>Popular Tags</h3>
<ul>
[termloop query="taxonomies=post_tag&orderby=count&order=desc&number=12"]
  <li>
    <a href="{{ link|raw }}" title="View all posts with the tag, {{ title|titlesafe }}">
      <span class="tag-icon"> </span> {{ title }}
      <span class="count">{{ count }}</span>
    </a>
  </li>
[/termloop]
</ul>
```

This tag returns the top 12 tags on the side, ordered by the number of posts with that tag.

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

Environments are optional features that help you reduce post duplication on pages with multiple query strings by saving the posts into a global registry that you can access in order to exclude posts from subsequent queries. Environments are defined directly on the shortcode. For example:

```
[loop query="my query" environment="main-posts"]
    template
[/loop]
```

This query parses `my query` (whatever you prefer here), and adds all of the post IDs it finds to an environment (a variable) called `main-posts`. In the same page in another loop, you can recall this environment and exclude all posts already called up in that environment, so that the posts you used before aren't duplicated elsewhere. To do so, you would use:

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

Environments are created first-come-first-serve (e.g., the first shortcode in the post code has first dibs on posts). You can also build environments without necessarily using them, as all posts are stored on a default environment called `loop_shortcode`. When you want to recall an environment, the plugin simply appends the following string to your query:

```php
$query .= "&{recall_environment_type}[]={environment_id}"
```

It does this for each post in the environment. So, for example, if you want to exclude all the posts, you would use `recall_environment_type="post__not_in"` in your shortcode. Then, this query simply appends a bunch of posts for the WP_Query object to *explicitly exclude from the current query*.

## Queries

For the basics of WordPress queries using the WP Query object, see [the WordPress manual on WP_Query](https://codex.wordpress.org/Class_Reference/WP_Query). The WP Query object takes both arrays and query strings as a valid query on the database. With this plugin, you will be working with query strings. A query string is a URI resource string that can be parsed into a set of variables and arrays in PHP.

Thus, `posts_per_page=10&cat=4,25,20,-410` is the PHP equivalent of something like:

```
    posts_per_page = 10
    cat = 4,25,20,-410
```

Similarly, you can use arrays for more advanced queries. To get around the fact that wordpress doesn't play nice when you use brackets (`[` and `]`) inside shortcodes, even if in quotations (I know, right?), you can use curly-brackets (`{` and `}`) instead. For example:

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

This query grabs five posts of the type "event" with the custom key named "date" (which in the backend, WordPress calls the meta key named "date") of between last week and forever into the future.

In the above example, this query loads five upcoming events, but keeps events for up to a week after they happen.

Getting your query right might take some trial and error.

### Query String Helpers

In the example above, you may have noticed that we used {{lastweek}} and {{>=}} in our query. We have to do things like this for a few reasons:

1. WordPress doesn't like you using brackets in your shortcodes at all. Even in quotations.
2. We need to strip out things that might mess up parsing your query string, like the `>=` symbol.
3. It's really useful to have a few date helpers to query things based on dates!

So this plugin provides a few helpers you can use in your query strings. The following are in the order that the yare parsed by Loop Shortcode:

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
                        // This includes things like equal signs that you don't want as part of the actual query string,
                        // special characters, and anything else that you're afraid might not play nice with the way
                        // PHP parses query strings

{0}                     // And finally, in the last step, all single curly braces are converted into brackets ([ and ])
```

These helpers allow complex queries, such as:

```
posts_per_page=5&post_type=event&order=ASC&orderby=meta_value_num&meta_key=date&meta_query{0}{key}=date&meta_query{0}{value}={{lastweek}}&meta_query{0}{compare}={{>=}}&meta_query{0}{type}=numeric
```

To be passed into a query object as:

```
posts_per_page=5&post_type=event&order=ASC&orderby=meta_value_num&meta_key=date&meta_query[0][key]=date&meta_query[0][value]=1456442898&meta_query[0][compare]=%3E%3D&meta_query[0][type]=numeric
```

Which PHP parses into something like:

```
  posts_per_page = 5
  post_type = event
  order = ASC
  orderby = meta_value_num
  meta_key = date
  meta_query = [
    [ key => date
      value => 1456442898
      compare => >=
      type => numeric
    ]
  ]
```

## Templates and Styling

You can put anything inside the shortcode tag as a template. This plugin provides you with a host of assorted variables and helpers to help you build posts from the loop output.

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

### Users

```php
{{ id }}                  // The user's ID
{{ username }}            // Their username
{{ nicename }}            // The "nice name" of the user (created by WordPress)
{{ display_name}}         // The user's display name (which they select in options)
{{ email }}               // User email address
{{ url }}                 // User personal website, which they enter in their profile
{{ joined }}              // The timestamp of the user's join date
{{ posts }}               // Number of posts by the user
{{ avatar }}              // The user's avatar image, generated with get_avatar()
{{ authorpage }}          // The author's profile page on your website, typically showing a list of their posts
{{ meta }}                // The author's meta data, generated with get_user_meta()
  {{ meta.first_name }}
  {{ meta.last_name }}
  {{ meta.nickname }}
  {{ meta.description }}  // Etc...
```

### Taxonomies/Terms

```php
{{ id }}      // The term's ID
{{ name }}    // The term's display name
{{ title }}   // Alias of name
{{ slug }}    // The slug of a term (e.g. "Popular posts" slug would be popular-posts)
{{ group }}
{{ taxonomy_id }}
{{ taxonomy }}
{{ description }}
{{ parent }}
{{ count }}   // Number of posts in this term
{{ link }}    // The link to the page displaying all posts from this term
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
