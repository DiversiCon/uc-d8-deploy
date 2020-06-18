# Big Hero component

This component uses responsive images in picture element that extend the full width of the site. It was originally created for the error pages.

- Heading, optional
- Description, optional
- Image, large (1380px wide)
- Image, small (768px wide)


## Variations

There are no design variations for this component.

## Usage

```html
<div class="c-bighero">
  <div class="c-bighero__text">
    <h1 class="c-bighero__heading">Oops!</h1>
    <div class="c-bighero__description">
      <p>The page you are looking for is not here. Try going back to the <a href="/it/showcase/">homepage</a>.</p>
    </div>
  </div>
  <picture class="c-bighero__img">
    <source media="(max-width: 768px)" srcset="/themes/custom/uccollege/it_showcase/images/gargoyle-sm.jpg"> <img src="/themes/custom/uccollege/it_showcase/images/gargoyle-lg" alt=""></picture>
</div>
```
