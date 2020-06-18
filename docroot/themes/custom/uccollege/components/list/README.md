# List component

The List component displays a list of content that can be links to other pages. A sidebar can be used within.

Contains,
  * Heading, optional (uses similar html as the Headline component)
  * Description, optional
  * Sidebar, optional
  * List items, required.

For each List Item,
  * url, optional 
  * image, optional, fallback included
  * title, required
  * description, optional
  * date, optional
  * source, optional (byline)
  

## Variations

 There are no color variations for this component.

## Usage

Example of List with Sidebar and Headline. If no Sidebar, the `c-list__sidebar` div should not be present. If no heading, the `c-list__headline` header should not be present. 

```html
<div class="c-list">
  <!--headline-->
  <header class="c-list__headline c-headline">
    <h1 class="c-headline__heading">Heading</h1> 
    <div class="c-headline__description">
      <p>This is a description. Ac diam ante a eu parturient luctus dis a suspendisse himenaeos sagittis ut molestie a scelerisque.</p>
    </div>
  </header>
  <div class="c-list__content-wrap">
    <section class="c-list__content">
      <!--list items go here-->
      <div class="c-list__item">
        <!--list item html goes here. see below.-->
      </div>
      <div class="c-list__item">
        ...
      </div>
      <div class="c-list__item">
        ...
      </div>
    </section>
    <!--sidebar-->
    <div class="c-list__sidebar">
      <!--sidebar components go here-->
    </div><!--/sidebar-->
  </div><!--/content-wrap-->
</div><!--/c-list-->
```

Example of List item html.

```html
 <div class="c-list__item">
  <!--image-->
  <div class="c-list__item-image">
    <a href="#" class="c-list__item-imagelink">
      <div class="c-list__item-imagewrap">
        <svg class="c-icon">
          <use xlink:href="/themes/custom/uccollege/dist/icons.svg#phoenix"></use>
        </svg> 
        <img src="https://picsum.photos/375" alt="">
      </div><!--/item-imagewrap-->
    </a><!--/item-imagelink-->
  </div><!--/item-image-->
  <!--text-->
  <div class="c-list__item-text">
    <h3 class="c-list__item-title"><a href="#" class="c-list__item-titlelink">This is a Title</a></h3>
    <div class="c-list__item-description">
      <p>This is a description. Ac diam ante a eu parturient luctus dis a suspendisse himenaeos sagittis ut molestie a scelerisque.</p>
    </div><!--/item-description-->
    <p class="c-list__item-meta">
      <span class="c-list__item-source">By <a href="#">Danika Kmetz</a></span> 
      <span class="c-list__separator"> | </span> 
      <time datetime="2018-09-04" class="c-list__item-date">September 4, 2018</time>
    </p><!--/item-meta-->
  </div><!--/item-text-->
</div><!--/c-list__item-->
```

## Vue Component

All the above is for manually building the HTML structure for a list. For data-driven lists (News, Search), the Vue component (`list.vue`) can be used. 

| Prop           | Required | Default | Accepts | Notes                                                 |
|----------------|----------|---------|---------|-------------------------------------------------------|
| datasource     | yes      |         | String  | JSON formatted content from a file or local endpoint. |
| categorysource | yes      |         | String  | JSON formatted content from a file or local endpoint. |
| items-per-page |          | 9       | Number  | Number of items to show per page                      |
| filtered       |          | true    | Boolean | Display filter in sidebar?                            |
| sidebar        |          | true    | Boolean | False will remove sidebar entirely                    |
| paginate       |          | true    | Boolean | Display pagination?                                   |
| pre-selected   |          | false   | Boolean | Comma-separated list of categories to pre-fill        |
| sorted         |          | false   | Boolean | Display sorter component at top?                      |
| filter-title   |          |         | String  | Text above the filter categories in sidebar           |

The List component acts as a hub for dynamic list content, and optionally wraps the `pager`, `filter` and `sorter` components. 

### Usage
```html
<uc-list
  datasource="/api/v1/news/list/"
  categorysource="/api/v1/news/section"
  :items-per-page="9"
  :filtered="true"
  :sidebar="true"
  :paginate="true"
  pre-selected="839,840"
  filter-title="College news">
</uc-list>
```

### Sidebar Content
Any elements that need to appear in the sidebar of a list (beneath the filter component) should be added as slot data within the `uc-list` component tags:

```html
<uc-list PROPS_HERE>
  slot content here
</uc-list>
```

### Category Overrides

There are two ways to pre-populate the list with one or more categories:
1. Pass a comma-delimited list of category ID's as a prop: `pre-selected: "839,840"`
1. Pass a comma-delimited list as a query parameter in the URL: `?section=839,840`

If both methods are used, whatever is in the URL query string will be used. 

### API Reference

#### News
| API URL                      | Notes                                                |
|------------------------------|------------------------------------------------------|
| /api/v1/news/section         | returns all categories/sections                      |
| /api/v1/news/list/all        | for all items                                        |
| /api/v1/news/list/999        | for items in category/section 999                    |
| /api/v1/news/list/999,123    | for items in categories/sections 999 and 123         |
| /api/v1/news/list/all?page=0 | where 0 is the first page of zero-index page numbers |
