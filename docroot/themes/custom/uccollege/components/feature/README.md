# Multi-Link Feature component

The Feature component is a 2-column block with a large image on the left and content on the right. The two columns stack on mobile. 

The Feature for housing is a variation that has a 2-column, 2-row grid containing an image with heading. 

The background of the Feature component can be changed by adding classes to the `c-feature` div. The available colors and their classes are:
  * blue (`s-bg-blue`)
  * burgundy/violet (`s-bg-tertiary`)
  * maroon (`s-bg-maroon`)
  * yellow (`s-bg-secondary`)

## Usage

```html
<div class="c-feature">
  <div class="c-feature__image">
    <a href="#">
      <img src="/themes/custom/uccollege/it_showcase/images/feature-academics.jpg" alt="">
    </a>
  </div>
  
  <div class="c-feature__caption">
    Sierra P Espinosa, ‘19 (Barcelona: Public Policy)
  </div>
  
  <div class="c-feature__wrap">
    <div class="c-feature__content">
      <h2 class="t-heading--topic">
        <a href="#">Academics</a>
      </h2>
      <h1 class="t-heading--medium">Majors and Minors</h1>
      <p>content here</p></div>
  </div>
</div>
```

For the 4-photo grid, insert this structure into `c-feature__image`:

```html
<div class="grid-4sq">
  <div class="grid-4sq__cell">
    <div class="grid-4sq__content"><a href="#"><span>Snell-Hitchcock Hall</span> 
      <img src="/themes/custom/uccollege/it_showcase/images/hall1.jpg" alt="Snell-Hitchcock Hall"></a>
    </div>
  </div>
  
  <div class="grid-4sq__cell">
    <div class="grid-4sq__content"><a href="#"><span>Renee Granville-Grossman</span> 
      <img src="/themes/custom/uccollege/it_showcase/images/hall2.jpg" alt="Renee Granville-Grossman"></a>
    </div>
  </div>
  
  <div class="grid-4sq__cell">
    <div class="grid-4sq__content"><a href="#"><span>Campus North</span> 
      <img src="/themes/custom/uccollege/it_showcase/images/hall3.jpg" alt="Campus North"></a>
   </div>
  </div>
  
  <div class="grid-4sq__cell">
    <div class="grid-4sq__content"><a href="#"><span>Max Palevsky</span> 
      <img src="/themes/custom/uccollege/it_showcase/images/hall4.jpg" alt="Max Palevsky"></a>
    </div>
  </div>
</div>
```
For any content in the right column that needs bottom-aligned with the photo (see the feature for Study Abroad), add this as a sibling to `c-feature__content`:

```html
<div class="c-feature__bottom">
  <!--bottom-aligned content here -->
</div>
```

To flip the layout, with the photo on the right, use the `c-feature--flipped` modifier:

```html
<div class="c-feature c-feature--flipped"></div>
```
