# UC Inline Gallery Component

This component handles embedding of inline photo galleries within articles.

There is one `uc-gallery` component with multiple `uc-gallery-photo` components. 

The gallery handles sliding with the [Swiper](http://idangero.us/swiper/api/) library.

Note that the gallery component can also be used to display single, full-width photos with captions. Just give it a single `uc-photo` component. It'll disable swiping, hide next/prev arrows and hide the photo count above the image.

## Sharing
When included in the hero component, galleries will display a share icon in the lower right corner. On hover, an AddToAny tooltip appears, allowing sharing to various social platforms. On click, the sharing options appear in a modal. The URL to share is specified via the "share" prop, set on the component.

## Usage

```html
<uc-gallery title="Photo Gallery" share="http://url-to-share.com">
  <uc-gallery-photo
    img-full="/themes/custom/uccollege/it_showcase/images/gallery1.jpg"
    img-large="/themes/custom/uccollege/it_showcase/images/gallery1-1024.jpg"
    img-medium="/themes/custom/uccollege/it_showcase/images/gallery1-768.jpg"
    img-small="/themes/custom/uccollege/it_showcase/images/gallery1-480.jpg"
    description="Description goes here"
    credit="Photo credit goes here">
  </uc-gallery-photo>
</uc-gallery>
```
