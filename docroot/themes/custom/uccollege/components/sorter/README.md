# sorter component

The sorter is the component above search results that allows users to switch between results that are ordered by date or ordered by rank (relevance).

When a new sort order is selected, the component emits an event to the List parent, which changes the sort type and re-fetches results. 

## Props
| Prop    | Required | Default | Accepts | Notes                        |
|---------|----------|---------|---------|------------------------------|
| sort-by |          | date    | String  | Sort type ('rank' or 'date') |
| start   |          |         | Number  | Starting result on the page  |
| end     |          |         | Number  | Ending result on the page    |
| total   |          |         | Number  | Total results found          |

**Note:** Currently, the sorter is loaded by the list component and its props passed automatically. 

## Usage

```html
<uc-sorter
  v-if="ready && isSearch && !noResults"
  :sort-by="sortBy"
  :start="sorterData.start"
  :end="sorterData.end"
  :total="sorterData.total">
</uc-sorter>
```

