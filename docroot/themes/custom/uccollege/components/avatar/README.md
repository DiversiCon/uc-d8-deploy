# Avatar component

The avatar component displays a circular image on the left, with a title, description and optional link on the right. If the image provided is not a square, the image will be cropped from the bottom.

## Usage

```html
<figure class="c-avatar">
  <div class="c-avatar__image">
    <div class="c-avatar__image-wrap">
      <img src="/themes/custom/uccollege/it_showcase/images/headshot-01.jpg" alt="Alt text here"> 
    </div>
  </div>
  <figcaption>
    <p class="c-avatar__title">Keme Carter, MD</p> 
    <p class="c-avatar__text">Associate Professor of Medicine<br> Associate Dean for Admissions</p> 
    <div class="c-avatar__link"> —
      <a href="#" target="_blank">
        A message from our dean
      </a>
    </div>
  </figcaption>
</figure>
```
