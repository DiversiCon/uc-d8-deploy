# UChicago Search (uc_search)

Custom module to provide search functionality via the Search API module.

## Includes

### Search page rendering.
* New route at /search.
* Implements searchform and list FE omponents.

### Search REST endpoint for FE component.
* New routes at `/api/v1/search/{order}/{keywords}`
* Query parameters:
  * `page` = zero indexed results page to return
  * `items_per_page` = items per results page
