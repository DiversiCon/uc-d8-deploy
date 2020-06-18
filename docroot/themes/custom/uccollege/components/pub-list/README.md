# Pub List component

The Pub List component is a list of publications or other such content. Unlike other lists, this one does NOT include images.

Each list item contains: 
  * url (optional)
  * title (can be a link)
  * authors
  * publication
  * pub_id

## Props
| Prop     | Required | Default                                           | Accepts | Notes                              |
|----------|----------|---------------------------------------------------|---------|------------------------------------|
| endpoint | Yes      | http://bsd-data.dev.uchicago.edu/api/node/faculty | String  | URL to the API endpoint            |
| headline | No       | Publications                                      | String  | Use to override default page title |


## Usage

```html
<uc-pub-list 
  headline="Publications"
  staff-id="81f1dd1b-72f7-44fc-a41e-98cea02151b7">
</uc-pub-list>
```
