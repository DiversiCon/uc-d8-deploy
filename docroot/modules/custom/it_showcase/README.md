#[] Showcase

Custom module to allow for individual components to be prototyped with static templates,
both for reference purposes and to open the door for front-end development earlier in the
process.

##Overview
The magic lies in the ability to define data models to describe various site elements and
their content using YAML files.  Site elements that can be modeled as Showcase Items include
components, pages, and API endpoints.  After a showcase item has been defined, new showcase
pages and template suggestions will be created to allow for development of markup and
front-end code.

Showcase allows the site to render HTML output within the context of the site/theme.  This
is achieved without the need for any content to be created on the site.

## Showcase Index
Pages provided by Showcase include:
* ./it/showcase - the Showcase index page listing all available prototypes.
* ./it/showcase/component/{id} - page includes a single component and related information.
* ./it/showcase/page/{id} - a whole page of prototype HTML.
* ./it/showcase/endpoint/{id} - a JSON API endpoint prototype.
* ./it/showcase/readme - display this file.
* ./it/showcase/status - (experimental) status page.
* ./it/showcase/refresh - refresh showcase data (after YAML files are changed).
* ./it/showcase/api/v1/list - API endpoint including list of all enabled showcase items
with grouping/categorization.

## Permissions
A new permission, `access showcase pages` is provided to limit access to showcase related
page, which will need to be granted to see any showcase pages.

## YAML Files
Everything is defined to the Showcase via YAML files located in the modules directory or
the default theme directory.

* __File name__: Any file named `showcase.yml` found in any subdirectory of the custom
modules or default theme directories will be included as a Showcase item definition.
* __File content__: A single `showcase.yml` file may define multiple showcase items.
* __File structure__: Content of YAML files should follow normal YAML conventions and
include the following
data structure to define each showcase item (see YAML Reference for details about each value):
  ```yaml
  showcase_id: string
    title: string - showcase item title
    description: string - showcase item description
    type: component|page|endpoint
    enabled: true|false
    attributes:
      category: string - comma delimited list of categories
      related_id: string - related showcase ID (only used for readme items)
      related_readme_ids: array - related readme showcase ID's (only used for readme items)
      index_hide: true|false - hide item in the showcase index when there is a related_id (only used for readme items)
      short_title: string - short title text used in cases where the full title is too much (only used for readme items)
      file: string - path to markdown file relative to docroot (only used for readme items)
      thumbnail: string - url to thumbnail image
      sidebar: [true|false]
      full_page:[true|false]
      body_class: string - classes to be added to the rendered page
    links:
      -
        text: string - link text
        url: string - relative or absolute url
        target: string - optional _blank designation
        please_note: experimental - should probably not be tied to component but component/site instead
      -
        ...additional links as needed.
    variants:
      -
       title: string - variant title for showcase pages
       caption: string - variant caption for showcase pages
       content:  (any values defined here will be passed to template in the content array)
         field: ''
         ...additional fields per item.
      -
       title: ''
       caption: ''
       content:  (any values defined here will be passed to template in the content array)
         field: ''
         ...additional fields per item.
  ```
* See also: `docroot/modules/custom/it_showcase/example.showcase.yml`

## YAML Reference
Each available YAML value is defined below in alphabetical order:

**attributes** *(optional)*:
Container for additional values that control the behavior and appearance of showcase items.

**attributes.body_class** *(optional)*:
Specified on or more body classes to be added to the `body` tag when a showcase item page
is rendered in the showcase.
* Only applicable to ***component*** and ***page*** items.
* Any values will be added to the classes of the `body` tag when the page is rendered.
* Example usage: `body_class: c_container`

**attributes.category** *(optional)*:
Allows showcase items to be categorized on the showcase index page.
* Comma separated list of one or more proper case categories.
* Example usage: `category: Search, Forms`

**attributes.file** *(optional)*:
Path relative to *docroot* that points to a valid markdown file (*.md) for readme items.
* Only applicable to ***readme*** items.
* No leading slash.
* Example usage : `file: modules/custom/example/README.md`

**attributes.full_page** *(optional)*:
Indicates whether or not to render a page with standard header/footer.
* Only applicable to ***page*** items.
* When `full_page` is specified the page will be rendered without standard header/footer
content.  Otherwise, header/footer will be rendered as on any other page.
* Default value: *false*
* Valid values: *true, false*
* Example usage: `full_page: true`

**attributes.index_hide** *(optional)*:
Indicates to hide a particular readme item on the showcase index.
* Only applicable to ***readme*** items.
* Default value: *false*
* Valid values: *true, false*
* Example usage: `index_hide: true`
* Item will only be hidden on the showcase index if the item also includes a valid
`attributes.related_id` value.
* See also: **attributes.related_id**

