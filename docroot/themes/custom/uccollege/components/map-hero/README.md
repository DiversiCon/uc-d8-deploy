# Map Hero component

The map hero initially displays a large image, with a "view map" button. On click, the photo is swapped with an interactive Google map. The button text changes to an X and when clicked, the photo is restored. 

This component utilizes the `gmap` component for actual display of the map.

Note that as the viewport is resized, js calculates the updated height of the image, and changes the height of the container. This is to ensure the size of the map container matches that of the photo container at all times.

| Prop  | Default | Accepts | Notes                     |
|-------|---------|---------|---------------------------|
| lat   | n/a     | string  | latitude of map location  |
| lon   | n/a     | string  | longitude of map location |
| image | n/a     | string  | path to photo             |

## Usage

```html
<uc-map-hero
  lat="{{ content.lat }}"
  lon="{{ content.lon }}"
  src="{{ content.image }}">
</uc-map-hero>
```
