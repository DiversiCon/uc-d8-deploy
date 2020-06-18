# Stats component

This component displays numeric stats. The number of stats is not limited, but if there are more than three, the items will wrap to a new row on smaller devices or if the items don't all fit on one row. See stats twig file for logic.

* heading, optional
* description, optional
* For each stat,
  * number, required
  * description, required
  

## Usage

With heading, description, and more than 3 items.

```html
<div class="c-stats">
  <h2 class="c-stats__heading t-heading--medium">Why UChicago?</h2>
  <p class="c-stats__description">Undergraduates thrive in an environment that encourages critical inquiry and independent thought.</p>
  <ul class="c-stats__list">
    <li class="c-stats__item c-stats__item--half">
      <strong class="c-stats__number">45</strong>
      <p class="c-stats__text"> percent of students study abroad </p>
    </li>
    <li class="c-stats__item c-stats__item--half">
      <strong class="c-stats__number">8</strong>
      <p class="c-stats__text"> percent of students are accepted </p>
    </li>
    <li class="c-stats__item c-stats__item--half">
      <strong class="c-stats__number">3</strong>
      <p class="c-stats__text"> third highest ranked school by U.S. News </p>
    </li>
    <li class="c-stats__item c-stats__item--half">
      <strong class="c-stats__number">89</strong>
      <p class="c-stats__text"> Nobel <br>laureates </p>
    </li>
  </ul>
</div>
```

With no heading or description, and only 3 items.

```html
<div class="c-stats">
  <ul class="c-stats__list">
    <li class="c-stats__item ">
      <strong class="c-stats__number">45</strong>
      <p class="c-stats__text"> percent of students that study abroad </p>
    </li>
    <li class="c-stats__item ">
      <strong class="c-stats__number">3</strong>
      <p class="c-stats__text"> third highest ranked school by U.S. News </p>
    </li>
    <li class="c-stats__item ">
      <strong class="c-stats__number">89</strong>
      <p class="c-stats__text"> Nobel <br>laureates </p>
    </li>
  </ul>
</div>
```
