document.addEventListener("DOMContentLoaded", function (event) {
  let init = () => {
    clearUrl();
  }

  let clearUrl = () => {
    let skinChangeTempUrlCleaner = document.querySelector("#skinChange_temp_urlCleaner");

    if(!skinChangeTempUrlCleaner) {
      return;
    }

    let anonSkinId = skinChangeTempUrlCleaner.getAttribute("anonSkinId");
    if(!anonSkinId) {{
      return;
    }}


    let suffix = skinChangeTempUrlCleaner.textContent;
    skinChangeTempUrlCleaner.remove();
    window.history.replaceState(null, '', anonSkinId === "default" ? "login.php" : `${anonSkinId}${suffix}`);
  }

  init();
});