# Purpose
This directory should only be used for templates specifically required by showcase components/pages.  Therefore, any template in this directory should have the following template override suffix:

    --it_showcase

Any paragraph (could be other entities in the future) that is rendered within the context of the showcase section will get an additional theme suggestion that looks for that suffix.  For example, the following is an override for normal paragraph--video.html.twig template:

    paragraph--video--it_showcase.html.twig

These templates will only be used when the it_showcase module is enabled and the item being themed appears within the context of the showcase section.
