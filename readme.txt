=== Kodlo Product Gallery for Elementor ===
Contributors: kodlo, imaginary222
Donate link: https://kodlo.dev/
Tags: woocommerce, elementor, grid, slider, product gallery
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 2.9.6
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build WooCommerce product galleries in Elementor with desktop grids, mobile sliders, variation galleries, videos, zoom, and magnifier.

== Description ==

**Build the product gallery your store needs with one Elementor widget.**

Kodlo Product Gallery for Elementor replaces the standard WooCommerce product gallery on product pages and in product templates. It brings the controls that are often split across separate gallery widgets into one coherent, responsive gallery that you can shape around each product and breakpoint.

The plugin was created for a practical reason: a clean desktop product grid, a flexible mobile slider, variation-specific media, video, image effects, and loading priorities should work together without forcing you to combine multiple widgets, maintain duplicate responsive layouts, or learn another settings screen.

### Why Use Kodlo Product Gallery for Elementor?

* **Design each breakpoint intentionally** - Present products as a structured desktop grid and turn the same media into a touch-friendly mobile slider at the Elementor breakpoint you choose.
* **Keep the workflow familiar** - Configure and style the widget through native Elementor panels, icons, responsive controls, and Global Colors, with no standalone Kodlo settings page.
* **Avoid duplicate responsive downloads** - One rendered media set powers both desktop and mobile behavior instead of asking visitors to download separate image collections.
* **Build with performance in mind** - Keep images in the native WordPress attachment flow and control priority loading for the media that can affect LCP.
* **Use the complete gallery for free** - The full feature set is included without advertisements, in-plugin upsells, or a separate premium gallery tier.

### Built for Responsive Product Presentation

Kodlo reads the current WooCommerce product on the frontend and provides a product fallback inside the Elementor editor, so the gallery remains visible and configurable while you build the layout.

* **Desktop Product Grid** - Choose from 1 to 10 columns, control gaps and image limits, hide the featured image, randomize image order, or stretch the first or last unmatched grid item across all columns.
* **Mobile Swiper Gallery** - Use a main image slider, optional thumbnail carousel, infinite loop, configurable thumbnail count and gap, navigation arrows, and a limited sliding pagination window.
* **Choose Where Each View Applies** - Keep the Grid on every screen by disabling Mobile View, keep the Slider on every screen by disabling Desktop View, or enable both and switch between them at any active Elementor breakpoint. Breakpoint values remain under your control in Elementor, so you decide exactly where the layout changes or whether it changes at all.
* **Optional Swipe Edge Bleed** - Preserve the mobile content gutter at rest, reveal slides through that gutter while dragging, and clip them at the screen edge. Configure the gutter and anchor navigation arrows to either the content edge or screen edge.
* **Stable Thumbnail First Paint** - The initial thumbnail row is laid out before Swiper initializes, reducing the full-width thumbnail flash and associated layout movement on mobile devices.

### Product Media That Goes Beyond Images

* **YouTube, Vimeo, and MP4 Video** - Add a Video URL directly to an image in the WordPress Media Library. The image remains the poster and thumbnail while the gallery provides inline playback.
* **Focused Video Playback** - Configure desktop/mobile autoplay, mute and loop behavior while keeping playback centered on the custom Elementor Play button. Native and provider control bars stay hidden; non-loop videos restore the poster and Play button when playback ends.
* **Zoom and Magnifier Effects** - Select None, Zoom, or Magnifier independently for desktop and mobile. Configure zoom strength, lens size, border, radius, and the accessible mobile magnifier toggle. Choose All images (the default) or First image only for each device. First image follows the gallery order and skips videos, including in looping sliders.
* **Captions, Links, and Elementor Lightbox** - Display attachment captions and link images to media files or attachment pages. Media-file links support the standard Elementor lightbox; choose Default to follow the global Lightbox configuration in Elementor settings, or enable or disable it for the individual gallery widget.
* **Sale Flash** - Display the WooCommerce sale badge in a preset or custom-styled position, with an optional link.

### Variation Galleries Inside WooCommerce

Build each variation gallery directly inside the familiar WooCommerce Product data panel. Select images through the standard WordPress Media Library, add or remove them, reorder them by drag and drop, or use the accessible ordering controls. The interface follows native WordPress and WooCommerce patterns, with no separate variation-gallery screen to manage.

