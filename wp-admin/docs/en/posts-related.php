<h2>Related Posts</h2>

<p>The Related Posts feature lets you manually select articles that are related to the current post. These can be displayed on the frontend as reading suggestions.</p>

<h3>How to Use</h3>
<ol>
    <li>Open a post for editing.</li>
    <li>In the sidebar, find the <strong>Related Posts</strong> metabox (below Custom Fields).</li>
    <li>Use the dropdown to select a post you want to link as related.</li>
    <li>The selected post appears as a tag with an × button to remove it.</li>
    <li>Add as many related posts as needed.</li>
    <li>Click <strong>Update</strong> (or <strong>Publish</strong>) to save.</li>
</ol>

<h3>How It Works</h3>
<ul>
    <li>Related posts are stored in the <code>post_relations</code> table, linking the current post to each related post with a sort order.</li>
    <li>Duplicate selections are automatically prevented.</li>
    <li>The dropdown lists all posts (except the current one and trashed posts).</li>
</ul>

<h3>Frontend Display</h3>
<p>Related posts can be queried from the <code>post_relations</code> table and displayed in the frontend template as a "Related Articles" section below the post content.</p>
