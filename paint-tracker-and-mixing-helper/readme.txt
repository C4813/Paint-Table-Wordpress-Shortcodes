=== Paint Tracker and Mixing Helper ===
Contributors: c4813
Tags: paints, miniature-painting, hobby, wargaming, colour-tools, paint-library, paint-ranges, mixing
Requires at least: 5.9
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 0.6.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shortcodes to display your miniature paint collection, plus mixing and shading tools for specific colours.

== Description ==

Paint Tracker and Mixing Helper is a WordPress plugin for managing your miniature paint collection and providing interactive colour tools on the front end. It helps you:

* Maintain a structured library of your paints
* Track which paints you currently own (“On the shelf”)
* Store useful links for each paint (tutorials, reviews, example builds, etc.)
* Display a searchable, sortable paint table on the frontend
* Use interactive tools such as:
  * A two-paint mixing helper
  * A shade helper with auto-generated lighter/darker mixes

The plugin creates and manages:

= Custom Post Type: "Paint Colours" =

Each paint is stored as a custom post, with:

* Name (post title)
* Paint number
* Hex colour
* “On the shelf” flag
* Optional linked posts/URLs (tutorials, reviews, examples, etc.) :contentReference[oaicite:0]{index=0}

= Custom Taxonomy: "Paint Ranges" =

Use paint ranges to group paints, for example:

* Vallejo Model Color
* Vallejo Game Color
* Citadel
* Army Painter
* Any other ranges you use

You can use a taxonomy ordering plugin (for example, “Category Order and Taxonomy Terms Order”) and the range dropdowns in this plugin will follow that custom order. :contentReference[oaicite:1]{index=1}

=== Shortcodes ===

== [paint_table] ==

Displays a table of paints, optionally filtered by range.

Supported attributes:

* `range` – paint range taxonomy slug (optional)
* `limit` – number of paints to show (`-1` to show all)
* `orderby` – `"meta_number"` (paint number) or `"title"`
* `shelf` – `"yes"` to show only paints that are on your shelf, `"any"` to show all paints

Example:

`[paint_table range="vallejo-model-color" limit="-1" orderby="meta_number" shelf="any"]` :contentReference[oaicite:2]{index=2}

=== Paint table display modes ===

The paint table can render colours in two different ways:

* **Dot mode** (default) – a small circular swatch column
* **Row mode** – the entire row background is coloured using the paint’s hex value, and the text colour is automatically adjusted for readability :contentReference[oaicite:3]{index=3}

You can choose the display mode on the plugin’s **Info & Settings** page:

* Dot mode shows a dedicated colour swatch column.
* Row mode applies the paint colour to the whole table row.

== [mixing-helper] ==

Displays the two-paint mixing tool:

* Choose two paints
* Optionally filter each paint dropdown by range
* Set the number of “parts” for each paint
* See the mixed colour and resulting hex value

This is useful for planning blends, transitions, highlights, and shadows. :contentReference[oaicite:4]{index=4}

== [shade-helper] ==

Displays the shade helper tool:

* Choose a base paint
* The plugin finds the darkest and lightest paints in the same range
* It generates a ladder of darker and lighter mixes (three steps each side)
* Each step shows the hex value and a mix description (e.g. “Darkest colour 1 : 3 Base colour”) :contentReference[oaicite:5]{index=5}

=== Shade helper page URL ===

To make colours in the paint table clickable and deep-link into the shade helper, set the **Shading page URL** on the plugin’s Info & Settings page.

* This should be the URL of a page where you are using the `[shade-helper]` shortcode.
* When set, clicking a swatch (dot mode) or a coloured row (row mode) in `[paint_table]` will send the chosen paint to the shade helper page and automatically select that colour.
* If you leave this field empty, swatches/rows remain non-clickable colour indicators. :contentReference[oaicite:6]{index=6}

== Importing paints from CSV ==

Go to **Paint Colours → Import from CSV** in the WordPress admin.

Options:

* Choose the paint range that newly imported paints should belong to
* Upload a CSV file with one paint per row

Supported columns:

* `title` – paint name
* `number` – paint number (optional)
* `hex` – hex colour (e.g. `#2f353a` or `2f353a`)
* `on_shelf` – `0` or `1` (optional)

An optional header row with column names (`title`, `number`, `hex`, `on_shelf`) is supported and automatically detected. :contentReference[oaicite:7]{index=7}

