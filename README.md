# Testable Entity and Field Documentation - Migration

YAML files with previous and new entity names, field name mappings, and
where appropriate migration of data from/to different fields.

Running these Drupal 8 migrations can be done in two ways:-

The first is how they are presently set-up. Migrating from one database
to another. This requires the SQL based migration source available at
https://www.drupal.org/project/migrate_drupal_d8
The additional work required would be to add missing migrations for
entities not documented such as Users.

The second way is to migrate within the exisiting database. The source
class for these migrations is based on the Entity API and is in Drupal core.
The source in the YAML wants to be changed from `d8_node` to 
`content_entity:node` and so forth.

Both methods at the moment only handle the currently live revision of an
entity.
