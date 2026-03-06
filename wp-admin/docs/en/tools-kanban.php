<?php
/**
 * Docs: Tools - Kanban Board (EN)
 */
?>
<h1>Kanban Board</h1>
<p class="docs-lead">The <strong>Kanban Board</strong> is a visual project management tool built into the admin panel. Organize tasks, track leads, or manage any workflow using drag-and-drop cards across custom columns &mdash; with full activity history.</p>

<div class="docs-screenshot">
    <img src="<?php echo get_docs_asset_url('kanban-board.png'); ?>" alt="Kanban Board" onerror="this.style.display='none'">
    <p class="docs-caption">The Kanban Board &mdash; multiple boards, columns, and drag-and-drop cards.</p>
</div>

<hr class="docs-divider">

<h2>Key Concepts</h2>
<ul class="docs-list">
    <li><strong>Board</strong> &mdash; A top-level workspace (e.g. &ldquo;Marketing&rdquo;, &ldquo;Support Tickets&rdquo;). Displayed as tabs at the top. You can have multiple boards.</li>
    <li><strong>Column</strong> &mdash; A stage within a board (e.g. &ldquo;To Do&rdquo;, &ldquo;In Progress&rdquo;, &ldquo;Done&rdquo;).</li>
    <li><strong>Card</strong> &mdash; A single task or item within a column. Cards can be moved, edited, or deleted.</li>
</ul>

<hr class="docs-divider">

<h2>Managing Boards</h2>
<ol class="docs-steps">
    <li><strong>Create a Board</strong><p>Click <strong>New Board</strong> in the header toolbar. Enter a name and confirm to create it. The new board appears as a tab.</p></li>
    <li><strong>Switch Boards</strong><p>Click any board tab at the top to switch to it. The active board is clearly highlighted.</p></li>
    <li><strong>Delete a Board</strong><p>Each board tab has a <strong>&times;</strong> (delete) button. Click it and confirm to permanently delete the board and all its columns and cards.</p></li>
</ol>

<hr class="docs-divider">

<h2>Managing Columns</h2>
<ol class="docs-steps">
    <li><strong>Add a Column</strong><p>Click the <strong>Add Column</strong> button in the header toolbar (next to New Board). Enter a column name to create a new stage in the current board.</p></li>
    <li><strong>Rename a Column</strong><p>Click the pencil icon (<i class="fa fa-pencil"></i>) on a column header to rename it inline.</p></li>
    <li><strong>Delete a Column</strong><p>Click the trash icon (<i class="fa fa-trash"></i>) on a column header. Note: deleting a column will remove all cards inside it.</p></li>
</ol>

<hr class="docs-divider">

<h2>Managing Cards</h2>
<ol class="docs-steps">
    <li><strong>Create a Card</strong><p>Click the <strong>+ Add Card</strong> button inside any column. Enter a title and optional description to create the card.</p></li>
    <li><strong>Edit a Card</strong><p>Click the pencil icon on a card to open the edit modal. You can update the title, description, and label.</p></li>
    <li><strong>Delete a Card</strong><p>Click the trash icon on a card and confirm the deletion.</p></li>
    <li><strong>Move a Card</strong><p>Drag and drop a card to any column to move it. The movement is automatically recorded in the Activity Log.</p></li>
</ol>

<hr class="docs-divider">

<h2>Activity Log</h2>
<p>Every card movement (drag-and-drop) is automatically logged. The log records:</p>
<ul class="docs-list">
    <li>The <strong>card title</strong> that was moved.</li>
    <li>The <strong>source column</strong> (moved from).</li>
    <li>The <strong>destination column</strong> (moved to).</li>
    <li>The <strong>user</strong> who moved it.</li>
    <li>The <strong>timestamp</strong> of the action.</li>
</ul>
<p>Click the <strong>Activity</strong> button in the header toolbar to view the full history panel.</p>

<hr class="docs-divider">

<h2>Form Builder Integration</h2>
<p>The Kanban Board integrates with the <strong>Form Builder</strong>. When a form has Kanban integration configured, every new form submission automatically creates a card in the specified column &mdash; the card title will contain the submitter's name and submission details.</p>

<div class="docs-info">
    <span class="dashicons dashicons-info"></span>
    <div>
        <strong>Use Case:</strong> Create a &ldquo;New Leads&rdquo; column on your board and connect your Contact Form to it. Every time someone submits the form, a card appears automatically in your &ldquo;New Leads&rdquo; column, ready to be tracked.
    </div>
</div>
