# What links here

## Summary

This module provides a sortable report of what node entities link to the currently viewed node. It does so by retaining a manifest of references in a `whatlinkshere` table and allows you to also see which field the reference occurs in.

Data is added/updated/removed on entity CRUD and field delete operations.

A Drupal console command is also available to scan all or individual nodes.

## Limitations

- Presently only works for node entities due to query complexity, but has the potential to scale for any entity types.
- Only looks for references in the following field types:
  - Entity reference (node bundles)
  - Text (long / with summary)
  - Link
- No views integration (yet)

## Install

- `composer require drupal/whatlinkshere`
- Enable the module via the UI/drush/drupal console.

## Uninstall

- Uninstall via the UI/drush/drupal console.
- `composer remove drupal/whatlinkshere`
