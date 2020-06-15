function set_featured(pid) {
  if(document.getElementById('fcb_'+pid).checked === true){
    var status = 'Yes';
  }else{
    var status = 'No';
  }
  //alert('ID: '+pid+'\nFeatured: '+status);
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      // Typical action to be performed when the document is ready:
      var r = JSON.parse(this.responseText);
      console.log(r);
      if (r.response === 'GOOD') {
        
        document.getElementById('fcb_span_'+pid).style.color = 'green';
        document.getElementById('fcb_span_'+pid).innerHTML = 'Saved';
        setTimeout(function(){
          document.getElementById('fcb_span_'+pid).innerHTML = '';
        },3000);
        
      } else {
        
        document.getElementById('fcb_span_'+pid).style.color = 'red';
        document.getElementById('fcb_span_'+pid).innerHTML = 'ERROR!';
        setTimeout(function(){
          document.getElementById('fcb_span_'+pid).innerHTML = '';
        },3000);
        
      }

    }
  }
  xhttp.open("GET", "../api/update-featured-status.php?pid="+pid+"&status="+status, true);
  xhttp.send();
}