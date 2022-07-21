/* example dialog that inserts the name of a Pet into the editor content */
//var  textcolor='blue';

    // This is the json format of https://gist.github.com/anthonykozak/84e07a2cf8c27d3e5a8f181742ca293d
// https://use.fontawesome.com/releases/v6.0.0/js/all.js
// icon size   fa-2xs fa-xs fa-sm fa-lg fa-xl
// fa-1x fa-2x fa-3x fa-4x fa-5x fa-6x fa-7x fa-8x fa-9x fa-10x
// extra version 5 kann nur fa-spin und fa-pulse
// fa-beat fa-fade fa-beat-fade fa-bounce fa-flip fa-shake fa-spin fa-spin-reverse fa-spin-pulse (fa-spin-pulse fa-spin-reverse)
//var akttextcolor='black';           // bei Klick auf Farbübername
tinymce.PluginManager.add('fontawesome', function(editor, url) {
	var version = 'v5.12.0';
    var translate = tinymce.util.I18n.translate;
    var font_awesome_path = editor.getParam('font_awesome_path');
    console.log('font_awesome_path for aaaaaaaaaaaaaaxxxxxxxxxxxxxxxxxxxxxxx fontawesome5.1:'+font_awesome_path);
var redial=false;                   // Kennzeichnng ob bei Tabwechsel ein redial durchgeführt werden soll
var mce_dialogApi;              // zwischenspeicher für dialogapi
var currentTab='tabIcons';                 // enthaelt den Namen des ktuellen Tabs (fuer Redial) notwendig
const iconSizes =    ['fa-sm' ,'fa-lg','fa-2x','fa-3x','fa-5x','fa-7x','fa-10x'];
const iconSizesStyle=['.875em','1.33333em','2em'  ,'3em'  ,'5em'  ,'7em'  ,'10em'];

const config = {
  textColor: 'black', 
  iconselected: false,
  iconLfnr: '0',
  iconType: 'fas',
  iconName: 'hourglass',  
  iconSize: 'fa-3x',
  iconSizeStyle: '3em',
  iconEffectClass: '',
}
const selectedFilters = {      // globaler Speicher fuer selectierten Filter
  membership: "free",
  style: "",
  category: "",
  search: "",
};
var jsonPath = url+"/json/";
var iconList="";

var iconListGroups = [];
var iconCategories = [];

var CategorieOptions = [];                      // enthaelt das Optionfel fuer die Listbox categorie
CategorieOptions.push ({text:'---',value:''});  // muss wohl sein, sonst funktioniert der erste Aufruf nicht
var FullIconHtml="";                            // enthaelt den html-String fuer alle Icons


function getAweIcon(extraStyle='') { 
  style = '';
  if( typeof config.textColor === 'undefined' || config.textColor === null || config.textColor==''){
    style=' style="'+extraStyle+';"';
  } else {
    style=' style="'+extraStyle+'color: '+config.textColor+';"';    
  }
  //console.log('PBD  getAweIcon start '+config.iconType+' fa-'+config.iconName+' size '+config.iconSize+' style '+style);    
  ret = '<i class="'+config.iconType+' fa-'+config.iconName+' '+config.iconSize+' '+config.iconEffectClass+'"'+style+'></i>';
  console.log('PBD  getAweIcon ende ret '+ret);    
  return ret;
}

/*
 * zeigt die Auswahl der moeglichen Groessen an
 * config.iconType enthält fab / fas Aus data-iconType Attribute bei select s. function selectIcon
 * config.icinName die iconName
 * config.textcolor die ausgewählte Farbe
 */
function showSelectSize() {
  console.log('show Selected Size '+'" data-iconType="'+config.iconType+'" data-name="'+config.iconName+'"'+'" color="'+config.textColor+'"');
  col='black';       // defaultcolor
  if( typeof config.textColor === 'undefined' || config.textColor === null || config.textColor==''){
    col='black';    
  } else {
    col=config.textColor;    
  }
  
  let style ="";
  cl ='fas';
  if (config.iconType === 'fab') {
    style="font-family:\'Font Awesome 5 brands\'; font-weight: 400; font-size: 3em;";
    style="font-family:\'Font Awesome 5 brands\'; font-weight: 400; font-size: 1.4em;";
  } else {
    style="font-family:\'Font Awesome 5 Free\'; font-weight: 900; font-size: 3em;";
    style="font-family:\'Font Awesome 5 Free\'; font-weight: 900; font-size: 1.4em;";
  }

  console.log('showSelectSize style '+style);
  gridHtml = '<div>';

  for (var i = 0; i < iconSizes.length; i++) { 
    sz=iconSizes[i];
    szst=' style="'+style+' font-size: '+iconSizesStyle[i]+';"';
//console.log('szst '+szst);
    gridHtml += '<i class="mce_fasizing ';
    gridHtml += config.iconType+' fa-'+config.iconName+' '+config.iconEffectClass+' '+sz+' "';   // class ende
    gridHtml += ' data-icon-size="'+sz+'"';
//    gridHtml += ' data-icon-iconSizesStyle="'+iconSizesStyle[i]+'" ';
    gridHtml += ' '+szst+'></i>';
   } 
  gridHtml += '</div>';
  console.log('show Selected Size ende');
  return gridHtml;
}
/*
 * zeigt die die Vorschau an
 * config.iconType enthält fab / fas Aus data-iconType Attribute bei select s. function selectIcon
 * config.icinName die iconName
 * config.textcolor die ausgewählte Farbe
 */
function showVorschau() {
console.log('PBD showVorschau');
  let style ="";
  cl ='fas';
  if (config.iconType === 'fab') {
    style="font-family:\'Font Awesome 5 brands\'; font-weight: 400; font-size: 3em;";
    style="font-family:\'Font Awesome 5 brands\'; font-weight: 400; font-size:"+config.iconSizeStyle+"; color:"+config.textColor+";";
  } else {
    style="font-family:\'Font Awesome 5 Free\'; font-weight: 900; font-size: 3em;";
    style="font-family:\'Font Awesome 5 Free\'; font-weight: 900; font-size:"+config.iconSizeStyle+"; color:"+config.textColor+";";
  }
  gridHtml = '<div>';


  gridHtml += '<i class="mce_fasizing '+config.iconType+' fa-'+config.iconName+' '+config.iconSize+' '+config.iconEffectClass+' " data-icon-size="'+config.iconSize+'" style="'+style+'"></i>';
  gridHtml += '</div>';
  //console.log('showVorschau grid '+gridHtml);
  return gridHtml
}


/* bearbeitet den geladen Html-code
 * wertet die Filter aus und setzt über das style-Attribute das Icon sichtbar oder nicht
 */
function filterIconHtml () {
  var els = document.querySelectorAll(".mce-icon-cell");
console.log ('filterIconHtml len mce-icon-cell els '+els.length);
  if (selectedFilters.category==='' && selectedFilters.style===''&& selectedFilters.search==='') {  // keine Filter alles anzeigen
    for (var i = 0; i < els.length; i++) {
      var e = els[i];
      e.style.display = "inline-flex";
    }
    return;
  }
  if (selectedFilters.category!=='' && selectedFilters.style===''&& selectedFilters.search==='') {  // nur categorie Filter
    console.log ('filterIconHtml select only category '+selectedFilters.category);
    for (var i = 0; i < els.length; i++) {
      var e = els[i];
      if (e.getAttribute("data-category")==selectedFilters.category) {
        e.style.display = "inline-flex";
      } else {
        e.style.display = "none";
      }
    }
    return;
  }
  if (selectedFilters.category=='' && selectedFilters.style!==''&& selectedFilters.search==='') {  // nur styles Filter
    console.log ('filterIconHtml select only style '+selectedFilters.style);
    for (var i = 0; i < els.length; i++) {
      var e = els[i];
      if (e.getAttribute("data-iconStyle")==selectedFilters.style) {
        nm=e.getAttribute("data-name");
        console.log ('filterIconHtml selected '+nm);
        e.style.display = "inline-flex";
      } else {
        e.style.display = "none";
      }
    }
    return;
  }
  if (selectedFilters.category==='' && selectedFilters.style===''&& selectedFilters.search!=='') {  // nur suche
    console.log ('filterIconHtml select only search '+selectedFilters.search);
    for (var i = 0; i < els.length; i++) {
      var e = els[i];
      nm=e.getAttribute("data-name");
      if (nm.includes(selectedFilters.search)){
        nm=e.getAttribute("data-name");
        console.log ('filterIconHtml selected '+nm);
        e.style.display = "inline-flex";
      } else {
        e.style.display = "none";
      }
    }
    return;
  }
  if (selectedFilters.category!=='' && selectedFilters.style!==''&& selectedFilters.search==='') {  // category und style
    console.log ('filterIconHtml select category und style '+selectedFilters.category+' '+selectedFilters.style);
    for (var i = 0; i < els.length; i++) {
      var e = els[i];
      if (e.getAttribute("data-category")==selectedFilters.category && e.getAttribute("data-iconStyle")==selectedFilters.style) {
        e.style.display = "inline-flex";
      } else {
        e.style.display = "none";
      }
    }
    return;
  }
  if (selectedFilters.category!=='' && selectedFilters.style===''&& selectedFilters.search!=='') {  // Category search
    console.log ('filterIconHtml select category search '+selectedFilters.category+' '+selectedFilters.search);
    for (var i = 0; i < els.length; i++) {
      var e = els[i];
      nm=e.getAttribute("data-name");
      if (e.getAttribute("data-category")==selectedFilters.category&&nm.includes(selectedFilters.search)) {
        e.style.display = "inline-flex";
      } else {
        e.style.display = "none";
      }
    }
    return;
  }
  if (selectedFilters.category==='' && selectedFilters.style!==''&& selectedFilters.search!=='') {  // style search
    console.log ('filterIconHtml select style search '+selectedFilters.style+' '+selectedFilters.search);
    for (var i = 0; i < els.length; i++) {
      var e = els[i];
      nm=e.getAttribute("data-name");
      if (e.getAttribute("data-iconStyle")==selectedFilters.style&&nm.includes(selectedFilters.search)) {
        e.style.display = "inline-flex";
      } else {
        e.style.display = "none";
      }
    }
    return;
  }
  if (selectedFilters.category!=='' && selectedFilters.style!==''&& selectedFilters.search!=='') {  // category style search
    console.log ('filterIconHtml select category style search '+selectedFilters.category+' '+selectedFilters.style+' '+selectedFilters.search);
    for (var i = 0; i < els.length; i++) {
      var e = els[i];
      nm=e.getAttribute("data-name");
      if (e.getAttribute("data-category")==selectedFilters.category && e.getAttribute("data-iconStyle")==selectedFilters.style&&nm.includes(selectedFilters.search)) {
        e.style.display = "inline-flex";
      } else {
        e.style.display = "none";
      }
    }
    return;
  }
}

// erzeugt dann Html-code der alle Icons enthält. geordnet nach categorien
function createFullIconHtml() {
console.log('createFullIconHtml')
  var lfnr=-1;   // laufende Nummer zu identifikation ds Icons id = mceicon_lfnr
  function groupHtml(categoriy, iconTitle) {
console.log('PBD groupHtml categoriy '+categoriy+' title '+iconTitle);
    var iconGroup = iconListGroups[categoriy];
    if( typeof iconGroup === 'undefined') {
console.log('PBD groupHtml iconGroup undefined');
      return '<div> Categoriy '+categoriy+' nicht vorhanden</div>'
    }
    var gridHtml="";
    var id;
    gridHtml += '<div class="mce-fontawesome-panel-content">';
    console.log('groupHtml iconGroup.length '+iconGroup.length+' categoriy '+categoriy); 
    for (var y = 0; y < (iconGroup.length / width); y++) {
      console.log('groupHtml iconGroup width '+width+' y '+y); 
      for (var x = 0; x < width; x++) {
        if (iconGroup[y * width + x]) {   // Einzelne Zeilen der Ausgabe
          lfnr++;
          id = iconGroup[y * width + x].objectID;
	      id = id.replace('i:', '');    //    objectId aus json i.a i:xxx  i: entfernen wird als title verwendet
          name = iconGroup[y * width + x].name;
          let iconStyle = iconGroup[y * width + x].styles[0];
          let cl ='fas';
          let style="";
          //if (categoriy === 'brands' || iconStyle === 'brands') {
          if (iconStyle === 'brands') {
            //style="font-family:\'Font Awesome 5 brands\'; font-weight: 400; font-size: 3em;";
            style="font-family:\'Font Awesome 5 brands\'; font-weight: 400; font-size: 1.4em;";
            cl='fab';
          } else {
            //style="font-family:\'Font Awesome 5 Free\'; font-weight: 900; font-size: 3em;";
            style="font-family:\'Font Awesome 5 Free\'; font-weight: 900; font-size: 1.4em;";
            cl='fas';
          }
          //console.log('groupHtml name '+name+' st '+st+' style '+style+ ' lfnr '+lfnr);							
          console.log('groupHtml name '+name+' lfnr '+lfnr);							
          gridHtml += '<div id="mceicon_'+lfnr+'" class="mce-icon-cell js-mce-fontawesome-insert" title="'+id+'" data-name="'+id+'" data-iconType="'+cl+'" data-id="'+lfnr+'" data-category="'+categoriy+'" data-iconStyle="'+iconStyle+'" >';
          //gridHtml += '<div id="mceicon_'+lfnr+'" class="mce-icon-cell js-mce-fontawesome-insert" title="'+id+'" data-name="'+id+'" data-iconType="'+cl+'" data-id="'+lfnr+'" data-category="'+categoriy+'" style="display:none" >';
          gridHtml += '<i class="faclass '+cl+' fa-'+id+'" style="'+style+'"></i>';
          //gridHtml += '<i class="faclass '+cl+' fa-'+id+'" style="'+style+'"></i><span>'+id+'</span>';
          gridHtml += '</div>';
        }
      }   // ende Schleife über width
      gridHtml += '<div style="clear:both"></div>';  // bendet den float:left und startet eine neue Zeile
    }  // end Schleife categorie.length
    gridHtml += '</div>';  // mce-fontawesome-panel-content
    return gridHtml;
  }  // ende function groupHtml
  var width = 23;   // Anzahl Icons pro Zeile
  console.log ('pbd selectedFilters.category '+selectedFilters.category+' iconCategories.length '+iconCategories.length);
  let panelHtml="";
  //console.log ('pbd panelHtml '+panelHtml);
  for (var i = 0; i < iconCategories.length; i++) {
    panelHtml += groupHtml(iconCategories[i], translate(iconCategories[i]))
  }
  return panelHtml;                      
}

function selectIcon(lfnr) {
  console.log('selectIcon icon '+lfnr)
  var els = document.querySelectorAll(".mce-icon-cell");
  console.log('selectIcon icon lfnr '+lfnr+' len seliccon '+els.length)
  for (var i = 0; i < els.length; i++) {
    var e = els[i];
    e.classList.remove("selectedIcon");     // lösche class selectedIcon
  }
  document.getElementById('mceicon_'+lfnr).classList.add("selectedIcon");                 // setze Object als selected
  config.iconType=document.getElementById('mceicon_'+lfnr).getAttribute("data-iconType");   // fas, fab ..
  config.iconName=document.getElementById('mceicon_'+lfnr).getAttribute("data-name");  // name ohne fa-
  config.iconLfnr=document.getElementById('mceicon_'+lfnr).getAttribute("data-id");          // laufende nummer
  config.iconselected = true;
  console.log('selectIcon setting class '+config.iconType+' name '+config.iconName+' lfnr '+config.iconLfnr)
  //mce_dialogApi.setEnabled('submitButton', true);
  mce_dialogApi.redial(getdialogConfig());
  mce_dialogApi.showTab("tabColor");
}
function selectIconSize(size) {
  config.iconSize = size;
  var els = document.querySelectorAll(".mce_fasizing");
  // search the fontsize for mce
  for (var i=0;i<iconSizes.length;i++) {
    if (config.iconSize == iconSizes[i]) {
      config.iconSizeStyle = iconSizesStyle[i];                    // merke die Groesse für die mce Darstellung in Vorschau
      break;
    } 
  }
  console.log('selectIconSize size '+config.iconSize+ ' els len '+els.length);
  
  for (var i = 0; i < els.length; i++) {
    var e = els[i];
    e.style.border = "2px solid #ffffff";
    //e.style.border = "2px solid blue";
    //e.style.border = "2px solid #ffffff00";
  }
  console.log ('selector '+".mce_fasizing." + size);
  els = document.querySelectorAll(".mce_fasizing." + size);
  console.log('selectIconSize size '+config.iconSize+ ' els1 len '+els.length);
  for (var i = 0; i < els.length; i++) {
    var e = els[i];
    e.style.border = "2px solid #207ab7";
  }
  redial=true;
}

function getdialogConfig(){
  return {
    title: 'Peters TabPanel',
    size: 'large',
    body: {
      type: 'tabpanel',
      tabs: [
        { name: 'tabIcons', title: 'Icons',                                   
          items: [ 
            {  type: "bar",items: [
                 { type: 'selectbox', name: 'freeSelect',label: 'Lizenz',disabled: false, size: 1, 
                     items: [
                        { value: 'free', text: 'Free' },
                        //{ value: 'pro', text: 'Pro' }    bei Pro weiss ich nicht was tun
                     ]
                 },  
                 {  type: 'listbox', name: 'selectStyle',label: 'Style',disabled: false, 
                      items: [
                        { text: '---', value: '' },
                        { text: 'Regular', value: 'regular' },
                        { text: 'Solid', value: 'solid' },
                        { text: 'Brand', value: 'brands' },
                     ],
                 },                
                 { type: 'listbox', name: 'selectCategorie', label: 'Categorie',disabled: false, items: CategorieOptions, }, 
                 { type: 'input', name: 'searchIcon',inputMode: 'text',label: 'Search', disabled: false,maximized: false},                
                 { type: 'listbox',name: 'selectEffect',label: 'Effect',disabled: false,
                   items: [
                     { text: '---', value: '' },
                     { text: 'Spin', value: 'fa-spin' },
                     { text: 'Pulse', value: 'fa-pulse' },
                   ],
                 },
               ],      // ende bar items
            },
            { type: 'htmlpanel', html: FullIconHtml },
          ]
        },           // ende tabIcons
        { name: 'tabColor', title: 'Farbauswahl',
          items: [        // Elemente des Tab Formulars
            { type: 'htmlpanel',html: showSelectSize() },
            { type: 'label', label: translate('select color'), items: [{ type: "colorpicker", name: "mce_colorpicker" }] },
              // extra version 5 kann nur fa-spin und fa-pulse
              // fa-beat fa-fade fa-beat-fade fa-bounce fa-flip fa-shake fa-spin fa-spin-reverse fa-spin-pulse (fa-spin-pulse fa-spin-reverse)
          ],         // ende tabColor Items
        },           // ende tabColor 
        { name: 'tabVorschau', title: 'Vorschau',
          items: [        // Elemente des Formulars
            { type: 'htmlpanel', html: '<div style="color:'+'inherit'+'">Vorschau</div>'},          
            { type: 'htmlpanel', html: showVorschau() }, 
          ]         
        },           // ende tabVorschau
      ] // ende tabs
    },
    buttons: [  // footer Buttons
      { type: 'submit', name: 'submitButton', buttonType: 'primary', text: translate('ok'), },
      { type: 'cancel', name: 'closeButton', text: translate('cancel'), },
    ],
    initialData: { },
    onSubmit: (api) => {
      res=getAweIcon();
      console.log ('submit '+res);
      if (!tinymce.activeEditor.execCommand('mceInsertContent', false,res)){
        console.log('!!! Fehler bei submit');
      }
      api.close();
    },
    onAction: (dialogApi, details) => {     // wird gerufen u.a bei button click
      // log the contents of details
      const data = dialogApi.getData();
      const v=data.mce_colorpicker; // get hexWert from colorpicker 
      //config.textColor=v;
      //console.log( 'onaction data color '+config.textColor);
      //mce_dialogApi.redial(getdialogConfig());
      //initListener();
    },
    onChange: function (dialogApi, details) {
      var data = dialogApi.getData();
      var nm = details.name;
      console.log ('pbd onchange nm '+nm);
      //let redial = false;
      switch (nm) {
        case 'selectCategorie':
          var val = data.selectCategorie;
          console.log ('selectCategorie nm '+nm+'  wert '+val+' old '+selectedFilters.category);
          selectedFilters.category = val; 
          filterIconHtml();
          //redial=true;
        break;
        
        case 'selectStyle':
          var val = data.selectStyle;
          console.log ('selectStyle nm '+nm+'  wert '+val+' old '+selectedFilters.style);
          selectedFilters.style = val; 
          filterIconHtml();
          //redial=true;
        break;
        case 'searchIcon':
          var val = data.searchIcon;
          console.log ('selectCategorie nm '+nm+'  wert '+val);
          selectedFilters.search = val; 
          filterIconHtml();
        break;
        case 'selectEffect':        
          var val = data.selectEffect;
          console.log ('selectEffect nm '+nm+'  wert '+val+' old '+config.iconEffectClass);
          config.iconEffectClass = val; 
          break;
          
        default:
          console.log ('onChange was '+nm);
        break;
        
      }
      /*
      if(redial) {
        mce_dialogApi.redial(getdialogConfig());
      }
      */
      /* Example of enabling and disabling a button, based on the checkbox state. */
      //var toggle = data.anyterms ? dialogApi.enable : dialogApi.disable;
      //toggle('uniquename');
    },
  
    onTabChange: (dialogApi, details) => {
      //mce_dialogApi=dialogApi;
      const data = mce_dialogApi.getData();
      /*
      Object.getOwnPropertyNames(data).forEach(function(val, idx, array) {
        console.log('onTabChange data '+val + ' -> ' + data[val]);
      });    
      Object.getOwnPropertyNames(details).forEach(function(val, idx, array) {
        console.log('onTabChange details '+val + ' -> ' + details[val]);
      });
      */
      console.log('onTabChange newname '+details.newTabName+' oldname '+details.oldTabName+' redial '+redial+' color '+config.textColor);
      console.log('PBD onTabChange setting group '+config.iconType+' name '+config.iconName);

      //var dataX = mce_dialogApi.getData();
      //akttextcolor=dataX.mce_colorpicker;
      //config.textColor=dataX.mce_colorpicker;        
      //mce_dialogApi.setData({ mce_colorpicker: config.textColor });
      if (details.newTabName == "tabColor") {
        console.log('PBD onTabChange tabcolor color'+config.textColor);
        //console.log('nach setdata');
        var els = document.querySelectorAll(".mce_fasizing");        // gib alle Elemente  mit der Grössenangabe
        console.log('tabColor len fasing '+els.length+' color '+config.textColor);
        for (var i = 0; i < els.length; i++) {                       // setze Farbe
          var e = els[i];
          e.style.color = config.textColor;
        }
      } else if (details.newTabName == "tabVorschau") {
        if (redial == true) {
          redial=false;
          mce_dialogApi.redial(getdialogConfig());
          mce_dialogApi.showTab("tabVorschau");
        }
      }
    },
  };
}
function initListener() {
  // Insert icon listener 
  console.log('initListener');
  document.addEventListener("click", function (e) {
    console.log("click function");
    if (e.target.parentElement.classList.contains("js-mce-fontawesome-insert")) {   //check auf icon select Auswahl
      console.log("click function1 icon selected");
      selectIcon(e.target.parentElement.getAttribute("data-id"));                //??? müste das icht die id, d.h laufende nummer sein
    } else if (e.target.parentElement.parentElement.classList.contains("js-mce-fontawesome-insert")) {
      console.log("click function2 icon selected");
      selectIcon(e.target.parentElement.parentElement.getAttribute("data-id"));
    }
    if (e.target.classList.contains("mce_fasizing")) {
      console.log("click function3 size selected");
      selectIconSize(e.target.getAttribute("data-icon-size"));           // check auf Größenauswahl
    } else if (e.target.parentElement.classList.contains("mce_fasizing")) {
      console.log("click function4 size selected");
      selectIconSize(e.target.parentElement.getAttribute("data-icon-size"));           // check auf Größenauswahl
    }
    if (e.target.classList.contains("tox-sv-palette") || e.target.classList.contains("tox-hue-slider")) {
      console.log("click function4");
      var dataX = mce_dialogApi.getData();
      //akttextcolor=dataX.mce_colorpicker;
      config.textColor=dataX.mce_colorpicker;        
      mce_dialogApi.setData({ mce_colorpicker: config.textColor });
      var els = document.querySelectorAll(".mce_fasizing");      // Elemente Größe
      console.log("click function3 len "+els.length+' color '+config.textColor);
      for (var i = 0; i < els.length; i++) {         // größenicon einfärben
        var e = els[i];
        e.style.color = config.textColor;
      }
    }
  });

  document.addEventListener("change", function (e) {
    if (e.target.classList.contains("fai_pickerColorInput")) {
      var dataX = mce_dialogApi.getData();
      var els = document.querySelectorAll("mce_fasizing");
      for (var i = 0; i < els.length; i++) {
        var e = els[i];
        e.style.color = dataX.mce_colorpicker;
      }
    }
  });  
}
function generateIconlist(data) {
  iconList=data.icons;
  console.log('PBD generateIconlist len '+iconList.length)
  // Sort the icons alphabetically
  iconList.sort(function(a, b) {
    if (a.objectID < b.objectID) { return -1; }
    if (a.objectID > b.objectID) { return 1; }
    return 0;
  });
  //iconListGroups['brands'] = [];
  //iconCategories.push('brands');
  var categoryName;
  console.log('PBD generateIconlist len '+iconList.length);
  for (var i = 0; i < iconList.length; i++) {
    //console.log('PBD generateIconlist objectID '+iconList[i].objectID+' membership pro len '+iconList[i].membership.pro.length+' membership free len '+iconList[i].membership.free.length);
    if (iconList[i].membership.free.length == 0) continue;         // icon hat keine free Version
    for (var ii = 0; ii < iconList[i].categories.length; ii++) {    // ueber alle categorien des Icons
      categoryName = iconList[i].categories[ii].toLowerCase().replace(/ (.)/g, function(match, group1) { // ist vor der categorie ein blanck so wird die categorie groß geschreiben
        console.log('PBD generateIconlist replace match '+match+' group1 '+group1);
               return group1.toUpperCase();
            }); 
			
      if (!iconListGroups[categoryName]) {
        iconListGroups[categoryName] = [];
        //console.log('PBD generateIconlist push to iconListGroups '+categoryName);
      }
      iconListGroups[categoryName].push(iconList[i]);    // icon zur categorie speichern
      //console.log('PBD generateIconlist push to categorie '+categoryName+' [] '+iconCategories[categoryName]);
      if (!iconCategories.includes(categoryName)) {
        iconCategories.push(categoryName);
        console.log('PBD generateIconlist push to categorie '+categoryName+' xx [] '+iconCategories[categoryName]);
      }

    }
//console.log('PBD push to styles[0] '+iconList[i].styles[0]);
/*
	if (iconList[i].styles[0] === 'brands') {
	  iconListGroups['brands'].push(iconList[i]);
//console.log('PBD push to brands '+iconList[i].name);
	}
*/
  }
  console.log('PBD generateIconlist iconCategories len '+iconCategories.length+' groups len '+iconListGroups.length);
}
function createCategorieOptions() {
  console.log ('showCategorieOption');
  const res =[];
  res.push ({text:'---',value:''});
  iconCategories.sort(function(a,b) {return a.replace(/[^a-z]/ig,'') > b.replace(/[^a-z]/ig,'') ? 1 : -1;});
  for(var i=0;i<iconCategories.length;i++) {
    res.push ({text:translate(iconCategories[i]),value:iconCategories[i]});
    //console.log ('showCategorieOption fill cat filter '+iconCategories[i]);
  }
  console.log('showCategorieOption res len '+res.length);
  return res;
}

var getJSON = function (url, callback) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", url, true);
                xhr.onload = function () {
                    callback(JSON.parse(xhr.responseText));
                };
                xhr.send();
};

function initMe(editor) {
  console.log ('initMe nun jsonPath '+jsonPath);

// initilisierungsRoutinen bevor der Manager aufgerufn wird
// json von Url lesen uebergibt den geparsten Text an callback

  console.log ('initMe vor windowManager open');

  mce_dialogApi = editor.windowManager.open(getdialogConfig());
  console.log('initMe nach windowmanager open');
  mce_dialogApi.block("Loading...");
console.log('initMe nach windowmanager block');


  getJSON(jsonPath+'aweicons_5.json', function (data) {
    generateIconlist(data);
    console.log ('initMe getJSON vor options '+jsonPath);
    CategorieOptions = createCategorieOptions();
    console.log('PBD initMe getJSON vor createFullIconHtml iconCategories len '+iconCategories.length);
    FullIconHtml=createFullIconHtml();          // html aller icons
    console.log ('initMe len FullIconHtml '+FullIconHtml.length);
    console.log ('initMe vor redial '+jsonPath);
    mce_dialogApi.redial(getdialogConfig());
    mce_dialogApi.unblock();
  });
  
  console.log('initMe vor Listener');
  initListener();
  //mce_dialogApi.setEnabled('submitButton', false);
/*
  editor.ui.registry.addIcon('syncIcon', '<svg class="icon"><use xlink:href="custom-icons.svg#bubbles4"></use></svg>');

  if (tinymce.activeEditor.schema.isValidChild('i', 'class'))
   alert('class is valid child of i.');
  
  if (tinymce.activeEditor.schema.getElementRule('i')) console.log('i is a valid element.');
  
  //tinymce.activeEditor.schema.addValidElements("i[class|contenteditable|style]");
  tinymce.activeEditor.schema.setValidElements("i[class|contenteditable|style]");
  //tinymce.activeEditor.schema.addValidElements("i[*]");
  if (tinymce.activeEditor.schema.isValidChild('i', 'style')) console.log('class is valid child of i.');
  else  console.log('class is not valid child of i.');
*/
}
    // Include plugin CSS
    editor.on('init', function() {
        var csslink = editor.dom.create('link', {
            rel: 'stylesheet',
            href: url + '/css/mce-panel.css'
        });
        document.getElementsByTagName('head')[0].appendChild(csslink);
       
        var falink = editor.dom.create('link', {
            rel: 'stylesheet',
            href: '/'+font_awesome_path
        });
       document.getElementsByTagName('head')[0].appendChild(falink);
console.log("pbd editor.on linkcss "+url + '/css/mce-panel.css '+'/'+font_awesome_path);
    });

  /* Add a button that opens a window */
  editor.ui.registry.addButton('fontawesome', {
        //icon: 'flag',
        icon: 'image',
        text: translate('Icons'),
        tooltip: translate('Icons'),
        onAction: function () { initMe(editor);} 
  });


    editor.ui.registry.addMenuItem('fontawesome', {
        icon: 'image',
        text: translate('Icons'),
        tooltip: translate('Icons'),
        onclick: function () { initMe(editor);}, 
        onAction: 'insert'
    });

});  // ende tinymce.PluginManager.add
tinymce.PluginManager.requireLangPack('fontawesome');