Variation-specific galleries are commonly placed behind a paid upgrade in product gallery plugins. Kodlo includes this functionality at no cost.

On the storefront, Kodlo supports both complete and partial variation matching. The gallery can respond as soon as a meaningful attribute such as color is selected. Payload fingerprints prevent unnecessary DOM and Swiper rebuilds when the effective visible gallery has not changed, reducing flicker between equivalent variations.

Variation galleries can be enabled or disabled for the widget as a whole, while desktop and mobile views each keep their own maximum image limit.

### Styling in Elementor

* Independent desktop, mobile, thumbnail, pagination, navigation, caption, sale badge, video, zoom, and magnifier controls.
* Aspect-ratio and object-fit presets for desktop images, mobile images, thumbnails, and stretched grid items.
* Normal, hover, and active states for borders, colors, backgrounds, opacity, and radius where applicable.
* Elementor icon library support for navigation, video, and mobile magnifier controls.
* Elementor Global Color-compatible color controls.

### Performance and Accessibility Foundations

Kodlo keeps product images in the native WordPress attachment flow, making its output straightforward for standard caching and image-optimization tools to process. Gallery interactions use lightweight Vanilla JavaScript, with jQuery retained only for WooCommerce variation events and Elementor's frontend init event.

* Uses native image dimensions, `srcset`, and `sizes` attachment data to reserve media space.
* Includes an optional Image Loading Optimization control, disabled by default, for setting per-context priority image counts in the desktop grid, mobile slider, and mobile thumbnails.
* When enabled, priority images receive `fetchpriority="high"` and `decoding="async"`; remaining rendered images receive `loading="lazy"` and `decoding="async"`.
* The optional Defer Off-Screen Images control, disabled by default, loads thumbnails outside the visible row after the page has loaded or on the first interaction with the gallery; with the desktop view off, mobile slides after the priority images are deferred as well.
* Deduplicates variation media metadata in the page payload and primes attachment caches before validation.
* Cleans up disconnected Swiper instances and scopes runtime state to each widget.
* Loads base assets through the Elementor widget dependency system only where the widget is used. Swiper loads only when the mobile gallery is enabled, magnifier and edge-bleed assets load only when their features are active on the frontend, and Media Views assets stay limited to relevant administration screens.
* Includes semantic buttons, localized keyboard-operable navigation controls, visible `:focus-visible` styles, descriptive video titles, active-thumbnail state, and hidden-state handling for limited pagination dots.
* Respects `prefers-reduced-motion` by disabling gallery transitions and automatic video playback where appropriate.

Accessibility depends on the complete page, theme, content, colors, and configuration. Kodlo provides accessibility-focused foundations but does not claim automatic compliance for every implementation.

### Try the Complete Gallery Before Installing

Select **Live Preview** on WordPress.org to launch a temporary WordPress Playground site with WooCommerce, Elementor, Kodlo Product Gallery, and a prepared variable product. The preview opens directly on a polished responsive product page built with Elementor, with two variation-specific sets of five purpose-made product photographs plus a video item. Switch between Graphite and Field Sage to see the gallery update, then use **Edit with Elementor** to change the desktop grid, test desktop, tablet, and mobile modes, inspect thumbnails and navigation, explore zoom and video behavior, and review the styling controls before adding the plugin to a real store.

The preview runs in an isolated disposable browser environment. It does not connect to your production WordPress site, store, customers, orders, or administrator account, and its changes disappear when the Playground session is closed or reset.

### WooGallery Compatibility

Kodlo includes an optional compatibility layer for WooGallery metadata. Activation safely revokes the previous notice generation, preserves any completed or interrupted progress, and checks only the exact `wcgs_video` and `woo_gallery_slider` keys on an authorized administrator request. If supported legacy data is available, Kodlo asks whether it should be migrated. Nothing is copied until a WooCommerce administrator explicitly selects **Migrate legacy data**. The migration freezes a per-key metadata snapshot and immediately processes protected 50-row AJAX batches on the current administration page while a progress notice remains visible. Public product views stay read-only. Every destination write is tied to the exact selected legacy row and shares an internal per-object lock with normal Kodlo metadata saves, so a newer or explicitly empty Kodlo value remains authoritative.

