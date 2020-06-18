# Events component

The Events component fetches event data from an external API, formats it and displays as a list. Each page of results represents grouped days of events within a specified span of 7 days. 

Note that these days are not necessarily contiguous. For example, if the range of 7 days queried only returns two day's worth of events, only those two days are displayed for that page of results. 

## Process

### Retrieval
The initial ajax call queries the API via a string of filters within the url query string. The query requests all events that fall between today's date and 7 days from now. 

### Normalization
The data is then run through the `normalize()` method, which takes the raw data object and flattens it to a more manageable object. For example, the original node of `attributes.field_event_dates.value` is converted to simply `start`.

### Splitting and Grouping
This new object is then processed to split multi-day events into separate nodes. For example, if an event's start date is 6/1 and the end date is 6/3, the result will be three unique entries for that event for 6/1, 6/2 and 6/3. Then this new expanded object is grouped into unique days of the week. It's this new object that's used to display the results list on the page.

## Search
This same component also handles the display of search results. When a search query is entered in the field, a new url string filter is created and results fetched from the API. The only limit is that the events have to be in the future. 

As opposed to the list results, search results are not grouped by day. Ten results are displayed at a time and only unique events are shown, along with their date ranges. So the example above would be a single event with a date range of 6/1 - 6/3. 

Also, when a search query is entered, the query string in the current URL is updated. This allows for linking directly to the events page with search results pre-populated. 

## Props
| Prop     | Required | Default | Accepts | Notes                                                                                            |
|----------|----------|---------|---------|--------------------------------------------------------------------------------------------------|
| endpoint | Yes      |         | String  | URL to the API endpoint                                                                          |
| featured | No       | false   | Boolean | Whether this list is included on a "featured events" page. If true, the background becomes gray |

## Usage

```html
<uc-events
		endpoint="http://bsd-data.dev.uchicago.edu/api/node/event"
		:featured="true"
</uc-events>
```

## Featured Events Component
The Featured Events component is very similar to the main Events component, but used to only display events flagged as "Featured" in the CMS. It also displays thumbnail images for each event.

### Layouts
The featured events component supports two layouts: 4-up cards that convert to sliders on mobile, and a 3-up full-page grid.

### Retrieval
The initial ajax query requests all events that are marked as "featured" that occur in the future. If the component is part of the 3-up grid, there is no limit to how many are fetched and displayed. If not, results are limited to four. These will be part of the 4-up, slider-enabled component. 

### Normalization
The data is then run through the `normalize()` method, which takes the raw data object and flattens it to a more manageable object. For example, the original node of `attributes.field_event_dates.value` is converted to simply `start`.

## Images
One difference of the featured events component is that it also retrieves image data. The data that's returned from the API will include this data, but it's contained in a separate node. So the `formatImages` method is run to create a new `images` object whose keys align with event ID's within the `results` object.

## Props
| Prop     | Required | Default | Accepts | Notes                                                                       |
|----------|----------|---------|---------|-----------------------------------------------------------------------------|
| endpoint | Yes      |         | String  | URL to the API endpoint                                                     |
| grid     | No       | false   | Boolean | Use True for full-page grid displays, false for 4-up slider-enabled display |

## Usage
```html
<uc-events-featured
		endpoint="http://bsd-data.dev.uchicago.edu/api/node/event"
		:grid="true"
</uc-events-featured>
```

## References
* JSON:API: https://jsonapi.org/
* Drupal 8 JSON:API Docs: https://www.drupal.org/docs/8/modules/jsonapi/jsonapi
