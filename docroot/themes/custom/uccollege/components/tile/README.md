# tile component

This component is for each individual tile within the homepage 4-up grid (currently the only place on the UCC site where they appear).


## Variations
The default tile has a white background, but there is a single "inverted" variant with a burgundy background and white text: `c-tile--inverted`. Tiles with a media icon, like the event with a calendar, have a fallback phoenix image.


## Usage

```html
<div class="c-tile">
  <div class="c-tile__img">
    <a href="#">
      <img src="photo.jpg" alt="alt here">
    </a>
  </div>
  <div class="c-tile__content">
    <h2>
      <a href="#">Article Title Here</a>
    </h2>
    <p>Text Here</p>
  </div>
</div>
```

### Inverted variation

```html
<div class="c-tile c-tile--inverted">
  <div class="c-tile__img">
    <a href="#">
      <img src="photo.jpg" alt="alt here">
    </a>
  </div>
  <div class="c-tile__content">
    <h2>
      <a href="#">Article Title Here</a>
    </h2>
    <p>Text Here</p>
  </div>
</div>
```

### Variation with Media Icon

```html
<div class="c-tile c-tile--inverted">
  <div class="c-tile__img">
    <a href="#">
      <div class="c-tile__img-imagewrap">
        <svg class="c-icon c-icon--phoenix"><use xlink:href="/themes/custom/uccollege/dist/icons.svg#phoenix"></use></svg>
        <div class="c-media-icon">
          <svg class="c-icon c-icon--calendar "><use xlink:href="/themes/custom/uccollege/dist/icons.svg#calendar"></use></svg>
        </div>
      </div>
    </a>
  </div>
  <div class="c-tile__content">
    <h2 class="c-tile__title"><a href="#">Human Genetics Seminar<br>Priya Moorjani</a></h2>
    <p><strong>Wed. Feb. 6, 2019</strong><br>12:00 – 1:00 p.m.<br>Knapp Center for Biomedical Discovery, Room 1103</p>
  </div>
</div>
```