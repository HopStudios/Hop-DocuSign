# Hop DocuSign plugin for Craft CMS 3.x

Integrates DocuSign functionalities into your freeform forms.

![Screenshot](resources/img/Hop-DocuSign.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require HopStudios/hop-docusign

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Hop DocuSign.

## Hop DocuSign Overview

Hop DocuSign is an extension to allow a seamless digital signing experience. Currently we only support Freeform form submissions but we're planning to add more support to other form services! Drop us a note if you'd like us to add support to your plugins at tech@hopstudios.com.

## Configuring Hop DocuSign

1. You have to have a template created in DocuSign (we need the role and the template id)
2. The template field labels will have to match your freeform fields handles
3. Save the login credentials in the Settings

## Using Hop DocuSign

Link the freeform (on craft) with the template (on DocuSign) in Hop Docusign. This will generate a signing url for the user to complete the signing and return back the the site when finished.

## Hop DocuSign Roadmap

* Add support to other form services
* Allow more configurations

Brought to you by [Hop Studios](https://www.hopstudios.com)