During migration, the notice shows exact processed-row progress and asks the administrator not to reload or leave the page. Failed or repeatedly stalled batches stop automatic requests and expose a Retry button. When both jobs finish, the page automatically loads a completion notice with **Keep legacy data** as the safest default and **Delete safely migrated legacy data** as a separate explicit action. Both controls share one native WordPress action row. Safe cleanup uses the same visible protected batch runner, keeps an independent snapshot and cursor for each legacy key, and never crosses the corresponding completed migration boundary. It removes an unchanged legacy row only while its exact Kodlo destination still exists with the migrated value. Unsupported, unmatched, unmigrated, later-created, concurrently changed, or subsequently changed rows remain untouched.

### SVG Safety

SVG attachments accepted by the standard WordPress and Elementor media workflow are supported like other image attachments, without an additional Kodlo-specific approval layer.

### Deactivation and Permanent Deletion

Deactivation stops the widget and its frontend behavior without deleting Elementor widget settings, attachment Video URLs, variation gallery assignments, or migration progress. Reactivating the plugin restores the configured galleries.

Permanent deletion through the WordPress Plugins screen removes Kodlo's internal migration state, temporary locks, and administrator failure notices. Authored gallery content remains in WordPress post metadata to prevent accidental product-data loss, so reinstalling the plugin can reuse it. Delete that product content separately only when you intentionally no longer need it.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/kodlo-product-gallery-for-elementor` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Ensure both WooCommerce and Elementor (Free or Pro) are installed and activated.
4. If Kodlo reports supported WooGallery metadata, review the detected data and select **Migrate legacy data** only when you want to copy it into Kodlo.
5. Open a WooCommerce product page or Elementor Single Product template, search for **Kodlo Product Gallery** in the widgets list, and drag it into your layout.

To evaluate the plugin first, use **Live Preview** on its WordPress.org page. The Playground setup installs the required free dependencies, creates a disposable variable product with two color-specific five-image galleries and video items, signs you into the temporary site, and opens a complete product page built with Elementor with the configured gallery already visible.

== Frequently Asked Questions ==

= Does it require Elementor Pro? =
The plugin requires WooCommerce 9.0 or newer and Elementor 3.22 or newer. The widget itself works with Elementor Free or Pro, but it needs a WooCommerce product context on the frontend. Place it on a product page or inside a Single Product template. Elementor Pro is required only for Elementor features such as building Single Product templates with Theme Builder.

= Can I test the widget before installing it on my store? =
Yes. Select **Live Preview** on WordPress.org. A temporary WordPress Playground site opens on a complete responsive product page with WooCommerce, Elementor, two selectable product finishes, ten purpose-made product photographs, a shared product video, and the Kodlo gallery already visible. Changing the finish demonstrates variation-specific galleries. On-page purchase controls are decorative and do not navigate or place an order. You can then use **Edit with Elementor** to safely inspect gallery controls and switch among desktop, tablet, and mobile modes without affecting a live website. The prepared MP4 is loaded from the plugin's WordPress.org assets without contacting a third-party video player.

= Can I use it on a normal non-product page? =
Only when that page has a valid WooCommerce product context. The frontend renderer looks for the current WooCommerce product and does not output a gallery when no product can be resolved. In the Elementor editor, the preview can fall back to the first product so the widget remains editable.

= Does it support variable WooCommerce products? =
Yes. Assign galleries in the WooCommerce variation editor. Kodlo supports complete variation events and partial matching while attributes are being selected.

= How do I attach videos to my gallery? =
Open an image in the WordPress Media Library, enter a YouTube, Vimeo, or direct MP4 URL in the Video URL field, and save the attachment. The image becomes the video poster and gallery thumbnail.

= Does video playback connect to an external service? =
Only when the gallery uses an external video URL. Starting a YouTube or Vimeo video loads that provider's player, and starting a remotely hosted MP4 requests that file from its host. Review the provider's privacy terms and your site's consent requirements before publishing external media.

= How does Mobile Swipe Edge Bleed work? =
Enable Swipe Edge Bleed in the widget's General settings section. Match Mobile Side Gutter to the horizontal mobile padding around the gallery. Slides remain inside the content gutter at rest, can travel through it while swiping, and are clipped again after the slider snaps. Navigation Arrows Edge applies the same content-edge or screen-edge anchor to both main and thumbnail arrows.

