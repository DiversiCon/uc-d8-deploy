# UChicago Organization Customizations (uc_organization)

Custom module that modifies the behavior of taxonomy terms in the organization
vocabulary.

## Includes
* URL override to a content page.
* Section based permission restrictions to content pages.

## Requires
Configuration related to the basic content/field settings are included
with the module.
* The following fields must exist for user entities:
    * field_allowed_organizations
    * field_all_sections
    * field_no_sections

To for a node to be controlled by this feature you must add the field
`field_organization` to the targeted node type, and use the
`Organization Autocomplete` widget on the form display settings.
