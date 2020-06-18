# Cards component

This component display cards with an image and text. Each card can be single width (default) or double width. Double widths require two images. The large image shows on devices larger than 1024, which the breakpoint at which the card width goes double. Below 1024 the double card looks like a single width card. Double width cards should be used sparingly and 2 double width cards should not be used next to each other.

Cards component contains:
 * heading, optional
 * description, optional
 * grid of cards, required, but no limit on number of cards

Each Card contains:
 * url, only the image and heading are clickable (optional) 
 * image, with alt tag, recommended dimensions 470px by 470px (optional, fallback included)
 * image large, recommended dimensions 792px by 384px (required for double cards that have an image)
 * heading, allows `<strong>` tags (optional)
 * description
  * can be rich text,
    ```html
      <div class="c-card__description">
        <p>Cubilia a nec placerat ullamcorper dolor a condimentum volutpat a posuere per.</p>
      </div>
    ```
  * or can contain a date and time,
    ```html
      <div class="c-card__description">
        <time datetime="2018-09-17 20:00" class="c-card__date">September 17, 2018 8pm</time>
      </div>
    ```
  * or can contain houses and room stats and room types
    * expansion stat 
    * houses stat
    * rooms stat
    * room types stat
      ```html
        <div class="c-card__description">
          <span class="c-card__stats c-card__expansion"><strong>Campus Expansion:</strong> 2020-21</span> 
          <span class="c-card__stats c-card__houses"><strong>Houses:</strong> 11</span> 
          <span class="c-card__stats c-card__rooms"><strong>Rooms:</strong> 142</span> 
          <span class="c-card__stats c-card__roomtypes"><strong>Room Types:</strong> Singles, Doubles &amp; Apartments</span>
        </div>
      ```

## Usage

Example of a single card with a link.

```html
<div class="c-card">
  <div class="c-card__img">
    <a href="#">
      <svg class="c-icon">
        <use xlink:href="/themes/custom/uccollege/dist/icons.svg#phoenix" />
      </svg>
      <picture>
        <img src="https://picsum.photos/470" alt="">
      </picture>
    </a>
  </div>
  <h2 class="c-card__heading"><a href="#">Burton Judson Courts</a></h2>
  <div class="c-card__description">
    <span class="c-card__stats c-card__houses"><strong>Houses:</strong> 6</span> 
    <span class="c-card__stats c-card__rooms"><strong>Rooms:</strong> 78</span> 
    <span class="c-card__stats c-card__roomtypes"><strong>Room Types:</strong> Singles &amp; Doubles</span>
  </div>
</div>
```

Example of a single card with a date and time.

```html
<div class="c-card">
  <div class="c-card__img">
    <a href="#">
      <svg class="c-icon">
        <use xlink:href="/themes/custom/uccollege/dist/icons.svg#phoenix"></use>
      </svg>
      <picture>
        <img src="https://picsum.photos/480?image=1077" alt="">
      </picture>
    </a>
  </div>
  <h2 class="c-card__heading"><a href="#">Lorem Ipsum</a></h2>
  <div class="c-card__description"><time datetime="2018-09-17 20:00" class="c-card__date">September 17, 2018 8pm</time></div>
</div>
```

For a double card, add the class `c-card--double`.

```html
<div class="c-card c-card--double">
  <div class="c-card__img">
    <svg class="c-icon">
      <use xlink:href="/themes/custom/uccollege/dist/icons.svg#phoenix" />
    </svg>
    <picture>
      <source media="(min-width: 1024px)" srcset="https://picsum.photos/800/400"> 
      <img src="https://picsum.photos/480" alt="">
    </picture>
  </div>
  <h2 class="c-card__heading"><strong>Coming soon:</strong> Woodlawn</h2>
  <div class="c-card__description">
    <p>Cubilia a nec placerat ullamcorper dolor a condimentum volutpat a posuere per.</p>
  </div>
</div>
```

For a grid of cards, wrap the cards in a container with class `c-cards`.

```html
<div class="c-cards">
  <h2 class="c-cards__heading">Home is where your house is</h2>
  <p class="c-cards__description">Do you prefer traditional or modern architecture? Are you into theater, or are you planning to do a lot of exercise? All of these factors can weigh into your decision as to what house is the bestfit for you. Explore below.</p>
  <div class="c-cards__content">
    <div class="c-card">
      ...
    </div>
    <div class="c-card">
      ...
    </div>
    <div class="c-card">
      ...
    </div>
  </div>
</div>
```