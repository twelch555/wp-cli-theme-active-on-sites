WP-CLI Plugin Active on Sites
===============================

A [WP-CLI](http://wp-cli.org/) command to list all sites in a Multisite network that have activated a given theme.

## Credit
This is a copy of Ian Dunn's iandunn/wp-cli-plugin-active-on-sites package altered to apply to themes instead of plugins. All the good bits are Ian's; all the errors are mine.

## Installing

`wp package install twelch555/wp-cli-theme-active-on-sites`

## Usage

`wp theme active-on-sites <theme_slug>`

### Options
[--field=<field>]
	Prints the value of a single field for each site.
[--fields=<fields>]
        Comma-separated list of fields to show.
[--format=<format>]
        Render output in a particular format.

## Example

```shell
> wp theme active-on-sites chaplin

Checking each site  100% [==================================================] 0:02 / 0:03

Sites where chaplin is active:
+---------+----------------------------------------+
| blog_id | url                                    |
+---------+----------------------------------------+
| 320     | http://2014.madrid.wordcamp.dev/       |
| 371     | http://2014.paris.wordcamp.dev/        |
| 413     | http://2015.london.wordcamp.dev/       |
| 464     | http://2015.milano.wordcamp.dev/       |
| 522     | http://2016.geneva.wordcamp.dev/       |
| 571     | http://2016.belfast.wordcamp.dev/      |
| 654     | http://2017.europe.wordcamp.dev/       |
+---------+----------------------------------------+
```