= Does the plugin include accessibility support? =
Yes. Kodlo includes keyboard-operable navigation controls, semantic buttons, localized labels, visible focus states, descriptive embedded-video titles, reduced-motion support, and pagination visibility management. Final accessibility still depends on the surrounding page, theme, content, colors, and widget configuration.

= How is image loading optimized? =
Kodlo always outputs attachment dimensions and responsive image data. By default it does not add custom `loading`, `fetchpriority`, or `decoding` attributes. If Image Loading Optimization is enabled, the configured first images in the desktop grid, mobile slider, and mobile thumbnails receive `fetchpriority="high"` and `decoding="async"`, while the remaining rendered images receive `loading="lazy"` and `decoding="async"`.

= Does it import data from WooGallery? =
Yes, with administrator consent. Activation resets the notice cycle, then the next authorized administrator request checks only WooGallery video URLs (`wcgs_video`) and variation gallery image lists (`woo_gallery_slider`). If either key is present, a WooCommerce administrator can explicitly start a fixed-snapshot migration. Protected 50-row AJAX batches then run immediately on the current page with processed-row progress, leave-page protection, and retry handling. Storefront requests never write migration data, and reactivation never resumes paused work without fresh consent.

After migration, **Keep legacy data** is the safest default. The separate **Delete safely migrated legacy data** action processes its own fixed snapshot in 50-row batches without crossing the completed migration boundary, and removes an unchanged row only while its exact Kodlo destination still exists. Unsupported, unmatched, unmigrated, concurrent, and later legacy rows are retained.

= What happens when I deactivate or uninstall the plugin? =
Deactivation stops the gallery functionality but preserves its data so the existing galleries return when the plugin is reactivated. Permanent deletion through the WordPress Plugins screen removes Kodlo's migration progress, temporary locks, and administrator failure notices. It intentionally preserves attachment Video URLs and variation gallery assignments because they are authored product content rather than disposable settings.

== Screenshots ==

1. A complete WooCommerce product page using the multi-column desktop Grid view.
2. The Field Sage variation in the mobile Slider view with arrows, pagination, thumbnails, and responsive product content.
3. Native Elementor controls for enabling the desktop Grid, choosing columns, and configuring image layout.
4. Mobile Slider controls with the Mobile Portrait breakpoint selected, plus infinite loop and image-limit settings.
5. Thumbnail visibility and thumbnails-per-view controls.
6. Pagination, visible-dot, main-arrow, thumbnail-arrow, and custom navigation-icon controls.
7. Video visibility, thumbnail indicator, play icon, autoplay, mute, and loop controls.
8. General media, lightbox, variation, sale flash, edge bleed, and image-loading controls.
9. Desktop Grid styling for image fit, aspect ratio, spacing, borders, and image effects.
10. Mobile Slider styling for image fit, aspect ratio, background, borders, and image effects.
11. Thumbnail styling for size, spacing, borders, backgrounds, opacity, and active states.
12. Navigation styling for pagination dots and main or thumbnail slider arrows.
13. Video styling for gallery and thumbnail play icons, positions, colors, and effects.
14. General styling for captions and the WooCommerce sale flash.
15. Variation-specific images and video managed inside the native WooCommerce Product data panel.
16. A Video URL added directly to an image on its native WordPress Media edit screen; the image remains the video thumbnail and poster.

== Security ==

