<?php
/**
 * Docs: Pages - GrapesJS Builder (EN)
 */
$base_path = '../docs/doc-files/grapesjs-widget/';
?>
<h1>GrapesJS Page Builder</h1>
<p class="docs-lead"><strong>GrapesJS</strong> is a visual drag &amp; drop page editor that lets you create rich page designs without writing code. Simply drag widgets onto the canvas and arrange them visually.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('builder-grapesjs.png'); ?>" alt="GrapesJS Editor" onerror="this.style.display='none'">
    <p class="docs-caption">The GrapesJS Editor interface.</p>
</div>

<hr class="docs-divider">

<h2>How to Use GrapesJS</h2>
<ol class="docs-steps">
    <li>
        <strong>Open the Editor</strong>
        <p>Create a new page and select <strong>GrapesJS</strong> as the builder, or edit an existing GrapesJS page.</p>
    </li>
    <li>
        <strong>Add Widgets</strong>
        <p>Click the <em>Blocks/Widgets</em> panel on the side of the editor, then drag the desired widget onto the canvas.</p>
    </li>
    <li>
        <strong>Edit Widget Content</strong>
        <p>Double-click on a widget on the canvas to enter edit mode and change the text or content inside.</p>
    </li>
    <li>
        <strong>Set Styles</strong>
        <p>Select a widget on the canvas, then use the <em>Style</em> panel on the right to set padding, margin, color, font, and other CSS properties.</p>
    </li>
    <li>
        <strong>Save the Page</strong>
        <p>Click the <strong>Save</strong> button in the top toolbar to save your page changes.</p>
    </li>
</ol>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('grapesjs-complete-widget.png'); ?>" alt="All GrapesJS Widgets" onerror="this.style.display='none'">
    <p class="docs-caption">The complete GrapesJS widget panel.</p>
</div>

<hr class="docs-divider">

<h2>Widget List</h2>
<p>Widgets are organized into 3 groups: <strong>Basic</strong>, <strong>Forms</strong>, and <strong>Sections</strong>.</p>

<!-- GROUP 1: BASIC -->
<h3>Group 1: Basic</h3>
<table class="docs-table docs-widget-table">
    <thead>
        <tr>
            <th style="width:80px">Icon</th>
            <th>Widget</th>
            <th>Function</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-1-column.png" alt="1 Column" onerror="this.style.display='none'"></td>
            <td><strong>1 Column</strong></td>
            <td>A single-column layout block, ideal for full-width content.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-2-columns.png" alt="2 Columns" onerror="this.style.display='none'"></td>
            <td><strong>2 Columns</strong></td>
            <td>Splits the area into two equal-width columns (50/50).</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-2-columns-3per4.png" alt="2 Columns 3/4" onerror="this.style.display='none'"></td>
            <td><strong>2 Columns 3/4</strong></td>
            <td>Two columns with a 3/4 and 1/4 ratio, ideal for content + sidebar layouts.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-3-columns.png" alt="3 Columns" onerror="this.style.display='none'"></td>
            <td><strong>3 Columns</strong></td>
            <td>Splits the area into three equal-width columns.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-text.png" alt="Text" onerror="this.style.display='none'"></td>
            <td><strong>Text</strong></td>
            <td>A block for adding plain text or paragraphs to the page.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-text-selection.png" alt="Text Selection" onerror="this.style.display='none'"></td>
            <td><strong>Text Selection</strong></td>
            <td>A text block with customizable style options (heading, paragraph).</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-image.png" alt="Image" onerror="this.style.display='none'"></td>
            <td><strong>Image</strong></td>
            <td>Inserts an image from the media library or an external URL.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-video.png" alt="Video" onerror="this.style.display='none'"></td>
            <td><strong>Video</strong></td>
            <td>Embeds a video from YouTube, Vimeo, or a direct video file.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-link.png" alt="Link" onerror="this.style.display='none'"></td>
            <td><strong>Link</strong></td>
            <td>Adds text with a hyperlink to a specific URL.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-link-block.png" alt="Link Block" onerror="this.style.display='none'"></td>
            <td><strong>Link Block</strong></td>
            <td>A content block that is entirely clickable as a single link.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-map.png" alt="Map" onerror="this.style.display='none'"></td>
            <td><strong>Map</strong></td>
            <td>Embeds an interactive Google Maps map on the page.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-divider.png" alt="Divider" onerror="this.style.display='none'"></td>
            <td><strong>Divider</strong></td>
            <td>A horizontal line to separate content sections.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-spacer.png" alt="Spacer" onerror="this.style.display='none'"></td>
            <td><strong>Spacer</strong></td>
            <td>Vertical whitespace to add spacing between elements.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-list.png" alt="List" onerror="this.style.display='none'"></td>
            <td><strong>List</strong></td>
            <td>A bullet list or numbered list.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-quotes.png" alt="Quotes" onerror="this.style.display='none'"></td>
            <td><strong>Quotes</strong></td>
            <td>A blockquote with a prominent style.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-social-icons.png" alt="Social Icons" onerror="this.style.display='none'"></td>
            <td><strong>Social Icons</strong></td>
            <td>A set of social media icons (Instagram, Facebook, Twitter, etc.) with links.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>basic-progress-bar.png" alt="Progress Bar" onerror="this.style.display='none'"></td>
            <td><strong>Progress Bar</strong></td>
            <td>A progress bar to show a percentage or visual achievement.</td>
        </tr>
    </tbody>
