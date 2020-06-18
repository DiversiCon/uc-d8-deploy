# Generic Content component

This is a generic content layout with a 900px content well on desktop and support for 1 or two columns, with a sidebar on either the left or right.

## Usage
### Single, centered column
```html
<div class="c-content">
    <div class="c-content__body">
        <p>Body here.</p>
    </div>
</div>
```

## Two column, sidebar on left
```html
<div class="c-content c-content--sidebar-left">
    <div class="c-content__sidebar">
        <p>Sidebar here</p>
    </div>
    <div class="c-content__body">
        <p>Body here.</p>
    </div>
</div>
```

## Two column, sidebar on right
```html
<div class="c-content c-content--sidebar-right">
    <div class="c-content__body">
        <p>Body here.</p>
    </div>
    <div class="c-content__sidebar">
        <p>Sidebar here</p>
    </div>
</div>
```
