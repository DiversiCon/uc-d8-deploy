# Search form component
The search form component is used within the global navigation on both mobile and desktop, as well as at the top of search pages. 

## Form Behavior
The search form, when submitted, will go to `/search` and pass along a URL param like: `?query=SEARCHTEXT`.

## Themes
The search form can be displayed with black text and icons, for use on pages, or as an "inverted" variation when used on maroon backgrounds. 

## Props
| Prop        | Required | Default | Accepts | Notes                                                                                                   |
|-------------|----------|---------|---------|---------------------------------------------------------------------------------------------------------|
| theme       |          |         |  String | Theme modifier. Will be appended to create a class like: c-searchform--THEME. Current options: inverted |
| placeholder |          | Search  |  String | Placeholder text to display in form input                                                               |

## Usage

```html
<uc-searchform placeholder="Search" theme="inverted"></uc-searchform>
```
