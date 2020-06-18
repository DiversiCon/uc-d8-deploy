# Alphalist Component

The Alphalist component takes JSON data from an endpoint and lists it on the page, grouped by a specified key, along with an alphabetical index for easy navigation.

Each group has an ID attached to it for the first letter of the grouping field. When a letter is clicked in the alphabet list, the page jumps down. 

## Search
A search field is also included, which searches the first name, last name, and interests fields. When a search is made, the letter selection and dropdown values are reset to defaults. The search query is also appended to the URL as a query string, for linking purposes.

## Dropdown Filter
A dropdown filter is also included, which allows for filtering by department. When a selection is made, the results are filtered. The search is reset as well, and the URL query string updated with department data, instead of search.

Note that when both dropdown or search selections are made, the alphabet list is updated to only reflect letters that correspond to visible results. 

## Default Department
If a page should display only a single department by default, specify it in this prop. If the dropdown is not hidden via the `hide-dropdown` prop, the dropdown will now be populated by sections to filter the results by.

## Sticky
When scrolling down, the search, dropdown and alphabet list pin themselves directly underneath the sticky nav. This is to ensure users can still easily change their options without scrolling all the way back up. 

## Data Caching
Due to the potential size of JSON payloads, all data is fetched at once and inserted into the browser's localStorage, along with a timestamp. Data will continue to be fetched from localStorage for 24 hours, after which a new call will be made for fresh data.

## Props
| Prop               | Required | Default    | Accepts | Notes                                                                                                                                     |
|--------------------|----------|------------|---------|-------------------------------------------------------------------------------------------------------------------------------------------|
| endpoint           | yes      |            | String  | JSON formatted content from a URL or local endpoint.                                                                                      |
| group-by           |          | 'lastname' | String  | data key used to group content by letter                                                                                                  |
| default-department |          | 'All'      | String  | Results will be filtered for only this department. If specified, and the dropdown is not hidden, the dropdown will now filter by section. |
| hide-dropdown      |          | false      | Boolean | If true, the dropdown will be hidden and search field centered.                                                                           |

## Usage
```html
<uc-alphalist 
  endpoint="/it/showcase/endpoint/alphalist-endpoint-faculty"
  group-by="lastname"
  default-department="Medicine"
  :hide-dropdown="true" />
```