</table>

<!-- GROUP 2: FORMS -->
<h3>Group 2: Forms</h3>
<table class="docs-table docs-widget-table">
    <thead>
        <tr>
            <th style="width:80px">Icon</th>
            <th>Widget</th>
            <th>Function</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-form.png" alt="Form" onerror="this.style.display='none'"></td>
            <td><strong>Form</strong></td>
            <td>The main form container. Place other form elements inside it.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-input.png" alt="Input" onerror="this.style.display='none'"></td>
            <td><strong>Input</strong></td>
            <td>A single-line text field for data input like name or email.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-text-area.png" alt="Textarea" onerror="this.style.display='none'"></td>
            <td><strong>Textarea</strong></td>
            <td>A multi-line text area for long messages or comments.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-select.png" alt="Select" onerror="this.style.display='none'"></td>
            <td><strong>Select</strong></td>
            <td>A dropdown (combobox) to select one option from a list.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-checkbox.png" alt="Checkbox" onerror="this.style.display='none'"></td>
            <td><strong>Checkbox</strong></td>
            <td>A checkbox to select one or multiple options simultaneously.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-radio.png" alt="Radio" onerror="this.style.display='none'"></td>
            <td><strong>Radio</strong></td>
            <td>A radio button to select a single option from several choices.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-label.png" alt="Label" onerror="this.style.display='none'"></td>
            <td><strong>Label</strong></td>
            <td>A label text associated with a form element to describe its input.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>forms-button.png" alt="Button" onerror="this.style.display='none'"></td>
            <td><strong>Button</strong></td>
            <td>A submit button or action button within a form.</td>
        </tr>
    </tbody>
</table>

