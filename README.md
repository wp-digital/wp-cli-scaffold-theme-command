innocode-digital/wp-cli-scaffold-theme-command
==============================================

Quick links: [Using](#using) | [Installing](#installing) | [Contributing](#contributing)

## Using

This package implements the following command:

### wp scaffold theme

Generates starter code for a theme based on Innocode theme skeleton.

    wp scaffold theme <slug> [--activate] [--enable-network] [--name=<title>] [--author=<full-name>] [--author_uri=<uri>] [--force]

See the [WP Theme Skeleton](https://github.com/innocode-digital/wp-theme-skeleton) for more details.

**OPTIONS**

	<slug>
		The slug for the new theme, used for prefixing functions.

	[--activate]
		Activate the newly downloaded theme.

	[--enable-network]
		Enable the newly downloaded theme for the entire network.

	[--name=<title>]
		What to put in the 'Theme Name:' header in 'style.css'. Default is <slug> with uppercase first letter.
		
    [--version=<version>]
        What to put in the 'Version:' header in 'style.css' and in the 'version' property in 'composer.json' and 'package.json' files. Default is '1.0.0'.
        
    [--description=<text>]
        What to put in the 'Description:' header in 'style.css' and in the 'description' property in 'composer.json' and 'package.json' files. Default is ''.

	[--author=<full-name>]
		What to put in the 'Author:' header in 'style.css'. Default is 'Innocode'.

	[--author_uri=<uri>]
		What to put in the 'Author URI:' header in 'style.css'. Default is 'https://innocode.com/'.

    [--text_domain=<domain>]
        What to put in the 'Text Domain:' header in 'style.css'. Default is <slug>.
        
    [--repo=<slug>]
        What is a repo on Github for this project. Default is 'innocode-digital/<slug>'.

	[--force]
		Overwrite files that already exist.

## Installing

Installing this package requires WP-CLI v2.0.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with:

    wp package install git@github.com:innocode-digital/wp-cli-scaffold-theme-command.git
    
To be able to authenticate to Github you need to add token with one of the following methods:

* Add to your local `auth.json` OAuth token:

~~~
{
    "github-oauth": {
        "github.com": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    }
}
~~~
    
* Define `GITHUB_PAT` constant in `wp-config.php`:

~~~
define( 'GITHUB_PAT', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx' );
~~~

To generate token go to [Settings / Developer settings](https://github.com/settings/tokens) with next scopes:

* `repo` - Full control of private repositories
* `user`
    * `read:user` - Read all user profile data