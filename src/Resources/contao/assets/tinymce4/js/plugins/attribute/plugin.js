// Created by Peter Broghammer
// pb-contao@gmx.de
// 
tinymce.PluginManager.requireLangPack('attribute');
tinymce.PluginManager.add('attribute', function(editor, url) {
    //console.log('plugin attribute');
    var majorVersion=tinymce.majorVersion;
    var minorVersion=tinymce.minorVersion
console.log('tinyversion attribute plugin: '+majorVersion+'.'+minorVersion);
    var translate = tinymce.util.I18n.translate;
    ///var attribute_path = editor.getParam('attribute_path');   evtl. Variable aus template holen
    //console.log('attribute_path:'+attribute_path);
    var getVal = function (t) {
      var val = prompt(t, "");
      return val;
    }
    var setNodeAttri = function (attri,val) {
        var node =   tinymce.activeEditor.selection.getNode();
//console.log('setNodeAttri node: ',node);
        var a = node.attributes.length;
//console.log('setNodeAttri node length: ',a);
        if (!val) {
          //node.setAttribute(attri,"");
          //alert ("Attribut geloescht: " + node.nodeName + " " + attri );
          node.removeAttribute(attri);
          return;
        }
        var cl =  node.getAttribute(attri);
        if (cl) { 
          node.setAttribute(attri,"");
          node.setAttribute(attri,cl + " " + val); 
        }
        else { node.setAttribute(attri,val); }
//        var cl =  node.getAttribute(attri);
//        console.log ("gesetzt: " + node.nodeName + " " + attri + "='" + cl + "'");
    };

//  WindowManager Configuration files
//  -------------------------------------------------------

    var attributeConfig={
       title: 'Attribute Dialog',
       tooltip: translate('set/add/remove attributes. if the attribute value is empty, the attribute is deleted.'),
       body: {
          type: 'panel',
          items: [
            { type: 'htmlpanel', html: '<div>erklaerung</div>'},  // wird dyn. ueberschrieben
            { type: 'htmlpanel', html: '<div>attribute</div>'},   // wird dyn. ueberschrieben
            { type: 'input',name: 'Name',label: 'Attribut Name',placeholder: translate('Attribute name')},
            { type: 'input',name: 'Wert',label: 'Attribut Wert',placeholder: translate('value of the attribute. empty=remove')}
          ]
       },
       buttons: [
         { type: 'cancel', name: 'closeButton',text: translate('Cancel')},
         { type: 'submit', name: 'submitButton',text: translate('Ok'),primary: true}
      ],
      /*
      initialData: {
        Name: '',
        Wert: ''
      },
      */
      onSubmit: function (api) {
        var data = api.getData();
        var name = data.Name;
        var value = data.Wert;
        console.log('Name: '+name+' Value: '+value);
        setNodeAttri(name,value);
        api.close();
      }
    };
    var classConfig={
       title: 'Class Dialog',
       tooltip: 'set/add/remove Classes. wenn Classwert existiert wird er geloescht.',
       body: {
          type: 'panel',
          items: [
            { type: 'htmlpanel', html: '<div>erklaerung</div>'},  // wird dyn. ueberschrieben
            { type: 'htmlpanel', html: '<div>attribute</div>'},   // wird dyn. ueberschrieben
            { type: 'input',name: 'Name',label: 'Class Name',placeholder: 'Name class remove wenn exist'},
          ]
       },
       buttons: [
         { type: 'cancel', name: 'closeButton',text: translate('Cancel')},
         { type: 'submit', name: 'submitButton',text: translate('Ok'),primary: true}
      ],
      onSubmit: function (api) {
        var data = api.getData();
        var name = data.Name.toLowerCase().trim();
        var value = data.Wert;

        console.log('Name: '+name);
        var removeMe=false;
        var classnames='';
        var node = tinymce.activeEditor.selection.getNode() ;
        for (let i = 0; i < node.attributes.length; i++) {
          if (nodeName=node.attributes.item(i).nodeName.toLowerCase().trim()=='class') {
            const clArray = node.attributes.item(i).nodeValue.split(" ");
            for (let j = 0;j < clArray.length; j++){
              if(clArray[j].toLowerCase().trim() == name) {
                removeMe=true;
                continue;  // loeschen
              }
              if (classnames.length==0) classnames += clArray[j].toLowerCase().trim();
              else classnames += ' '+clArray[j].toLowerCase().trim();
            }  
          }
        }        
        console.log('classnames: '+classnames);
        if (removeMe) { 
          setNodeAttri('class','');                         // zuerst mal alle löschen
          setNodeAttri('class',classnames);
        } else {
console.log('set '+name);
          setNodeAttri('class',name);
        }
        api.close();
      }
    };        //  ende class config
    var styleConfig={
       title: 'Style Dialog',
       tooltip: 'set/add/remove Styles. wenn Style existiert wird er geloescht.',
       body: {
          type: 'panel',
          items: [
            { type: 'htmlpanel', html: '<div>erklaerung</div>'},  // wird dyn. ueberschrieben
            { type: 'htmlpanel', html: '<div>attribute</div>'},   // wird dyn. ueberschrieben
            { type: 'input',name: 'Name',label: 'Style Name',placeholder: 'Style remove wenn exist'},
            { type: 'input',name: 'Wert',label: 'Style Wert',placeholder: 'Wert des Style.'}
          ]
       },
       buttons: [
         { type: 'cancel', name: 'closeButton',text: translate('Cancel')},
         { type: 'submit', name: 'submitButton',text: translate('Ok'),primary: true}
      ],
      onSubmit: function (api) {
        //value=getVal("Style ??"); 
        //setNodeAttri('style',value);
        //      setNodeAttri('data-mce-style',value);
        var data = api.getData();
        var name = data.Name.toLowerCase().trim();
        var value = data.Wert;
        console.log('Name: '+name+' Value: '+value);
        var removeMe=false;
        var stylenames='';
        var node = tinymce.activeEditor.selection.getNode() ;
        for (let i = 0; i < node.attributes.length; i++) {
          if (nodeName=node.attributes.item(i).nodeName.toLowerCase().trim()=='style') {
//console.log('action style i',i,node.attributes.item(i).nodeValue)
            const clArray = node.attributes.item(i).nodeValue.split(";");
            for (let j = 0;j < clArray.length; j++){
              if (clArray[j]) {
                const styleArray = clArray[j].split(":");
//console.log('action style item[0]',styleArray[0],':[1]',styleArray[1]);
                if (styleArray[0].toLowerCase().trim()==name) {
                  removeMe=true;
//console.log('skip',name)
                  continue;  // loeschen
                }
//console.log('add',styleArray[0].toLowerCase().trim(),styleArray[1]);
                stylenames += styleArray[0].toLowerCase().trim()+':'+styleArray[1]+';';
              } else {
              console.log('item',i,'empty');
              }
            }  
          }
        }        

        setNodeAttri('style','');                         // zuerst mal alle löschen
        setNodeAttri('data-mce-style','');
        if (removeMe) { 
          console.log('styles: submit '+stylenames + ' removeMe '+removeMe);
          setNodeAttri('style',stylenames);
          setNodeAttri('data-mce-style',stylenames);
        } else {
          stylenames += name+':'+value+';'
          console.log('styles: submit '+stylenames + ' removeMe '+removeMe);
          setNodeAttri('style',stylenames);
          setNodeAttri('data-mce-style',stylenames);
        }

        api.close();
      }
    };            // ende styleConfig
    var onclickConfig={
       title: 'onclick Dialog',
       tooltip: 'add/remove onklick. wenn onclick existiert wird er geloescht.',
       body: {
          type: 'panel',
          items: [
            { type: 'htmlpanel', html: '<div>erklaerung</div>'},  // wird dyn. ueberschrieben
            { type: 'htmlpanel', html: '<div>attribute</div>'},   // wird dyn. ueberschrieben
            { type: 'input',name: 'Script',label: 'js script',placeholder: 'Javascript'}
          ]
       },
       buttons: [
         { type: 'cancel', name: 'closeButton',text: translate('Cancel')},
         { type: 'submit', name: 'submitButton',text: translate('Ok'),primary: true}
      ],
      onSubmit: function (api) {
        //value=getVal("Style ??"); 
        //setNodeAttri('style',value);
        //      setNodeAttri('data-mce-style',value);
        var data = api.getData();
        var script = data.Script.toLowerCase().trim();
        console.log('script: ',script);
        setNodeAttri('onclick','');                         // zuerst mal alle löschen
        if (script) {
          if (script.substr(-1)==';') setNodeAttri('onclick',script);
          else setNodeAttri('onclick',script+';');
        }
        api.close();
      }
    };            // ende onclickConfig
    
//  ende WindowManager Configuration files
//  -------------------------------------------------------
    
    editor.ui.registry.addMenuButton('Attribute', {
      text: 'Attribute',
      icon: 'code-sample',
      tooltip: 'Attribute eintragen',    
      fetch: function (callback) {
        var items = [
          {
            type: 'menuitem',
            text: translate('edit attribut'),
            onAction: function() {
              // Open Attribute Dialog
              var node = tinymce.activeEditor.selection.getNode() ;
              var text=translate('Inserting attributes allowed? (settings) tag')+': &lt;'+node.tagName+'&gt;';
              attributeConfig.body.items[0]={ type: 'htmlpanel', html: text};
              var htmltext = 'Vorhandene Attribute:';
              var anzattries = node.attributes.length;
console.log('main anz attributes length: ',anzattries);
              for (let i = 0; i < node.attributes.length; i++) {
                htmltext+='<br>'+node.attributes.item(i).nodeName+'='+node.attributes.item(i).nodeValue;
              } 
              attributeConfig.body.items[1]={ type: 'htmlpanel', html: htmltext};
              editor.windowManager.open(attributeConfig);
              //console.log('nach winopen');
            }
          },
          {
            type: 'menuitem',
            text: translate('edit class'),
            onAction: function() {
              // Open Class Dialog
              var node = tinymce.activeEditor.selection.getNode() ;
console.log('mainnode: ',node,'tag ',node.tagName);
              var text=translate('Inserting classes allowed? (settings) tag')+': &lt;'+node.tagName+'&gt;';
              classConfig.body.items[0]={ type: 'htmlpanel', html: text};
              var htmltext = 'Vorhandene Class:';
              var anzattries = node.attributes.length;
console.log('main anz class length: ',anzattries);
              for (let i = 0; i < node.attributes.length; i++) {
                if (nodeName=node.attributes.item(i).nodeName.toLowerCase().trim()=='class') {
                  const clArray = node.attributes.item(i).nodeValue.split(" ");
                  for (let j = 0;j < clArray.length; j++){
                    htmltext+='<br>'+clArray[j];
                  }
                }
              } 
              classConfig.body.items[1]={ type: 'htmlpanel', html: htmltext};
              editor.windowManager.open(classConfig);
              //console.log('nach winopen');
            }
          },
          {
            type: 'menuitem',
            text: translate('edit style'),
            onAction: function() {
              // Open Style Dialog
              var node = tinymce.activeEditor.selection.getNode() ;
console.log('mainnode: ',node,'tag ',node.tagName);
              var text=translate('Inserting styles allowed? (settings) tag')+': &lt;'+node.tagName+'&gt;';
              styleConfig.body.items[0]={ type: 'htmlpanel', html: text};
              var htmltext = 'Vorhandene Styles:';
              var anzattries = node.attributes.length;
              for (let i = 0; i < node.attributes.length; i++) {
                if (nodeName=node.attributes.item(i).nodeName.toLowerCase().trim()=='style') {
                  const clArray = node.attributes.item(i).nodeValue.split(";");
console.log('main substyles anzahl styles '+clArray.length);
                  for (let j = 0;j < clArray.length; j++){
console.log('main substyles  '+clArray.length);
                    htmltext+='<br>'+clArray[j];
                  }
                }
              } 
              styleConfig.body.items[1]={ type: 'htmlpanel', html: htmltext};
              editor.windowManager.open(styleConfig);
              //console.log('nach winopen');
            }
          },
          {
            type: 'menuitem',
            text: translate('edit onclick'),
            onAction: function() {
              // Open Style Dialog
              var node = tinymce.activeEditor.selection.getNode() ;
console.log('mainnode: ',node,'tag ',node.tagName);
              var text=translate('Inserting styles allowed? (settings) tag')+': &lt;'+node.tagName+'&gt;';
              onclickConfig.body.items[0]={ type: 'htmlpanel', html: text};
              var htmltext = 'Vorhandene Styles:';
              var anzattries = node.attributes.length;
              for (let i = 0; i < node.attributes.length; i++) {
                if (nodeName=node.attributes.item(i).nodeName.toLowerCase().trim()=='onclick') {
                  const clArray = node.attributes.item(i).nodeValue.split(";");
console.log('main substyles anzahl styles '+clArray.length);
                  for (let j = 0;j < clArray.length; j++){
console.log('main substyles  '+clArray.length);
                    htmltext+='<br>'+clArray[j];
                  }
                }
              } 
              onclickConfig.body.items[1]={ type: 'htmlpanel', html: htmltext};
              editor.windowManager.open(onclickConfig);
            }
          }
        ];
        callback(items);
      }
    });

    // Include plugin CSS
});
