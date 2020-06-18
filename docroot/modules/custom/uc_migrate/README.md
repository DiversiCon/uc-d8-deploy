# UChicago Migrate (uc_migrate)

Includes configuration for legacy content migration routines.  The idea is to keep these configurations isolated from the normal site configuration.

## Migration Notes
1. Only users that logged in over the last year have been migrated.
1. News stories will no longer be categorized as Announcements, Achievements or Press Releases.

## Migration Process
Migration process should be executed as follows:
1. Adjust existing users.
    1. Change user 1 username to admin-one.
    1. Create user temp.
    1. Delete user rsilverman, transfer content to temp user.
1. Execute migrate script.
    1. migrate_users
    1. migrate_taxonomy_category
    1. migrate_taxonomy_section
    1. migrate_taxonomy_tags
    1. migrate_file
    1. migrate_media_image
    1. migrate_media_document
    1. migrate_content_person
    1. migrate_content_story_images
    1. migrate_content_story_smartbody
    1. migrate_content_story
1. Adjust migrated users.
    1. Delete user temp, transfer content to rsilverman.
    1. Delete user admin, transfer content to admin-one.
    1. Rename admin-one user to admin.
    1. It would be great to transfer all content owned by Anonymous to rsilverman.


## Development Notes
1. __Person Issues__
    1. _Short biography or summary_ (body) - will not be migrated unless it is deemed necessary.
        1. We could create a new short bio field if needed.
        1. What is being used in Deans list vs. detail page?
    1. How are we accommodating link to full profile? 
    1. _Address and phone/fax number_ (field_directoryaddress) - contains inconsistent information.
        1. Only 8 nodes affected.  Original ID's ()
        ```
        +-----------+
        | entity_id |
        +-----------+
        |       981 |
        |      1011 |
        |      1016 |
        |      1021 |
        |      1351 |
        |      1996 |
        |      2541 |
        |      2911 |
        +-----------+
        ```
        1. Some include a fax number which we do not specifically support.  Should we?
    1. _Person type_ - based on directory category.
    1. No mapping for:
        1. field_phone (target)
        1. field_positions (target)
        1. field_hide_in_directory (target)
        1. body (source) - Short biography
        1. Link text to full biography (source)
1. __Story Issues__
    1. Do Achievements, Announcements and Press Release section terms need to be children of News?
    1. Do we need to make any adjustments to aliases?
    1. Nodes with multiple right-hand images will be better suited to manual update based on volume:
        ```
        1901 |   2
        2246 |   2
        2416 |   2
        2771 |   3
        1931 |   4
        2666 |   4
        ```
    1. Nodes with embedded video (13):
        ```
        select entity_id from field_data_body where body_value like '%[video:%';
        +-----------+
        | entity_id |
        +-----------+
        |        23 |
        |        41 |
        |        66 |
        |       196 |
        |       211 |
        |       256 |
        |       281 |
        |       616 |
        |       771 |
        |      1121 |
        |      1756 |
        |      2046 |
        |      2161 |
        +-----------+
        ```
    1. Nodes with potential photo inset (17):
        ```
        select entity_id from field_data_body where body_value like '%class="image-narrow%' and bundle = 'news';
        +-----------+
        | entity_id |
        +-----------+
        |      1671 |
        |      1761 |
        |      1876 |
        |      1981 |
        |      1991 |
        |      2221 |
        |      2241 |
        |      2246 |
        |      2271 |
        |      2286 |
        |      2301 |
        |      2406 |
        |      2651 |
        |      2751 |
        |      2781 |
        |      3121 |
        |      3126 |
        +-----------+

        ```    
        