**attributes.related_id** *(optional)*:
Identifies the showcase item that is related to a particular readme item.
* Only applicable to ***readme*** items.
* When a `related_id` is specified, the readme will automatically be attached to the
links section of the related item.
* The `related_id` must include the base showcase_id, and the showcase type in the form
of *[showcase_id].[type]*.
* Example usage: `related_id: accordion.component`
* See also: **attributes.index_hide**

**attributes.related_readme_ids** *(optional)*:
Identifies other readme showcase items that are related to a particular
readme item.
* Only applicable to **readme** items.
* When `related_readme_ids` is specified, links to those readme files/pages
will be added to the bottom of the parent readme file when rendered.
* The `related_readme_ids` must include the base showcase_id, and the showcase type in the form
of *[showcase_id].[type]*.
* Example usage:
```
related_readme_ids:
  - project-release-notes.readme
  - project-known-issues.readme
  ...
```

**attributes.short_title** *(optional)*:
Used as the link text when a readme item is attached to a related showcase item.
* Only applicable to ***readme*** items.
* Only useful if the item also includes a valid `attributes.related_id` value.
* May be used for other purposes in the future.
* Example usage: `short_title: Guide`
* See also: **attributes.related_id**

**attributes.sidebar** *(optional)*:  **DEPRECATED** -
Was used to categorize sidebar components (vs. full-width components)
* Only applicable to ***components*** items.
* Default value: *false*
* Valid values: *true, false*
* Example usage: `sidebar: true`

**attributes.thumbnail** *(optional)*:
Path relative to *docroot* that points to a valid image file to be used as a thumbnail to
represent an item on the showcase index.
* No leading slash.
* Example usage: `thumbnail: themes/custom/mytheme/showcase/images/example.jpg`

**description** *(optional)*:
The description of the showcase item, which is used to briefly describe the item with
more detail than the title alone.

**enabled** *(optional)*:
Defines whether or not a showcase items is enabled.  Disabled items with be excluded from
the showcase index and most other operations.
* Default value: *true*
* Valid values: *true, false*
* Example usage: `enabled: false`

**links** *(optional)*:
Container for array of values used to represent links related to the showcase item.  This
could include links to Zeplin, JIRA, etc.
* When a ***readme*** item includes `attributes.related_id` a link to that item will be
automatically included for the targeted item.
* Since this section may include links that are specific to an individual project, the
process of transferring a showcase item from one project to the next will require additional
effort.  We may eventually seek to resolve this with project specific enhancements.

**links.target** *(optional)*:
Link target as needed.
* Example usage: `target: _blank`

**links.text** *(optional)*:
Text to be displayed when rendering the link.  If no link text is provided then the URL
should be used as the link text.

**links.url** *(required)*:
Absolute or relative URL to linked content.
* No leading slash on relative URL's.
* Example usage: `url: http://external.link`

**showcase_id** *(required)*:
The primary key for each showcase item definition.
* Should only include letters, numbers, underlines and dashes.
* Each showcase item id must be unique within each type.  For example, you may not have
two components with the same `showcase_id`; but, you may have a component and an endpoint
with the same `showcase_id`.
* Duplicate items within the same type will be silently overwritten with the last
definition encountered.
* Since module directories are scanned before the default theme directory, an item
definition generated by a module level YAML file may be overridden by an intentional
duplicate defined at the theme level.
* The specified `showcase_id` will be used to generate URL's to showcase item pages and
to build template suggestions; thus, it is important to choose sensible identifiers.

**title** *(required)*:
The main title for the showcase item, which is used to identify the item on showcase index
pages.

**type** *(required)*:
The type of the showcase item.
* Valid values: *component, endpoint, page, readme*
* Used to namespace both showcase item pages and showcase item definitions.

**variants** *(optional)*:
Container for showcase item content including some support for multiple variants.
* Not used for ***readme*** items.
* Multiple variants only supported for ***component*** items.
* Always expressed as an array, even when only one is being defined.

**variants.caption** *(optional)*:
Text used to describe an individual variant  on the corresponding showcase component page
with more detail than the variant title alone.

**variants.title** *(optional)*:
Title used to identify an individual variant on the corresponding showcase component page.

**variants.content** *(optional)*:
Container for showcase item variant content.
* All elements defined under the `variants.content` key will be passed to the Twig template
as a variable called `showcase`.

##Template Files
A new template suggestion is created for each component and page defined as a showcase item.
The related template file may be placed in any subdirectory in the default theme directory
and the name will be simply include the showcase id:
* `showcase--<showcase_id>.html.twig`
* i.e. `showcase-searchform.html.twig`

##Specific Details Related to Components
Quick notes:
* You may reuse a component in a Twig template by using:
  ```
    it_showcase_component('component_name', 0)
  ```

## Reserved words:
Do not use any of the following terms as a showcase item id:
* block
* html
* node
* paragraph
* table
* user
* ...

##More to come...
This file seriously needs some work.

* https://github.com/erusev/parsedown
* http://parsedown.org/demo
* https://markdown.unicorn.fail

