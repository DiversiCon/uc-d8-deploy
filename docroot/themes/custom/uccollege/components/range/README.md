# Range component

This component displays one or more sets of values, separated by dashed lines. Each set of values has a descriptive title above it, and the component can contain an optional headline.

Note the `span` tags before each left and right value. This is "prefix" text and is visually hidden. This is added so that screen readers will speak a more fluid sentence, rather than two numbers. In the example below, a screen reader will say: "MCAT Range from 504 to 528".

The default prefixes are "to" and "from", but anything could be used, like "between" and "and".

## Usage

```html
<div class="c-range">
    <h2 class="t-heading--medium">Academics</h2>
	<p>MCAT Range</p>
	<div class="c-range__wrap">
		<div class="c-range__left"><span>from </span>504
		</div>
		<div aria-hidden="true" class="c-range__center">
			<svg class="c-icon">
				<use xlink:href="/themes/custom/uccollege/dist/icons.svg#range"></use>
			</svg>
		</div>
		<div class="c-range__right"><span>to </span>528
		</div>
	</div>
</div>
```
