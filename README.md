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
        The slug for the new theme.

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
        
	[--skeleton_source=<source>]
		What is a source of skeleton theme. Possible values are 'github' and 'zip'. Default is 'github'.
    
    [--source_username=<username>]
    	What is a username on Github. Default is 'innocode-digital'.
    
    [--source_repo=<repo>]
		What is a repository on Github. No need to use it when <skeleton_source> is 'zip'. Default is 'wp-theme-skeleton'.
    
    [--source_url=<url>]
    	What is an URL of source. Applicable only when <skeleton_source> is 'zip'.
    
    [--skip-env]
    	Don't generate .env file.
    
    [--skip-install-notice]
    	Don't show notice about need to run installation commands.

## Installing

Installing this package requires WP-CLI v2.0.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with:

    wp package install git@github.com:innocode-digital/wp-cli-scaffold-theme-command.git
    
To be able to authenticate to Github, so it will be possible to retrieve your user data and insert 
into `composer.json` as author as well as into `package.json` as contributor during scaffolding and 
also in case when you want to use private skeleton repository, you need to add token with one of 
the following methods:

* Add to your local `auth.json` OAuth token from the command line:

~~~
composer config -g github-oauth.github.com xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
~~~

* Add to your local `auth.json` OAuth token manually in `$HOME/.composer/auth.json`:

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

To generate token go to [Settings / Developer settings](https://github.com/settings/tokens). 
Token should has at least next scopes:

* `repo` - Full control of private repositories
* `user`
    * `read:user` - Read all user profile data