== Exporting paints to CSV ==

Go to **Paint Colours → Export to CSV** in the WordPress admin.

Options:

* Filter paints by range
* Optionally limit the export to paints marked as “On the shelf”

Exported columns:

* `title` – paint name
* `number` – paint number
* `hex` – hex value
* `on_shelf` – `0` or `1`
* `ranges` – pipe-separated list of assigned paint range names :contentReference[oaicite:8]{index=8}

== Additional features ==

* Range dropdowns respect custom taxonomy term order (e.g. from the “Taxonomy Terms Order” plugin)
* Paint table sorting uses paint numbers when available for a more natural order
* Shade helper supports unique paint selection by post ID, so paints sharing the same hex value do not clash
* Clean, responsive frontend styling
* Custom JavaScript dropdowns for a consistent look across browsers :contentReference[oaicite:9]{index=9}

== Installation ==

1. Upload the `paint-tracker-and-mixing-helper` folder to the `/wp-content/plugins/` directory, or install the plugin via **Plugins → Add New** in the WordPress admin.
2. Activate the plugin through the **Plugins** screen.
3. In the WordPress admin, go to **Paint Colours** to add paint ranges and paint colours.
4. Create or edit a page and insert one of the shortcodes:
   * `[paint_table]` – to show your paint table
   * `[mixing-helper]` – to show the mixing helper
   * `[shade-helper]` – to show the shade helper
5. Optional: Go to **Paint Colours → Info & Settings** to:
   * Choose between dot mode or row mode for the paint table
   * Set the Shading page URL so that table colours can deep-link into the shade helper.

== Frequently Asked Questions ==

= How do I make the colours in the paint table clickable? =

Set the **Shading page URL** on the plugin’s **Info & Settings** page. This should be the URL of the page where you are using `[shade-helper]`.

Once set:

* In dot mode, clicking a colour swatch opens the shade helper page with that paint selected.
* In row mode, clicking the coloured row does the same.

If the URL field is left blank, swatches/rows are not clickable.

= Can I use paints from multiple ranges? =

Yes. You can create as many paint ranges as you like (e.g. Vallejo Model Color, Vallejo Game Color, Citadel, Army Painter, etc.) and assign paints to those ranges. The mixer and shade helper tools both support filtering paints by range. :contentReference[oaicite:10]{index=10}

= What happens if two paints share the same hex value? =

The shade helper uses the paint’s internal post ID to identify the selected paint, so it will correctly select the paint you clicked even if another paint in a different range shares the same hex code.

= Does this plugin track inventory or prices? =

No. It tracks which paints you “have on the shelf” (a simple on/off flag) and their basic colour information. It does not track pricing, stock counts, or purchases.

= Can I uninstall this plugin safely? =

Yes, but any custom post data (Paint Colours) and taxonomy terms (Paint Ranges) will remain in your database unless you remove them manually or provide a cleanup routine. If you plan to remove it permanently, you may wish to export your paints to CSV first.

== Screenshots ==

1. Paint table in dot mode with circular colour swatches.
2. Paint table in row mode with entire rows tinted by paint colour.
3. Two-paint mixing helper showing range and paint dropdowns and the mixed result.
4. Shade helper showing a ladder of lighter and darker mixes.
5. Paint details meta box in the admin (number, hex, on-shelf flag, links).
6. CSV import and export screens in the admin.

== Changelog ==

= 0.6.3 =

* Ensure shade helper selects paints by post ID so that paints with identical hex codes from different ranges do not conflict.
* Improve deep-linking behaviour from the paint table into the shade helper.

= 0.6.2 =

* Add “row highlight” display mode so the entire table row can be coloured using the paint’s hex value.
* Adjust table styling for better contrast and responsiveness.

= 0.6.1 =

* Refine frontend CSS for paint table links and clickable rows.
* Tidy up admin interface and dropdown behaviour.

= 0.6.0 =

* Initial public-ready refactor of the plugin.
* Introduce structured meta for paints, multiple link support, CSV import/export screens, and shortcode-based mixing and shade helpers.

== Upgrade Notice ==

= 0.6.3 =

Improves shade helper accuracy when multiple paints share the same hex value. Update is recommended so that clicking a colour in the paint table always selects the correct paint in the shade helper.
