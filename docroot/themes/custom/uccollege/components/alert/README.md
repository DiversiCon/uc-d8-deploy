# alert component

The global alert component is used to show important messages sitewide, and appears under the global navigation.

When the X "close" button is clicked, a timestamp equal to one hour from the current time is saved to localstorage with the key of `ucc-alert-sleep`. A visitor who closes the alert will not have it displayed to them again for the next hour. 

The alert will be shown on all pages that include its code, unless it's within it's "sleep" period.

Note: For testing purposes, you can remove this localstorage entry by going to the Application tab in Chrome Tools, and selecting the UC domain from the localstorage list and deleting the `ucc-alert-sleep` entry.

## Props
| Prop        | Required | Default | Accepts | Notes                                |
|-------------|----------|---------|---------|--------------------------------------|
| heading     | yes      |         | String  | Red heading ("Take Note" or "Alert") |
| description | yes      |         | String  | Text of message                      |
| url         |          |         | String  | URL link                             |
| url-text    |          |         | String  | Text of the link ("More")            |
| url-target  |          | _self   | String  | Use _blank to open in new window     |

## Usage

```html
<uc-alert
  heading="{{ content.heading }}"
  description="{{ content.description }}"
  url="{{ content.url }}"
  url-text="{{ content.urlText }}"
  url-target="{{ content.urlTarget }}">
</uc-alert>
```
