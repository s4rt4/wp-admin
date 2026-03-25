<!DOCTYPE html>
<html lang="en">
<head>
<?php require_once __DIR__ . '/db_config.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' &lsaquo; ' . htmlspecialchars(get_option('blogname', get_option('site_title', 'Admin'))) : 'Admin Dashboard'; ?></title>
<?php
$_fav = get_option('site_favicon', '');
if ($_fav): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($_fav); ?>">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($_fav); ?>">
<?php
endif; ?>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="colors.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
    <script>if(localStorage.getItem('wp_dark_mode')==='true')document.documentElement.classList.add('dark-mode');</script>

    <!-- Spotlight / Global Search (Ctrl+K) -->
    <style>
    #spotlight-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999999;align-items:flex-start;justify-content:center;padding-top:15vh;}
    #spotlight-overlay.open{display:flex;}
    #spotlight-box{background:#fff;width:560px;max-width:92vw;border-radius:10px;box-shadow:0 16px 48px rgba(0,0,0,.3);overflow:hidden;}
    #spotlight-input{width:100%;padding:14px 18px 14px 44px;border:none;font-size:16px;outline:none;background:transparent;color:#1d2327;}
    #spotlight-header{position:relative;border-bottom:1px solid #e0e0e0;}
    #spotlight-header i{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:#9ca3ae;font-size:16px;}
    #spotlight-results{max-height:340px;overflow-y:auto;padding:6px 0;}
    .sp-item{display:flex;align-items:center;gap:10px;padding:9px 18px;cursor:pointer;font-size:13px;color:#1d2327;text-decoration:none;transition:background .08s;}
    .sp-item:hover,.sp-item.active{background:#f0f6fc;color:#0073aa;}
    .sp-item i{width:20px;text-align:center;color:#9ca3ae;font-size:14px;flex-shrink:0;}
    .sp-item:hover i,.sp-item.active i{color:#0073aa;}
    .sp-item .sp-label{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .sp-item .sp-hint{font-size:11px;color:#9ca3ae;flex-shrink:0;}
    .sp-section{padding:6px 18px 3px;font-size:10px;font-weight:700;text-transform:uppercase;color:#9ca3ae;letter-spacing:.5px;}
    #spotlight-footer{padding:6px 14px;border-top:1px solid #e0e0e0;font-size:11px;color:#9ca3ae;display:flex;gap:12px;}
    #spotlight-footer kbd{background:#f0f0f1;border:1px solid #ddd;border-radius:3px;padding:1px 5px;font-size:10px;}
    html.dark-mode #spotlight-box{background:#2c3338;}
    html.dark-mode #spotlight-input{color:#e0e2e4;}
    html.dark-mode #spotlight-header{border-bottom-color:#404952;}
    html.dark-mode .sp-item{color:#c3c4c7;}
    html.dark-mode .sp-item:hover,html.dark-mode .sp-item.active{background:#3c434a;color:#72aee6;}
    html.dark-mode #spotlight-footer{border-top-color:#404952;}
    html.dark-mode #spotlight-footer kbd{background:#1a1d21;border-color:#404952;color:#c3c4c7;}
    </style>
    <div id="spotlight-overlay">
        <div id="spotlight-box">
            <div id="spotlight-header">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="spotlight-input" placeholder="Search posts, pages, users, actions..." autocomplete="off">
            </div>
            <div id="spotlight-results"></div>
            <div id="spotlight-footer">
                <span><kbd>↑↓</kbd> Navigate</span>
                <span><kbd>Enter</kbd> Open</span>
                <span><kbd>Esc</kbd> Close</span>
            </div>
        </div>
    </div>
    <script>
    (function(){
        var overlay=document.getElementById('spotlight-overlay'),input=document.getElementById('spotlight-input'),results=document.getElementById('spotlight-results');
        var items=[
            {label:'Dashboard',icon:'fa-gauge-high',url:'index.php',section:'Pages'},
            {label:'New Post',icon:'fa-plus',url:'post-new.php',section:'Actions'},
            {label:'New Page',icon:'fa-plus',url:'builder.php',section:'Actions'},
            {label:'All Posts',icon:'fa-newspaper',url:'posts.php',section:'Pages'},
            {label:'All Pages',icon:'fa-file-lines',url:'pages.php',section:'Pages'},
            {label:'Media Library',icon:'fa-images',url:'media.php',section:'Pages'},
            {label:'Comments',icon:'fa-comments',url:'comments.php',section:'Pages'},
            {label:'Calendar',icon:'fa-calendar-days',url:'calendar.php',section:'Pages'},
            {label:'Kanban Board',icon:'fa-columns',url:'kanban.php',section:'Pages'},
            {label:'Analytics',icon:'fa-chart-line',url:'analytics.php',section:'Pages'},
            {label:'Form Builder',icon:'fa-rectangle-list',url:'form-builder.php',section:'Pages'},
            {label:'Users',icon:'fa-users',url:'users.php',section:'Pages'},
            {label:'Messages',icon:'fa-envelope',url:'messages.php',section:'Pages'},
            {label:'Plugins',icon:'fa-plug',url:'plugins.php',section:'Pages'},
            {label:'Menus',icon:'fa-bars',url:'menus.php',section:'Pages'},
            {label:'Customize',icon:'fa-palette',url:'themes.php',section:'Pages'},
            {label:'General Settings',icon:'fa-gear',url:'settings-general.php',section:'Settings'},
            {label:'SMTP Email',icon:'fa-envelope-open-text',url:'settings-smtp.php',section:'Settings'},
            {label:'Redirects',icon:'fa-arrow-right-arrow-left',url:'redirects.php',section:'Pages'},
            {label:'Audit Log',icon:'fa-shield-halved',url:'audit-log.php',section:'Pages'},
            {label:'Updates',icon:'fa-arrows-rotate',url:'update.php',section:'Settings'},
            {label:'Security Headers',icon:'fa-lock',url:'security.php',section:'Settings'},
            {label:'File Integrity',icon:'fa-fingerprint',url:'integrity.php',section:'Settings'},
            {label:'Login Security',icon:'fa-user-shield',url:'login-security.php',section:'Settings'},
            {label:'Data Explorer',icon:'fa-database',url:'data-explorer.php',section:'Pages'},
            {label:'Bulk SEO Editor',icon:'fa-magnifying-glass-chart',url:'seo-editor.php',section:'Pages'},
            {label:'Documentation',icon:'fa-book',url:'docs.php',section:'Pages'},
        ];
        var activeIdx=-1;

        function open(){overlay.classList.add('open');input.value='';input.focus();render('');activeIdx=-1;}
        function close(){overlay.classList.remove('open');}

        function render(q){
            q=q.toLowerCase();
            var filtered=q?items.filter(function(i){return i.label.toLowerCase().indexOf(q)>-1||i.section.toLowerCase().indexOf(q)>-1;}):items;
            var sections={};
            filtered.forEach(function(i){if(!sections[i.section])sections[i.section]=[];sections[i.section].push(i);});
            var html='';
            Object.keys(sections).forEach(function(s){
                html+='<div class="sp-section">'+s+'</div>';
                sections[s].forEach(function(i,idx){
                    html+='<a class="sp-item" href="'+i.url+'"><i class="fa-solid '+i.icon+'"></i><span class="sp-label">'+i.label+'</span></a>';
                });
            });
            if(!html)html='<div style="padding:20px;text-align:center;color:#999;font-size:13px;">No results found.</div>';
            results.innerHTML=html;
            activeIdx=-1;
        }

        function navigate(dir){
            var els=results.querySelectorAll('.sp-item');
            if(!els.length)return;
            if(activeIdx>=0&&els[activeIdx])els[activeIdx].classList.remove('active');
            activeIdx+=dir;
            if(activeIdx<0)activeIdx=els.length-1;
            if(activeIdx>=els.length)activeIdx=0;
            els[activeIdx].classList.add('active');
            els[activeIdx].scrollIntoView({block:'nearest'});
        }

        document.addEventListener('keydown',function(e){
            if((e.ctrlKey||e.metaKey)&&e.key==='k'){e.preventDefault();overlay.classList.contains('open')?close():open();}
            if(e.key==='Escape'&&overlay.classList.contains('open'))close();
            if(overlay.classList.contains('open')){
                if(e.key==='ArrowDown'){e.preventDefault();navigate(1);}
                if(e.key==='ArrowUp'){e.preventDefault();navigate(-1);}
                if(e.key==='Enter'){e.preventDefault();var els=results.querySelectorAll('.sp-item');if(activeIdx>=0&&els[activeIdx])window.location.href=els[activeIdx].href;}
            }
        });
        input.addEventListener('input',function(){render(this.value);});
        overlay.addEventListener('click',function(e){if(e.target===overlay)close();});
    })();
    </script>
</head>
<?php
// Get User Color Scheme, default to 'fresh'
$admin_color = get_option('admin_color_scheme', 'fresh');
?>
<body class="wp-admin admin-color-<?php echo htmlspecialchars($admin_color); ?>">
    <!-- Top Admin Bar (Simplified) -->
    <?php require_once 'topbar.php'; ?>

    <div class="content-wrapper">
