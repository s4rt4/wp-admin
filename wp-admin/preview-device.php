<?php
require_once 'auth_check.php';
require_once 'db_config.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    die("Invalid Page Slug");
}

// Fetch Page Title for display
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT title FROM pages WHERE slug = ?");
$stmt->execute([$slug]);
$page = $stmt->fetch();
$pageTitle = $page ? $page['title'] : 'Preview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview - <?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 0; overflow: hidden; display: flex; flex-direction: column; height: 100vh; background: #2d2d2d; font-family: sans-serif; }
        
        /* Toolbar */
        .preview-toolbar {
            height: 50px;
            background: #2d2d2d;
            border-bottom: 1px solid #3c3c3c;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            color: #fff;
            position: relative;
        }
        
        .title { position: absolute; left: 20px; font-size: 14px; color: #ccc; font-weight: 500; }
        .close-btn { position: absolute; right: 20px; color: #ccc; text-decoration: none; font-size: 14px; }
        .close-btn:hover { color: #fff; }

        .btn-device {
            background: #3c3c3c;
            border: 1px solid #555;
            color: #ccc;
            width: 40px;
            height: 32px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .btn-device:hover { background: #4c4c4c; color: #fff; }
        .btn-device.active { background: #007acc; color: #fff; border-color: #007acc; }
        
        .btn-rotate {
            background: transparent;
            border: none;
            color: #aaa;
            cursor: pointer;
            margin-left: 10px;
        }
        .btn-rotate:hover { color: #fff; }

        /* Preview Area */
        .preview-area {
            flex: 1;
            background: #1e1e1e; /* Dark background behind frame */
            display: flex;
            justify-content: center;
            align-items: center; /* Center horizontally and vertically */
            overflow: hidden; /* Hide scrollbars of container in desktop mode */
            position: relative;
        }
        
        iframe {
            background: #fff;
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            transition: all 0.3s ease;
        }
        
        /* Device Sizes */
        iframe.desktop { 
            width: 100%; 
            height: 100%; 
            border-radius: 0; 
            box-shadow: none;
        }
        
        iframe.tablet { 
            width: 768px; 
            height: 1024px; 
            max-height: calc(100% - 40px); /* Leave space for margins */
            border-radius: 8px; 
            margin: 20px;
        }
        
        iframe.mobile { 
            width: 375px; 
            height: 667px; 
            max-height: calc(100% - 40px); 
            border-radius: 8px; 
            margin: 20px;
        }
        
    </style>
</head>
<body>

    <div class="preview-toolbar">
        <div class="title">Previewing: <?php echo htmlspecialchars($pageTitle); ?></div>
        
        <button class="btn-device active" onclick="setMode('desktop')" title="Desktop">
            <i class="fa-solid fa-desktop"></i>
        </button>
        <button class="btn-device" onclick="setMode('tablet')" title="Tablet (768px)">
            <i class="fa-solid fa-tablet-screen-button"></i>
        </button>
        <button class="btn-device" onclick="setMode('mobile')" title="Mobile (375px)">
            <i class="fa-solid fa-mobile-screen-button"></i>
        </button>
        
        <button class="btn-rotate" onclick="reloadFrame()" title="Refresh">
            <i class="fa-solid fa-rotate-right"></i>
        </button>

        <a href="#" class="close-btn" onclick="window.close()">Close</a>
    </div>

    <div class="preview-area">
        <iframe id="frame" class="desktop" src="../view.php?slug=<?php echo htmlspecialchars($slug); ?>&preview=true"></iframe>
    </div>

    <script>
        function setMode(mode) {
            const frame = document.getElementById('frame');
            
            // Buttons
            document.querySelectorAll('.btn-device').forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active'); // Assumes click comes from button
            
            // Frame
            frame.className = mode;
        }

        function reloadFrame() {
            const frame = document.getElementById('frame');
            frame.src = frame.src;
        }
        
        // Optional: Auto-detect button state via class check if needed, but handled by click.
    </script>
</body>
</html>
