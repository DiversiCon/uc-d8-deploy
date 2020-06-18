# UC Video Embed Component

This component handles Youtube video embeds with custom overlaid controls. If a poster image url is specified, it loads that above the embedded video, along with a custom play control button, video duration, and sharing buttons. 

When play is clicked, the overlay disappears and the video starts playing. 

## Sharing
When included in the hero component, videos will display a share icon in the lower right corner. On click, the user is taken to the video on Youtube, via a new browser window.

| Prop        | Default | Accepts | Required | Notes                                                                              |
|-------------|:-------:|:-------:|:--------:|------------------------------------------------------------------------------------|
| poster      |         |  String |          | Placeholder image for the video. If blank, the poster from Youtube will be fetched |
| videoid     |         |  String |    Yes   | Youtube ID for the video                                                           |
| heading     |         |  String |          | Bold text above the video                                                          |
| description |         |  String |          | Caption under the video                                                            |
| credit      |         |  String |          | Photo credit. Will be included in parentheses after the description.               |

## Usage

```html
<uc-video
  poster="https://img.youtube.com/vi/X-pnF6j2b5w/mqdefault.jpg"
  videoid="XxQX_dkiryQ"
  heading="This is the heading above the video"
  description="Description for caption"
  credit="credit for caption">
</uc-video>
```
