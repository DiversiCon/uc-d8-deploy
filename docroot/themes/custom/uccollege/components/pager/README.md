# Pager component

The Pager handles pagination of results output by the List component. 

The actual logic for displaying the paging links is handled by the [vuejs-paginate](https://www.npmjs.com/package/vuejs-paginate) plugin.

## Usage
**HTML Structure**

```html
<nav role="navigation" aria-labelledby="pagination-heading" class="pager">
  <ul class="pager__items js-pager__items">
    <li class="pager__item is-active pager__item--link"><a href="?page=0" title="Current page">1</a></li>
    <li class="pager__item pager__item--link"><a href="?page=1" title="Go to page 2">2</a></li>
    <li class="pager__item pager__item--link"><a href="?page=2" title="Go to page 3">3</a></li>
    <li class="pager__item pager__item--link"><a href="?page=3" title="Go to page 4">4</a></li>
    <li class="pager__item pager__item--link"><a href="?page=4" title="Go to page 5">5</a></li>
    <li class="pager__item pager__item--link"><a href="?page=5" title="Go to page 6">6</a></li>
    <li class="pager__item pager__item--link"><a href="?page=6" title="Go to page 7">7</a></li>
    <li class="pager__item pager__item--link"><a href="?page=7" title="Go to page 8">8</a></li>
    <li class="pager__item pager__item--link"><a href="?page=8" title="Go to page 9">9</a></li>
    <li role="presentation" class="pager__item pager__item--ellipsis">—</li>
    <li class="pager__item pager__item--last pager__item--link"><a href="?page=11" title="Go to last page"><span aria-hidden="true">12</span></a></li>
    <li class="pager__item pager__item--next pager__item--link"><a href="?page=1" title="Go to next page" rel="next"><span aria-hidden="true">Next ›</span></a></li>
  </ul>
</nav>
```

**Vue Component**
```html
<uc-pager
  :on-page="CALLBACK_FUNCTION"
  :page="1"
  :total="32">
</uc-pager>
```

## Props
| Prop    | Required | Default | Accepts  | Notes                                         |
|---------|----------|---------|----------|-----------------------------------------------|
| on-page | yes      |         | Function | Callback function to run links are clicked    |
| page    |          | 1       | Number   | Currently active page                         |
| total   | yes      |         | Number   | Total number of pages based on items-per-page |
