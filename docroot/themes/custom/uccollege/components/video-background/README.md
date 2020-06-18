# Video Background component

This component renders a looping background video with an optional heading. 

This uses the HTML `<video>` tag, and will auto play in just about every modern browser, desktop and mobile. In some older versions of iOS, auto play will be disabled, but if a poster image is specified, that will be displayed.

The component only supports a single `src` attribute, as everything plays mp4 video formats equally well. 

## Controls
For accessibility, clicking anywhere on the video will pause it. Clicking again will resume playback. Icons in the lower right will toggle between play and pause as well. 

## Props
| Prop    | Required | Default | Accepts | Notes                                                                                                                                                 |
|---------|----------|---------|---------|-------------------------------------------------------------------------------------------------------------------------------------------------------|
| src     | Yes      |         | String  | Path to the local video file                                                                                                                          |
| poster  |          |         | String  | Poster image that displays prior to video being ready to play. Also displayed on mobile devices that don't support autoplaying video (older iOS, etc) |
| heading |          |         | String  | Text for heading. HTML is allowed.                                                                                                                    |
| tag     |          | h1      | String  | Tag to use for the heading                                                                                                                            |
| width   |          | 100%    | String  | Width of video on page                                                                                                                                |
| height  |          | 100%    | String  | Height of video on page                                                                                                                               |

Note that in most cases, it's fine to omit width and height, unless the video will be inside a container that's much larger than the video should be.

## Usage

```html
<uc-video-background
  heading="Transformative <br>Education"
  tag="h1"
  poster="/themes/custom/uccollege/it_showcase/images/homepage-fpo.jpg"
  src="/themes/custom/uccollege/it_showcase/video/homepage-fpo.mp4">
</uc-video-background>

```
