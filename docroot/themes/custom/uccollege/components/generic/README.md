# Generic component

The Generic component is intended for the sidebar.

- Heading text
- For each title/text section,
  - Title, optional
  - Text, optional
  
If there are multiple title/text sections, a border will separate them.

## Usage

```html

<div class="c-generic">
  <div class="c-generic__ctn">
    <h2 class="c-generic__heading">Event Schedule</h2>
      <div class="c-generic__section">
        <div class="c-generic__item">
          <p><strong>Monday</strong></p>
          <p>Introductions, keynote, meet and greet.</p>
        </div>
      </div>
      <div class="c-generic__section">
        <div class="c-generic__item">
          <p><strong>Tuesday</strong></p>
          <p>Training sessions, lunch, presentations.</p>
        </div>
      </div>
  </div>
</div>
```
