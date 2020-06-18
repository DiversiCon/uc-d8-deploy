# Filter component

The filter component is used in conjunction with the List component to filter data by category. 

The filter's props are passed to it by the List component. As selections are made, it triggers the callback function within the List component, which then handles AJAX calls to the API endpoint. 

The "All" option in the filter will select all categories. When All is selected, it cannot be de-selected, except by de-selecting at least one other choice. When all categories are chosen, the "All" checkbox will also get checked.

On load, the component's options can be pre-populated via two methods:
1. Pass a comma-delimited list of category ID's as a prop: `pre-selected: "839,840"`. This would be passed via the parent List component.
1. Pass a comma-delimited list as a query parameter in the URL: `?section=839,840`

If both methods are used, whatever is in the URL query string will be used. 

| Prop         | Required | Default | Accepts  | Notes                                             |
|--------------|----------|---------|----------|---------------------------------------------------|
| categories   | yes      |         | Array    | Array of category names/Id's to populate list     |
| on-select    | yes      |         | Function | Callback function to run when selections are made |
| title        |          |         | String   | Text above filter options                         |
| pre-selected |          |         | String   | comma-delimited list of category ID's             |

## Usage
Note that this component will always be used in conjunction with the list component, which will handle population of its props.

```html
<uc-filter
  :onSelect="CALLBACK_FUNCTION"
  :categories="ARRAY"
  preSelected="STRING"
  title="STRING">
</uc-filter>
```
