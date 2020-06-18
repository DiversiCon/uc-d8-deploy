# slider component

The slider component converts a container of items into a swipeable carousel using swiper.js.

Currently used for callouts, it only needs a valid structure to work. Inside the main `<uc-slider>` container, there needs to be a single root-level container (like a `<ul>`), and a series of children inside (like multiple `<li>` elements).

When mounted, the component will determine if it should initialize the slider, based on a default breakpoint of 768px. That breakpoint can be overridden with a prop value. It will then add necessary css classes for styling the slider to the inner containers. 

Note that if a callout within a slider contains rich text (as designated by the `c-callout__text` class), the breakpoint is automatically reset to 1024px. This is to prevent the text from appearing as very narrow, long columns on tablets.

## Slide Counts
At screen widths of 768px and below, the slider will always display one slide at a time. If the slider has a breakpoint set higher than that, two slides at a time will display.

For example: A callout has a prop passed to `:breakpoint` of 1024. The slider will initialize at all widths up to 1024. It will show one slide at a time up until 768px, then will show two. 

## Auto Slider
If a slider contains more than 4 items, it will always initialize the slider functionality for all breakpoints. 

## Props

| Prop       | Default | Accepts | Notes                                                                                                                                                               |
|------------|---------|---------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| breakpoint | 768     | number  | At this width and below, the slider will initialize. If set to zero, the slider will initialize at all breakpoints, and above 1025px, prev/next arrows will display |

## Usage

```html
<uc-slider class="c-callout" v-cloak>
  <ul>
    <li>
      <a href="#">
        <img src="/themes/custom/uccollege/it_showcase/images/callout1.jpg" alt="Doc Films"> 
        <h4>Doc Films</h4>
      </a>
    </li>
  </ul>
</uc-slider>
```

### With overridden breakpoint
```html
<uc-slider class="c-callout" :breakpoint="1024" v-cloak>
  <ul>
    <li>
      <a href="#">
        <img src="/themes/custom/uccollege/it_showcase/images/callout1.jpg" alt="Doc Films"> 
        <h4>Doc Films</h4>
      </a>
    </li>
  </ul>
</uc-slider>
```

Note: If any element within the callout has a class of `c-callout__text`, the breakpoint will be overridden to 1024px regardless of the prop setting. This is to convert that content to a slider earlier to prevent narrow columns.
