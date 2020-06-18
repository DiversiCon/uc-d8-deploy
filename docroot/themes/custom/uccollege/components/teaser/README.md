# teaser component

Teasers are small objects with an image thumbnail and link, with optional topic links and headlines. They should usually be wrapped in the `.l-stripe` container.

Each thumbnail can also have a nested media-icon component for podcast or video icons.

## JSON Feed
Teasers can also be generated from JSON data, using the `uc-teaser` Vue component (see example below). Media icons will be displayed if a "type" value of either "video" or "podcast" is passed. 

## Usage

```html
<div class="c-teaser l-stripe ">
  <h2>UChicago News</h2>
  <h3>The Latest News from UChicago</h3>
  <ul>
    <!-- repeat list items here -->
    <li>
      <div class="c-teaser__img">
        <a href="#">
          <img src="/themes/custom/uccollege/it_showcase/images/teaser1.jpg" alt="UChicago launches test-optional admissions process">
        </a>
      </div>
      <h4 class="c-teaser__topic">
        <a href="#">Stories</a>
      </h4>
      <a href="#" class="c-teaser__link">UChicago launches test-optional admissions process</a>
    </li>
  </ul>
</div>
```
### Example with media icon and subtitle

```html
<div class="c-teaser l-stripe ">
  <h2>UChicago News</h2>
  <h3>The Latest News from UChicago</h3>
  <ul>
    <!-- repeat list items here -->
    <li>
      <div class="c-teaser__img">
        <a href="#">
          <div class="c-media-icon">
            <svg class="c-icon c-icon--play">
              <use xlink:href="/themes/custom/uccollege/dist/icons.svg#play"></use>
            </svg>
          </div>
          <img src="/themes/custom/uccollege/it_showcase/images/teaser1.jpg" alt="UChicago launches test-optional admissions process">
        </a>
      </div>
      <h4 class="c-teaser__topic">
        <a href="#">Stories</a>
      </h4>
      <a href="#" class="c-teaser__link">UChicago launches test-optional admissions process</a>
      <div class="c-teaser__subtitle">Angela Shen, ’16</div>
    </li>
  </ul>
</div>
```

## Vue Component

| Prop         | Default | Accepts | Notes                                     |
|--------------|---------|---------|-------------------------------------------|
| heading      |         | String  | Heading text                              |
| topic        |         | String  | Text for topic heading                    |
| topic-url    |         | String  | Topic link url                            |
| mobileslider | false   | Boolean | Should teasers become a slider on mobile? |
| datasource   |         | String  | Endpoint URL for teaser data              |

### Example output from Vue component with JSON data

```html
<uc-teaser
  heading="The Latest News from UChicago"
  topic="UChicago News"
  topic-url="#"
  :mobileslider="true"
  datasource="JSON-URL-HERE">
</uc-teaser>
```
