# sidebar-facts component

Sidebar block with yellow background, repeating blocks with a heading, link and paragraph. Currently used in the 4-up grid on the homepage.

On mobile, the paragraphs are hidden, and a topic heading with link appears at the top.

## Usage

```html
<div class="c-sidebar-facts">
  <h2 class="t-heading--topic hide-for-large">
    <a href="#" target="_self">About the College</a>
  </h2>
  
  <div class="c-sidebar-facts__item">
    <h3><a href="#" target="_self">Our History and Culture</a></h3>
    <p class="show-for-large">
      Our students are rigorous thinkers from diverse backgrounds who 
      welcome and thrive on intellectual debate.</p>
  </div>
    
  <div class="c-sidebar-facts__item">
    <h3><a href="#" target="_self">Why UChicago?</a></h3>
    <p class="show-for-large">
    A distinguished faculty of scholars and scientists teach students of 
    diverse backgrounds and intellectual interests how, not what, to think.</p>
  </div>
</div>
```
