[![CircleCI](https://circleci.com/gh/dof-dss/nidirect-site-modules.svg?style=svg)](https://circleci.com/gh/dof-dss/nidirect-site-modules)

# NI Direct custom modules

This repository houses the custom modules required for the NI Direct website.


## Breadcrumbs
Custom navigation breadcrumbs.

## Cold Weather Payments
- Weather station entity.
- Cold Weather Period field, widget and formatter.
- Search form.
- Search block and token.
- Cold Weather Payments REST API.

## Common
- Cron job for audit date updates.
- Save top_level_theme to node on save.
- Invalidate taxonomy cache tags on node sav or deletion.
- Add additional guidance on LinkIt to WYSIWYG editor.
- Display singular/plural header on Recipes search View.
- Display singular/plural header on Publications search View.
- Provide banner field for application, article, publication and health_condition entities.
- Description added to theme/subtheme field for landing pages.
- Switch between entity embed view modes for edit and view conditions.
- Site search block.

## Contacts
- AtoZ block.
- Listing controller.
- Tweaks to exposed View form.
- Update View Query to alter query conditions.
- Process View display to alter sort links.

## Custom Blocks
- Article teasers by Topic block.

## Driving Instructor
- Removes sort/order by options from exposed form.
- Display singular/plural header on search View.
- Disable Driving Instructor title field and generate title on node save.
- Prevent selection of parent category terms on node edit form.
- Remove second portion of the postcode if present on search query.
- Hide empty text, result count and sort options on initial display of search View.
- Provide custom sort links based on url parameters. 
 
## Error Pages
- Provides template suggestions for error pages.

## GP
- GP entity.
- GP entity forms.
- GP entity and access controllers.
- Lookup lead and member GPs for practices.
- GPs field for practices.
- Disable Practice title field and generate title on node save.
- Validation on Practice form for practice pr surgery name.

## Health Conditions
- Blocks for AtoZ and Related Conditions.
- Health Conditions listing controller.
- Update exposed View form with reset link.
- Add summary and related conditions to node view.
- Update node title if viewing alternative health condition.

## Hospital Waiting Times
- Fetch waiting times from API.
- Setting form for configuring API calls.
- Ultimate Cron job for calling API.
- Token for displaying waiting times.
- Theme hook and twig template for displaying waiting times.

## Money Advice Articles
- Migration API configuration for importing Money Advice Service articles.
- Ultimate Cron job calling Migration API.
- Migration process function to alter article fields. 

## News
- News listings controller.
- Change date published label.

## Related Content
- Blocks for Recipes by Course, ingredient, season, special diet.
- Display related content on View.
- Set View title to parent theme title. 

## School Closures
- School closures service and interface.
- C2KSchools closure service.
- School closures settings form.
- Theme hook and twig template.
- School closures Token.
- JS for filtering closure results.

## Webforms
- Webform handler for Quiz Results.
- Theme hooks and twig templates for Quiz Results.
