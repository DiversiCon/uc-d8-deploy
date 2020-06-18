# Infographic component

This is a simple component for displaying an infographic. In most cases, this will be a pie chart, bar graph or other visual representation of data within an image. 

The `alt` tag is blank, and the description handled by the content within the container specified by `aria-labelledby`. This text should be a description of the data a sighted user would see. Just be sure each infographic on the page has a unique ID for the spans.

Note: This _could_ be done with just markup, without a dedicated component, but leaving it open for any future functionality needs, based on infographic complexity, etc.

## Usage

```html
<figure class="c-infographic">
    <a href="#" target="_blank">
	    <img src="/themes/custom/uccollege/it_showcase/images/infographic.png" alt="" aria-labelledby="infographic-1">
    </a>
    <span id="infographic-1" aria-hidden="true">
        Pie chart showing 41% of students with no GAP years, 33% with 
        1 GAP year and 26% with 2+ GAP years
    </span>
</figure>
```
