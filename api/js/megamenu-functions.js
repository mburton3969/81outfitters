var urlParams = new URLSearchParams(window.location.search);
var path = urlParams.get('path');
var filters = urlParams.get('filter');
if((filters) && filters !== ''){
  var stop = 'Y';
}else{
  var stop = urlParams.get('stop');
}
(function navigate_categories(cid){
  console.log('QS Filters: '+filters);
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        // Typical action to be performed when the document is ready:
        var r = JSON.parse(this.responseText);
        console.log(r);
        document.getElementById('button-filter-parent').innerHTML = r.cat_name+' <span class="caret"></span>';
        console.log('Res Filters: '+r.filters);
        if(stop !== 'Y'){
          window.location = "index.php?route=product/category&path="+r.parent_path+"&filter="+r.filters+"&stop=Y";
        }
        //Add Filter Buttons...
        var fa = filters.split(',');
        console.warn('Filters: '+filters);
        console.warn('Filters: '+fa);
        for(var i = 0; i < fa.length; i++){
          if(fa[i] != null || fa[i] !== ''){
            add_filter_button(fa[i]);
          }
        }
      }
  };
  xhttp.open("GET", "api/get-nav-details.php?path="+path, true);
  xhttp.send();
})();


function setPath(p){
  window.location = "index.php?route=product/category&path="+p+"&filter="+filters;
}

function add_filter_button(fid){
  if(fid === 0 || fid === '0' || fid === '' || fid == null){
    console.warn('Empty FID...');
    return;
  }
  fid.replace(null,'');
  console.log('FID: '+fid);
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        // Typical action to be performed when the document is ready:
        var r = JSON.parse(this.responseText);
        console.log(r);
        
        //Add Filter Buttons...
        var btn = document.createElement('button');
        btn.setAttribute('type','button');
        btn.setAttribute('id','button-filter');
        btn.setAttribute('class','btn-primary');
        btn.setAttribute('style','margin-right:5px;border-radius:30px;padding:15px !important;font-size:15px;line-height:0px;text-transform:capitalize;');
        btn.innerHTML = r.filter_name+' <i class="fa fa-times-circle" onclick="remove_filter(\''+r.filter_id+'\');"></i>';
        //Add Button...
        setTimeout(function(){
          document.getElementById('attribute-button-container').appendChild(btn);
        },500);
        
      }
  };
  xhttp.open("GET", "api/get-filter-details.php?fid="+fid, true);
  xhttp.send();
}

function remove_filter(fid){
  console.log(fid);
  var f = filters.split(',');
  var index = f.indexOf(fid);
  console.log(f);
  console.log(index);
  f.splice(index,1);
  console.log(f);
  window.location = "index.php?route=product/category&path="+path+"&filter="+f+"&stop=Y";
}

//<button type="button" id="button-filter" class="btn-primary" style="border-radius:30px;">dress <i class="fa fa-times-circle"></i> </button> 
