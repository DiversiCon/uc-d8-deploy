# photo component

This is a generic photo component that takes in responsive images and caption data and outputs `<picture>` elements. 

This component would usually be auto-included within the photo gallery component. 

## Usage

```html
<uc-photo
  img-full="/themes/custom/ucnews/it_showcase/images/gallery1.jpg"
  img-large="/themes/custom/ucnews/it_showcase/images/gallery1-1024.jpg"
  img-medium="/themes/custom/ucnews/it_showcase/images/gallery1-768.jpg"
  img-small="/themes/custom/ucnews/it_showcase/images/gallery1-480.jpg"
  description="Photo description"
  credit="Photo credit or other notes">
</uc-photo>
```
