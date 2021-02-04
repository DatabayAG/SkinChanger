$( document ).ready(() => {
  $("button[name=add]").on("click", addCell);
  $("button[name=remove]").on("click", removeCell)
});

function addCell(){
  let cell = $(this).parent().parent();
  let clone = cell.clone().insertAfter(cell);
  clone.find("button[name=add]").on("click", addCell);
  clone.find("button[name=remove]").on("click", removeCell);
}

function removeCell(){
  let cell = $(this).parent().parent();
  let cells = cell.parent().find(".selectAllocation_cell");

  if(cells.length > 1){
    cell.remove();
  }
}