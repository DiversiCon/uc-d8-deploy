# Group selector component

The Group selector component retrieves a JSON data object and displays the output as a series of lists with photos. It wraps a single `<uc-dropdown>` component, which serves as a filter to refine display results. 

## Filter Linking
Both the selector and dropdown components support "deep linking" to specific results. This is done via query parameters in the URL. So, to link to a page listing minors in Social Sciences, build a URL like: `?group=Office of the Dean of the College`.

Also, as selections are made on dropdowns, the query params on the current page are updated dynamically. This URL can be copied and pasted as a deep link elsewhere. 

| Prop           | Required | Default       | Accepts | Notes                                                                                             |
|----------------|----------|---------------|---------|---------------------------------------------------------------------------------------------------|
| datasource     |          | programs.json | Array   | JSON formatted content from a file or local endpoint.                                             |
| filterkey      | yes      |               | String  | When a dropdown selection is made, which key in the data object should be used to return results? |
| group-sort     |          | empty string  | String  | Direction to sort groups of results. Accepts either 'asc' or 'desc'.                              |
| label          |          | "Filter by"   | String  | Text label to the left of the dropdown.                                                           |
| default-option |          | All           | String  | Default option to pre-populate dropdown                                                           |
| hide-dropdown  |          | false         | Boolean | Hides the dropdown                                                                                |

Note that this component only sorts the actual "groups" of data, not the lists of data per group. The component will assume the data is in the proper order, as retrieved from the cms. If no grouping order is specified (via `group-sort`), results will be grouped in the order they appear in the dropdown.

## Usage

```html
<uc-selector-group 
  datasource="something.json"
  filterkey="year"
  group-sort="desc"
  default-option="2016"
  :hide-dropdown="true"
  label="Select a year">
</uc-selector-group>
```
