<?php
session_start();
$page_title = "動漫聖地巡禮地圖";

// Include Leaflet CSS and JS in extra_css and extra_js logic if needed, 
// but here I'll just put them in the head via $extra_css.
// Include Leaflet CSS and JS
// IMPORTANCE: leaflet.js must be loaded BEFORE any plugins like markercluster
$extra_css = '
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <link rel="stylesheet" href="css/common.css?v=' . time() . '">
    <link rel="stylesheet" href="css/dark_common.css?v=' . time() . '">
    <link rel="stylesheet" href="css/anime_map.css?v=' . time() . '">
';

require_once("../DB/DB_open.php");
require_once("inc/header.php");

// Fetch all songs for map selection
$all_songs_json = "[]";
// Modified query: removed 'cover_path' which does not exist. trying 'file_path' or just basics.
$sql_s = "SELECT id, title, artist, file_path FROM songs ORDER BY title ASC";
$res_s = mysqli_query($link, $sql_s);

if ($res_s) {
    if (mysqli_num_rows($res_s) > 0) {
        $songs_arr = [];
        while ($row_s = mysqli_fetch_assoc($res_s)) {
            // Use get_cover.php helper to fetch cover image by song ID
            $songs_arr[] = [
                'id' => $row_s['id'],
                'title' => $row_s['title'],
                'artist' => $row_s['artist'],
                'cover' => 'get_cover.php?id=' . $row_s['id']
            ];
        }
        $all_songs_json = json_encode($songs_arr);
    } else {
        echo "<script>console.warn('Map: No songs found in database.');</script>";
    }
} else {
    // Safely encode error to avoid JS syntax errors
    $err = json_encode(mysqli_error($link));
    echo "<script>console.error('Map: Song fetch error:', $err);</script>";
}
?>
<script>
    const allSongsMapData = <?php echo $all_songs_json; ?>;
    console.log("Loaded songs for map:", allSongsMapData.length);
</script>

<div id="content-container" style="max-width: 100%; width: 100%; height: calc(100vh - 80px); margin: 0; padding: 0; border-radius: 0; position: relative; overflow: hidden;">
    
    <!-- GitHub-style Tab Bar -->
    <div class="map-nav-tabs">
        <div class="nav-tab-item active" id="tab-map" onclick="switchView('map')">
            <img src="img/map.jpg" class="icon" style="width:20px; height:20px; object-fit:contain; border-radius:4px;"> 音域地圖
        </div>
        <div class="nav-tab-item" id="tab-board" onclick="switchView('board')">
            <span class="icon">💬</span> 留言板
            <span class="counter" id="board-counter">0</span>
        </div>
    </div>

    <!-- Main Content Area -->
    <div id="main-content-wrapper">
        
        <!-- View 1: Map -->
        <div id="map-view" class="view-content active">
            <div id="map"></div>
            
            <!-- Map Controls (Zoom, Add) -->
            <div class="map-controls-group">
                 <button class="btn-control" onclick="openUploadModal()" title="新增打卡">
                    <span>+</span> 
                </button>
            </div>
            
            <!-- Sidebar for List (Overlay on Map) -->
            <div class="map-sidebar-left" id="map-sidebar"></div>
            <button id="sidebar-toggle">☰</button>
        </div>
        
        <!-- View 2: Message Board -->
        <div id="message-board-view" class="view-content" style="display:none;">
            <div class="board-container" id="board-grid">
                <!-- Grid Items injected by JS -->
            </div>
            <div id="board-pagination" class="pagination-container">
                <!-- Pagination Buttons injected by JS -->
            </div>
            
            <div style="margin-top: 60px;">
                <?php include "foot.html"; ?>
            </div>
        </div>
        
    </div>

    <!-- Enhanced Upload Modal -->
    <div id="upload-modal" class="modal-overlay" style="display: none;" onclick="if(event.target === this) closeUploadModal()">
        <div class="modal-box modal-lg">
            <div class="modal-header">
                <h3>新建動漫聖地</h3>
                <button class="close-btn" onclick="closeUploadModal()">&times;</button>
            </div>
            
            <form id="upload-form" onsubmit="handleUpload(event)">
                <!-- Step 1: Anime Selection -->
                <div class="form-section" id="section-anime-search">
                    <label class="section-label">1. 選擇對應作品 (必填)</label>
                    <div class="search-input-wrapper">
                        <input type="text" id="anime-search-input" placeholder="輸入關鍵字搜尋作品 (例如: 你的名字)" autocomplete="off">
                        <button type="button" class="btn-search-api" onclick="searchAnimeFromApi()">搜尋</button>
                    </div>
                    
                    <!-- Search Results -->
                    <div id="anime-results-list" class="anime-results-list">
                        <!-- Items injected by JS -->
                        <div class="empty-hint">請輸入關鍵字並點擊搜尋...</div>
                    </div>
                    
                    <!-- Selected Anime Preview -->
                    <div id="selected-anime-preview" style="display:none;">
                        <div class="selected-anime-card">
                            <img id="selected-anime-cover" src="">
                            <div class="selected-anime-info">
                                <div id="selected-anime-title" class="title"></div>
                                <div id="selected-anime-year" class="year"></div>
                                <button type="button" class="btn-change" onclick="resetAnimeSelection()">更換作品</button>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="anime_name" id="final-anime-name" required>
                </div>

                <hr class="divider">

                <!-- Step 2: Location Details -->
                <div class="form-section">
                    <label class="section-label">2. 地點資訊</label>
                    <div class="form-group row">
                        <div class="col">
                            <label>地點名稱</label>
                            <input type="text" name="location_name" id="input-location-name" required placeholder="例如：須賀神社">
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col">
                            <label>緯度</label>
                            <input type="text" name="latitude" id="input-lat" required readonly style="background:#222; cursor:not-allowed;">
                        </div>
                        <div class="col">
                            <label>經度</label>
                            <input type="text" name="longitude" id="input-lng" required readonly style="background:#222; cursor:not-allowed;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>場景描述 / 心得</label>
                        <textarea name="description" rows="3" placeholder="描述這個聖地的特色，或是在作品中出現的場景..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>上傳實景照片 (選填)</label>
                        <input type="file" name="image" accept="image/*">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-submit">確認建立聖地</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="js/player_bridge.js?v=5"></script>
<script src="js/anime_map.js?v=<?php echo time(); ?>"></script>

<?php 
require_once("../DB/DB_close.php");
// No footer needed for a full-screen map, or at least keep it minimal
?>
</body>
</html>
