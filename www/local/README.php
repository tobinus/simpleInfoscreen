<?php
die();
?>

This folder is used for content that is to be displayed on the infoscreen
(slides, so to speak). You can also put images and so on here, and use them in
html files.

Note: The files in this folder will automatically be accessed through load.php, and not directly,
when used in a slideshow. This is done to change the cache headers (to prevent
outdated versions from being displayed for too long). It is still possible to
access these pages directly by entering their url (local/...) in the browser.

About Twig templates:
* It is possible to create and use Twig templates, by using the .twig file extension.
* Twig templates will be rendered when accessed through load.php (as is the case
  when it is used in a slideshow).
* See http://twig.sensiolabs.org/doc/templates.html for more information about
  creating and using templates.
* One useful way to use templates would be to have one base twig-template
  for common things like stylesheet, headers, footers and so on, and extend that
  base file in the more specific templates. This way, you can get a consistent
  look and feel between the different slides.
* WARNING: Twig templates will be outputted as PLAIN TEXT (NOT rendered) when
  accessed directly (by navigating to local/... manually). Don't put any sensitive
  data inside!