<!-- GROUP 3: SECTIONS -->
<h3>Group 3: Sections</h3>
<table class="docs-table docs-widget-table">
    <thead>
        <tr>
            <th style="width:80px">Icon</th>
            <th>Widget</th>
            <th>Function</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-hero-section.png" alt="Hero Section" onerror="this.style.display='none'"></td>
            <td><strong>Hero Section</strong></td>
            <td>A homepage hero section with a background image, large title, and CTA button.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-hero-2-column.png" alt="Hero 2 Column" onerror="this.style.display='none'"></td>
            <td><strong>Hero 2 Column</strong></td>
            <td>A two-column hero: one for text/title, one for an image or illustration.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-navbar.png" alt="Navbar" onerror="this.style.display='none'"></td>
            <td><strong>Navbar</strong></td>
            <td>A horizontal navigation bar with a logo and menu links.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-navbar-responsive.png" alt="Navbar Responsive" onerror="this.style.display='none'"></td>
            <td><strong>Navbar Responsive</strong></td>
            <td>A responsive navigation with a hamburger menu for mobile views.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-dynamic-navbar.png" alt="Dynamic Navbar" onerror="this.style.display='none'"></td>
            <td><strong>Dynamic Navbar</strong></td>
            <td>A dynamic navigation that can change its appearance on page scroll.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-call-to-action.png" alt="Call to Action" onerror="this.style.display='none'"></td>
            <td><strong>Call to Action</strong></td>
            <td>A CTA section with promotional text and a prominent action button.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-card.png" alt="Card" onerror="this.style.display='none'"></td>
            <td><strong>Card</strong></td>
            <td>A content card with an image, title, description, and action link.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-feature-gris.png" alt="Feature Grid" onerror="this.style.display='none'"></td>
            <td><strong>Feature Grid</strong></td>
            <td>A features/services grid with an icon, title, and short description per item.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-gallery-3x2.png" alt="Gallery 3x2" onerror="this.style.display='none'"></td>
            <td><strong>Gallery 3x2</strong></td>
            <td>A 3-column x 2-row photo gallery grid for displaying image collections.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-carousel.png" alt="Carousel" onerror="this.style.display='none'"></td>
            <td><strong>Carousel</strong></td>
            <td>An image or content slider that cycles automatically or manually.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-pricing-table.png" alt="Pricing Table" onerror="this.style.display='none'"></td>
            <td><strong>Pricing Table</strong></td>
            <td>A plan/service pricing comparison table with features per tier.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-testimonial.png" alt="Testimonial" onerror="this.style.display='none'"></td>
            <td><strong>Testimonial</strong></td>
            <td>A customer testimonial section with photo, name, and quote.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-reviews.png" alt="Reviews" onerror="this.style.display='none'"></td>
            <td><strong>Reviews</strong></td>
            <td>A review display with star ratings, reviewer name, and comment.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-tabs.png" alt="Tabs" onerror="this.style.display='none'"></td>
            <td><strong>Tabs</strong></td>
            <td>Tabbed content that can switch between several different content panels.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-icon-list.png" alt="Icon List" onerror="this.style.display='none'"></td>
            <td><strong>Icon List</strong></td>
            <td>A bullet list with an icon to the left of each item.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-media-list.png" alt="Media List" onerror="this.style.display='none'"></td>
            <td><strong>Media List</strong></td>
            <td>A content list with thumbnail images and descriptive text alongside.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-search-bar.png" alt="Search Bar" onerror="this.style.display='none'"></td>
            <td><strong>Search Bar</strong></td>
            <td>A search box to integrate on a site page.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-countdown.png" alt="Countdown" onerror="this.style.display='none'"></td>
            <td><strong>Countdown</strong></td>
            <td>A countdown timer towards a specific date/time.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-typed.png" alt="Typed" onerror="this.style.display='none'"></td>
            <td><strong>Typed</strong></td>
            <td>An animated typing text effect that cycles through different strings.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-post-grid.png" alt="Post Grid" onerror="this.style.display='none'"></td>
            <td><strong>Post Grid</strong></td>
            <td>A grid of blog post content displayed dynamically.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-offcanvas.png" alt="Offcanvas" onerror="this.style.display='none'"></td>
            <td><strong>Offcanvas</strong></td>
            <td>A side panel that slides in from the left/right of the screen when clicked.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-tooltip.png" alt="Tooltip" onerror="this.style.display='none'"></td>
            <td><strong>Tooltip</strong></td>
            <td>A short descriptive text that appears when hovering over an element.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-styled-form.png" alt="Styled Form" onerror="this.style.display='none'"></td>
            <td><strong>Styled Form</strong></td>
            <td>A ready-to-use contact form with a professional design.</td>
        </tr>
        <tr>
            <td><img src="<?php echo $base_path; ?>sections-custom-code.png" alt="Custom Code" onerror="this.style.display='none'"></td>
            <td><strong>Custom Code</strong></td>
            <td>Inserts custom HTML/CSS/JS code directly into the page.</td>
        </tr>
    </tbody>
</table>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> Use the <strong>Custom Code</strong> widget if you need to insert third-party scripts or embeds (such as Mailchimp forms, chat widgets, or tracking pixels) into the page.
    </div>
</div>
