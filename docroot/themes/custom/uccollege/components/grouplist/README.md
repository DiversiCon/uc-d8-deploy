# Group List Component

Group List component displays a list of research groups. The data comes from a JSON endpoint.

## Example Endpoint Data Structure
```json
{
  "items": [
    {
      "name": "Aschwalon Group",
      "url": "#",
      "image": "https://via.placeholder.com/300&text=FPO",
      "members": 28,
      "description": "Lorem ipsum dolor.",
      "topics": [
        "Quantum Physics",
        "Argonne Laboratory"
      ],
      "latest_publication": {
        "name": "Spin-phonon interactions in silicon carbide addressed by Gaussian acoustics",
        "url": "#"
      }
    },
    {
      "name": "Bernien Group",
      "url": "#",
      "image": "https://via.placeholder.com/300&text=FPO",
      "members": 2,
      "description": "Lorem ipsum dolor.",
      "topics": [
        "Quantum Physics",
        "Quantum Systems"
      ],
      "latest_publication": null
    }
  ]
}
```

## Props
| Prop     | Required | Default                                           | Accepts | Notes                              |
|----------|----------|---------------------------------------------------|---------|------------------------------------|
| endpoint | Yes      | /it/showcase/endpoint/grouplist-endpoint          | String  | URL to the API endpoint            |


## Usage

```html
<uc-grouplist
  endpoint="/it/showcase/endpoint/grouplist-endpoint">
</uc-grouplist>
```
