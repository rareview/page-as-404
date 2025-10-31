# Wordpress Plugin Starter by Rareview
The lightweight and core-friendly way to manage your site&#x27;s 404 response using a standard Page within your site.

## Description

Take control of your site's 404 Page Not Found error response with this simple, lightweight and core-friendly plugin.

Out of the box, WordPress treats 404 pages as a technical matter, with themes encouraged to include a hard-coded `404.php` template. But that means it's difficult for site owners to make changes.

404 pages have become a space for websites and publishers to have a little fun, putting a smile on visitors' faces as their journey is halted. They can also serve a useful navigational purpose, guiding users to the site's latest or most popular content, or offering a search box.

By using WordPress's built-in Pages feature, you can design and serve a feature-rich 404 page, making full use of WordPress content and navigation blocks. And you can edit and maintain it as easily as any other Page.

Just activate this plugin, then select your desired Page from the dropdown list on the Settings &amp;rarr; Reading page.

## Installation

1. Upload and activate the plugin in the usual way
1. Create and publish a Not Found page.
1. Go to your site&#x27;s Settings &amp;rarr; Reading page
1. Choose your Not Found page from the dropdown list.
1. Save your updated Settings.

## Frequently Asked Questions

**Will my 404 Page show up in other unwanted contexts?**

No. The plugin removes your designated 404 page from generated lists of Pages including on-site search results, blocks/widgets, REST API queries, and `sitemap.xml`. A `noindex` tag is added to the page header, asking external search engines to ignore it.

**Will my 404 Page work with...**

Your 404 Page is just a normal Page.

**What error code is served?**

A standard 404 error code is served, unless you access the URL of the Page itself - in which case, it will be a 200 success message.

**Where is the Settings page for this plugin?**

There is no Settings page; just an extra dropdown on the existing Settings > Reading page. The 404 Page itself exists as a normal Page, and can be found among your site's Pages.

**What happens with my theme's 404 template?**

Once a Page has been selected, any calls to the 404 template are intercepted, and replaced by a request for the selected 404 Page. If your theme contains a `404.php` template, it will be ignored.

**Where are all the Pro features?**

This plugin keeps things simple and clutter-free. There are [numerous plugins in the WordPress repository](https://wordpress.org/plugins/search/404/) already, which add functionality to redirect users, log details, or send alerts in the event of a 404 error. If you are looking for extra features, you will probably find them there.