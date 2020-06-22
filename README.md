# Hop DocuSign plugin for Craft CMS 3.x

Integrates DocuSign functionalities into your freeform forms.

![Screenshot](resources/img/Hop-DocuSign.png)

## Hop DocuSign Overview

Hop DocuSign is an extension to enable a seamless DocuSign signing experience for your customers. Currently we only support Freeform form submissions but we're planning to add  support to other form services! Drop us a note if you'd like us to add support to your plugins at tech@hopstudios.com.

### Check out our demo site! [https://hopdocusign.hopstudios.com](https://hopdocusign.hopstudios.com)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Buy the plugin from the Plugin Store: [https://plugins.craftcms.com/hop-docusign](https://plugins.craftcms.com/hop-docusign)

2. In the Control Panel, go to Settings → Plugins and click the “Install” button for Hop DocuSign.

## Configuring Hop DocuSign

1. Go to Settings > Hop DocuSign and save the login credentials
2. Create the template or use any existing templates in DocuSign (we need the role name and the template id)
3. Create the Freeform form
4. The template field labels need to match with your freeform field handles so that the data can be transferred and filled in

## Using Hop DocuSign

Link the freeform (on CraftCMS) with the template (on DocuSign) with Hop Docusign. This will generate a signing url for the user to complete the signing and return back the the site when finished.

1. Go to Hop DocuSign and + New Template
2. Follow the instructions and fill out every field
3. The template id is not too visible on the DocuSign edit screen so we suggest grabbing it from the url
4. Now try filling out your web form! You should be taken to the signing page when you hit submit

If you wish to send the user a copy of the completed document, please adjust your DocuSign settings using this reference (Embedded/Captive Signing): https://support.docusign.com/en/articles/Why-aren-t-my-signers-receiving-DocuSign-Notification-emails

## Hop DocuSign Roadmap

* Add support to other form services
* Allow more configurations

Brought to you by [Hop Studios](https://www.hopstudios.com)
