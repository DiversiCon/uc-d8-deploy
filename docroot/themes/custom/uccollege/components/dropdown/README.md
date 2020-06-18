# dropdown component

The dropdown component is a stylized UI to simulate a form select element. It's originally built to support the program selector, but can be used within other components, like the contact form.

When a selection is made, the callback function specified in the `on-select` prop will be triggered. Also, the query params in the URL will be updated for deep-linking purposes.

| Prop         | Default | Accepts  | Notes                                                                                                                      |
|--------------|---------|----------|----------------------------------------------------------------------------------------------------------------------------|
| name         | n/a     | String   | The name used in the markup of the component                                                                               |
| display-name | n/a     | String   | The user-facing name displayed for the dropdown                                                                            |
| options      | n/a     | Array    | JSON-formatted string of data. This can be an object or inline JSON                                                        |
| on-select    | n/a     | Function | Function that should trigger when a selection is made from the list. This will normally be passed from a parent component. |

## Dropdown sizing
Because the button and list are two separate things, and the list has to be `position: absolute`, the component calculates the actual widths, so that items don't wrap prematurely, etc. To do the calculation, the list is temporarily set to `position: relative` and the width of that is set as the new width of both the list and button.

## Usage

With passing objects to `:options` and `:on-select`:

```html
<uc-dropdown
  name="division"
  display-name="Collegiate Division"
  :options="divisions"
  :on-select="selectDivision">
</uc-dropdown>
```

With inline JSON:

```html
<uc-dropdown
  name="division"
  display-name="Collegiate Division"
  :options="[
    {
      'name' : 'Option One',
      'value' : '1'
    },
    {
      'name' : 'Option Two',
      'value' : '2'
    },
    {
      'name' : 'Option Three',
      'value' : '3'
    }
  ]"
  :on-select="function() { return true }">
</uc-dropdown>
```

Note that the `value` key in the JSON object is optional. Without it, the `name` content will be used for the value of each dropdown option.
