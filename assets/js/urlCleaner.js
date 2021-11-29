document.addEventListener("DOMContentLoaded", function (event) {
  let init = () => {
    clearUrl();
  }

  let clearUrl = () => {
    const urlParams = new URLSearchParams(window.location.search);
    let anonSkinId = urlParams.get("anonSkinId");
    let skinChangeTempUrlCleaner = document.querySelector("#skinChange_temp_urlCleaner");

    if(!anonSkinId || !skinChangeTempUrlCleaner) {
      return;
    }

    let suffix = skinChangeTempUrlCleaner.textContent;
    skinChangeTempUrlCleaner.remove();
    window.history.replaceState(null, '', anonSkinId === "default" ? "login.php" : `${anonSkinId}.${suffix}`);
  }

  init();
});