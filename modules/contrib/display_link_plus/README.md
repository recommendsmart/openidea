CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

This module improves on core's "Empty Node Frontpage behavior" area plugin
(used in headers and footers) when used on views that show a restrictive set of
content types (e.g. one or two) by allowing a site builder to add links that
point directly to one or more forms to add content, of a specific content type.
You can also specify a class, for example to format as a button.

The intent is to make overall site maintenance easier by providing relevant and
intuitive editorial links in context.


INSTALLATION
------------

 * Install the Smart Date module as you would normally install a contributed
   Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


CONFIGURATION
-------------

  * To use the field provided by this module, add a new field to the Header or
    Footer of your view. Look for Add Content by Bundle link. Click the
    checkbox and click apply to add the field.

  * In the configuration screen, you have the option to specify:
    - If the link should display even if the view has no results.
    - Which bundle (content type, for nodes) the link should be to create.
      Note that it is possible to add multiple buttons, so if your view
      includes content from multiple bundles, you can add a button for each.
    - The label text for the link.
    - One or more CSS classes to apply to the link. The "button" class is
      recommended, as this will style the link as a button, consistent with
      many other actions in the Drupal interface.
    - Target, whether to show the form in a separate page, or overlaid on the
      view as a modal or off-screen dialog.
      - If a modal or dialog is chosen, the width in pixels for the overlay.


MAINTAINERS
-----------

 * Current Maintainer: Martin Anderson-Clutz (mandclu) - https://www.drupal.org/u/mandclu
