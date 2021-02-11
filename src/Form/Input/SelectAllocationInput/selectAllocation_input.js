document.addEventListener("DOMContentLoaded", function (event) {

  /**
   * Initializes the existing buttons on page load.
   */
  let init = (inputClass = '.selectAllocation_input') => {
    document.querySelectorAll(inputClass + ' .selectAllocation_row').forEach(element => {
      element.querySelector("button[name=add]").addEventListener("click", addRow);
      element.querySelector("button[name=remove]").addEventListener("click", removeRow);
    })
  }

  /**
   * Adds a new row below the previous one.
   * @param event
   */
  let addRow = (event) => {
    let row = event.target.parentNode.parentNode;
    let clone = row.cloneNode(true);
    row.after(clone);
    let errorMessage = clone.querySelector(".help-block");
    if(errorMessage){
      errorMessage.remove();
    }
    clone.querySelector("button[name=add]").addEventListener("click", addRow);
    clone.querySelector("button[name=remove]").addEventListener("click", removeRow);
  }

  /**
   * Removes the row
   * @param event
   */
  let removeRow = (event) => {
    let row = event.target.parentNode.parentNode;
    let rows = row.parentNode.querySelectorAll(".selectAllocation_row");
    if(rows.length > 1){
      row.remove();
    }
  }

  init();
});