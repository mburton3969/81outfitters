var urlParams = new URLSearchParams(window.location.search);
var path = urlParams.get('path');
var filters = urlParams.get('filter');
var limit = urlParams.get('limit');
if(limit === '' || limit === null){
  limit = '25';
}
var fgs = urlParams.get('fgs');
if((filters) && filters !== ''){
  var stop = 'Y';
}else{
  var stop = urlParams.get('stop');
}

function navigate_categories(cid){
  if(cid){
    var use_path = cid;
  }else{
    var use_path = path;
  }
  console.log('QS Filters: '+filters);
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        // Typical action to be performed when the document is ready:
        var r = JSON.parse(this.responseText);
        console.log(r);
        document.getElementById('button-filter-parent').innerHTML = r.cat_0_name+' <span class="caret"></span>';
        document.getElementById('gender-button').style.display = 'inline';
        console.log('Res Filters: '+r.filters);
        if(document.getElementById('filter-group10')){
          document.getElementById('filter-group10').style.display = 'none';
          document.getElementById('filter-link10').style.display = 'none';
        }
        if(r.level === '2'){
          if(document.getElementById('filter-group9')){
            document.getElementById('filter-group9').style.display = 'none';
            document.getElementById('filter-link9').style.display = 'none';
          }
          if(document.getElementById('filter-group8')){
            document.getElementById('filter-group8').style.display = 'none';
            document.getElementById('filter-link8').style.display = 'none';
          }
        }
        if(r.level === '1'){
          if(document.getElementById('filter-group9')){
            document.getElementById('filter-group9').style.display = 'none';
            document.getElementById('filter-link9').style.display = 'none';
          }
        }
        if(stop !== 'Y'){
          //window.location = "index.php?route=product/category&path="+r.parent_path+"&filter="+r.filters+"&stop=Y";
          console.log('Line 45');
          //window.location = "index.php?route=product/category&path="+use_path+"&filter="+r.filters+"&fgs="+r.filter_groups+"&limit="+limit+"&stop=Y";
        }
        filters = r.filters;
        fgs = r.filter_groups;
        //Add Filter Buttons...
        var fa = filters.split(',');
        console.warn('Filters: '+filters);
        console.warn('Filters: '+fa);
        for(var i = 0; i < fa.length; i++){
          if(fa[i] != null || fa[i] !== ''){
            add_filter_button(fa[i]);
            sleep(500);
          }
        }
      }
  };
  xhttp.open("GET", "api/get-nav-details.php?path="+path, true);
  xhttp.send();
}

(function(){
  navigate_categories();
})();


function setPath(p){
  console.log('Line 70');
  window.location = "index.php?route=product/category&path="+p+"&filter="+filters+"&limit="+limit+"&fgs="+fgs;
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
        btn.innerHTML = r.filter_name+' <i class="fa fa-times-circle" onclick="trigger_preloader();remove_filter(\''+r.filter_id+'\','+(r.cat_level - 1)+');"></i>';
        //Add Button...
        setTimeout(function(){
          document.getElementById('attribute-button-container').appendChild(btn);
        },500);
        
      }
  };
  xhttp.open("GET", "api/get-filter-details.php?fid="+fid, true);
  xhttp.send();
}

function remove_filter(fid,parent_lvl){
  console.log(fid);
  var f = filters.split(',');
  var index = f.indexOf(fid);
  console.log(f);
  console.log(index);
  f.splice(index,1);
  console.log(f);
  if(parent_lvl !== ''){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          // Typical action to be performed when the document is ready:
          var r = JSON.parse(this.responseText);
          console.log(r);
          if(r.response === 'GOOD'){
            var new_path = r.category_id;
            console.log('Line 123');
            window.location = "index.php?route=product/category&path="+new_path+"&filter="+f+"&limit="+limit+"&fgs="+fgs;
          }else{
            var new_path = r.category_id;
            console.log('Line 127');
            window.location = "index.php?route=product/category&path="+new_path+"&filter="+f+"&limit="+limit+"&fgs="+fgs;
          }

        }
    };
    xhttp.open("GET", "api/get-path-details.php?lvl="+parent_lvl+"&path="+path, true);
    xhttp.send();
  }else{
    console.log('Line 136');
    window.location = "index.php?route=product/category&path="+path+"&filter="+f+"&fgs="+fgs+"&limit="+limit+"&stop=Y";
  }
}


//Delay Function...
function sleep(milliseconds) {
  console.warn('Sleeping For '+milliseconds+' milliseconds...');
  const date = Date.now();
  let currentDate = null;
  do {
    currentDate = Date.now();
  } while (currentDate - date < milliseconds);
}