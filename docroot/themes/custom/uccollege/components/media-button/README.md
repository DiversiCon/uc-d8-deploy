# Media Button component

The Media Button is a flexible wrapper for displaying icons for videos and podcasts. It's anchored to the bottom left of a container and scales proportionately, keeping a square aspect ratio.

For videos, a play icon is displayed, while podcasts display the podcast icon. The video button will also display the video's duration in the lower left corner. Currently, podcasts will *not* display their duration in this component. 

The component can potentially be constructed with three different tags: `<a>, <div>, <button>`. Buttons should be used on actual video players. Divs would be used in places like the homepage tile grids, where the button isn't actually clickable on its own.

## Component props

- **duration** (optional)
- **tag** (optional, default is "div")
  - Specifies the html element to use
  - Using the "a" tag also requires use of the "url" prop
- **url** (optional) 
- **buttontype** (optional, default is "video")
  - This determines what icon to display

## Usage
By default, this will output a div with the "play" icon, no link and no duration.

*Note that within the `<uc-video>` component, the media button component will automatically be assigned a duration, provided by the Youtube API.*

```html
<uc-media-button></uc-media-button>
```

## Examples
Video button with duration.

```html
<uc-media-button duration="1:00" tag="button"></uc-media-button>
```

Podcast link.

```html
<uc-media-button tag="a" url="/podcasts" buttontype="podcast"></uc-media-button>
```
Also note that when the podcast button displays, it appends the UChicago Podcasts logo to its immediate parent, so that it will display in the lower right corner of that container.
