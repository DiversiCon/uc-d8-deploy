# gmap (Google Map) component

The gmap component simply loads an interactive Google map with the provided coordinates. 

This component is also used to render the map for the map-hero component.

NOTE: The map component does not currently add the red marker to the map, as shown in the designs, due to an inconsistency between where the red marker is placed and where Google Maps already shows the hall location. This can be revisited once map authoring is set up on the backend and this can be tested more thoroughly.

| Prop    |                 Default                 | Accepts | Required | Notes                              |
|---------|:---------------------------------------:|:-------:|:--------:|------------------------------------|
| lat     |                                         | string  | Yes      | latitude of map location           |
| lon     |                                         | string  | Yes      | longitude of map location          |
| heading |                                         | string  |          | Optional heading (h3)              |
| zoom    | 18                                      | Number  |          | Zoom level for initial map display |
| apikey  | AIzaSyClm6qw2yjOWH8V-dmAZfcWKHSU9TBwHBU | String  |          |                                    |

## Usage

```html
<uc-gmap
    heading="Map of Hyde Park"
    zoom="15"
    lat="41.7937859"
    lon="-87.5981995">
</uc-gmap>
```
