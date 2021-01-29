$( document ).ready(() => {
  let counter = 0;

  const refreshRows = (newCounterValue) => {
    counter = newCounterValue;
    let keyElements = $("select[id*=\"" + `[key]` + "\"]");
    let valueElements = $("select[id*=\"" + `[value]` + "\"]");

    keyElements.each(function( index ) {
      let newValue = `rows[key][${index}]`;
      $( this ).attr("id", newValue);
      $( this ).attr("name", newValue);
    })

    valueElements.each(function( index ) {
      let newValue = `rows[value][${index}]`;
      $( this ).attr("id", `rows[value][${index}]`);
      $( this ).attr("name", `rows[value][${index}]`);
    })

  }

  let observer = new MutationObserver(function(mutations) {
    if ($("button[name^=\"" + "cmd[addrows]" + `[${counter}]` + "\"]").length) {
      counter++;
      refreshRows(counter);
    }
    else if(counter > 1){
      refreshRows(counter -1);
    }
  });
  observer.observe(document.body, { //document.body is node target to observe
    childList: true, //This is a must have for the observer with subtree
    subtree: true //Set to true if changes must also be observed in descendants.
  });

});