# Program selector component

The Program selector component retrieves a JSON data object and displays the output as accordions. It wraps two `<uc-dropdown>` components, which serve as filters to refine display results. 

## Filter Linking
Both the selector and dropdown components support "deep linking" to specific results. This is done via query parameters in the URL. So, to link to a page listing minors in Social Sciences, build a URL like: `?division=Social Sciences&type=Minor`.

Also, as selections are made on dropdowns, the query params on the current page are updated dynamically. This URL can be copied and pasted as a deep link elsewhere. 

## Default Filtering
The component can also be pre-filtered via props. You can select default values for both the "division" and "type" dropdowns, plus hide either dropdown or both. See prop options below:

## Props

| Prop             | Default       | Accepts | Notes                                                 |
|------------------|---------------|---------|-------------------------------------------------------|
| datasource       | programs.json | Array   | JSON formatted content from a file or local endpoint. |
| default-division | All           | String  | Division name to pre-populate dropdown                |
| default-type     | All           | String  | Program type to pre-populate dropdown                 |
| hide-division    | false         | Boolean | Hides the division dropdown                           |
| hide-type        | false         | Boolean | Hides the type dropdown                               |

## Usage

```html
<uc-selector-program 
  datasource="something.json"
  default-division="Social Sciences"
  default-type="Minor"
  :hide-division="true"
  :hide-type="true"
></uc-selector-program>
```
