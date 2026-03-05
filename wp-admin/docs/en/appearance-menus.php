<?php
/**
 * Docs: Appearance - Menus (EN)
 */
?>
<h1>Navigation Menu Settings</h1>
<p class="docs-lead">The <strong>Appearance &rarr; Menus</strong> page allows you to create and manage the navigation menus displayed on your site, such as the header menu, footer menu, or sidebar.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('menus-pages.png'); ?>" alt="Menu Settings" onerror="this.style.display='none'">
    <p class="docs-caption">The site navigation menu settings page.</p>
</div>

<hr class="docs-divider">

<h2>How to Create a New Menu</h2>
<ol class="docs-steps">
    <li>
        <strong>Open Menu Settings</strong>
        <p>Click <strong>Appearance &rarr; Menus</strong> in the admin sidebar.</p>
    </li>
    <li>
        <strong>Name the Menu</strong>
        <p>At the top, fill in the <em>Menu Name</em> field with a recognizable name (e.g., "Main Menu", "Footer Menu").</p>
    </li>
    <li>
        <strong>Add Menu Items</strong>
        <p>In the left panel, choose items to add to the menu:</p>
        <ul class="docs-list">
            <li><strong>Pages</strong> — Select from the list of existing pages.</li>
            <li><strong>Posts</strong> — Choose articles from the post list.</li>
            <li><strong>Custom Links</strong> — Manually enter a URL and link name.</li>
            <li><strong>Categories</strong> — Add a link to a category archive page.</li>
        </ul>
        <p>Click <strong>Add to Menu</strong> after selecting.</p>
    </li>
    <li>
        <strong>Arrange Order &amp; Hierarchy</strong>
        <p>Drag and drop menu items in the right panel to change their order. Drag to the right to create a sub-menu (dropdown).</p>
    </li>
    <li>
        <strong>Assign Menu Location</strong>
        <p>In the <em>Menu Settings</em> section, check the location where this menu will be displayed (Header, Footer, etc.).</p>
    </li>
    <li>
        <strong>Save the Menu</strong>
        <p>Click the <strong>Save Menu</strong> button.</p>
    </li>
</ol>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> Use <em>Custom Links</em> to add links to external sites or pages not yet created in the admin, such as links to social media profiles.
    </div>
</div>

<hr class="docs-divider">

<h2>Menu Shortcodes</h2>
<p>Every menu you create automatically generates a <strong>Shortcode</strong>. You can copy this shortcode and paste it anywhere on your site, such as inside posts, pages (including Page Builders), or widgets, to instantly display the menu.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('menu-shortcodes.png'); ?>" alt="Menu Shortcodes" onerror="this.style.display='none'">
    <p class="docs-caption">Shortcode column in the menu settings list.</p>
</div>

<div class="docs-tip">
    <span class="dashicons dashicons-lightbulb"></span>
    <div>
        <strong>Tip:</strong> The use of shortcodes is incredibly <em>powerful</em>. For more advanced shortcode usage utilizing custom code, check the <a href="docs.php?topic=tools-snippets&lang=en">Tools &rarr; Snippets</a> section.
    </div>
</div>
