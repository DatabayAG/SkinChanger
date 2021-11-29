document.addEventListener("DOMContentLoaded", function (event) {
  let init = () => {
    clearUrl();
  }

  let clearUrl = () => {
    const urlParams = new URLSearchParams(window.location.search);
    let anonSkinId = urlParams.get("anonSkinId");
    let anonStyleId = urlParams.get("anonStyleId");

    if(!anonSkinId || !anonStyleId) {
      return;
    }

    window.history.replaceState(null, '', anonSkinId === "default" ? "login.php" : `${anonSkinId}.html`);
  }

  init();
});