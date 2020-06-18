# navigation component

This is the global navigation component for the site and is contained within the masthead component. 

## Desktop
On desktop, the navigation is a bar underneath the masthead with top-level section links and a search button. On section link hover, a dropdown menu is revealed that contains the section title (linked to landing page), section description and subnav links. 

Also on desktop, as the user scrolls down the page, the masthead scrolls out of site, but the nav bar anchors itself to the top of the page. Everything else remains the same, except a crest-only logo appears to the left, which provides a link to the homepage.

## Mobile
On mobile, the the navigation is hidden by default. When the masthead hamburger icon is clicked, the navigation is revealed, top-level section links only. 

Clicking on a caret icon to the right of an item will expand it to reveal subnav links. Clicking on a top-level title by itself will take the user straight to that page without expanding the menu.

Also on mobile, the affiliate/prospect links within the switcher component fall directly below the main nav.

## Search
When the seach icon is clicked (either on mobile or desktop), a search menu will appear. This will cover the mobile menu, if it's open. Typing a search term and submitting will go to a separate search URL.

## Props
The navigation component accepts no props directly, but gets its data from the parent `uc-masthead` component. See the masthead README and the nav.json sample file for details.

## State Management
State for things like whether the mobile search or mobile nav are active, whether the masthead should be active, etc, are handled by [Vuex](https://vuex.vuejs.org). This is set up through a global `store` variables in `main.js`. When triggers like the search icon or hamburger menu get clicked, they update the global store which changes state accordingly in both components. This eliminates the need for several event emitters. 

## Usage

```html
<uc-navigation></uc-navigation>
```
