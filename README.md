# Ad Layers

The Ad Layers plugin ("ad-layers") will provide an advanced mechanism for ad ops teams and other digital producers to customize the layout of advertisements in WordPress templates with minimal developer intervention.

# Managing Ad Layers

The plugin adds an Ad Layers custom post type to the WordPress dashboard for managing available add layers. There are multiple options under this menu.

## Add New Ad Layer

This allows you to create a new ad layer. This is set up as a WordPress custom post type with the following fields:

### Title
The standard WordPress title field acts as a label to reference this ad layer.

### Ad Slots
Click "Add an ad unit" to add one or more ad slots that are part of this ad layer.

### Page Types
Click "Add a page type" to add one or more pages on which this layer can appear. 

### Taxonomies
Click "Add a taxonomy" to add one or more taxonomies associated with this ad layer. Note this only works with taxonomy archives or single posts.

### Post Types
Click "Add a post type" to add one or more post types associated with this ad layer. Note this only works with post type archives or single posts. 

### Terms
All taxonomies that are associated with Ad Layers (category and post_tag by default) will display their standard term selection meta boxes. Choose one or more terms from each for targeting. Note that an "OR" condition is used for matching.

### Custom Targeting
Click "Add custom targeting" to choose one or more DFP custom targeting variables to add to the DFP request produced by this ad layer. The value can be:
- The term(s) associated with the page. This will only be populated on taxonomy archives or single posts. The taxonomies associated with Ad Layers are available for selection.
- The post type of the page. This will only be populated on post type archives or single posts.
- The author. This will only be populated on author archives or single posts.
- Other. This can be any free-form text value and will be static anywhere the ad layer is displayed.