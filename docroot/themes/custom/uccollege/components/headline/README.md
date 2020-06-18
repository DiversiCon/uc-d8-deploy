# Headline component

Headline for all page types. Contains an h1 and has an optional topic link, description, and date. The font-sizes are slightly bigger when used within an article/story. The theme can also be changed by adding `c-headline--lg` to the main container. This will increase the font sizes of the elements on larger breakpoints.

## Usage

```html
<header class="c-headline c-headline--lg">
  <a href="#" class="c-headline__topic t-heading--topic">Topic</a>
  <h1 class="c-headline__heading">Headline</h1>
  <div class="c-headline__description">
    <p>This is an optional description. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean enim ex, dictum blandit viverra quis.</p>
  </div> 
  <p class="c-headline__date">August 9, 2018</p>
</header>
```

