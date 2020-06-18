# switcher component

There are a few blocks on the site that need to display different content to "affiliates" or "prospects". This is handled by the Switcher component. The `<uc-switcher>` tags wrap two named slots that contain both sets of content. 

The component then tries to find a cookie that could indicate they are a affiliate and display one slot or another in the DOM (currently set to: `SignOnDefault`).

For development, you can force display of "affiliate" content by adding the query param of `?affiliate=true` to the URL. 

## Usage

```html
<uc-switcher>
  <template slot="affiliate">
    <div>affiliate content goes here</div>
  </template>

  <template slot="prospect">
    <div>Prospect (default) content goes here</div>
  </template>
</uc-switcher>
```
