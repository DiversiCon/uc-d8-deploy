# Tint Embed component

This component displays a tiled set of social media cards from the [Tint service](https://www.tintup.com). The client sets up each Tint feed, and uses those ID's to output the tiles on the homepage. 

The number of rows and columns is configurable via props. The default is to show 4 columns, with 3 total cards on mobile and 8 on desktop. If a number of rows is specified, the number of needed cards is calculated automatically.

An optional query string can be passed as well to filter the results by keyword.

## Props
| Prop               | Required | Default | Accepts | Notes                            |
|--------------------|----------|---------|---------|----------------------------------|
| personalization-id | yes      |         | Number  | Personalization ID from Tint     |
| tint-id            | yes      |         | String  | Tint ID                          |
| query              |          |         | String  | Search keyword to filter results |
| columns            |          | 4       | Number  | Number of columns                |
| rows               |          |         | Number  | Number of rows                   |

## Usage

```html
<uc-tint-embed
  :personalization-id="926525"
  tint-id="college-test"
  query="thaler"
  :columns="4"
  :rows="3">
</uc-tint-embed>
```

## Component Details

Because the Tint embed script fires as soon as it's loaded on the page, it's loaded into the `<head>` **only** after the `uc-tint-embed` component is ready. 

Once the component has made any necessary calculations, it sets a data variable of `this.ready = true` which inserts the Tint div into the DOM. 

It then emits an event called `embed-ready` which a global Vue mixin (in main.js) is listening for. The external Tint JavaScript file is then loaded and executes on the div container.
