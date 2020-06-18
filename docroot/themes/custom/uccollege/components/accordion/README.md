# accordion component

Expanding/collapsing accordion component. The Vue component controls the opening/closing of the accordion and ARIA attribute management. 

Accordions are a combination of two components: `uc-accordion-group` and `uc-accordion`. The group component can include any number of accordion items.

## Props

### Accordion Group
| Prop            | Default | Accepts | Notes                                                          |
|-----------------|---------|---------|----------------------------------------------------------------|
| single          | false   | boolean | When true, allows only one accordion item to be open at a time |
| columns         | 1       | 1, 2    | Number of columns each accordion item should take up           |
| heading         |         | string  | Optional heading (h2) that will appear above the group         |
| headingCentered | false   | boolean | If true, heading is centered and larger                        |

### Accordion Item

| Prop        | Default | Accepts            | Notes                                                 |
|-------------|---------|--------------------|-------------------------------------------------------|
| title       | n/a     | string             | Text used for the heading/button of the accordion     |
| subtitle    | n/a     | string             | Optional subtitle that appears in the button          |
| inset-image | false   | boolean            | Should the button contain an inset image?             |
| mobile-only | false   | boolean            | Does this appear as an accordion item ONLY on mobile? |
| open        | false   | boolean            | Should this item be expanded by default?              |
| tag         | h3      | any valid html tag | What HTML tag to use for the item title?              |

## Accessibility
You can navigate between accordion headings either with the tab key or up/down arrows. To open, hit enter or space. Once you start tabbing into an open accordion item, you can still jump to the next or previous accordion headings with up and down arrows. Hitting ESC will close the current item.

## Usage

```html
<uc-accordion-group v-cloak :single="true" :columns="2" :headerCentered="true">
  <uc-accordion title="Collegiate Divisions" :mobile-only="true" :open="true" tag="h2">
    <p>Accordion content here</p>
  </uc-accordion>

  <uc-accordion title="50 Majors & 33 Minors" :mobile-only="true" :inset-image="true">
    <p>Accordion content here</p>
  </uc-accordion>
</uc-accordion-group>
```
