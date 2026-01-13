// Backward compatibility for search.php etc
function playSong(title, artist, src, cover, id, type, typeId, typeName) {
  playContextSong(title, artist, src, cover, id, type, typeId, typeName);
}

// Explicit function for context-aware playback
function playContextSong(title, artist, src, cover, id, type, typeId, typeName) {
  // Debug to verify we are running the new code
  console.log("Bridge playContextSong:", type, typeId, typeName);

  if (window.parent && window.parent !== window && window.parent.playSong) {
    window.parent.playSong(title, artist, src, cover, id, type, typeId, typeName);
  } else {
    console.warn("Player not found in parent window.");
  }
}
