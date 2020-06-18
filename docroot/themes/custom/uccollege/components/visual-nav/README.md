# visual-nav component

The visual nav consists of four photos in a CSS Grid, with text labels and links to other pages. 

Below 768px, each grid becomes a square. On desktop, each image except the one in `c-visual-nav__cell2` should be square by default. For the cell2 image, it's rectangular on desktop (590 x 290) and on mobile it's a square. 

## Usage

```html
<div class="c-visual-nav">
  <h2 class="t-heading--medium">{{ content.heading }}</h2>
  <ul>
    <!-- loop for four cells -->
    <li class="c-visual-nav__cell2">
      <a href="#">
        <div class="c-visual-nav__cta">Why Uchicago?</div>
        <picture>
          <source media="(max-width: 767px)" srcset="/themes/custom/uccollege/it_showcase/images/visual-nav2-square.jpg">
          <img src="/themes/custom/uccollege/it_showcase/images/visual-nav2.jpg" alt="Why Uchicago?">
        </picture>
      </a>
    </li>
    <!-- end loop -->
  </ul>
</div>
```
