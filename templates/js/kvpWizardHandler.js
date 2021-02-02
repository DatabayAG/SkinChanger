$( document ).ready(() => {
  let counter = 0;
  let ajaxAllocationsUrl = $("#ajax_allocations_url").val();
  let defaultSkin = "default";


  function isValueInSelect($select, data_value){
    return $($select).children('option').map(function(index, opt){
      return opt.value;
    }).get().includes(data_value);
  }

  $.ajax({
    url: ajaxAllocationsUrl,
    type: "GET",
    dataType: 'text',
    cache: false,
    contentType: false,
    processData: false,
    success: (responseString) => {
      let existingAllocations = JSON.parse(responseString);

      if(existingAllocations.length > 0){
        let keyElements = $("select[id*=\"" + `[key]` + "\"]");
        let valueElements = $("select[id*=\"" + `[value]` + "\"]");

        keyElements.each(function( index ) {
          $( this ).val(existingAllocations[index].key);
        });

        valueElements.each(function ( index ) {
          if(!isValueInSelect($( this ), existingAllocations[index].value)){
            $( this ).val(defaultSkin);
          }
          else{
            $( this ).val(existingAllocations[index].value);
          }
        });
      }
    }
  });



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
      $( this ).attr("id", newValue);
      $( this ).attr("name", newValue);
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