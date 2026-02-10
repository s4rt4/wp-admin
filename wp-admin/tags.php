<?php
require_once 'auth_check.php';
$page_title = 'Tags';
require_once 'header.php';
require_once 'sidebar.php';
require_once 'db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Add/Update Tag
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_tag'])) {
    $name = trim($_POST['tag-name']);
    $slug = trim($_POST['tag-slug']);
    $description = trim($_POST['tag-description']);
    $id = isset($_POST['tag-id']) ? intval($_POST['tag-id']) : 0;

    if (!empty($name)) {
        if (empty($slug)) {
            $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));
        } else {
            $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug));
        }

        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE tags SET name=?, slug=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $slug, $id);
            if ($stmt->execute()) {
                echo "<div class='notice notice-success is-dismissible'><p>Tag updated.</p></div>";
            } else {
                echo "<div class='notice notice-error is-dismissible'><p>Error updating tag: " . $conn->error . "</p></div>";
            }
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO tags (name, slug) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $slug);
            if ($stmt->execute()) {
                echo "<div class='notice notice-success is-dismissible'><p>Tag added.</p></div>";
            } else {
                echo "<div class='notice notice-error is-dismissible'><p>Error adding tag: " . $conn->error . "</p></div>";
            }
        }
    }
}

// Handle Delete Tag
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM tags WHERE id = $id");
    echo "<script>window.location.href='tags.php';</script>";
}

// Handle Edit Fetch
$edit_tag = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result_edit = $conn->query("SELECT * FROM tags WHERE id = $id");
    if ($result_edit->num_rows > 0) {
        $edit_tag = $result_edit->fetch_assoc();
    }
}

// Fetch Tags
$result = $conn->query("SELECT * FROM tags ORDER BY name ASC");
?>

<div id="wpcontent">
    <div class="wrap">
        <h1 class="wp-heading-inline">Tags</h1>
        <hr class="wp-header-end">

        <div id="col-container" style="display: flex; gap: 20px; margin-top: 20px;">
            
            <!-- Left Column: Add/Edit Tag -->
            <div id="col-left" style="width: 35%;">
                <div class="form-wrap">
                    <h2><?php echo $edit_tag ? 'Edit Tag' : 'Add New Tag'; ?></h2>
                    <form id="addtag" method="post" action="tags.php" class="validate">
                        <?php if ($edit_tag): ?>
                            <input type="hidden" name="tag-id" value="<?php echo $edit_tag['id']; ?>">
                        <?php endif; ?>

                        <div class="form-field form-required term-name-wrap">
                            <label for="tag-name">Name</label>
                            <input name="tag-name" id="tag-name" type="text" value="<?php echo $edit_tag ? htmlspecialchars($edit_tag['name']) : ''; ?>" size="40" aria-required="true" required>
                            <p>The name is how it appears on your site.</p>
                        </div>
                        <div class="form-field term-slug-wrap">
                            <label for="tag-slug">Slug</label>
                            <input name="tag-slug" id="tag-slug" type="text" value="<?php echo $edit_tag ? htmlspecialchars($edit_tag['slug']) : ''; ?>" size="40">
                            <p>The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</p>
                        </div>
                        <div class="form-field term-description-wrap">
                            <label for="tag-description">Description</label>
                            <textarea name="tag-description" id="tag-description" rows="5" cols="40"></textarea>
                            <p>The description is not prominent by default; however, some themes may show it.</p>
                        </div>
                        <p class="submit">
                            <input type="submit" name="submit_tag" id="submit" class="button button-primary" value="<?php echo $edit_tag ? 'Update Tag' : 'Add New Tag'; ?>">
                            <?php if ($edit_tag): ?>
                                <a href="tags.php" class="button">Cancel</a>
                            <?php endif; ?>
                        </p>
                    </form>
                </div>
            </div>

            <!-- Right Column: Tag List -->
            <div id="col-right" style="width: 65%;">
                <table class="wp-list-table widefat fixed striped tags">
                    <thead>
                        <tr>
                            <th id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"></th>
                            <th scope="col" class="manage-column column-name column-primary sortable desc"><span>Name</span></th>
                            <th scope="col" class="manage-column column-slug">Slug</th>
                            <th scope="col" class="manage-column column-posts num sortable desc"><span>Count</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="iedit level-0">
                                    <td class="check-column"><input type="checkbox" name="delete_tags[]" value="<?php echo $row['id']; ?>"></td>
                                    <td class="name column-name has-row-actions column-primary" data-colname="Name">
                                        <strong><a class="row-title" href="tags.php?action=edit&id=<?php echo $row['id']; ?>" aria-label="Edit “<?php echo htmlspecialchars($row['name']); ?>”"><?php echo htmlspecialchars($row['name']); ?></a></strong>
                                        <div class="row-actions">
                                            <span class="edit"><a href="tags.php?action=edit&id=<?php echo $row['id']; ?>" aria-label="Edit “<?php echo htmlspecialchars($row['name']); ?>”">Edit</a> | </span>
                                            <span class="delete"><a href="tags.php?action=delete&id=<?php echo $row['id']; ?>" class="delete-tag aria-button-if-js" aria-label="Delete “<?php echo htmlspecialchars($row['name']); ?>”" role="button" onclick="return confirm('Are you sure?')">Delete</a></span>
                                        </div>
                                    </td>
                                    <td class="slug column-slug" data-colname="Slug"><?php echo htmlspecialchars($row['slug']); ?></td>
                                    <td class="posts column-posts" data-colname="Count"><a href="#">0</a></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- Custom Admin Styles -->
<style>
    /* Form Styles */
    .form-wrap { background: #fff; padding: 10px 20px 20px; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
    .form-field { margin-bottom: 20px; }
    .form-field label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 5px; }
    .form-field input[type="text"], .form-field textarea { width: 100%; padding: 6px; border: 1px solid #8c8f94; border-radius: 4px; font-size: 14px; }
    .form-field p { font-size: 13px; color: #646970; margin: 5px 0 0; font-style: italic; }
    
    /* Table Styles */
    .dataTables_wrapper { background: #fff; padding: 10px; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
    table.dataTable.no-footer { border-bottom: 1px solid #c3c4c7; }
    .row-actions { visibility: hidden; font-size: 12px; }
    tr:hover .row-actions { visibility: visible; }
    .button-primary { background: #2271b1; border-color: #2271b1; color: #fff; text-decoration: none; text-shadow: none; padding: 4px 12px; font-size: 13px; line-height: 2.15384615; min-height: 30px; border-radius: 3px; cursor: pointer; border-width: 1px; border-style: solid; }
    .button-primary:hover { background: #135e96; border-color: #135e96; color: #fff; }
</style>

<!-- jQuery & DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('.wp-list-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pageLength": 20,
            "columnDefs": [
                { "orderable": false, "targets": 0 }
            ],
            "language": {
                "search": "Search Tags:",
                "lengthMenu": "Show _MENU_ items"
            }
        });


        // Auto-generate Slug
        let slugChanged = false;
        $('#tag-slug').on('input', function() {
            slugChanged = true;
        });

        $('#tag-name').on('input', function() {
            if (!slugChanged) {
                let name = $(this).val();
                let slug = name.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '') // Remove invalid chars
                    .replace(/\s+/g, '-')         // Replace spaces with -
                    .replace(/-+/g, '-');         // Collapse dashes
                $('#tag-slug').val(slug);
            }
        });
    });
</script>

<?php require_once 'footer.php'; ?>
