# callout component

Callouts are lists with circular images followed by a headline and optional subhead and rich text. They align horizontally and can be made part of a slider component. There can be any number of items. If there are more than 4, however, be sure to wrap it in `uc-slider` and include a `breakpoint` prop of 0, so that the slider component is enabled on desktop, with visible arrow navigation.

Wrapping links are optional, and you can specify non-circular images by using the `c-callout--rect` class. 

## Usage

```html
<div class="c-callout">
  <ul>
    <li>
      <a href="#">
        <img src="/themes/custom/uccollege/it_showcase/images/callout1.jpg" alt="Doc Films"> 
        <h4>Doc Films</h4>
      </a>
    </li>
  </ul>
</div>
```

### Convert to slider with subhead, no link

```html
<uc-slider class="c-callout" v-cloak>
  <ul>
    <li>
      <img src="/themes/custom/uccollege/it_showcase/images/callout1.jpg" alt="Doc Films"> 
      <h4>Doc Films: 
        <span>I'm a subhead</span>
      </h4>
    </li>
  </ul>
</uc-slider>
```

## Slider with rich text, rectangular images and custom breakpoint

```html
<uc-slider class="c-callout c-callout--rect" :breakpoint="1024" v-cloak>
  <ul>
    <li>
      <a href="#">
        <img src="/themes/custom/uccollege/it_showcase/images/callout1.jpg" alt="Doc Films"> 
        <h4>Doc Films: 
          <span>I'm a subhead</span>
        </h4>
        <div class="c-callout__text">
          text here
        </div>
      </a>
    </li>
  </ul>
</uc-slider>
```

If the quote or rich-text variant are used, the 1024 breakpoint is needed
