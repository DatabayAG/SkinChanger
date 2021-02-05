document.addEventListener("DOMContentLoaded", function (event) {

  /**
   * Initializes the existing buttons on page load.
   */
  const init = (inputClass = '.selectAllocation_input') => {
    document.querySelectorAll(inputClass + ' .selectAllocation_row').forEach(element => {
      element.querySelector("button[name=add]").addEventListener("click", addRow);
      element.querySelector("button[name=remove]").addEventListener("click", removeRow);
    })
  }

  /**
   * Adds a new row below the previous one.
   * @param event
   */
  const addRow = (event) => {
    let row = event.target.parentNode.parentNode;
    let clone = row.cloneNode(true);
    row.after(clone);
    clone.querySelector("button[name=add]").addEventListener("click", addRow);
    clone.querySelector("button[name=remove]").addEventListener("click", removeRow);
  }

  /**
   * Removes the row
   * @param event
   */
  const removeRow = (event) => {
    let row = event.target.parentNode.parentNode;
    let rows = row.parentNode.querySelectorAll(".selectAllocation_row");
    if(rows.length > 1){
      row.remove();
    }
  }

  init();
});