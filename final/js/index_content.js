// Mode 1: Content Page (Song List) Logic

function playSongInContext(title, artist, src, cover, id) {
  // Play from 'all' context starting at this song
  if (window.parent && window.parent.loadQueue) {
    window.parent.loadQueue("all", 0, id); // Load all, play specific ID
  }
}



// Playlist Modal Logic (Delegate to Parent App Shell)
function openPlaylistModal(songId) {
  if (window.parent && window.parent.openPlaylistModal) {
    window.parent.openPlaylistModal(songId);
  } else {
    console.error("Parent shell not found for playlist modal.");
  }
}

// These are now handled by the parent shell
/*
function showCreateForm(songId) { ... }
function createPlaylist(songId) { ... }
function toggleSongInPlaylist(songId, playlistId, isInPlaylist) { ... }
*/
