function trigger_preloader() {
  var pl = document.getElementById("page-preloader");
  pl.classList.add("visible");
}


$(function() {
  $("body").click(function(e) {
    if (e.target.id == "linker") {
      console.log('Linker Engaged');
      trigger_preloader();
    }
  });
})