**Security Contact:** [https://kodlo.dev/](https://kodlo.dev/)

Report a suspected vulnerability privately through the **Contact** button at that address. Please include every affected plugin version you reproduced, WordPress, PHP, WooCommerce, Elementor and browser details, observed and potential impact, reproducible steps, the required user role or capabilities, and a minimal proof of concept.

Do not include passwords, API keys, access tokens, customer data, or other personal data. Redact sensitive values from logs and screenshots. Do not publish exploit details in a support topic before the report can be assessed and coordinated disclosure arranged. Use the public support forum only for non-sensitive support questions.

The packaged `SECURITY.md` documents supported versions, coordinated disclosure, and an expected initial response within five business days.

Security fixes target the latest published version, so keeping the plugin up to date is recommended.

PHP 8.2 or newer is recommended for production because it remains supported by the PHP project. PHP 8.1 is the plugin's minimum compatibility requirement, not a recommendation to operate an unsupported PHP branch.

== Privacy ==

Kodlo Product Gallery for Elementor does not send gallery settings, product data, telemetry, or analytics to Kodlo. Attachment Video URLs and variation gallery image IDs are stored in WordPress post metadata, while Elementor stores the widget configuration in its normal document data.

The optional WooGallery compatibility workflow stores consent, progress, and temporary lock state in WordPress options. It reads only the documented legacy metadata keys and does not copy or delete legacy values without the administrator actions described above.

Permanent plugin deletion removes that operational state and temporary administrator notices. Gallery content stored in attachment and variation post metadata is preserved to prevent accidental product-data loss.

When a visitor starts a YouTube or Vimeo video, the browser loads the corresponding provider player. A direct MP4 URL is requested from the host named in that URL. Those external services may process visitor data under their own policies; site owners are responsible for selecting providers and configuring any required privacy notice or consent mechanism.

== Changelog ==

= 2.9.6 =
* Fix: Desktop and mobile style settings no longer override each other. Desktop magnifier zoom, lens size and lens border, desktop Object fit, Image Background, corner radius and borders now apply to the desktop view only, the mobile ones to the mobile view only, and every change shows immediately in the Elementor editor. Elementor's generated CSS is refreshed once after the update.
* Fix: Hover icon color and hover background of the navigation arrows now apply.
* Fix: Image captions keep their line height when Elementor's Swiper styles are loaded.
* Performance: New "Defer Off-Screen Images" option (Content > General, with Image Loading Optimization). Thumbnails outside the visible row, and mobile slides after the priority images when the desktop view is off, load after the page has loaded or on the first interaction with the gallery. Images of the desktop grid and the Elementor editor are not affected.
* Performance: The mobile slider no longer downloads every slide image when it starts, and lazy-loaded thumbnails carry `srcset` with `sizes="auto"`.
* Compatibility: The mobile slider loads Elementor's own Swiper stylesheet.
* Performance: A variation chosen before the gallery has started (for example a pre-selected attribute on page load) is compared with the server-rendered images instead of rebuilding every slide, and the gallery is no longer initialized twice, so the mobile sliders are built once.
* Reliability: A gallery error no longer breaks the product page or the Elementor editor. It is logged in WooCommerce > Status > Logs (source `kodlo-product-gallery-for-elementor`) and the WooCommerce product images are shown instead.
* Developers: New `kodlo_gallery_default_image_ids` filter for the images of the default gallery, the one shown before a variation is chosen.

= 2.9.4 =
* Usability: A variation that has its own image but no variation gallery now shows its image followed by the product gallery instead of a single image. Developers can turn this off with the `kodlo_gallery_variation_fallback_to_product_gallery` filter.
* Feature: The new Magnifier images setting applies the magnifier to all images (the default) or to the first image only, separately for desktop and mobile. The first image follows the gallery order and skips videos, including in looping sliders.
* Compatibility: Tested with WordPress 7.1, WooCommerce 11.2.2, Elementor 4.3.6 and Elementor Pro 4.3.6.

= 2.9.3 =
* Accessibility: While a gallery video plays, the gallery link that holds the hidden poster keeps an accessible name (the poster's alt text, or the "Product video player" label), so keyboard and screen-reader users no longer meet an unnamed link; the name is removed again when the poster returns.

= 2.9.2 =
* Live Preview: Fixed Playground setup permissions so Elementor Lightbox settings are applied reliably.
* Live Preview: Added a polished variable-product Playground demo with two selectable finishes, ten product photographs, one shared direct MP4 product video, a starter-free Elementor editing flow, a stable Kodlo gallery, and focused Lightbox controls without Zoom or Share.
* Lifecycle: Removes internal migration state, temporary locks, and administrator failure notices on uninstall while preserving authored gallery content.
* Security: Added a packaged vulnerability disclosure policy and documented the isolated Live Preview data boundary.
* Documentation: Expanded feature guidance, pre-install testing, dependency setup, privacy, deactivation, and permanent deletion behavior.
