# Masthead Component

This is the global masthead at the very top of all pages. The masthead also encompasses the navigation component. 

The masthead itself contains the UC logo, a logo for "the college" and secondary navigation links. These secondary links are inside a `uc-switcher` component for displaying different links to either "affiliates" or everyone else (see the Switcher component README for details).

## Desktop
On desktop, the masthead scrolls out of site when the user scrolls down the page. It only comes back when the user scrolls back to the very top. Note the nav bar will become sticky as the user scrolls down.

## Mobile
On mobile, the masthead is condensed down to a crest-only logo, "the college" logo and a hamburger icon which triggers the navigation. 

The mobile masthead is also sticky and anchored to the top at all times. 

When the hamburger icon is clicked, the masthead turns red, and the search icon replaces the crest logo. Clicking the search icon will fire a js event that tells the search component (nested in the navigation component) to display. 

## Props
The only prop the masthead accepts is `navData`, which is a JSON object that contains all the information for:

1. Top-level section nav 
1. Top-level section link (desktop only)
1. Top-level section description (desktop only)
1. Secondary navigation
1. Links for affiliates
1. Links for prospects

Note: The masthead itself only uses the data for displaying the switcher links. All other navigation data is passed through to the `uc-navigation` component. 

## JSON
While the JSON should be passed directly into the component as a prop, a nav.json file is provided in the navigation component folder for structural reference.

## State Management
State for things like whether the mobile search or mobile nav are active, whether the masthead should be active, etc, are handled by [Vuex](https://vuex.vuejs.org). This is set up through a global `store` variable in `main.js`. When triggers like the search icon or hamburger menu get clicked, they update the global store which changes state accordingly in both components. This eliminates the need for several event emitters. 

## Usage

```html
<uc-masthead :nav-data='{ JSON HERE }'>
</uc-masthead>
```
