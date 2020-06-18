# quotator component

The Quotator component chooses a quotation at random to display on the homepage.

It gets quote data from an array of objects passed into the `quotes` prop. The link that is displayed with the quote is also configurable via props.

Note that the quotation marks around the quote and the dash before the attribution are *automatically* added by the component.

## Props

| Prop     | Default | Accepts | Notes                                                                          |
|----------|---------|---------|--------------------------------------------------------------------------------|
| quotes   | n/a     | Array   | Each object in the array must contain key/values for: text, attribution, image |
| linktext | n/a     | String  | Text for the link to display below the quote                                   |
| linkurl  | n/a     | String  | Url to use for the link below the quote                                        |

## Usage

```html
<uc-quotator 
  :quotes="array-of-objects-here" 
  linktext="About the College" 
  linkurl="#">
</uc-quotator>
```
